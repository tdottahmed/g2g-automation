<?php

namespace App\Jobs;

use App\Models\OfferTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PostOfferTemplate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected OfferTemplate $template;

    /**
     * Create a new job instance.
     */
    public function __construct(OfferTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Prepare media data
            $mediaData = [];
            if ($this->template->medias) {
                $medias = is_string($this->template->medias)
                    ? json_decode($this->template->medias, true)
                    : $this->template->medias;

                if (json_last_error() === JSON_ERROR_NONE && is_array($medias)) {
                    foreach ($medias as $media) {
                        $mediaData[] = [
                            'title' => $media['title'] ?? 'Media',
                            'Link'  => $media['link'] ?? '',
                        ];
                    }
                } else {
                    Log::error('Invalid media data format', [
                        'template_id' => $this->template->id,
                        'medias'      => $this->template->medias,
                    ]);
                }
            }

            // Generate cookie filename from email (first part before @)
            $emailParts  = explode('@', $this->template->userAccount->email);
            $emailPrefix = $emailParts[0];
            $cookieFile  = base_path($emailPrefix . '.json');

            $deliveryMethod = is_string($this->template->delivery_method)
                ? json_decode($this->template->delivery_method, true)
                : $this->template->delivery_method;

            // Prepare input data for Node.js script
            $inputData = [
                'Title'                     => $this->template->title,
                'Description'               => $this->template->description,
                'Town Hall Level'           => $this->template->th_level,
                'King Level'                => $this->template->king_level,
                'Queen Level'               => $this->template->queen_level,
                'Warden Level'              => $this->template->warden_level,
                'Champion Level'            => $this->template->champion_level,
                'Default price (unit)'      => (string) $this->template->price,
                'Minimum purchase quantity' => $this->template->min_purchase_quantity,
                'Instant delivery'          => $this->template->instant_delivery ? 1 : 0,
                'mediaData'                 => $mediaData,
                'user_email'                => $this->template->userAccount->email,
                'password'                  => Crypt::decryptString($this->template->userAccount->password),
                'cookies'                   => $cookieFile,
                'user_id'                   => $this->template->userAccount->id,
                'Delivery hour'             => $deliveryMethod['speed_hour'] ?? "0",
                'Delivery minute'           => $deliveryMethod['speed_min'] ?? "30",
            ];

            $process = new Process([
                'node',
                base_path('scripts/automation/post-offers.js'),
                base64_encode(json_encode($inputData)),
            ]);

            $process->setWorkingDirectory(base_path('scripts/automation'));
            $process->setTimeout(null); // no timeout

            $process->run(function ($type, $buffer) {
                if ($type === Process::ERR) {
                    Log::error('NODE_ERR: ' . $buffer);
                } else {
                    Log::info('NODE_OUT: ' . $buffer);
                }
            });

            if ($process->isSuccessful()) {
                // Update last_posted_at timestamp
                $this->template->update(['last_posted_at' => now()]);

                // Decrement offers_to_generate if set
                if ($this->template->offers_to_generate && $this->template->offers_to_generate > 0) {
                    $this->template->decrement('offers_to_generate');

                    // Optionally deactivate template if no more offers to generate
                    if ($this->template->fresh()->offers_to_generate <= 0) {
                        $this->template->update(['is_active' => false]);
                        Log::info('Template deactivated - all offers generated', [
                            'template_id' => $this->template->id,
                        ]);
                    }
                }

                Log::info('✅ Node script executed successfully', [
                    'template_id' => $this->template->id,
                    'output'      => $process->getOutput(),
                    'remaining_offers' => $this->template->fresh()->offers_to_generate ?? 'unlimited',
                ]);
            } else {
                Log::error("❌ Failed to process template", [
                    'template_id' => $this->template->id,
                    'error'       => $process->getErrorOutput(),
                    'output'      => $process->getOutput(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception processing template", [
                'template_id' => $this->template->id,
                'exception'   => $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\OfferTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class RunOfferAutomation extends Command
{
    protected $signature = 'offer:automation';
    protected $description = 'Run offer posting automation';

    public function handle(): void
    {
        $rateLimit = cache()->get('offer_automation_rate_limit', 3);

        $templates = OfferTemplate::with('userAccount')
            ->where('is_active', 1)
            ->orderBy('last_posted_at')
            ->get();

        foreach ($templates as $index => $template) {
            if (!$template->userAccount) {
                $this->error("No user account found for template ID: {$template->id}");
                continue;
            }

            try {
                // Prepare media data
                $mediaData = [];
                if ($template->medias) {
                    $medias = is_string($template->medias) ? json_decode($template->medias, true) : $template->medias;

                    if (json_last_error() === JSON_ERROR_NONE && is_array($medias)) {
                        foreach ($medias as $media) {
                            $mediaData[] = [
                                'title' => $media['title'] ?? 'Media',
                                'Link' => $media['link'] ?? '',
                            ];
                        }
                    } else {
                        $this->error("Invalid media data for template ID: {$template->id}");
                        \Log::error('Invalid media data format', [
                            'template_id' => $template->id,
                            'medias' => $template->medias
                        ]);
                    }
                }

                // Generate cookie filename from email (first part before @)
                $emailParts = explode('@', $template->userAccount->email);
                $emailPrefix = $emailParts[0];
                $cookieFile = base_path($emailPrefix . '.json');
                $deliveryMethod = is_string($template->delivery_method)
                    ? json_decode($template->delivery_method, true)
                    : $template->delivery_method;

                // Prepare input data for Node.js script
                $inputData = [
                    'Title' => $template->title,
                    'Description' => $template->description,
                    'Town Hall Level' => $template->th_level,
                    'King Level' => $template->king_level,
                    'Queen Level' => $template->queen_level,
                    'Warden Level' => $template->warden_level,
                    'Champion Level' => $template->champion_level,
                    'Default price (unit)' => (string) $template->price,
                    'Minimum purchase quantity' => $template->min_purchase_quantity,
                    'Instant delivery' => $template->instant_delivery ? 1 : 0,
                    'mediaData' => $mediaData,
                    'user_email' => $template->userAccount->email,
                    'password' => Crypt::decryptString($template->userAccount->password),
                    'cookies' => $cookieFile,
                    'user_id' => $template->userAccount->id,
                    'Delivery hour' => $deliveryMethod['speed_hour'] ?? "0",
                    'Delivery minute' => $deliveryMethod['speed_min'] ?? "30"
                ];

                $this->info("Processing template: {$template->title}");
                $this->info("Cookie file: {$cookieFile}");
                $this->info("Media data count: " . count($mediaData));

                $process = new \Symfony\Component\Process\Process([
                    'node',
                    base_path('scripts/automation/post-offers.js'),
                    base64_encode(json_encode($inputData))
                ]);

                $process->setWorkingDirectory(base_path('scripts/automation'));
                $process->setTimeout(null); // no timeout (run as long as needed)

                $process->run(function ($type, $buffer) {
                    if (\Symfony\Component\Process\Process::ERR === $type) {
                        $this->error('NODE_ERR: ' . $buffer);
                    } else {
                        $this->info('NODE_OUT: ' . $buffer);
                    }
                });

                if ($process->isSuccessful()) {
                    $template->update(['last_posted_at' => now()]);
                    $this->info("✅ Successfully processed template: {$template->title}");

                    \Log::info('Node script executed successfully', [
                        'template_id' => $template->id,
                        'output' => $process->getOutput()
                    ]);
                } else {
                    $this->error("❌ Failed to process template: {$template->title}");

                    \Log::error('Node script failed', [
                        'template_id' => $template->id,
                        'error' => $process->getErrorOutput(),
                        'output' => $process->getOutput(),
                    ]);
                }

                // Rate limiting
                if ($index < count($templates) - 1) {
                    $delay = intval(60 / $rateLimit);
                    $this->info("Waiting {$delay} seconds before next template...");
                    sleep($delay);
                }
            } catch (\Exception $e) {
                $this->error("Exception processing template {$template->id}: " . $e->getMessage());
                \Log::error('Automation exception', [
                    'template_id' => $template->id,
                    'exception' => $e->getMessage()
                ]);
            }
        }

        $this->info('Automation completed.');
    }
}

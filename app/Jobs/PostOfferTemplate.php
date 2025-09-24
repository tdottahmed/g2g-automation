<?php

namespace App\Jobs;

use App\Models\OfferTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PostOfferTemplate implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    protected OfferTemplate $template;

    public function __construct(OfferTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = json_encode([
            'Title' => $this->template->title,
            'Description' => $this->template->description,
            'Town Hall Level' => $this->template->th_level,
            'King Level' => $this->template->king_level,
            'Queen Level' => $this->template->queen_level,
            'Warden Level' => $this->template->warden_level,
            'Champion Level' => $this->template->champion_level,
            'Default price (unit)' => $this->template->price,
            'Minimum purchase quantity' => $this->template->min_purchase_quantity,
            'Media gallery' => $this->template->medias,
            'Instant delivery' => $this->template->instant_delivery
        ]);


        
        try {
            $escapedData = escapeshellarg($data);

            $scriptPath = base_path('scripts/automation/post-offers.js');
            $process = new Process(['node', $scriptPath, $escapedData]);

            // Set timeout (optional)
            $process->setTimeout(60); // 60 seconds

            // Run process
            $process->run();

            if (!$process->isSuccessful()) {
                // Log error
                logger()->error('Node script failed: ' . $process->getErrorOutput());
                throw new ProcessFailedException($process);
            }

            logger()->info('Node script executed successfully: ' . $process->getOutput());
        } catch (\Throwable $e) {
            logger()->error('Exception while running Node script: ' . $e->getMessage());
        }
    }
}

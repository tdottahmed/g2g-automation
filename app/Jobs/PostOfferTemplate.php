<?php

namespace App\Jobs;

use App\Models\OfferTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PostOfferTemplate implements ShouldQueue
{
    use Dispatchable, Queueable;

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
        // Prepare data from database
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

        $scriptPath = base_path('scripts/automation/post-offers.js');

        try {
            // Pass JSON directly as argument, no escapeshellarg
            $process = new Process([
                'node',
                $scriptPath,
                $data
            ]);

            // Optional: set working directory if needed
            $process->setWorkingDirectory(base_path('scripts/automation'));

            // Optional: set timeout
            $process->setTimeout(60);

            // Run the Node script
            $process->run();

            // Check for errors
            if (!$process->isSuccessful()) {
                logger()->error('Node script failed', [
                    'error' => $process->getErrorOutput(),
                    'output' => $process->getOutput(),
                ]);
                throw new ProcessFailedException($process);
            }

            // Log success
            logger()->info('Node script executed successfully', [
                'output' => $process->getOutput(),
            ]);
        } catch (\Throwable $e) {
            logger()->error('Exception while running Node script', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\PostOfferTemplate;
use App\Models\OfferTemplate;
use Illuminate\Console\Command;

class RunOfferAutomation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offer:automation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run offer posting automation';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rateLimit = cache()->get('offer_automation_rate_limit', 3);

        $templates = OfferTemplate::where('is_active', true)
            ->orderBy('last_posted_at')
            ->get();

        foreach ($templates as $index => $template) {
            PostOfferTemplate::dispatch($template);
            // ->delay(now()->addSeconds(intval(60 / $rateLimit) * $index));

            $template->update(['last_posted_at' => now()]);
        }

        $this->info('Automation dispatched successfully.');
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\PostOfferTemplate;
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
            // need here to run the job
            dispatch(new PostOfferTemplate($template));
            $this->info("Dispatched job for template ID: {$template->title}");
        }
    }
}

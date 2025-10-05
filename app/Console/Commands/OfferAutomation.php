<?php

namespace App\Console\Commands;

use App\Jobs\PostOfferTemplate;
use App\Models\OfferTemplate;
use App\Models\UserAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OfferAutomation extends Command
{
    protected $signature = 'offer:automation
                            {--user_account_id= : Process templates for specific user account}
                            {--all : Process all active templates}';

    protected $description = 'Run offer posting automation without scheduling';

    public function handle(): void
    {
        $userAccountId = $this->option('user_account_id');
        $allUsers = $this->option('all');

        $this->info('🚀 Starting offer automation...');

        // Validate input
        if (!$userAccountId && !$allUsers) {
            $this->error('❌ You must provide either --user_account_id or --all option.');
            Command::FAILURE;
        }

        try {
            if ($userAccountId) {
                $this->processUser($userAccountId);
            } elseif ($allUsers) {
                $this->processAll();
            }
        } catch (\Exception $e) {
            $this->error("❌ Unexpected error: " . $e->getMessage());
            Log::error('Offer automation failed', [
                'user_account_id' => $userAccountId,
                'all_users' => $allUsers,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Command::FAILURE;
        }

        Command::SUCCESS;
    }

    protected function processUser($userAccountId)
    {
        $user = UserAccount::find($userAccountId);

        if (!$user) {
            $this->error("❌ User not found: {$userAccountId}");
            Command::FAILURE;
        }

        $this->info("👤 Processing user: {$user->email}");

        // Get only template IDs instead of full models
        $templateIds = OfferTemplate::where('user_account_id', $userAccountId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (empty($templateIds)) {
            $this->warn("ℹ️ No active templates for this user.");
            return Command::SUCCESS;
        }

        $this->info("📋 Found " . count($templateIds) . " active template(s)");

        // Pass IDs instead of full models
        dispatch(new PostOfferTemplate($templateIds, $userAccountId));

        $this->info("✅ Dispatched " . count($templateIds) . " templates for {$user->email}");

        Log::info('User automation completed', [
            'user_account_id' => $userAccountId,
            'user_email' => $user->email,
            'template_ids_count' => count($templateIds),
        ]);

        return Command::SUCCESS;
    }

    protected function processAll()
    {
        $this->info("🌍 Processing all active templates for all users...");

        // Get template IDs grouped by user
        $templateGroups = OfferTemplate::where('is_active', true)
            ->get(['id', 'user_account_id'])
            ->groupBy('user_account_id')
            ->map(function ($group) {
                return $group->pluck('id')->toArray();
            });

        if ($templateGroups->isEmpty()) {
            $this->warn("❌ No active templates found.");
            return Command::SUCCESS;
        }

        $this->info("📋 Found templates across {$templateGroups->count()} users");

        $dispatchedJobs = 0;
        $dispatchedTemplates = 0;

        foreach ($templateGroups as $userId => $templateIds) {
            $user = UserAccount::find($userId);
            $this->info("👤 User: " . ($user->email ?? 'Unknown'));

            // Pass IDs instead of full models
            dispatch(new PostOfferTemplate($templateIds, $userId));
            $dispatchedJobs++;
            $dispatchedTemplates += count($templateIds);

            $this->info("   📦 Dispatched " . count($templateIds) . " templates");
        }

        $this->info("✅ All active templates dispatched for all users.");
        $this->info("📊 Summary: {$dispatchedTemplates} templates across {$dispatchedJobs} users");

        Log::info('All-users automation completed', [
            'total_templates' => $dispatchedTemplates,
            'total_users' => $templateGroups->count(),
            'dispatched_jobs' => $dispatchedJobs,
        ]);

        return Command::SUCCESS;
    }
}

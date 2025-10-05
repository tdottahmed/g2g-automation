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

        // Process all users if --all flag is not set
        if ($allUsers != true) {
            $this->processUser($userAccountId);
        }
        $this->processAll();
    }

    /**
     * 🔹 Process offers for a specific user
     */
    protected function processUser($userAccountId)
    {
        $user = UserAccount::find($userAccountId);

        if (!$user) {
            return $this->error("❌ User not found: {$userAccountId}");
        }

        $this->info("👤 Processing user: {$user->email}");

        $templates = OfferTemplate::where('user_account_id', $userAccountId)
            ->where('is_active', true)
            ->get();

        if ($templates->isEmpty()) {
            return $this->warn("ℹ️ No active templates for this user.");
        }

        dispatch(new PostOfferTemplate($templates->all()));

        $this->info("✅ Dispatched {$templates->count()} templates for {$user->email}");

        Log::info('User automation completed', [
            'user_account_id' => $userAccountId,
            'templates_count' => $templates->count(),
        ]);
    }

    /**
     * 🔹 Process all templates for all users
     */
    protected function processAll()
    {
        $this->info("🌍 Processing all active templates for all users...");

        $templates = OfferTemplate::where('is_active', true)->get();

        if ($templates->isEmpty()) {
            return $this->warn("❌ No active templates found.");
        }

        $grouped = $templates->groupBy('user_account_id');

        foreach ($grouped as $userId => $userTemplates) {
            $user = $userTemplates->first()->userAccount;
            $this->info("👤 User: " . ($user->email ?? 'Unknown'));
            dispatch(new PostOfferTemplate($userTemplates->all()));
            $this->info("   📦 Dispatched {$userTemplates->count()} templates");
        }

        $this->info("✅ All active templates dispatched for all users.");

        Log::info('All-users automation completed', [
            'total_templates' => $templates->count(),
            'total_users' => $grouped->count(),
        ]);
    }
}

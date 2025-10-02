<?php
// app/Services/AutomationService.php

namespace App\Services;

use App\Jobs\PostOfferTemplate;
use App\Jobs\ProcessOfferTemplate;
use App\Models\UserAccount;
use App\Models\OfferTemplate;
use App\Models\AutomationSession;
use App\Models\AutomationProgress;
use App\Models\OfferAutomationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutomationService
{
    public function startAutomation($userAccountId, $templateIds = null)
    {
        return DB::transaction(function () use ($userAccountId, $templateIds) {
            // Get active templates for this user
            $templates = OfferTemplate::where('user_account_id', $userAccountId)
                ->where('is_active', true);

            if ($templateIds) {
                $templates = $templates->whereIn('id', $templateIds);
            }

            $templates = $templates->get();

            if ($templates->isEmpty()) {
                throw new \Exception('No active templates found for this account.');
            }

            // Dispatch single job for all templates of this user
            ProcessOfferTemplate::dispatch($userAccountId, $templates->pluck('id')->toArray());

            return [
                'user_account_id' => $userAccountId,
                'templates_count' => $templates->count(),
                'status' => 'dispatched'
            ];
        });
    }

    public function stopAutomation($userAccountId)
    {
        return DB::transaction(function () use ($userAccountId) {
            // Get current running session
            $session = AutomationSession::where('user_account_id', $userAccountId)
                ->where('status', 'running')
                ->first();

            if ($session) {
                $session->update([
                    'status' => 'stopped',
                    'completed_at' => now(),
                ]);

                // Update progress records
                AutomationProgress::where('automation_session_id', $session->id)
                    ->where('status', 'processing')
                    ->update([
                        'status' => 'failed',
                        'error_message' => 'Automation stopped by user',
                        'completed_at' => now(),
                    ]);
            }

            return $session;
        });
    }

    public function getAutomationStatus($userAccountId)
    {
        $currentSession = AutomationSession::where('user_account_id', $userAccountId)
            ->where('status', 'running')
            ->first();

        if (!$currentSession) {
            return ['is_running' => false];
        }

        $progress = AutomationProgress::where('automation_session_id', $currentSession->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'is_running' => true,
            'session' => $currentSession,
            'progress' => $progress,
            'total_templates' => $currentSession->total_templates,
            'processed' => $currentSession->processed_templates,
            'successful' => $currentSession->successful_posts,
            'failed' => $currentSession->failed_posts,
            'progress_percentage' => $currentSession->total_templates > 0
                ? round(($currentSession->processed_templates / $currentSession->total_templates) * 100, 2)
                : 0,
        ];
    }

    public function getRealTimeProgress($sessionId)
    {
        $session = AutomationSession::with(['progress.template'])->find($sessionId);

        if (!$session) {
            return null;
        }

        $currentProcessing = AutomationProgress::with('template')
            ->where('automation_session_id', $sessionId)
            ->where('status', 'processing')
            ->first();

        // Get recent logs for this session
        $recentLogs = OfferAutomationLog::where('automation_session_id', $sessionId)
            ->latest()
            ->limit(10)
            ->get();

        return [
            'session' => $session,
            'current_processing' => $currentProcessing,
            'recent_logs' => $recentLogs,
        ];
    }

    public function getUserAccountsWithStatus()
    {
        return UserAccount::withCount(['templates' => function ($query) {
            $query->where('is_active', true);
        }])->get()->map(function ($account) {
            $status = $this->getAutomationStatus($account->id);
            $account->automation_status = $status;
            return $account;
        });
    }
}

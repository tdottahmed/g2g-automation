<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use App\Models\UserAccount;

class OfferAutomationController extends Controller
{
    public function dashboard()
    {
        $intervalMinutes = (int) (
            ApplicationSetup::where('type', 'schedule_interval_minutes')->value('value') ?? 15
        );

        $userAccounts = UserAccount::with(['offers' => function ($q) {
            $q->orderByDesc('is_active')->orderByDesc('last_posted_at');
        }])->withCount([
            'offers as total_templates',
            'offers as active_templates_count' => fn ($q) => $q->where('is_active', true),
            'offers as queue_delete_count'     => fn ($q) => $q->where('queue_delete', true),
        ])->get();

        $activeTemplates = OfferTemplate::where('is_active', true)->count();

        $pendingCount = OfferTemplate::where('is_active', true)
            ->where(function ($q) use ($intervalMinutes) {
                $q->where(function ($inner) {
                    $inner->whereNotNull('offers_to_generate')
                          ->where('offers_to_generate', '>', 0);
                })->orWhere(function ($inner) use ($intervalMinutes) {
                    $inner->whereNull('last_posted_at')
                          ->orWhere('last_posted_at', '<', now()->subMinutes($intervalMinutes));
                });
            })->count();

        $postedToday = OfferAutomationLog::where('status', 'success')
            ->whereDate('executed_at', today())
            ->count();

        $failedToday = OfferAutomationLog::where('status', 'failed')
            ->whereDate('executed_at', today())
            ->count();

        $recentLogs = OfferAutomationLog::with('template')
            ->latest('executed_at')
            ->limit(20)
            ->get();

        return view('admin.offer-automation.dashboard', compact(
            'userAccounts',
            'recentLogs',
            'activeTemplates',
            'pendingCount',
            'postedToday',
            'failedToday',
            'intervalMinutes'
        ));
    }

    public function getUserTemplates($userAccountId)
    {
        $templates = OfferTemplate::where('user_account_id', $userAccountId)
            ->select(['id', 'title', 'is_active', 'last_posted_at', 'created_at'])
            ->get();

        return response()->json($templates);
    }
}

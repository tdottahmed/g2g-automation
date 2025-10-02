<?php
// app/Http/Controllers/AutomationController.php

namespace App\Http\Controllers;

use App\Services\AutomationService;
use App\Models\UserAccount;
use App\Models\OfferTemplate;
use App\Models\AutomationSession;
use App\Models\OfferAutomationLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AutomationController extends Controller
{
    protected $automationService;

    public function __construct(AutomationService $automationService)
    {
        $this->automationService = $automationService;
    }

    /**
     * Main automation dashboard
     */
    public function dashboard(): View
    {
        $userAccounts = UserAccount::withCount(['templates' => function ($query) {
            $query->where('is_active', true);
        }])->get();

        // Get status for each account
        $accountsWithStatus = $userAccounts->map(function ($account) {
            $status = $this->automationService->getAutomationStatus($account->id);
            $account->automation_status = $status;
            return $account;
        });

        $recentSessions = AutomationSession::with('userAccount')
            ->latest()
            ->limit(5)
            ->get();

        $recentLogs = OfferAutomationLog::with(['template', 'automationSession'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.automation.dashboard', compact(
            'accountsWithStatus',
            'recentSessions',
            'recentLogs'
        ));
    }

    /**
     * Start automation for a user account
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'user_account_id' => 'required|exists:user_accounts,id',
            'template_ids' => 'sometimes|array'
        ]);

        // try {
            $session = $this->automationService->startAutomation(
                $request->user_account_id,
                $request->template_ids
            );
            

            return response()->json([
                'success' => true,
                'message' => 'Automation started successfully!',
                'session' => $session,
                'redirect' => route('automation.dashboard')
            ]);
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $e->getMessage(),
        //     ], 422);
        // }
    }

    /**
     * Stop automation for a user account
     */
    public function stop(Request $request): JsonResponse
    {
        $request->validate([
            'user_account_id' => 'required|exists:user_accounts,id',
        ]);

        try {
            $session = $this->automationService->stopAutomation($request->user_account_id);

            return response()->json([
                'success' => true,
                'message' => 'Automation stopped successfully!',
                'session' => $session,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get automation status for a user account
     */
    public function status($userAccountId): JsonResponse
    {
        try {
            $status = $this->automationService->getAutomationStatus($userAccountId);

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get real-time progress for a session
     */
    public function progress($sessionId): JsonResponse
    {
        try {
            $progress = $this->automationService->getRealTimeProgress($sessionId);

            return response()->json([
                'success' => true,
                'data' => $progress,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Automation logs page
     */
    public function logs(Request $request): View
    {
        $query = OfferAutomationLog::with(['template', 'automationSession.userAccount'])
            ->latest();

        // Filter by status
        if ($request->has('status') && in_array($request->status, ['success', 'failed'])) {
            $query->where('status', $request->status);
        }

        // Filter by user account
        if ($request->has('user_account_id')) {
            $query->whereHas('template', function ($q) use ($request) {
                $q->where('user_account_id', $request->user_account_id);
            });
        }

        // Filter by date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20);
        $userAccounts = UserAccount::all();

        return view('automation.logs', compact('logs', 'userAccounts'));
    }

    /**
     * Session details page
     */
    public function sessionDetails($sessionId): View
    {
        $session = AutomationSession::with([
            'userAccount',
            'progress.template',
            'logs.template'
        ])->findOrFail($sessionId);

        $progressStats = $session->progress()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('automation.session-details', compact('session', 'progressStats'));
    }

    /**
     * Get templates for a user account (for template selection)
     */
    public function getUserTemplates($userAccountId): JsonResponse
    {
        $templates = OfferTemplate::where('user_account_id', $userAccountId)
            ->where('is_active', true)
            ->get(['id', 'title', 'th_level', 'price']);

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }
}

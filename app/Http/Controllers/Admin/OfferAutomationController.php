<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferAutomationController extends Controller
{
    public function dashboard()
    {
        $intervalMinutes = (int) (
            ApplicationSetup::where('type', 'schedule_interval_minutes')->value('value') ?? 15
        );

        // Load counts only — templates are fetched lazily via AJAX per account
        $userAccounts = UserAccount::withCount([
            'offerTemplates as total_templates',
            'offerTemplates as permanent_templates_count' => fn ($q) => $q->where('is_permanent', true),
            'offerTemplates as queued_posts_count'        => fn ($q) => $q->where('offers_to_generate', '>', 0),
        ])->latest()->get();

        $totalTemplates   = OfferTemplate::count();
        $permanentCount   = OfferTemplate::where('is_permanent', true)->count();
        $queuedPostsCount = OfferTemplate::where('offers_to_generate', '>', 0)->count();

        $postedToday = OfferAutomationLog::where('status', 'success')
            ->whereDate('executed_at', today())
            ->count();

        $failedToday = OfferAutomationLog::where('status', 'failed')
            ->whereDate('executed_at', today())
            ->count();

        $recentLogs = OfferAutomationLog::with('template')
            ->latest('executed_at')
            ->limit(15)
            ->get();

        // Non-permanent template counts per game per account (for delete modal step 2)
        $accountGameCounts = DB::table('offer_template_user_account as pivot')
            ->join('offer_templates', 'offer_templates.id', '=', 'pivot.offer_template_id')
            ->where('offer_templates.is_permanent', false)
            ->select('pivot.user_account_id', 'offer_templates.game', DB::raw('count(*) as game_count'))
            ->groupBy('pivot.user_account_id', 'offer_templates.game')
            ->get()
            ->groupBy('user_account_id');

        return view('admin.offer-automation.dashboard', compact(
            'userAccounts',
            'recentLogs',
            'totalTemplates',
            'permanentCount',
            'queuedPostsCount',
            'postedToday',
            'failedToday',
            'intervalMinutes',
            'accountGameCounts'
        ));
    }

    /**
     * AJAX: return paginated templates for one account.
     * Supports ?search=, ?permanent= (permanent|non_permanent), ?page=
     */
    public function getUserTemplates(Request $request, UserAccount $userAccount)
    {
        $query = OfferTemplate::whereHas('userAccounts', fn ($q) => $q->where('user_accounts.id', $userAccount->id));

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->permanent === 'permanent') {
            $query->where('is_permanent', true);
        } elseif ($request->permanent === 'non_permanent') {
            $query->where('is_permanent', false);
        }

        $templates = $query
            ->select(['id', 'title', 'is_permanent', 'offers_to_generate', 'last_posted_at', 'price', 'game', 'game_data'])
            ->latest()
            ->paginate(25);

        return response()->json($templates);
    }
}

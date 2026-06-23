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
            'offerTemplates as queued_posts_count'        => fn ($q) => $q->where('offer_template_user_account.offers_to_generate', '>', 0),
        ])->latest()->get();

        $totalTemplates   = OfferTemplate::count();
        $permanentCount   = OfferTemplate::where('is_permanent', true)->count();
        $queuedPostsCount = DB::table('offer_template_user_account')->where('offers_to_generate', '>', 0)->count();

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

        // All template counts per game per account (for post-by-account modal step 2)
        $accountAllGameCounts = DB::table('offer_template_user_account as pivot')
            ->join('offer_templates', 'offer_templates.id', '=', 'pivot.offer_template_id')
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
            'accountGameCounts',
            'accountAllGameCounts'
        ));
    }

    /**
     * AJAX: return paginated templates for one account.
     * Supports ?search=, ?permanent= (permanent|non_permanent), ?page=
     */
    public function getUserTemplates(Request $request, UserAccount $userAccount)
    {
        $query = $userAccount->offerTemplates()->withPivot('offers_to_generate');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->permanent === 'permanent') {
            $query->where('is_permanent', true);
        } elseif ($request->permanent === 'non_permanent') {
            $query->where('is_permanent', false);
        }

        $paginated = $query
            ->select(['offer_templates.id', 'offer_templates.title', 'offer_templates.is_permanent',
                      'offer_templates.last_posted_at', 'offer_templates.price', 'offer_templates.game', 'offer_templates.game_data'])
            ->latest('offer_templates.id')
            ->paginate(25);

        // Expose pivot's offers_to_generate as a top-level field for the frontend
        $paginated->getCollection()->transform(function ($template) {
            $template->offers_to_generate = $template->pivot->offers_to_generate ?? 0;
            unset($template->pivot);
            return $template;
        });

        return response()->json($paginated);
    }
}

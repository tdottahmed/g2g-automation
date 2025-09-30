<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferScheduler;
use App\Models\UserAccount;
use App\Models\OfferTemplate;
use Illuminate\Http\Request;

class OfferSchedulerController extends Controller
{
    /**
     * Display a listing of the schedulers.
     */
    public function index()
    {
        $schedulers = OfferScheduler::with(['userAccount', 'offerTemplate'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.offer-schedulers.index', compact('schedulers'));
    }

    /**
     * Show the form for creating a new scheduler.
     */
    public function create()
    {
        $userAccounts = UserAccount::all();
        $offerTemplates = OfferTemplate::all();

        return view('admin.offer-schedulers.create', compact('userAccounts', 'offerTemplates'));
    }

    /**
     * Store a newly created scheduler in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_account_id' => 'nullable|exists:user_accounts,id',
            'offer_template_id' => 'nullable|exists:offer_templates,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'timezone' => 'required|string|max:255',
            'days' => 'nullable|array',
            'posts_per_cycle' => 'required|integer|min:1',
            'interval_minutes' => 'required|integer|min:1',
            'max_posts_per_day' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Ensure at least one of user_account_id or offer_template_id is set
        if (empty($validated['user_account_id']) && empty($validated['offer_template_id'])) {
            return back()->withErrors(['error' => 'Please select either a User Account or an Offer Template'])->withInput();
        }

        OfferScheduler::create($validated);

        return redirect()->route('offer-schedulers.index')
            ->with('success', 'Scheduler created successfully.');
    }

    /**
     * Display the specified scheduler.
     */
    public function show(OfferScheduler $offerScheduler)
    {
        $offerScheduler->load(['userAccount', 'offerTemplate']);

        return view('admin.offer-schedulers.show', compact('offerScheduler'));
    }

    /**
     * Show the form for editing the specified scheduler.
     */
    public function edit(OfferScheduler $offerScheduler)
    {
        $userAccounts = UserAccount::all();
        $offerTemplates = OfferTemplate::all();

        return view('admin.offer-schedulers.edit', compact('offerScheduler', 'userAccounts', 'offerTemplates'));
    }

    /**
     * Update the specified scheduler in storage.
     */
    public function update(Request $request, OfferScheduler $offerScheduler)
    {
        $validated = $request->validate([
            'user_account_id' => 'nullable|exists:user_accounts,id',
            'offer_template_id' => 'nullable|exists:offer_templates,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'timezone' => 'required|string|max:255',
            'days' => 'nullable|array',
            'posts_per_cycle' => 'required|integer|min:1',
            'interval_minutes' => 'required|integer|min:1',
            'max_posts_per_day' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Ensure at least one of user_account_id or offer_template_id is set
        if (empty($validated['user_account_id']) && empty($validated['offer_template_id'])) {
            return back()->withErrors(['error' => 'Please select either a User Account or an Offer Template'])->withInput();
        }

        $offerScheduler->update($validated);

        return redirect()->route('offer-schedulers.index')
            ->with('success', 'Scheduler updated successfully.');
    }

    /**
     * Remove the specified scheduler from storage.
     */
    public function destroy(OfferScheduler $offerScheduler)
    {
        $offerScheduler->delete();

        return redirect()->route('offer-schedulers.index')
            ->with('success', 'Scheduler deleted successfully.');
    }

    /**
     * Toggle scheduler status.
     */
    public function toggleStatus($id)
    {
        $scheduler = OfferScheduler::findOrFail($id);
        $scheduler->update(['is_active' => !$scheduler->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $scheduler->is_active,
            'message' => $scheduler->is_active ? 'Scheduler activated' : 'Scheduler deactivated',
        ]);
    }

    /**
     * Reset daily counter for a scheduler.
     */
    public function resetCounter($id)
    {
        $scheduler = OfferScheduler::findOrFail($id);
        $scheduler->update([
            'posts_today' => 0,
            'posts_today_date' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Counter reset successfully',
        ]);
    }
}

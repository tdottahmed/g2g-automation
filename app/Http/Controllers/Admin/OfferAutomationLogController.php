<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use Illuminate\Http\Request;

class OfferAutomationLogController extends Controller
{
    public function index(Request $request)
    {
        $query = OfferAutomationLog::with('template')
            ->latest();

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('template')) {
            $query->where('offer_template_id', $request->template);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('executed_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('executed_at', '<=', $request->to_date);
        }

        $logs = $query->paginate(20);
        $templates = OfferTemplate::where('is_active', true)->get();

        return view('admin.offer-logs.index', compact('logs', 'templates'));
    }

    public function show(OfferAutomationLog $offerLog)
    {
        return view('admin.offer-logs.show', ['log' => $offerLog]);
    }

    public function clear(Request $request)
    {
        OfferAutomationLog::truncate();

        return redirect()
            ->route('offer-logs.index')
            ->with('success', 'All logs have been cleared successfully.');
    }
}

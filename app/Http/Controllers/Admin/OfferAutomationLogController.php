<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferAutomationLog;
use Illuminate\Http\Request;

class OfferAutomationLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = OfferAutomationLog::when($request->status, function ($query, $status) {
            $query->where('status', $status);
        })
            ->when($request->from_date, function ($query, $fromDate) {
                $query->whereDate('executed_at', '>=', $fromDate);
            })
            ->when($request->to_date, function ($query, $toDate) {
                $query->whereDate('executed_at', '<=', $toDate);
            })
            ->latest()
            ->paginate(20);

        return view('admin.offer-logs.index', compact('logs'));
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

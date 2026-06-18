<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferTemplate;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class OfferTemplateController extends Controller
{
    public function index(Request $request)
    {
        $offers = OfferTemplate::with('userAccount')
            ->when($request->filled('account'), fn ($q) => $q->where('user_account_id', $request->account))
            ->when($request->status === 'permanent',    fn ($q) => $q->where('is_permanent', true))
            ->when($request->status === 'non_permanent', fn ($q) => $q->where('is_permanent', false))
            ->latest()
            ->get();

        $userAccounts = UserAccount::orderBy('owner_name')->get();

        return view('admin.offer-templates.index', compact('offers', 'userAccounts'));
    }

    public function create()
    {
        $userAccounts = UserAccount::latest()->get();
        return view('admin.offer-templates.create', compact('userAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_account_id'        => 'required|integer|exists:user_accounts,id',
            'title'                  => 'required|string|max:255',
            'th_level'               => 'required|string',
            'king_level'             => 'required|string',
            'queen_level'            => 'required|string',
            'warden_level'           => 'required|string',
            'champion_level'         => 'required|string',
            'price'                  => 'required|numeric|min:0',
            'currency'               => 'required|string|max:3',
            'region'                 => 'required|string|max:255',
            'medias'                 => 'nullable|array',
            'medias.*.title'         => 'nullable|string|max:255',
            'medias.*.link'          => 'nullable|url|max:500',
            'delivery_quantity_from' => 'required|numeric|min:1',
            'delivery_speed_hour'    => 'required|numeric|min:0',
            'delivery_speed_min'     => 'required|numeric|min:0|max:59',
        ]);

        $deliveryData = [
            'method'        => 'manual',
            'quantity_from' => $request->delivery_quantity_from,
            'speed_hour'    => $request->delivery_speed_hour,
            'speed_min'     => $request->delivery_speed_min,
        ];

        $data = [
            'user_account_id' => $request->user_account_id,
            'title'           => $request->title,
            'description'     => $request->description,
            'th_level'        => $request->th_level,
            'king_level'      => $request->king_level,
            'queen_level'     => $request->queen_level,
            'warden_level'    => $request->warden_level,
            'champion_level'  => $request->champion_level,
            'price'           => $request->price,
            'currency'        => $request->currency,
            'region'          => $request->region,
            'is_permanent'    => $request->boolean('is_permanent'),
            'medias'          => $request->filled('medias') ? json_encode($request->medias) : null,
            'delivery_method' => json_encode($deliveryData),
        ];

        try {
            OfferTemplate::create($data);
            return redirect()->route('offer-templates.index')->with('success', 'Offer template created successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error creating offer template: ' . $th->getMessage());
            return back()->with('error', 'An error occurred while creating the offer template. Please try again.')->withInput();
        }
    }

    public function edit(OfferTemplate $offerTemplate)
    {
        $userAccounts = UserAccount::latest()->get();
        return view('admin.offer-templates.edit', compact('offerTemplate', 'userAccounts'));
    }

    public function update(Request $request, OfferTemplate $offerTemplate)
    {
        $request->validate([
            'user_account_id' => 'required|integer|exists:user_accounts,id',
            'title'           => 'required|string|max:255',
            'th_level'        => 'required|string',
            'king_level'      => 'required|string',
            'queen_level'     => 'required|string',
            'warden_level'    => 'required|string',
            'champion_level'  => 'required|string',
            'price'           => 'required|numeric|min:0',
            'currency'        => 'required|string|max:3',
            'region'          => 'required|string|max:255',
            'medias'          => 'nullable|array',
            'medias.*.title'  => 'nullable|string|max:255',
            'medias.*.link'   => 'nullable|url|max:500',
        ]);

        $deliveryData = [
            'method'        => 'manual',
            'quantity_from' => $request->delivery_quantity_from ?? 0,
            'speed_hour'    => $request->delivery_speed_hour ?? 0,
            'speed_min'     => $request->delivery_speed_min ?? 0,
        ];

        $data = [
            'user_account_id' => $request->user_account_id,
            'title'           => $request->title,
            'description'     => $request->description,
            'th_level'        => $request->th_level,
            'king_level'      => $request->king_level,
            'queen_level'     => $request->queen_level,
            'warden_level'    => $request->warden_level,
            'champion_level'  => $request->champion_level,
            'price'           => $request->price,
            'currency'        => $request->currency,
            'region'          => $request->region,
            'is_permanent'    => $request->boolean('is_permanent'),
            'medias'          => $request->filled('medias') ? array_values($request->medias) : null,
            'delivery_method' => json_encode($deliveryData),
        ];

        try {
            $offerTemplate->update($data);
            return redirect()->route('offer-templates.index')->with('success', 'Offer template updated successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error updating offer template: ' . $th->getMessage());
            return back()->withErrors(['error' => 'An error occurred while updating the offer template. Please try again.'])->withInput();
        }
    }

    public function destroy(Request $request, OfferTemplate $offerTemplate)
    {
        $offerTemplate->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('offer-templates.index')->with('success', 'Offer template deleted successfully.');
    }

    public function togglePermanent(Request $request, OfferTemplate $offerTemplate)
    {
        if ($request->has('is_permanent')) {
            $offerTemplate->is_permanent = (bool) $request->input('is_permanent');
        } else {
            $offerTemplate->is_permanent = !$offerTemplate->is_permanent;
        }

        $offerTemplate->save();

        return response()->json(['success' => true, 'is_permanent' => $offerTemplate->is_permanent]);
    }

    public function queuePost(OfferTemplate $offerTemplate)
    {
        $offerTemplate->increment('offers_to_generate');

        return response()->json([
            'success'            => true,
            'offers_to_generate' => $offerTemplate->fresh()->offers_to_generate,
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_permanent,unmark_permanent,queue_post,delete',
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:offer_templates,id',
        ]);

        $ids    = $request->ids;
        $action = $request->action;

        switch ($action) {
            case 'mark_permanent':
                OfferTemplate::whereIn('id', $ids)->update(['is_permanent' => true]);
                break;
            case 'unmark_permanent':
                OfferTemplate::whereIn('id', $ids)->update(['is_permanent' => false]);
                break;
            case 'queue_post':
                OfferTemplate::whereIn('id', $ids)->increment('offers_to_generate');
                break;
            case 'delete':
                OfferTemplate::whereIn('id', $ids)->delete();
                break;
        }

        return response()->json(['success' => true, 'action' => $action, 'count' => count($ids)]);
    }
}

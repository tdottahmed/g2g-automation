<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfferTemplate;
use App\Models\UserAccount;
use Illuminate\Http\Request;

class OfferTemplateController extends Controller
{
    public function index()
    {
        $offers = OfferTemplate::with('userAccount')->latest()->get();

        $userAccounts = UserAccount::get();
        $currencies = OfferTemplate::distinct()->pluck('currency')->filter();
        $regions = OfferTemplate::distinct()->pluck('region')->filter();

        return view('admin.offer-templates.index', compact('offers', 'userAccounts', 'currencies', 'regions'));
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

        // Prepare delivery method JSON
        $deliveryData = [
            'method'        => 'manual', // always manual
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
            'medias'          => $request->filled('medias') ? json_encode($request->medias) : null,
            'delivery_method' => json_encode($deliveryData),
        ];

        try {
            OfferTemplate::create($data);

            return redirect()
                ->route('offer-templates.index')
                ->with('success', 'Offer template created successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error creating offer template: ' . $th->getMessage());
            return back()
                ->with('error', 'An error occurred while creating the offer template. Please try again.')
                ->withInput();
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
            'medias'          => $request->filled('medias') ? array_values($request->medias) : null,
            'delivery_method' => json_encode($deliveryData)
        ];

        try {
            $offerTemplate->update($data);
            return redirect()
                ->route('offer-templates.index')
                ->with('success', 'Offer template updated successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error updating offer template: ' . $th->getMessage());
            return back()
                ->withErrors(['error' => 'An error occurred while updating the offer template. Please try again.'])
                ->withInput();
        }
    }


    public function destroy(OfferTemplate $offerTemplate)
    {
        $offerTemplate->delete();
        return redirect()->route('offer-templates.index')->with('success', 'Offer template deleted successfully.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $offer = OfferTemplate::findOrFail($id);

        if ($request->has('status')) {
            $offer->is_active = $request->input('status');
        } else {
            $offer->is_active = !$offer->is_active;
        }

        $offer->save();

        return redirect()->back()->with('success', 'Offer status updated successfully!');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:offer_templates,id'
        ]);

        try {
            $action = $request->action;
            $ids = $request->ids;
            $count = count($ids);

            switch ($action) {
                case 'activate':
                    OfferTemplate::whereIn('id', $ids)->update(['is_active' => true]);
                    $message = "Successfully activated {$count} offer template(s).";
                    break;

                case 'deactivate':
                    OfferTemplate::whereIn('id', $ids)->update(['is_active' => false]);
                    $message = "Successfully deactivated {$count} offer template(s).";
                    break;

                case 'delete':
                    OfferTemplate::whereIn('id', $ids)->delete();
                    $message = "Successfully deleted {$count} offer template(s).";
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Log::error('Bulk action failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request: ' . $e->getMessage()
            ], 500);
        }
    }
}

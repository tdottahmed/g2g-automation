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
            ->when($request->filled('game'),    fn ($q) => $q->where('game', $request->game))
            ->when($request->status === 'permanent',     fn ($q) => $q->where('is_permanent', true))
            ->when($request->status === 'non_permanent', fn ($q) => $q->where('is_permanent', false))
            ->latest()
            ->get();

        $userAccounts = UserAccount::orderBy('owner_name')->get();

        return view('admin.offer-templates.index', compact('offers', 'userAccounts'));
    }

    public function create()
    {
        $userAccounts = UserAccount::latest()->get();
        $games = OfferTemplate::GAMES;
        return view('admin.offer-templates.create', compact('userAccounts', 'games'));
    }

    public function store(Request $request)
    {
        $game = $request->input('game', 'clash_of_clans');

        $request->validate(array_merge([
            'user_account_id'        => 'required|integer|exists:user_accounts,id',
            'game'                   => 'required|in:' . implode(',', array_keys(OfferTemplate::GAMES)),
            'title'                  => 'required|string|max:255',
            'price'                  => 'required|numeric|min:0',
            'currency'               => 'required|string|max:3',
            'region'                 => 'required|string|max:255',
            'medias'                 => 'nullable|array',
            'medias.*.title'         => 'nullable|string|max:255',
            'medias.*.link'          => 'nullable|url|max:500',
            'delivery_quantity_from' => 'required|numeric|min:1',
            'delivery_speed_hour'    => 'required|numeric|min:0',
            'delivery_speed_min'     => 'required|numeric|min:0|max:59',
        ], $this->gameDataRules($game)));

        $data = [
            'user_account_id' => $request->user_account_id,
            'game'            => $game,
            'game_data'       => $this->extractGameData($request),
            'title'           => $request->title,
            'description'     => $request->description,
            'price'           => $request->price,
            'currency'        => $request->currency,
            'region'          => $request->region,
            'is_permanent'    => $request->boolean('is_permanent'),
            'medias'          => $request->filled('medias') ? json_encode($request->medias) : null,
            'delivery_method' => json_encode([
                'method'        => 'manual',
                'quantity_from' => $request->delivery_quantity_from,
                'speed_hour'    => $request->delivery_speed_hour,
                'speed_min'     => $request->delivery_speed_min,
            ]),
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
        $games = OfferTemplate::GAMES;
        return view('admin.offer-templates.edit', compact('offerTemplate', 'userAccounts', 'games'));
    }

    public function update(Request $request, OfferTemplate $offerTemplate)
    {
        $game = $request->input('game', 'clash_of_clans');

        $request->validate(array_merge([
            'user_account_id' => 'required|integer|exists:user_accounts,id',
            'game'            => 'required|in:' . implode(',', array_keys(OfferTemplate::GAMES)),
            'title'           => 'required|string|max:255',
            'price'           => 'required|numeric|min:0',
            'currency'        => 'required|string|max:3',
            'region'          => 'required|string|max:255',
            'medias'          => 'nullable|array',
            'medias.*.title'  => 'nullable|string|max:255',
            'medias.*.link'   => 'nullable|url|max:500',
        ], $this->gameDataRules($game)));

        $data = [
            'user_account_id' => $request->user_account_id,
            'game'            => $game,
            'game_data'       => $this->extractGameData($request),
            'title'           => $request->title,
            'description'     => $request->description,
            'price'           => $request->price,
            'currency'        => $request->currency,
            'region'          => $request->region,
            'is_permanent'    => $request->boolean('is_permanent'),
            'medias'          => $request->filled('medias') ? array_values($request->medias) : null,
            'delivery_method' => json_encode([
                'method'        => 'manual',
                'quantity_from' => $request->delivery_quantity_from ?? 0,
                'speed_hour'    => $request->delivery_speed_hour ?? 0,
                'speed_min'     => $request->delivery_speed_min ?? 0,
            ]),
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

    private function gameDataRules(string $game): array
    {
        return match($game) {
            'clash_of_clans' => [
                'game_data.th_level'       => 'required|integer',
                'game_data.king_level'     => 'nullable|integer',
                'game_data.queen_level'    => 'nullable|integer',
                'game_data.warden_level'   => 'nullable|integer',
                'game_data.champion_level' => 'nullable|integer',
            ],
            'brawl_stars' => [
                'game_data.platform' => 'required|in:Android,iOS,Android & iOS',
                'game_data.trophies' => 'required|integer|min:0',
                'game_data.brawlers' => 'required|integer|min:0',
                'game_data.skins'    => 'nullable|integer|min:0',
            ],
            'clash_royale' => [
                'game_data.king_level'     => 'required|integer',
                'game_data.arena'          => 'required|string|max:100',
                'game_data.level_16_cards' => 'nullable|integer|min:0',
                'game_data.level_15_cards' => 'nullable|integer|min:0',
                'game_data.level_14_cards' => 'nullable|integer|min:0',
            ],
            'hay_day' => [
                'game_data.platform' => 'required|in:Android,iOS,PC,Android & iOS',
            ],
            'mobile_legends' => [
                'game_data.platform' => 'required|in:Android,iOS,Android & iOS',
                'game_data.rank'     => 'required|string|max:100',
                'game_data.heroes'   => 'nullable|integer|min:0',
                'game_data.skins'    => 'nullable|integer|min:0',
            ],
            'call_of_duty_mobile' => [
                'game_data.platform' => 'required|in:Android,iOS,PC,Android & iOS',
                'game_data.rank'     => 'required|string|max:100',
            ],
            default => [],
        };
    }

    private function extractGameData(Request $request): array
    {
        $d    = $request->input('game_data', []);
        $keys = match($request->input('game')) {
            'clash_of_clans'      => ['th_level', 'king_level', 'queen_level', 'warden_level', 'champion_level'],
            'brawl_stars'         => ['platform', 'trophies', 'brawlers', 'skins'],
            'clash_royale'        => ['king_level', 'arena', 'level_16_cards', 'level_15_cards', 'level_14_cards'],
            'hay_day'             => ['platform'],
            'mobile_legends'      => ['platform', 'rank', 'heroes', 'skins'],
            'call_of_duty_mobile' => ['platform', 'rank'],
            default               => array_keys($d),
        };
        return array_intersect_key($d, array_flip($keys));
    }
}

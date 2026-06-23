<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use App\Models\UserAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AutomationApiController extends Controller
{
    public function pending(Request $request): JsonResponse
    {
        $intervalMinutes = (int) (
            ApplicationSetup::where('type', 'schedule_interval_minutes')->value('value') ?? 15
        );

        $schedulerWindows = json_decode(
            ApplicationSetup::where('type', 'scheduler_windows')->value('value') ?? '[]',
            true
        );

        $accountId = $request->query('account_id');

        $templates = OfferTemplate::with('userAccounts')
            ->when($accountId, fn ($q) => $q->whereHas('userAccounts', fn ($q2) => $q2->where('user_accounts.id', $accountId)))
            ->get();

        $byAccount = [];

        foreach ($templates as $template) {
            $forced = $template->offers_to_generate && $template->offers_to_generate > 0;

            if (!$forced && !$this->isWithinSchedulerWindows($schedulerWindows)) {
                continue;
            }

            if (!$forced && !$template->shouldPostNow($intervalMinutes)) {
                continue;
            }

            foreach ($template->userAccounts as $userAccount) {
                if ($accountId && (string) $userAccount->id !== (string) $accountId) {
                    continue;
                }
                $uid = $userAccount->id;
                if (!isset($byAccount[$uid])) {
                    $byAccount[$uid] = ['account' => $userAccount, 'templates' => []];
                }
                $byAccount[$uid]['templates'][] = $this->formatTemplate($template);
            }
        }

        $result = array_values(array_map(fn ($entry) => [
            'user_id'   => $entry['account']->id,
            'email'     => $entry['account']->email,
            'password'  => $entry['account']->password,
            'templates' => $entry['templates'],
        ], $byAccount));

        return response()->json([
            'users'                     => $result,
            'schedule_interval_minutes' => $intervalMinutes,
            'server_time'               => now()->toIso8601String(),
        ]);
    }

    public function success(OfferTemplate $template, Request $request): JsonResponse
    {
        $details = $request->input('details', []);

        $template->update(['last_posted_at' => now()]);

        $remainingOffers = null;
        if ($template->offers_to_generate && $template->offers_to_generate > 0) {
            $template->decrement('offers_to_generate');
            $remainingOffers = $template->fresh()->offers_to_generate;
        }

        OfferAutomationLog::logSuccess(
            $template,
            "Successfully posted offer via local runner for template '{$template->title}'",
            array_merge($details, [
                'remaining_offers' => $remainingOffers ?? 'unlimited',
                'posted_at'        => now()->toIso8601String(),
            ])
        );

        return response()->json([
            'success'          => true,
            'remaining_offers' => $remainingOffers ?? 'unlimited',
        ]);
    }

    public function failed(OfferTemplate $template, Request $request): JsonResponse
    {
        $error   = $request->input('error', 'Unknown error');
        $details = $request->input('details', []);

        OfferAutomationLog::logFailed(
            $template,
            "Failed to post offer via local runner for template '{$template->title}': {$error}",
            array_merge($details, [
                'error'     => $error,
                'failed_at' => now()->toIso8601String(),
            ])
        );

        return response()->json(['success' => true]);
    }

    /**
     * Returns accounts queued for delete-all, with their permanent template titles so the
     * desktop runner can skip those offers on g2g.com.
     */
    public function pendingDeleteAll(): JsonResponse
    {
        $accounts = UserAccount::where('queue_delete_all', true)
            ->with(['offerTemplates' => fn ($q) => $q->where('is_permanent', true)->select('offer_templates.id', 'offer_templates.title')])
            ->get();

        $users = $accounts->map(fn ($a) => [
            'user_id'          => $a->id,
            'email'            => $a->email,
            'password'         => $a->password,
            'permanent_titles' => $a->queue_force_delete_all ? [] : $a->offerTemplates->pluck('title')->values()->all(),
        ])->values()->all();

        return response()->json(['users' => $users, 'server_time' => now()->toIso8601String()]);
    }

    public function deleteAllComplete(UserAccount $userAccount, Request $request): JsonResponse
    {
        $details = $request->input('details', []);

        $userAccount->update(['queue_delete_all' => false, 'queue_force_delete_all' => false]);

        OfferAutomationLog::create([
            'offer_template_id' => null,
            'status'            => 'success',
            'message'           => "Delete-all completed for account '{$userAccount->email}'",
            'details'           => array_merge($details, ['account_id' => $userAccount->id, 'completed_at' => now()->toIso8601String()]),
            'executed_at'       => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteAllFailed(UserAccount $userAccount, Request $request): JsonResponse
    {
        $error = $request->input('error', 'Unknown error');

        OfferAutomationLog::create([
            'offer_template_id' => null,
            'status'            => 'failed',
            'message'           => "Delete-all failed for account '{$userAccount->email}': {$error}",
            'details'           => ['account_id' => $userAccount->id, 'error' => $error, 'failed_at' => now()->toIso8601String()],
            'executed_at'       => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function userAccounts(): JsonResponse
    {
        $accounts = UserAccount::withCount([
            'offerTemplates as total_templates_count',
            'offerTemplates as non_permanent_count' => fn ($q) => $q->where('is_permanent', false),
        ])->orderBy('email')->get();

        return response()->json([
            'accounts' => $accounts->map(fn ($a) => [
                'id'                    => $a->id,
                'email'                 => $a->email,
                'total_templates_count' => $a->total_templates_count,
                'non_permanent_count'   => $a->non_permanent_count,
            ])->values()->all(),
        ]);
    }

    /**
     * Returns all non-permanent offer templates for one account.
     * Used by the desktop app "Delete Non-Permanent" action.
     */
    public function getNonPermanentOffers(UserAccount $userAccount): JsonResponse
    {
        $offers = $userAccount->offerTemplates()
            ->where('is_permanent', false)
            ->select(['offer_templates.title', 'offer_templates.price'])
            ->orderBy('offer_templates.title')
            ->get()
            ->map(fn ($o) => [
                'title' => $o->title,
                'price' => $o->price !== null ? (string) $o->price : null,
            ])
            ->values()
            ->all();

        return response()->json([
            'user_id' => $userAccount->id,
            'email'   => $userAccount->email,
            'offers'  => $offers,
        ]);
    }

    public function heartbeat(): JsonResponse
    {
        return response()->json([
            'status'      => 'ok',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function formatTemplate(OfferTemplate $template): array
    {
        $deliveryMethod = is_array($template->delivery_method)
            ? $template->delivery_method
            : (json_decode($template->delivery_method ?? '{}', true) ?? []);

        $medias = is_array($template->medias)
            ? $template->medias
            : (json_decode($template->medias ?? '[]', true) ?? []);

        $mediaData = [];
        if (!empty($medias)) {
            foreach ($medias as $media) {
                if (!empty($media['link'])) {
                    $mediaData[] = [
                        'title' => $media['title'] ?? 'Media',
                        'Link'  => $media['link'],
                    ];
                }
            }
        }

        return [
            'template_id'               => $template->id,
            'game'                      => $template->game,
            'game_data'                 => $template->game_data ?? [],
            'Title'                     => $template->title ?? 'Untitled Offer',
            'Description'               => $template->description ?? '',
            'Default price (unit)'      => (string) ($template->price ?? '0'),
            'Minimum purchase quantity' => $template->minimum_order_quantity ?? 1,
            'Instant delivery'          => $template->instant_delivery ? 1 : 0,
            'Delivery hour'             => $deliveryMethod['speed_hour'] ?? '0',
            'Delivery minute'           => $deliveryMethod['speed_min'] ?? '30',
            'mediaData'                 => $mediaData,
            'offers_to_generate'        => $template->offers_to_generate,
            'last_posted_at'            => $template->last_posted_at?->toIso8601String() ?? null,
            'is_permanent'              => $template->is_permanent,
        ];
    }

    private function isWithinSchedulerWindows(array $windows): bool
    {
        if (empty($windows)) {
            return true;
        }

        $now = now();
        foreach ($windows as $window) {
            $start = Carbon::parse($now->format('Y-m-d') . ' ' . ($window['start'] ?? '00:00'));
            $end   = Carbon::parse($now->format('Y-m-d') . ' ' . ($window['end'] ?? '23:59'));

            if ($now->between($start, $end)) {
                return true;
            }
        }

        return false;
    }
}

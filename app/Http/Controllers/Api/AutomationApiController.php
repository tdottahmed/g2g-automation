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
    public function pending(): JsonResponse
    {
        $intervalMinutes = (int) (
            ApplicationSetup::where('type', 'schedule_interval_minutes')->value('value') ?? 15
        );

        $schedulerWindows = json_decode(
            ApplicationSetup::where('type', 'scheduler_windows')->value('value') ?? '[]',
            true
        );

        $templates = OfferTemplate::with('userAccount')->get();

        $result = [];

        foreach ($templates->groupBy('user_account_id') as $userTemplates) {
            $userAccount = $userTemplates->first()->userAccount;
            if (!$userAccount) {
                continue;
            }

            $pendingTemplates = [];

            foreach ($userTemplates as $template) {
                $forced = $template->offers_to_generate && $template->offers_to_generate > 0;

                if (!$forced && !$this->isWithinSchedulerWindows($schedulerWindows)) {
                    continue;
                }

                if (!$forced && !$template->shouldPostNow($intervalMinutes)) {
                    continue;
                }

                $pendingTemplates[] = $this->formatTemplate($template);
            }

            if (!empty($pendingTemplates)) {
                $result[] = [
                    'user_id'   => $userAccount->id,
                    'email'     => $userAccount->email,
                    'password'  => $userAccount->password,
                    'templates' => $pendingTemplates,
                ];
            }
        }

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
            ->with(['offers' => fn ($q) => $q->where('is_permanent', true)->select('id', 'user_account_id', 'title')])
            ->get();

        $users = $accounts->map(fn ($a) => [
            'user_id'          => $a->id,
            'email'            => $a->email,
            'password'         => $a->password,
            'permanent_titles' => $a->offers->pluck('title')->values()->all(),
        ])->values()->all();

        return response()->json(['users' => $users, 'server_time' => now()->toIso8601String()]);
    }

    public function deleteAllComplete(UserAccount $userAccount, Request $request): JsonResponse
    {
        $details = $request->input('details', []);

        $userAccount->update(['queue_delete_all' => false]);

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
        $accounts = UserAccount::withCount('offers as total_templates_count')
            ->orderBy('email')
            ->get();

        return response()->json([
            'accounts' => $accounts->map(fn ($a) => [
                'id'                    => $a->id,
                'email'                 => $a->email,
                'total_templates_count' => $a->total_templates_count,
            ])->values()->all(),
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

        $mediaData = [];
        if (!empty($template->medias)) {
            foreach ($template->medias as $media) {
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
            'Title'                     => $template->title ?? 'Untitled Offer',
            'Description'               => $template->description ?? '',
            'Town Hall Level'           => $template->th_level ?? '',
            'King Level'                => $template->king_level ?? '',
            'Queen Level'               => $template->queen_level ?? '',
            'Warden Level'              => $template->warden_level ?? '',
            'Champion Level'            => $template->champion_level ?? '',
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAccount;
use App\Models\OfferTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class OfferAutomationController extends Controller
{
    public function dashboard()
    {
        $userAccounts = UserAccount::withCount([
            'offers as total_templates',
            'offers as active_templates_count' => function ($query) {
                $query->where('is_active', true);
            }
        ])->get();

        return view('admin.offer-automation.dashboard', compact('userAccounts'));
    }

    public function runForUser(Request $request, $userAccountId)
    {
        $request->validate([
            'mode' => 'required|in:relation,direct'
        ]);
        $userAccount = UserAccount::findOrFail($userAccountId);

        try {
            $exitCode = Artisan::call('offer:automation', [
                '--user_account_id' => $userAccountId,
                '--all' => $request->mode == 'direct' ? true : false,
            ]);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Automation started for {$userAccount->email}",
                    'output' => $output,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Automation failed for {$userAccount->email}",
                'output' => $output,
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Offer automation error', [
                'user_account_id' => $userAccountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Automation error: {$e->getMessage()}",
            ], 500);
        }
    }


    public function runForTemplate(Request $request, $templateId)
    {
        $request->validate([
            'force' => 'boolean'
        ]);

        $template = OfferTemplate::with('userAccount')->findOrFail($templateId);

        $exitCode = Artisan::call('offer:post', [
            '--template_id' => $templateId,
            '--force' => $request->force ?? false
        ]);

        $output = Artisan::output();

        if ($exitCode === 0) {
            return response()->json([
                'success' => true,
                'message' => "Template '{$template->title}' queued for posting",
                'output' => $output
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to queue template',
            'output' => $output
        ], 500);
    }

    public function getUserTemplates($userAccountId)
    {
        $templates = OfferTemplate::where('user_account_id', $userAccountId)
            ->select(['id', 'title', 'is_active', 'last_posted_at', 'created_at'])
            ->get();

        return response()->json($templates);
    }
}

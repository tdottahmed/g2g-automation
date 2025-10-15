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
        $userAccount = UserAccount::findOrFail($userAccountId);

        try {
            $exitCode = Artisan::call('offer:automation', [
                '--user_account_id' => $userAccountId,
            ]);

            $output = Artisan::output();

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Posting started for {$userAccount->email}",
                    'output' => $output,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Posting failed for {$userAccount->email}",
                'output' => $output,
            ], 500);
        } catch (\Exception $e) {
            logger()->error('Offer automation error', [
                'user_account_id' => $userAccountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Posting error: {$e->getMessage()}",
            ], 500);
        }
    }

    public function runForAllUsers(Request $request)
    {
        try {
            $exitCode = Artisan::call('offer:automation', [
                '--all' => true,
            ]);

            $output = Artisan::output();

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Posting started for all users",
                    'output' => $output,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Posting failed for all users",
                'output' => $output,
            ], 500);
        } catch (\Exception $e) {
            logger()->error('Offer automation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "All users posting error: {$e->getMessage()}",
            ], 500);
        }
    }

    public function getUserTemplates($userAccountId)
    {
        $templates = OfferTemplate::where('user_account_id', $userAccountId)
            ->select(['id', 'title', 'is_active', 'last_posted_at', 'created_at'])
            ->get();

        return response()->json($templates);
    }
}

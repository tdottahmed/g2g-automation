<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use App\Models\UserAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = $this->getKeyMetrics();
        $chartData = $this->getChartData();
        $recentLogs = $this->getRecentLogs();
        $systemHealth = $this->getSystemHealth();
        $userAccountStats = $this->getUserAccountStats();

        return view('admin.dashboard.index', array_merge(
            $metrics,
            [
                'chartData' => $chartData,
                'recentLogs' => $recentLogs,
                'userAccountStats' => $userAccountStats,
            ],
            $systemHealth
        ));
    }

    private function getKeyMetrics()
    {
        // Total successful posts (counting actual successful template posts)
        $totalOffersPosted = OfferAutomationLog::where('status', 'success')
            ->get()
            ->sum(function ($log) {
                $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                $templates = $details['templates'] ?? [];
                return collect($templates)->where('status', 'completed')->count();
            });

        // Template statistics
        $totalTemplates = OfferTemplate::count();
        $activeTemplates = OfferTemplate::where('is_active', true)->count();

        // Success rate based on actual template success, not just log status
        $recentLogs = OfferAutomationLog::where('executed_at', '>=', now()->subDays(7))->get();

        $totalTemplatesProcessed = 0;
        $successfulTemplates = 0;

        foreach ($recentLogs as $log) {
            $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
            $templates = $details['templates'] ?? [];
            $totalTemplatesProcessed += count($templates);
            $successfulTemplates += collect($templates)->where('status', 'completed')->count();
        }

        $successRate = $totalTemplatesProcessed > 0 ?
            round(($successfulTemplates / $totalTemplatesProcessed) * 100, 1) : 0;

        // Average execution time
        $avgExecutionTime = $this->getAverageExecutionTime();

        // Calculate trend based on successful posts
        $offerTrend = $this->calculateOfferTrend();

        return [
            'totalOffersPosted' => $totalOffersPosted ?: 0,
            'totalTemplates' => $totalTemplates ?: 0,
            'activeTemplates' => $activeTemplates ?: 0,
            'successRate' => $successRate ?: 0,
            'avgExecutionTime' => $avgExecutionTime ?: 0,
            'offerTrend' => $offerTrend,
        ];
    }

    private function calculateOfferTrend()
    {
        try {
            // Current period (last 7 days) - count successful template posts
            $currentPeriod = OfferAutomationLog::where('executed_at', '>=', now()->subDays(7))
                ->get()
                ->sum(function ($log) {
                    $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                    $templates = $details['templates'] ?? [];
                    return collect($templates)->where('status', 'success')->count();
                });

            // Previous period (7 days before that)
            $previousPeriod = OfferAutomationLog::whereBetween('executed_at', [now()->subDays(14), now()->subDays(7)])
                ->get()
                ->sum(function ($log) {
                    $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                    $templates = $details['templates'] ?? [];
                    return collect($templates)->where('status', 'success')->count();
                });

            if ($previousPeriod > 0) {
                $trend = (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
                return round($trend, 1);
            }

            return $currentPeriod > 0 ? 100 : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getAverageExecutionTime()
    {
        try {
            $logs = OfferAutomationLog::where('status', 'success')
                ->where('executed_at', '>=', now()->subDays(7))
                ->get()
                ->filter(function ($log) {
                    $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                    return isset($details['execution_time_seconds']);
                });

            if ($logs->count() > 0) {
                return round($logs->avg(function ($log) {
                    $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                    return $details['execution_time_seconds'];
                }), 2);
            }

            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getChartData()
    {
        $dates = collect();
        $successful = collect();
        $failed = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates->push($date->format('M j'));

            $logs = OfferAutomationLog::whereDate('executed_at', $date)->get();

            $successCount = 0;
            $failedCount = 0;

            foreach ($logs as $log) {
                $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                $templates = $details['templates'] ?? [];
                $successCount += collect($templates)->where('status', 'completed')->count();
                $failedCount += collect($templates)->where('status', 'failed')->count();
            }

            $successful->push($successCount);
            $failed->push($failedCount);
        }

        return [
            'dates' => $dates,
            'successful' => $successful,
            'failed' => $failed,
        ];
    }


    private function getRecentLogs()
    {
        return OfferAutomationLog::with('template')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($log) {
                $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                $templates = $details['templates'] ?? [];
                $successCount = collect($templates)->where('status', 'completed')->count();
                $totalCount = count($templates);

                $log->success_count = $successCount;
                $log->total_templates = $totalCount;
                return $log;
            });
    }

    private function getUserAccountStats()
    {
        try {
            $totalAccounts = UserAccount::count();
            $activeToday = OfferAutomationLog::whereDate('executed_at', today())
                ->get()
                ->pluck('details.user_account_id')
                ->filter()
                ->unique()
                ->count();

            return [
                'total' => $totalAccounts,
                'active_today' => $activeToday,
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'active_today' => 0];
        }
    }

    private function getSystemHealth()
    {
        try {
            // Queue health
            $pendingJobs = DB::table('jobs')->count();
            $queueHealth = [
                'count' => $pendingJobs,
                'status' => $pendingJobs < 10 ? 'success' : ($pendingJobs < 50 ? 'warning' : 'danger')
            ];

            // Storage health
            $totalSpace = disk_total_space(storage_path());
            $usedSpace = $totalSpace - disk_free_space(storage_path());
            $usagePercent = $totalSpace > 0 ? round(($usedSpace / $totalSpace) * 100, 1) : 0;

            $storageHealth = [
                'usage' => $usagePercent . '%',
                'status' => $usagePercent < 70 ? 'success' : ($usagePercent < 90 ? 'warning' : 'danger')
            ];

            // Log health - count today's template posts, not just logs
            $todayLogs = OfferAutomationLog::whereDate('created_at', today())->get();
            $todayPosts = 0;
            foreach ($todayLogs as $log) {
                $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                $todayPosts += count($details['templates'] ?? []);
            }

            $logHealth = [
                'count' => $todayPosts,
                'status' => $todayPosts < 100 ? 'success' : ($todayPosts < 500 ? 'warning' : 'danger')
            ];

            // Error health - count failed template posts, not just failed logs
            $recentErrors = OfferAutomationLog::where('created_at', '>=', now()->subHours(24))
                ->get()
                ->sum(function ($log) {
                    $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                    $templates = $details['templates'] ?? [];
                    return collect($templates)->where('status', 'failed')->count();
                });

            $errorHealth = [
                'count' => $recentErrors,
                'status' => $recentErrors == 0 ? 'success' : ($recentErrors < 10 ? 'warning' : 'danger')
            ];

            return compact('queueHealth', 'storageHealth', 'logHealth', 'errorHealth');
        } catch (\Exception $e) {
            return [
                'queueHealth' => ['count' => 0, 'status' => 'success'],
                'storageHealth' => ['usage' => '0%', 'status' => 'success'],
                'logHealth' => ['count' => 0, 'status' => 'success'],
                'errorHealth' => ['count' => 0, 'status' => 'success'],
            ];
        }
    }

    private function convertTo24Hour(string $time12h): string
    {
        if (empty($time12h)) return '00:00';
        if (preg_match('/^\d{1,2}:\d{2}$/', $time12h)) return $time12h;

        try {
            $time = \DateTime::createFromFormat('h:i A', $time12h);
            return $time ? $time->format('H:i') : '00:00';
        } catch (\Exception $e) {
            return '00:00';
        }
    }

    private function isTimeInWindow(string $currentTime, string $startTime, string $endTime): bool
    {
        if ($endTime < $startTime) {
            return $currentTime >= $startTime || $currentTime <= $endTime;
        }
        return $currentTime >= $startTime && $currentTime <= $endTime;
    }
}

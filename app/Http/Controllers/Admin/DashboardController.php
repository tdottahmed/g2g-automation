<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        // Ensure all variables have default values
        $metrics = $this->getKeyMetrics();
        $chartData = $this->getChartData();
        $schedulerData = $this->getSchedulerStatus();
        $recentLogs = $this->getRecentLogs();
        $topTemplates = $this->getTopTemplates();
        $systemHealth = $this->getSystemHealth();

        return view('admin.dashboard.index', array_merge(
            $metrics,
            [
                'chartData' => $chartData,
                'recentLogs' => $recentLogs,
                'topTemplates' => $topTemplates,
            ],
            $schedulerData,
            $systemHealth
        ));
    }

    private function getKeyMetrics()
    {
        // Total offers posted (successful ones)
        $totalOffersPosted = OfferAutomationLog::where('status', 'success')->count();

        // Template statistics
        $totalTemplates = OfferTemplate::count();
        $activeTemplates = OfferTemplate::where('is_active', true)->count();

        // Success rate for last 7 days
        $recentLogs = OfferAutomationLog::where('executed_at', '>=', now()->subDays(7))->get();
        $successCount = $recentLogs->where('status', 'success')->count();
        $totalCount = $recentLogs->count();
        $successRate = $totalCount > 0 ? round(($successCount / $totalCount) * 100, 1) : 0;

        // Average execution time
        $avgExecutionTime = $this->getAverageExecutionTime();

        // Calculate trend (compare with previous period)
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
            // Current period (last 7 days)
            $currentPeriod = OfferAutomationLog::where('status', 'success')
                ->where('executed_at', '>=', now()->subDays(7))
                ->count();

            // Previous period (7 days before that)
            $previousPeriod = OfferAutomationLog::where('status', 'success')
                ->whereBetween('executed_at', [now()->subDays(14), now()->subDays(7)])
                ->count();

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
                    return isset($log->details['execution_time_seconds']);
                });

            if ($logs->count() > 0) {
                return round($logs->avg(function ($log) {
                    return $log->details['execution_time_seconds'];
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

            $successCount = OfferAutomationLog::where('status', 'success')
                ->whereDate('executed_at', $date)
                ->count();

            $failedCount = OfferAutomationLog::where('status', 'failed')
                ->whereDate('executed_at', $date)
                ->count();

            $successful->push($successCount);
            $failed->push($failedCount);
        }

        return [
            'dates' => $dates,
            'successful' => $successful,
            'failed' => $failed,
        ];
    }

    private function getSchedulerStatus()
    {
        try {
            $windows = json_decode(
                ApplicationSetup::where('type', 'scheduler_windows')->first()->value ?? '[]',
                true
            );

            $currentTime = now();
            $currentTimeStr = $currentTime->format('H:i');
            $isActive = false;
            $todayWindows = [];

            foreach ($windows as $window) {
                $start = $this->convertTo24Hour($window['start'] ?? '00:00');
                $end = $this->convertTo24Hour($window['end'] ?? '00:00');

                $windowActive = $this->isTimeInWindow($currentTimeStr, $start, $end);
                $isActive = $isActive || $windowActive;

                $todayWindows[] = [
                    'start' => $window['start'] ?? '00:00',
                    'end' => $window['end'] ?? '00:00',
                    'is_active' => $windowActive,
                    'is_upcoming' => $start > $currentTimeStr,
                ];
            }

            // Find next window
            $nextWindow = collect($todayWindows)
                ->where('is_upcoming', true)
                ->sortBy('start')
                ->first();

            if ($nextWindow) {
                $nextWindowStart = $nextWindow['start'];
            } else {
                $firstWindow = collect($todayWindows)->sortBy('start')->first();
                $nextWindowStart = $firstWindow ? $firstWindow['start'] . ' (tomorrow)' : 'Not configured';
            }

            return [
                'isSchedulerActive' => $isActive,
                'todayWindows' => $todayWindows,
                'nextWindowStart' => $nextWindowStart,
                'nextRunIn' => '5 minutes',
            ];
        } catch (\Exception $e) {
            return [
                'isSchedulerActive' => false,
                'todayWindows' => [],
                'nextWindowStart' => 'Not configured',
                'nextRunIn' => 'N/A',
            ];
        }
    }

    private function getRecentLogs()
    {
        return OfferAutomationLog::with('template')
            ->latest()
            ->take(5)
            ->get();
    }

    private function getTopTemplates()
    {
        try {
            $templates = OfferTemplate::withCount(['logs as success_count' => function ($query) {
                $query->where('status', 'success');
            }])
                ->withCount('logs')
                ->having('success_count', '>', 0)
                ->orderBy('success_count', 'desc')
                ->take(5)
                ->get();

            return $templates->map(function ($template) {
                $template->success_rate = $template->logs_count > 0
                    ? round(($template->success_count / $template->logs_count) * 100, 1)
                    : 0;
                return $template;
            });
        } catch (\Exception $e) {
            return collect();
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

            // Log health
            $todayLogs = OfferAutomationLog::whereDate('created_at', today())->count();
            $logHealth = [
                'count' => $todayLogs,
                'status' => $todayLogs < 100 ? 'success' : ($todayLogs < 500 ? 'warning' : 'danger')
            ];

            // Error health
            $recentErrors = OfferAutomationLog::where('status', 'failed')
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            $errorHealth = [
                'count' => $recentErrors,
                'status' => $recentErrors == 0 ? 'success' : ($recentErrors < 5 ? 'warning' : 'danger')
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

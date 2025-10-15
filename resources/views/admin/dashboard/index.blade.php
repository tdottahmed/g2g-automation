<x-layouts.admin.master>
  <div class="row">
    <div class="col-xl-3 col-md-4">
      <x-data-display.stat-card icon="ri-file-text-line" label="Active Templates" value="{{ $activeTemplates }}"
                                subtitle="Out of {{ $totalTemplates }}" bg-class="bg-success" />
    </div>

    <div class="col-xl-3 col-md-4">
      <x-data-display.stat-card icon="ri-user-line" label="User Accounts" value="{{ $userAccountStats['total'] }}"
                                subtitle="{{ $userAccountStats['active_today'] }} active today" bg-class="bg-info" />
    </div>

    <div class="col-xl-3 col-md-6">
      <x-data-display.stat-card icon="ri-checkbox-circle-line" label="Success Rate" value="{{ $successRate }}%"
                                subtitle="Last 7 days" bg-class="bg-warning" />
    </div>

    <div class="col-xl-3 col-md-6">
      <x-data-display.stat-card icon="ri-time-line" label="Avg. Execution Time" value="{{ $avgExecutionTime }}s"
                                subtitle="Per automation run" bg-class="bg-danger" />
    </div>
  </div>

  <div class="row mt-2">
    <div class="col-xl-8">
      <x-data-display.card>
        <x-slot name="header">
          <h5 class="card-title">
            <i class="ri-bar-chart-line me-2"></i>Daily Performance (Last 7 Days)
          </h5>
        </x-slot>
        <div id="performanceChart" style="height: 300px;"></div>
      </x-data-display.card>
    </div>
    <div class="col-xl-4">
      <x-data-display.card>
        <x-slot name="header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title">
              <i class="ri-history-line me-2"></i>Recent Activity
            </h5>
            <a href="{{ route('offer-logs.index') }}" class="btn btn-sm btn-outline-primary">
              View All
            </a>
          </div>
        </x-slot>
        <div class="table-responsive">
          <table class="table-borderless table-centered mb-0 table">
            <tbody>
              @forelse($recentLogs as $log)
                <tr>
                  <td style="width: 50px;">
                    <div class="avatar-sm">
                      <span
                            class="avatar-title bg-{{ $log->status === 'success' ? 'success' : 'danger' }}-subtle text-{{ $log->status === 'success' ? 'success' : 'danger' }} rounded-circle">
                        <i class="ri-{{ $log->status === 'success' ? 'check' : 'close' }}-line"></i>
                      </span>
                    </div>
                  </td>
                  <td>
                    <h6 class="mb-1">
                      @php
                        $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
                        $userAccountId = $details['user_account_id'] ?? null;
                        $userAccount = $userAccountId ? findUserAccountById($userAccountId) : 'N/A';
                      @endphp
                      Account #{{ $userAccount->owner_name ?? 'N/A' }} ({{ $userAccount->email ?? 'N/A' }})
                    </h6>
                    <p class="text-muted small mb-0">
                      {{ $log->success_count }}/{{ $log->total_templates }} successful •
                      {{ $log->executed_at->diffForHumans() }}
                    </p>
                  </td>
                  <td class="text-end">
                    <span
                          class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}-subtle text-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                      {{ ucfirst($log->status) }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="py-4 text-center">
                    <div class="text-muted">
                      <i class="ri-inbox-line display-4"></i>
                      <p class="mt-2">No recent activity</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </x-data-display.card>
    </div>
  </div>

  <div class="row mt-2">
    <div class="col-12">
      <x-data-display.card>
        <x-slot name="header">
          <h5 class="card-title">
            <i class="ri-heart-pulse-line me-2"></i>System Health
          </h5>
        </x-slot>
        <div class="row text-center">
          <div class="col-md-3">
            <div class="p-3">
              <div class="avatar-lg mx-auto mb-3">
                <div
                     class="avatar-title bg-{{ $queueHealth['status'] }}-subtle text-{{ $queueHealth['status'] }} rounded-circle">
                  <i class="ri-list-check fs-24"></i>
                </div>
              </div>
              <h4>{{ $queueHealth['count'] }}</h4>
              <p class="text-muted mb-0">Pending Jobs</p>
              <small class="text-{{ $queueHealth['status'] }}">
                @if ($queueHealth['status'] == 'success')
                  Healthy
                @elseif($queueHealth['status'] == 'warning')
                  Warning
                @else
                  Critical
                @endif
              </small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3">
              <div class="avatar-lg mx-auto mb-3">
                <div
                     class="avatar-title bg-{{ $storageHealth['status'] }}-subtle text-{{ $storageHealth['status'] }} rounded-circle">
                  <i class="ri-database-2-line fs-24"></i>
                </div>
              </div>
              <h4>{{ $storageHealth['usage'] }}</h4>
              <p class="text-muted mb-0">Storage Used</p>
              <small class="text-{{ $storageHealth['status'] }}">
                @if ($storageHealth['status'] == 'success')
                  Healthy
                @elseif($storageHealth['status'] == 'warning')
                  Warning
                @else
                  Critical
                @endif
              </small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3">
              <div class="avatar-lg mx-auto mb-3">
                <div
                     class="avatar-title bg-{{ $logHealth['status'] }}-subtle text-{{ $logHealth['status'] }} rounded-circle">
                  <i class="ri-archive-line fs-24"></i>
                </div>
              </div>
              <h4>{{ $logHealth['count'] }}</h4>
              <p class="text-muted mb-0">Today's Posts</p>
              <small class="text-{{ $logHealth['status'] }}">
                @if ($logHealth['status'] == 'success')
                  Normal
                @elseif($logHealth['status'] == 'warning')
                  High
                @else
                  Very High
                @endif
              </small>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3">
              <div class="avatar-lg mx-auto mb-3">
                <div
                     class="avatar-title bg-{{ $errorHealth['status'] }}-subtle text-{{ $errorHealth['status'] }} rounded-circle">
                  <i class="ri-error-warning-line fs-24"></i>
                </div>
              </div>
              <h4>{{ $errorHealth['count'] }}</h4>
              <p class="text-muted mb-0">Recent Errors</p>
              <small class="text-{{ $errorHealth['status'] }}">
                @if ($errorHealth['status'] == 'success')
                  No Issues
                @elseif($errorHealth['status'] == 'warning')
                  Some Issues
                @else
                  Many Issues
                @endif
              </small>
            </div>
          </div>
        </div>
      </x-data-display.card>
    </div>
  </div>

  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var options = {
          series: [{
            name: 'Successful Posts',
            data: @json($chartData['successful'])
          }, {
            name: 'Failed Posts',
            data: @json($chartData['failed'])
          }],
          chart: {
            type: 'bar',
            height: 300,
            toolbar: {
              show: false
            }
          },
          colors: ['#0ab39c', '#f06548'],
          plotOptions: {
            bar: {
              horizontal: false,
              columnWidth: '55%',
              endingShape: 'rounded'
            },
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
          },
          xaxis: {
            categories: @json($chartData['dates']),
          },
          yaxis: {
            title: {
              text: 'Number of Posts'
            }
          },
          fill: {
            opacity: 1
          },
          tooltip: {
            y: {
              formatter: function(val) {
                return val + " posts"
              }
            }
          },
          legend: {
            position: 'top',
            horizontalAlign: 'right',
          },
          grid: {
            borderColor: '#f1f1f1',
          }
        };

        var chart = new ApexCharts(document.querySelector("#performanceChart"), options);
        chart.render();
      });
    </script>
  @endpush
</x-layouts.admin.master>

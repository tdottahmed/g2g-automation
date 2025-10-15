<x-layouts.admin.master>
  <div class="row">
    {{-- Key Metrics --}}
    <div class="col-xl-3 col-md-6">
      <x-data-display.stat-card icon="ri-shopping-bag-line" label="Total Offers Posted" value="{{ $totalOffersPosted }}"
                                trend="{{ $offerTrend }}" trend-type="{{ $offerTrend >= 0 ? 'up' : 'down' }}"
                                bg-class="bg-primary" />
    </div>

    <div class="col-xl-3 col-md-6">
      <x-data-display.stat-card icon="ri-file-text-line" label="Active Templates" value="{{ $activeTemplates }}"
                                subtitle="Out of {{ $totalTemplates }}" bg-class="bg-success" />
    </div>

    <div class="col-xl-3 col-md-6">
      <x-data-display.stat-card icon="ri-checkbox-circle-line" label="Success Rate" value="{{ $successRate }}%"
                                subtitle="Last 7 days" bg-class="bg-info" />
    </div>

    <div class="col-xl-3 col-md-6">
      <x-data-display.stat-card icon="ri-time-line" label="Avg. Execution Time" value="{{ $avgExecutionTime }}s"
                                subtitle="Per offer" bg-class="bg-warning" />
    </div>
  </div>
  <div class="row mt-4">
    {{-- Recent Activity & Performance Chart --}}
    <div class="col-xl-8">
      <x-data-display.card>
        <x-slot name="header">
          <h5 class="card-title">
            <i class="ri-bar-chart-line me-2"></i>Offer Performance (Last 7 Days)
          </h5>
        </x-slot>
        <div id="performanceChart" style="height: 300px;"></div>
      </x-data-display.card>
    </div>

    {{-- Scheduler Status --}}
    <div class="col-xl-4">
      <x-data-display.card>
        <x-slot name="header">
          <h5 class="card-title">
            <i class="ri-timer-line me-2"></i>Scheduler Status
          </h5>
        </x-slot>
        <div class="py-3 text-center">
          @if ($isSchedulerActive)
            <div class="avatar-lg mx-auto mb-3">
              <div class="avatar-title bg-success-subtle text-success rounded-circle">
                <i class="ri-play-circle-line fs-24"></i>
              </div>
            </div>
            <h4 class="text-success">Active</h4>
            <p class="text-muted mb-2">Currently within scheduler windows</p>
            <div class="mt-3">
              <small class="text-muted">Next run in:</small>
              <h5 class="text-primary">{{ $nextRunIn }}</h5>
            </div>
          @else
            <div class="avatar-lg mx-auto mb-3">
              <div class="avatar-title bg-secondary-subtle text-secondary rounded-circle">
                <i class="ri-pause-circle-line fs-24"></i>
              </div>
            </div>
            <h4 class="text-secondary">Inactive</h4>
            <p class="text-muted mb-2">Outside scheduler windows</p>
            <div class="mt-3">
              <small class="text-muted">Next window starts:</small>
              <h5 class="text-primary">{{ $nextWindowStart }}</h5>
            </div>
          @endif
        </div>

        <div class="mt-4">
          <h6 class="fw-semibold">Active Windows Today:</h6>
          <div class="mt-2">
            @forelse($todayWindows as $window)
              <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <span class="text-muted">{{ $window['start'] }} - {{ $window['end'] }}</span>
                @if ($window['is_active'])
                  <span class="badge bg-success-subtle text-success">Now</span>
                @elseif($window['is_upcoming'])
                  <span class="badge bg-warning-subtle text-warning">Upcoming</span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary">Completed</span>
                @endif
              </div>
            @empty
              <p class="text-muted mb-0 text-center">No windows configured</p>
            @endforelse
          </div>
        </div>
      </x-data-display.card>
    </div>
  </div>
  </div>

  <div class="row mt-4">
    {{-- Recent Activity --}}
    <div class="col-xl-6">
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
                      @if ($log->template)
                        {{ Str::limit($log->template->title, 40) }}
                      @else
                        <span class="text-muted">Template Deleted</span>
                      @endif
                    </h6>
                    <p class="text-muted small mb-0">{{ $log->executed_at->diffForHumans() }}</p>
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

    {{-- Template Performance --}}
    <div class="col-xl-6">
      <x-data-display.card>
        <x-slot name="header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title">
              <i class="ri-trophy-line me-2"></i>Top Performing Templates
            </h5>
            <a href="{{ route('offer-templates.index') }}" class="btn btn-sm btn-outline-primary">
              View All
            </a>
          </div>
        </x-slot>
        <div class="table-responsive">
          <table class="table-borderless table-centered mb-0 table">
            <tbody>
              @forelse($topTemplates as $template)
                <tr>
                  <td style="width: 50px;">
                    <div class="avatar-sm">
                      <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                        <i class="ri-file-text-line"></i>
                      </span>
                    </div>
                  </td>
                  <td>
                    <h6 class="mb-1">{{ Str::limit($template->title, 35) }}</h6>
                    <p class="text-muted small mb-0">
                      {{ $template->success_count }} successful posts
                    </p>
                  </td>
                  <td class="text-end">
                    <span class="badge bg-success-subtle text-success">
                      {{ $template->success_rate }}% success
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="py-4 text-center">
                    <div class="text-muted">
                      <i class="ri-file-text-line display-4"></i>
                      <p class="mt-2">No template data</p>
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

  <div class="row mt-4">
    {{-- System Health --}}
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
              <p class="text-muted mb-0">Today's Logs</p>
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
            name: 'Successful',
            data: @json($chartData['successful'])
          }, {
            name: 'Failed',
            data: @json($chartData['failed'])
          }],
          chart: {
            type: 'area',
            height: 300,
            toolbar: {
              show: false
            }
          },
          colors: ['#0ab39c', '#f06548'],
          dataLabels: {
            enabled: false
          },
          stroke: {
            curve: 'smooth',
            width: 2
          },
          fill: {
            type: 'gradient',
            gradient: {
              shadeIntensity: 1,
              inverseColors: false,
              opacityFrom: 0.45,
              opacityTo: 0.05,
              stops: [20, 100, 100, 100]
            },
          },
          xaxis: {
            categories: @json($chartData['dates']),
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

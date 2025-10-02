<x-layouts.admin.master>
  <div class="row">
    <div class="col-12">
      <!-- Header Section -->
      <div class="page-header">
        <div class="row align-items-center">
          <div class="col">
            <div class="page-pretitle">Automation</div>
            <h1 class="page-title">
              <i class="ri-robot-line text-primary me-2"></i>
              Automation Dashboard
            </h1>
          </div>
          <div class="col-auto">
            <div class="btn-list">
              <button class="btn btn-outline-secondary" onclick="refreshDashboard()">
                <i class="ri-refresh-line me-1"></i> Refresh
              </button>
              <a href="{{ route('automation.logs') }}" class="btn btn-outline-info">
                <i class="ri-history-line me-1"></i> View Logs
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row row-deck">
        <div class="col-sm-6 col-lg-3">
          <div class="card card-sm">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="bg-primary avatar text-white">
                    <i class="ri-user-line"></i>
                  </span>
                </div>
                <div class="col">
                  <div class="font-weight-medium">{{ $accountsWithStatus->count() }} User Accounts</div>
                  <div class="text-muted">Total registered accounts</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card card-sm">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="bg-success avatar text-white">
                    <i class="ri-play-circle-line"></i>
                  </span>
                </div>
                <div class="col">
                  <div class="font-weight-medium">
                    {{ $accountsWithStatus->where('automation_status.is_running', true)->count() }} Active</div>
                  <div class="text-muted">Running sessions</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card card-sm">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="bg-info avatar text-white">
                    <i class="ri-file-list-line"></i>
                  </span>
                </div>
                <div class="col">
                  <div class="font-weight-medium">{{ $accountsWithStatus->sum('templates_count') }} Templates</div>
                  <div class="text-muted">Active templates</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="card card-sm">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="bg-warning avatar text-white">
                    <i class="ri-calendar-line"></i>
                  </span>
                </div>
                <div class="col">
                  <div class="font-weight-medium">{{ $recentSessions->count() }} Sessions</div>
                  <div class="text-muted">Today's activity</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Accounts Section -->
      <div class="card mt-4">
        <div class="card-header">
          <h3 class="card-title">
            <i class="ri-user-line me-2"></i>
            User Accounts
          </h3>
        </div>
        <div class="card-body">
          @if ($accountsWithStatus->count() > 0)
            <div class="row">
              @foreach ($accountsWithStatus as $account)
                <div class="col-md-6 col-xl-4">
                  <div
                       class="card card-sm {{ $account->automation_status['is_running'] ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                      <div class="d-flex align-items-center mb-3">
                        <span class="avatar bg-blue-lt me-3">
                          <i class="ri-user-3-line text-blue"></i>
                        </span>
                        <div class="flex-fill">
                          <div class="font-weight-medium text-truncate">{{ $account->email }}</div>
                          <div class="text-muted">
                            {{ $account->templates_count }} active templates
                          </div>
                        </div>
                        <span
                              class="badge {{ $account->automation_status['is_running'] ? 'bg-success' : 'bg-secondary' }}">
                          {{ $account->automation_status['is_running'] ? 'Running' : 'Stopped' }}
                        </span>
                      </div>

                      @if ($account->automation_status['is_running'])
                        <div class="mb-3">
                          <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted">Progress</span>
                            <span
                                  class="text-muted small">{{ $account->automation_status['progress_percentage'] }}%</span>
                          </div>
                          <div class="progress progress-sm">
                            <div class="progress-bar progress-bar-indeterminate bg-success"
                                 style="width: {{ $account->automation_status['progress_percentage'] }}%">
                            </div>
                          </div>
                          <div class="small text-muted mt-1 text-center">
                            {{ $account->automation_status['processed'] }}/{{ $account->automation_status['total_templates'] }}
                            templates completed
                          </div>
                        </div>
                      @endif

                      <div class="d-flex gap-2">
                        @if (!$account->automation_status['is_running'])
                          <button class="btn btn-success btn-sm start-automation flex-fill"
                                  data-account-id="{{ $account->id }}">
                            <i class="ri-play-line me-1"></i> Start Automation
                          </button>
                        @else
                          <button class="btn btn-danger btn-sm stop-automation flex-fill"
                                  data-account-id="{{ $account->id }}">
                            <i class="ri-stop-line me-1"></i> Stop Automation
                          </button>
                          <a href="{{ route('automation.session-details', $account->automation_status['session']['id']) }}"
                             class="btn btn-info btn-sm">
                            <i class="ri-eye-line"></i>
                          </a>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="empty">
              <div class="empty-icon">
                <i class="ri-user-line" style="font-size: 3rem;"></i>
              </div>
              <p class="empty-title">No user accounts found</p>
              <p class="empty-subtitle text-muted">
                Add user accounts to start automation.
              </p>
            </div>
          @endif
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="row mt-4">
        <!-- Recent Sessions -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="ri-history-line me-2"></i>
                Recent Sessions
              </h3>
            </div>
            <div class="card-body">
              @if ($recentSessions->count() > 0)
                <div class="divide-y">
                  @foreach ($recentSessions as $session)
                    <div class="py-3">
                      <div class="d-flex align-items-center">
                        <span
                              class="avatar avatar-sm {{ $session->status === 'completed'
                                  ? 'bg-success'
                                  : ($session->status === 'running'
                                      ? 'bg-warning'
                                      : 'bg-danger') }} me-3">
                          <i
                             class="ri-{{ $session->status === 'completed'
                                 ? 'check-line'
                                 : ($session->status === 'running'
                                     ? 'play-line'
                                     : 'close-line') }}"></i>
                        </span>
                        <div class="flex-fill">
                          <div class="font-weight-medium">{{ $session->userAccount->email }}</div>
                          <div class="text-muted small">
                            {{ $session->processed_templates }}/{{ $session->total_templates }} templates •
                            {{ $session->successful_posts }} successful
                          </div>
                        </div>
                        <div class="text-end">
                          <span
                                class="badge {{ $session->status === 'completed'
                                    ? 'bg-success'
                                    : ($session->status === 'running'
                                        ? 'bg-warning'
                                        : 'bg-danger') }}">
                            {{ ucfirst($session->status) }}
                          </span>
                          <div class="text-muted small mt-1">
                            {{ $session->created_at->diffForHumans() }}
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="py-4 text-center">
                  <i class="ri-inbox-line" style="font-size: 3rem;"></i>
                  <p class="text-muted mt-2">No recent sessions</p>
                </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Recent Logs -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="ri-clipboard-line me-2"></i>
                Recent Logs
              </h3>
            </div>
            <div class="card-body">
              @if ($recentLogs->count() > 0)
                <div class="divide-y">
                  @foreach ($recentLogs as $log)
                    <div class="py-3">
                      <div class="d-flex align-items-start">
                        <span
                              class="avatar avatar-sm {{ $log->status === 'success' ? 'bg-success' : 'bg-danger' }} me-3">
                          <i class="ri-{{ $log->status === 'success' ? 'check-line' : 'close-line' }}"></i>
                        </span>
                        <div class="flex-fill">
                          <div class="font-weight-medium">{{ Str::limit($log->message, 70) }}</div>
                          <div class="text-muted small">
                            {{ $log->template->title ?? 'Unknown Template' }}
                          </div>
                        </div>
                        <div class="text-muted small ms-3 text-nowrap">
                          {{ $log->created_at->diffForHumans() }}
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="py-4 text-center">
                  <i class="ri-file-list-line" style="font-size: 3rem;"></i>
                  <p class="text-muted mt-2">No recent logs</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function refreshDashboard() {
        window.location.reload();
      }

      // Start automation for all templates
      $(document).on('click', '.start-automation', function() {
        const accountId = $(this).data('account-id');
        const button = $(this);

        // Show loading state
        button.prop('disabled', true).html('<i class="ri-loader-4-line animate-spin me-1"></i> Starting...');

        $.ajax({
          url: '{{ route('automation.start') }}',
          method: 'POST',
          data: {
            user_account_id: accountId,
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              showAlert('Automation started successfully! All templates will be processed.', 'success');
              setTimeout(() => {
                window.location.reload();
              }, 1500);
            } else {
              showAlert(response.message, 'error');
              button.prop('disabled', false).html('<i class="ri-play-line me-1"></i> Start Automation');
            }
          },
          error: function(xhr) {
            showAlert('Failed to start automation. Please try again.', 'error');
            button.prop('disabled', false).html('<i class="ri-play-line me-1"></i> Start Automation');
          }
        });
      });

      // Stop automation
      $(document).on('click', '.stop-automation', function() {
        const accountId = $(this).data('account-id');
        const button = $(this);

        if (!confirm('Are you sure you want to stop automation for this account? All current progress will be lost.')) {
          return;
        }

        // Show loading state
        button.prop('disabled', true).html('<i class="ri-loader-4-line animate-spin me-1"></i> Stopping...');

        $.ajax({
          url: '{{ route('automation.stop') }}',
          method: 'POST',
          data: {
            user_account_id: accountId,
            _token: '{{ csrf_token() }}'
          },
          success: function(response) {
            if (response.success) {
              showAlert('Automation stopped successfully!', 'success');
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            } else {
              showAlert(response.message, 'error');
              button.prop('disabled', false).html('<i class="ri-stop-line me-1"></i> Stop Automation');
            }
          },
          error: function(xhr) {
            showAlert('Failed to stop automation. Please try again.', 'error');
            button.prop('disabled', false).html('<i class="ri-stop-line me-1"></i> Stop Automation');
          }
        });
      });

      // Alert function with Velzone styling
      function showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' :
          type === 'error' ? 'alert-danger' :
          type === 'warning' ? 'alert-warning' : 'alert-info';

        const icon = type === 'success' ? 'ri-checkbox-circle-line' :
          type === 'error' ? 'ri-error-warning-line' :
          type === 'warning' ? 'ri-alert-line' : 'ri-information-line';

        const alert = $(`
          <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999; min-width: 300px;">
            <div class="d-flex">
              <div>
                <i class="${icon} me-2"></i>
                ${message}
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          </div>
        `);

        $('body').append(alert);

        setTimeout(() => {
          alert.alert('close');
        }, 5000);
      }

      //   // Auto-refresh for running sessions
      //   @if ($accountsWithStatus->where('automation_status.is_running', true)->count() > 0)
      //     setInterval(() => {
      //       refreshDashboard();
      //     }, 10000); // Refresh every 10 seconds if there are running sessions
      //   @endif
    </script>
  @endpush
</x-layouts.admin.master>

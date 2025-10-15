<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">
          <i class="ri-history-line me-2"></i>{{ __('Offer Automation Logs') }}
        </h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" id="refreshLogs">
            <i class="ri-refresh-line"></i> {{ __('Refresh') }}
          </button>
          <form action="{{ route('offer-logs.clear') }}" method="POST" class="d-inline-block">
            @csrf
            <button type="button" class="btn btn-outline-danger" id="clearLogs">
              <i class="ri-delete-bin-line"></i> {{ __('Clear Logs') }}
            </button>
          </form>
        </div>
      </div>
    </x-slot>

    {{-- Filters --}}
    <div class="row mb-3">
      <div class="col-md-3">
        <label class="form-label">{{ __('Status') }}</label>
        <select class="form-select" id="statusFilter">
          <option value="">{{ __('All Status') }}</option>
          <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('Success') }}</option>
          <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('From Date') }}</label>
        <input type="date" class="form-control" id="fromDateFilter" value="{{ request('from_date') }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('To Date') }}</label>
        <input type="date" class="form-control" id="toDateFilter" value="{{ request('to_date') }}">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="button" class="btn btn-outline-primary w-100" id="applyFilters">
          <i class="ri-filter-line me-1"></i> {{ __('Apply Filters') }}
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table-hover table align-middle">
        <thead class="table-light">
          <tr>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Message') }}</th>
            <th>{{ __('Templates') }}</th>
            <th>{{ __('Duration') }}</th>
            <th>{{ __('Posted At') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>

        <tbody>
          @forelse ($logs as $log)
            @php
              $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
              $templatesCount = $details['templates_count'] ?? 0;
              $executionTime = $details['execution_time_seconds'] ?? 0;
              $templates = collect($details['templates'] ?? []);
              $successCount = $templates->where('status', 'completed')->count();
              $userAccount = isset($details['user_account_id'])
                  ? findUserAccountById($details['user_account_id'])
                  : null;
            @endphp

            <tr>
              {{-- ✅ Status & Account --}}
              <td>
                @if ($userAccount)
                  <small class="d-block text-muted">
                    Account #{{ $userAccount->owner_name ?? 'N/A' }} ({{ $userAccount->email ?? 'N/A' }})
                  </small>
                @endif

                <span
                      class="badge rounded-pill bg-{{ $log->status === 'success' ? 'success' : 'danger' }}-subtle text-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                  <i class="ri-{{ $log->status === 'success' ? 'check' : 'close' }}-line me-1"></i>
                  {{ ucfirst($log->status) }}
                </span>
              </td>

              {{-- ✅ Message --}}
              <td>
                <span class="fw-medium d-block">
                  {{ Str::limit($log->message, 70) }}
                </span>
              </td>

              {{-- ✅ Templates --}}
              <td class="text-center">
                <span class="fw-bold text-primary">{{ $successCount }}/{{ $templatesCount }}</span>
                <br>
                <small class="text-muted">{{ __('completed') }}</small>
              </td>

              {{-- ✅ Duration --}}
              <td>
                <span class="text-muted">{{ number_format($executionTime, 2) }}s</span>
              </td>

              {{-- ✅ Posted At --}}
              <td>
                @php
                  $executedAt = $log->executed_at ? \Carbon\Carbon::parse($log->executed_at) : now();
                @endphp
                <div class="text-nowrap">
                  <div class="fw-medium">{{ $executedAt->format('M j, Y') }}</div>
                  <small class="text-muted">{{ $executedAt->format('g:i A') }}</small>
                </div>
              </td>

              {{-- ✅ Actions --}}
              <td>
                <a href="{{ route('offer-logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary">
                  <i class="ri-eye-line"></i> {{ __('View') }}
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-5 text-center">
                <div class="text-muted">
                  <i class="ri-inbox-line display-4"></i>
                  <h5 class="mt-2">{{ __('No logs found') }}</h5>
                  <p class="mb-0">{{ __('Automation logs will appear here after offers are processed.') }}</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($logs->hasPages())
      <div class="mt-3">
        {{ $logs->links('pagination::bootstrap-5') }}
      </div>
    @endif
  </x-data-display.card>

  @push('scripts')
    <script>
      $(document).ready(function() {
        // Refresh logs
        $('#refreshLogs').on('click', function() {
          window.location.reload();
        });

        // Apply filters
        $('#applyFilters').on('click', function() {
          applyFilters();
        });

        // Apply filters on Enter key in date fields
        $('#fromDateFilter, #toDateFilter').on('keypress', function(e) {
          if (e.which === 13) {
            applyFilters();
          }
        });

        function applyFilters() {
          const status = $('#statusFilter').val();
          const fromDate = $('#fromDateFilter').val();
          const toDate = $('#toDateFilter').val();

          // Build URL with filters
          let url = new URL(window.location.href);
          let params = new URLSearchParams();

          if (status) params.append('status', status);
          if (fromDate) params.append('from_date', fromDate);
          if (toDate) params.append('to_date', toDate);

          window.location.href = url.pathname + '?' + params.toString();
        }

        // Clear logs
        $('#clearLogs').on('click', function() {
          const button = $(this);

          Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
            if (result.isConfirmed) {
              // Disable buttons
              button.prop('disabled', true);
              $('#refreshLogs').prop('disabled', true);

              // Add loading state
              button.html('<i class="ri-loader-4-line ri-spin"></i> Deleting...');

              // Submit the parent form
              button.closest('form').submit();
            }
          });
        });
      });
    </script>
  @endpush
</x-layouts.admin.master>

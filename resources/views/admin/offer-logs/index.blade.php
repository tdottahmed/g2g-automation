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
          <option value="success">{{ __('Success') }}</option>
          <option value="failed">{{ __('Failed') }}</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('Template') }}</label>
        <select class="form-select" id="templateFilter">
          <option value="">{{ __('All Templates') }}</option>
          @foreach ($templates as $template)
            <option value="{{ $template->id }}">{{ $template->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('From Date') }}</label>
        <input type="date" class="form-control" id="fromDateFilter">
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('To Date') }}</label>
        <input type="date" class="form-control" id="toDateFilter">
      </div>
    </div>

    <table class="table-responsive table">
      <thead>
        <th>{{ __('Template') }}</th>
        <th>{{ __('Status') }}</th>
        <th>{{ __('Message') }}</th>
        <th>{{ __('Duration') }}</th>
        <th>{{ __('Executed At') }}</th>
        <th>{{ __('Actions') }}</th>
      </thead>

      <tbody>
        @forelse ($logs as $log)
          <tr>
            <td>
              @if ($log->template)
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <i class="ri-file-text-line text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-2">
                    <span class="fw-semibold">{{ Str::limit($log->template->title, 50) }}</span>
                    <br>
                    <small class="text-muted">ID: {{ $log->template->id }}</small>
                  </div>
                </div>
              @else
                <span class="text-muted">{{ __('Template Deleted') }}</span>
              @endif
            </td>
            <td>
              <span
                    class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}-subtle text-{{ $log->status === 'success' ? 'success' : 'danger' }} rounded-pill">
                <i class="ri-{{ $log->status === 'success' ? 'check' : 'close' }}-line me-1"></i>
                {{ ucfirst($log->status) }}
              </span>
            </td>
            <td>
              <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $log->message }}">
                {{ Str::limit($log->message, 60) }}
              </span>
            </td>
            <td>
              @if (isset($log->details['execution_time_seconds']))
                <span class="text-muted">{{ number_format($log->details['execution_time_seconds'], 2) }}s</span>
              @else
                <span class="text-muted">N/A</span>
              @endif
            </td>
            <td>
              <div class="text-nowrap">
                {{ $log->executed_at->format('M j, Y') }}<br>
                <small class="text-muted">{{ $log->executed_at->format('g:i A') }}</small>
              </div>
            </td>
            <td>
              <a href="{{ route('offer-logs.show', $log->id) }}" class="btn btn-primary btn-sm">
                <i class="ri-eye-line"></i> {{ __('View Details') }}
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="py-4 text-center">
              <div class="text-muted">
                <i class="ri-inbox-line display-4"></i>
                <h5 class="mt-2">{{ __('No logs found') }}</h5>
                <p>{{ __('Automation logs will appear here after offers are processed.') }}</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>

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

        // Simple client-side filtering (you can enhance with AJAX)
        function applyFilters() {
          const status = $('#statusFilter').val();
          const template = $('#templateFilter').val();
          const fromDate = $('#fromDateFilter').val();
          const toDate = $('#toDateFilter').val();

          // Build URL with filters
          let url = new URL(window.location.href);
          let params = new URLSearchParams();

          if (status) params.append('status', status);
          if (template) params.append('template', template);
          if (fromDate) params.append('from_date', fromDate);
          if (toDate) params.append('to_date', toDate);

          window.location.href = url.pathname + '?' + params.toString();
        }

        // Apply filters on change
        $('#statusFilter, #templateFilter, #fromDateFilter, #toDateFilter').on('change', function() {
          applyFilters();
        });
      });

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
    </script>
  @endpush
</x-layouts.admin.master>

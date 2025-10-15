<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">
          <i class="ri-information-line me-2"></i>{{ __('Automation Log Details') }}
        </h5>
        <div class="d-flex gap-2">
          <a href="{{ route('offer-logs.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line"></i> {{ __('Back to Logs') }}
          </a>
        </div>
      </div>
    </x-slot>
    @php
      $details = is_array($log->details) ? $log->details : json_decode($log->details, true);
    @endphp

    {{-- Basic Information --}}
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card bg-light border-0">
          <div class="card-body">
            <h6 class="card-title mb-3">{{ __('Execution Summary') }}</h6>
            <div class="row">
              <div class="col-6 mb-2">
                <small class="text-muted d-block">{{ __('Status') }}</small>
                <span class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                  {{ ucfirst($log->status) }}
                </span>
              </div>
              <div class="col-6 mb-2">
                <small class="text-muted d-block">{{ __('User Account') }}</small>
                @php
                  $userAccount = findUserAccountById($details['user_account_id']);
                @endphp
                <strong>#{{ $userAccount ? $userAccount->owner_name : 'N/A' }}</strong>
                ({{ $userAccount ? $userAccount->email : 'N/A' }})
              </div>
              <div class="col-6 mb-2">
                <small class="text-muted d-block">{{ __('Templates') }}</small>
                <strong>{{ $details['templates_count'] ?? 0 }} total</strong>
              </div>
              <div class="col-6 mb-2">
                <small class="text-muted d-block">{{ __('Attempt') }}</small>
                <strong>{{ $details['attempt'] ?? 1 }}</strong>
              </div>
              <div class="col-6 mb-2">
                <small class="text-muted d-block">{{ __('Duration') }}</small>
                <strong>{{ number_format($details['execution_time_seconds'] ?? 0, 2) }}s</strong>
              </div>
              <div class="col-6 mb-2">
                <small class="text-muted d-block">{{ __('Started At') }}</small>
                <strong>{{ $details['started_at'] ?? $log->executed_at->format('M j, Y g:i A') }}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card bg-light border-0">
          <div class="card-body">
            <h6 class="card-title mb-3">{{ __('Process Message') }}</h6>
            <p class="mb-0">{{ $log->message }}</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Execution Steps --}}
    @if (isset($details['steps']))
      <div class="row mb-4">
        <div class="col-12">
          <h6 class="mb-3">{{ __('Execution Steps') }}</h6>
          <div class="list-group">
            @foreach ($details['steps'] as $stepName => $step)
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="text-capitalize fw-semibold">{{ str_replace('_', ' ', $stepName) }}</span>
                    @if (isset($step['timestamp']))
                      <small class="text-muted ms-2">({{ $step['timestamp'] }})</small>
                    @endif
                  </div>
                  <span class="badge bg-{{ $step['status'] === 'completed' ? 'success' : 'danger' }}">
                    {{ ucfirst($step['status']) }}
                  </span>
                </div>
                @if ($stepName === 'configuration' && isset($step['templates_prepared']))
                  <small class="text-muted">Templates prepared: {{ $step['templates_prepared'] }}</small>
                @endif
                @if ($stepName === 'node_execution' && isset($step['exit_code']))
                  <small class="text-muted">Exit code: {{ $step['exit_code'] }}</small>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    {{-- Templates --}}
    @if (isset($details['templates']) && count($details['templates']) > 0)
      <div class="row mb-4">
        <div class="col-12">
          <h6 class="mb-3">{{ __('Templates Processed') }} ({{ count($details['templates']) }})</h6>
          <div class="table-responsive">
            <table class="table-bordered table-striped table">
              <thead>
                <tr>
                  <th width="80">{{ __('ID') }}</th>
                  <th>{{ __('Title') }}</th>
                  <th width="120">{{ __('Status') }}</th>
                  <th width="100">{{ __('Completed At') }}</th>
                  <th width="200">{{ __('Error') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($details['templates'] as $template)
                  <tr>
                    <td>{{ $template['id'] }}</td>
                    <td>
                      <div style="max-width: 500px; word-wrap: break-word;">
                        {{ $template['title'] }}
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-{{ $template['status'] === 'success' ? 'success' : 'danger' }}">
                        {{ ucfirst($template['status']) }}
                      </span>
                    </td>
                    <td>
                      <div class="text-nowrap">
                        <div class="fw-medium">
                          {{ \Carbon\Carbon::parse($template['completed_at'] ?? now())->format('M j, Y') }}
                        </div>
                      </div>

                    </td>
                    <td>
                      @if ($template['status'] === 'failed' && isset($template['error']))
                        <span class="text-danger small">{{ $template['error'] }}</span>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

    {{-- Process Output --}}
    @if (
        (isset($details['node_output']) && !empty($details['node_output'])) ||
            (isset($details['node_error']) && !empty($details['node_error'])))
      <div class="row mb-4">
        <div class="col-12">
          <h6 class="mb-3">{{ __('Process Output') }}</h6>
          <div class="row">
            @if (isset($details['node_output']) && !empty($details['node_output']))
              <div class="col-md-12">
                <label class="form-label">{{ __('Output') }}</label>
                <pre class="bg-light rounded p-3" style="max-height: 200px; overflow-y: auto;"><code>{{ $details['node_output'] }}</code></pre>
              </div>
            @endif
            @if (isset($details['node_error']) && !empty($details['node_error']))
              <div class="col-md-12">
                <label class="form-label text-danger">{{ __('Error Output') }}</label>
                <pre class="bg-danger rounded p-3 text-white" style="max-height: 200px; overflow-y: auto;"><code>{{ $details['node_error'] }}</code></pre>
              </div>
            @endif
          </div>
        </div>
      </div>
    @endif

    {{-- Raw JSON Data --}}
    <div class="row">
      <div class="col-12">
        <h6 class="mb-3">{{ __('Raw Data') }}</h6>
        <pre class="bg-light rounded p-3" style="max-height: 400px; overflow-y: auto;"><code>@json($details, JSON_PRETTY_PRINT)</code></pre>
      </div>
    </div>

  </x-data-display.card>
</x-layouts.admin.master>

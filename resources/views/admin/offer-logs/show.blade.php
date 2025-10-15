<x-layouts.admin.master>
  <div class="row">
    <div class="col-12">
      <x-data-display.card>
        <x-slot name="header">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">
                <i class="ri-information-line me-2"></i>{{ __('Offer Log Details') }}
              </h5>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item">
                    <a href="{{ route('offer-logs.index') }}">{{ __('Offer Logs') }}</a>
                  </li>
                  <li class="breadcrumb-item active">#{{ $log->id }}</li>
                </ol>
              </nav>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('offer-logs.index') }}" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> {{ __('Back to Logs') }}
              </a>
            </div>
          </div>
        </x-slot>

        <div class="row">
          {{-- Basic Information --}}
          <div class="col-md-6">
            <x-data-display.card class="h-100">
              <x-slot name="header">
                <h6 class="card-title mb-0">
                  <i class="ri-file-text-line me-2"></i>{{ __('Basic Information') }}
                </h6>
              </x-slot>

              <div class="table-responsive">
                <table class="table-borderless mb-0 table">
                  <tbody>
                    <tr>
                      <td class="fw-semibold" style="width: 140px;">{{ __('Log ID') }}:</td>
                      <td>#{{ $log->id }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Status') }}:</td>
                      <td>
                        <span
                              class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}-subtle text-{{ $log->status === 'success' ? 'success' : 'danger' }} rounded-pill">
                          <i class="ri-{{ $log->status === 'success' ? 'check' : 'close' }}-line me-1"></i>
                          {{ ucfirst($log->status) }}
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Template') }}:</td>
                      <td>
                        @if ($log->template)
                          <a href="{{ route('offer-templates.edit', $log->template->id) }}" class="text-primary">
                            {{ $log->template->title }}
                          </a>
                          <br>
                          <small class="text-muted">ID: {{ $log->template->id }}</small>
                        @else
                          <span class="text-muted">{{ __('Template Deleted') }}</span>
                        @endif
                      </td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Executed At') }}:</td>
                      <td>
                        {{ $log->executed_at->format('F j, Y g:i A') }}<br>
                        <small class="text-muted">{{ $log->executed_at->diffForHumans() }}</small>
                      </td>
                    </tr>
                    @if (isset($log->details['execution_time_seconds']))
                      <tr>
                        <td class="fw-semibold">{{ __('Duration') }}:</td>
                        <td>
                          <span class="fw-semibold text-primary">
                            {{ number_format($log->details['execution_time_seconds'], 2) }} seconds
                          </span>
                        </td>
                      </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </x-data-display.card>
          </div>

          {{-- Execution Summary --}}
          <div class="col-md-6">
            <x-data-display.card class="h-100">
              <x-slot name="header">
                <h6 class="card-title mb-0">
                  <i class="ri-play-circle-line me-2"></i>{{ __('Execution Summary') }}
                </h6>
              </x-slot>

              <div class="table-responsive">
                <table class="table-borderless mb-0 table">
                  <tbody>
                    <tr>
                      <td class="fw-semibold" style="width: 140px;">{{ __('Attempt') }}:</td>
                      <td>#{{ $log->details['attempt'] ?? 1 }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Started At') }}:</td>
                      <td>{{ $log->details['started_at'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                      <td class="fw-semibold">{{ __('Completed At') }}:</td>
                      <td>{{ $log->details['completed_at'] ?? ($log->details['failed_at'] ?? 'N/A') }}</td>
                    </tr>
                    @if (isset($log->details['remaining_offers']))
                      <tr>
                        <td class="fw-semibold">{{ __('Remaining Offers') }}:</td>
                        <td>
                          @if ($log->details['remaining_offers'] === 'unlimited')
                            <span class="badge bg-info-subtle text-info">Unlimited</span>
                          @else
                            <span
                                  class="badge bg-primary-subtle text-primary">{{ $log->details['remaining_offers'] }}</span>
                          @endif
                        </td>
                      </tr>
                    @endif
                    @if (isset($log->details['template_deactivated']) && $log->details['template_deactivated'])
                      <tr>
                        <td class="fw-semibold">{{ __('Template Status') }}:</td>
                        <td>
                          <span class="badge bg-warning-subtle text-warning">
                            <i class="ri-alert-line me-1"></i> Auto-Deactivated
                          </span>
                        </td>
                      </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </x-data-display.card>
          </div>
        </div>

        {{-- Execution Steps --}}
        @if (isset($log->details['steps']))
          <div class="row mt-4">
            <div class="col-12">
              <x-data-display.card>
                <x-slot name="header">
                  <h6 class="card-title mb-0">
                    <i class="ri-list-check-2 me-2"></i>{{ __('Execution Steps') }}
                  </h6>
                </x-slot>

                <div class="timeline">
                  @foreach ($log->details['steps'] as $stepName => $step)
                    <div class="timeline-item">
                      <div class="timeline-line"></div>
                      <div class="timeline-icon">
                        @if ($step['status'] === 'completed')
                          <i class="ri-checkbox-circle-line text-success"></i>
                        @elseif($step['status'] === 'failed')
                          <i class="ri-close-circle-line text-danger"></i>
                        @else
                          <i class="ri-loader-4-line text-warning"></i>
                        @endif
                      </div>
                      <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-center">
                          <h6 class="text-capitalize mb-1">{{ str_replace('_', ' ', $stepName) }}</h6>
                          <span
                                class="badge bg-{{ $step['status'] === 'completed' ? 'success' : ($step['status'] === 'failed' ? 'danger' : 'warning') }}-subtle text-{{ $step['status'] === 'completed' ? 'success' : ($step['status'] === 'failed' ? 'danger' : 'warning') }}">
                            {{ ucfirst($step['status']) }}
                          </span>
                        </div>
                        <p class="text-muted mb-1">
                          <small>{{ $step['timestamp'] ?? 'N/A' }}</small>
                        </p>
                        @if (isset($step['media_count']))
                          <p class="mb-0">
                            <small class="text-info">
                              <i class="ri-image-line me-1"></i>
                              {{ $step['media_count'] }} media files processed
                            </small>
                          </p>
                        @endif
                        @if (isset($step['error']))
                          <p class="text-danger mb-0">
                            <small><i class="ri-error-warning-line me-1"></i> {{ $step['error'] }}</small>
                          </p>
                        @endif
                      </div>
                    </div>
                  @endforeach
                </div>
              </x-data-display.card>
            </div>
          </div>
        @endif

        {{-- Node.js Output --}}
        @if (isset($log->details['node_output']))
          <div class="row mt-4">
            <div class="col-12">
              <x-data-display.card>
                <x-slot name="header">
                  <h6 class="card-title mb-0">
                    <i class="ri-terminal-line me-2"></i>{{ __('Node.js Output') }}
                  </h6>
                </x-slot>

                <div class="bg-dark text-light rounded p-3">
                  <pre class="text-light mb-0" style="font-size: 0.875rem; max-height: 400px; overflow-y: auto;"><code>{{ $log->details['node_output'] }}</code></pre>
                </div>
              </x-data-display.card>
            </div>
          </div>
        @endif

        {{-- Error Details --}}
        @if ($log->status === 'failed' && (isset($log->details['exception']) || isset($log->details['node_error'])))
          <div class="row mt-4">
            <div class="col-12">
              <x-data-display.card class="border-danger">
                <x-slot name="header">
                  <h6 class="card-title text-danger mb-0">
                    <i class="ri-error-warning-line me-2"></i>{{ __('Error Details') }}
                  </h6>
                </x-slot>

                <div class="alert alert-danger mb-0">
                  @if (isset($log->details['exception']))
                    <h6 class="alert-heading">{{ __('Exception') }}</h6>
                    <p class="mb-2">{{ $log->details['exception'] }}</p>
                  @endif

                  @if (isset($log->details['node_error']))
                    <h6 class="alert-heading mt-3">{{ __('Node.js Error') }}</h6>
                    <pre class="mb-0" style="font-size: 0.875rem; white-space: pre-wrap;">{{ $log->details['node_error'] }}</pre>
                  @endif
                </div>
              </x-data-display.card>
            </div>
          </div>
        @endif

      </x-data-display.card>
    </div>
  </div>

  @push('styles')
    <style>
      .timeline {
        position: relative;
        padding-left: 30px;
      }

      .timeline-item {
        position: relative;
        padding-bottom: 20px;
      }

      .timeline-line {
        position: absolute;
        left: -30px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
      }

      .timeline-item:last-child .timeline-line {
        display: none;
      }

      .timeline-icon {
        position: absolute;
        left: -40px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
      }

      .timeline-content {
        background: white;
      }
    </style>
  @endpush
</x-layouts.admin.master>

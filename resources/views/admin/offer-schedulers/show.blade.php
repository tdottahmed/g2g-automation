<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Scheduler Details') }}</h5>
        <div>
          <x-action.link href="{{ route('offer-schedulers.edit', $offerScheduler->id) }}" icon="ri-edit-box-line"
                         class="me-2">
            {{ __('Edit') }}
          </x-action.link>
          <x-action.link href="{{ route('offer-schedulers.index') }}" icon="ri-list-check">
            {{ __('Scheduler List') }}
          </x-action.link>
        </div>
      </div>
    </x-slot>

    <div class="row">
      {{-- Basic Information --}}
      <div class="col-md-6">
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="ri-information-line me-1"></i> Basic Information</h6>
          </div>
          <div class="card-body">
            <table class="table-borderless mb-0 table">
              <tr>
                <th width="40%" class="text-muted">Type:</th>
                <td>
                  @if ($offerScheduler->offer_template_id)
                    <span class="badge bg-primary">
                      <i class="ri-file-list-line"></i> Template-Level
                    </span>
                  @else
                    <span class="badge bg-info">
                      <i class="ri-user-line"></i> Account-Level
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Applied To:</th>
                <td>
                  @if ($offerScheduler->offer_template_id)
                    <strong>{{ $offerScheduler->offerTemplate->title ?? '-' }}</strong>
                    <br><small class="text-muted">Template ID: {{ $offerScheduler->offer_template_id }}</small>
                  @else
                    <strong>{{ $offerScheduler->userAccount->owner_name ?? '-' }}</strong>
                    <br><small class="text-muted">{{ $offerScheduler->userAccount->email ?? '' }}</small>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Status:</th>
                <td>
                  @if ($offerScheduler->is_active)
                    <span class="badge bg-success">
                      <i class="ri-checkbox-circle-line"></i> Active
                    </span>
                  @else
                    <span class="badge bg-secondary">
                      <i class="ri-close-circle-line"></i> Inactive
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Created:</th>
                <td>{{ $offerScheduler->created_at->format('M d, Y H:i') }}</td>
              </tr>
              <tr>
                <th class="text-muted">Last Updated:</th>
                <td>{{ $offerScheduler->updated_at->format('M d, Y H:i') }}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      {{-- Time Configuration --}}
      <div class="col-md-6">
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="ri-time-line me-1"></i> Time Configuration</h6>
          </div>
          <div class="card-body">
            <table class="table-borderless mb-0 table">
              <tr>
                <th width="40%" class="text-muted">Time Window:</th>
                <td>
                  <strong>{{ $offerScheduler->start_time }}</strong> to
                  <strong>{{ $offerScheduler->end_time }}</strong>
                </td>
              </tr>
              <tr>
                <th class="text-muted">Timezone:</th>
                <td>
                  <i class="ri-map-pin-line"></i> {{ $offerScheduler->timezone }}
                </td>
              </tr>
              <tr>
                <th class="text-muted">Active Days:</th>
                <td>
                  @if ($offerScheduler->days && count($offerScheduler->days) > 0)
                    @foreach ($offerScheduler->days as $day)
                      <span class="badge badge-soft-primary">{{ ucfirst($day) }}</span>
                    @endforeach
                  @else
                    <span class="badge badge-soft-secondary">All Days</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Window Status:</th>
                <td>
                  @if ($offerScheduler->isWithinTimeWindow())
                    <span class="badge bg-success">
                      <i class="ri-check-line"></i> Currently Within Window
                    </span>
                  @else
                    <span class="badge bg-warning">
                      <i class="ri-time-line"></i> Outside Window
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Today Active:</th>
                <td>
                  @if ($offerScheduler->isActiveToday())
                    <span class="badge bg-success">
                      <i class="ri-check-line"></i> Yes
                    </span>
                  @else
                    <span class="badge bg-secondary">
                      <i class="ri-close-line"></i> No
                    </span>
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      {{-- Rate Limiting --}}
      <div class="col-md-6">
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-warning text-white">
            <h6 class="mb-0"><i class="ri-speed-line me-1"></i> Rate Limiting</h6>
          </div>
          <div class="card-body">
            <table class="table-borderless mb-0 table">
              <tr>
                <th width="40%" class="text-muted">Posts Per Cycle:</th>
                <td><strong>{{ $offerScheduler->posts_per_cycle }}</strong> offers</td>
              </tr>
              <tr>
                <th class="text-muted">Interval:</th>
                <td><strong>{{ $offerScheduler->interval_minutes }}</strong> minutes</td>
              </tr>
              <tr>
                <th class="text-muted">Max Per Day:</th>
                <td>
                  @if ($offerScheduler->max_posts_per_day)
                    <strong>{{ $offerScheduler->max_posts_per_day }}</strong> posts
                  @else
                    <span class="badge bg-info">Unlimited</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Can Run Now:</th>
                <td>
                  @if ($offerScheduler->canRunNow())
                    <span class="badge bg-success">
                      <i class="ri-check-line"></i> Yes
                    </span>
                  @else
                    <span class="badge bg-warning">
                      <i class="ri-time-line"></i> Waiting for Interval
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Should Run:</th>
                <td>
                  @if ($offerScheduler->shouldRun())
                    <span class="badge bg-success fs-6">
                      <i class="ri-checkbox-circle-line"></i> Ready to Run
                    </span>
                  @else
                    <span class="badge bg-secondary fs-6">
                      <i class="ri-close-circle-line"></i> Not Ready
                    </span>
                  @endif
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      {{-- Statistics --}}
      <div class="col-md-6">
        <div class="card mb-3 border-0 shadow-sm">
          <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="ri-bar-chart-line me-1"></i> Statistics</h6>
          </div>
          <div class="card-body">
            <table class="table-borderless mb-0 table">
              <tr>
                <th width="40%" class="text-muted">Posts Today:</th>
                <td>
                  <h4 class="mb-0">
                    <span
                          class="badge bg-{{ $offerScheduler->posts_today >= ($offerScheduler->max_posts_per_day ?? 999) ? 'danger' : 'success' }}">
                      {{ $offerScheduler->posts_today }}
                      @if ($offerScheduler->max_posts_per_day)
                        / {{ $offerScheduler->max_posts_per_day }}
                      @endif
                    </span>
                  </h4>
                </td>
              </tr>
              <tr>
                <th class="text-muted">Counter Date:</th>
                <td>
                  {{ $offerScheduler->posts_today_date ? $offerScheduler->posts_today_date->format('M d, Y') : '-' }}
                </td>
              </tr>
              <tr>
                <th class="text-muted">Last Run:</th>
                <td>
                  @if ($offerScheduler->last_run_at)
                    {{ $offerScheduler->last_run_at->format('M d, Y H:i:s') }}
                    <br><small class="text-muted">{{ $offerScheduler->last_run_at->diffForHumans() }}</small>
                  @else
                    <span class="text-muted">Never</span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Daily Limit:</th>
                <td>
                  @if ($offerScheduler->hasReachedDailyLimit())
                    <span class="badge bg-danger">
                      <i class="ri-alert-line"></i> Limit Reached
                    </span>
                  @else
                    <span class="badge bg-success">
                      <i class="ri-check-line"></i> Within Limit
                    </span>
                  @endif
                </td>
              </tr>
              <tr>
                <th class="text-muted">Actions:</th>
                <td>
                  <form action="{{ route('offer-schedulers.reset-counter', $offerScheduler->id) }}" method="POST"
                        class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm"
                            onclick="return confirm('Reset daily counter?')">
                      <i class="ri-restart-line"></i> Reset Counter
                    </button>
                  </form>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Overall Status --}}
    <div class="alert alert-{{ $offerScheduler->shouldRun() ? 'success' : 'warning' }} border-0">
      <div class="d-flex align-items-center">
        <i class="ri-{{ $offerScheduler->shouldRun() ? 'checkbox-circle' : 'alert' }}-line fs-3 me-3"></i>
        <div>
          <h6 class="mb-1">Overall Status</h6>
          <p class="mb-0">
            This scheduler is <strong>{{ $offerScheduler->shouldRun() ? 'READY TO RUN' : 'NOT READY' }}</strong>.
            @if (!$offerScheduler->shouldRun())
              <br>
              <small>
                Reasons:
                @if (!$offerScheduler->is_active)
                  Scheduler is inactive.
                @endif
                @if (!$offerScheduler->isActiveToday())
                  Today is not an active day.
                @endif
                @if (!$offerScheduler->isWithinTimeWindow())
                  Outside time window.
                @endif
                @if ($offerScheduler->hasReachedDailyLimit())
                  Daily limit reached.
                @endif
                @if (!$offerScheduler->canRunNow())
                  Waiting for interval to elapse.
                @endif
              </small>
            @endif
          </p>
        </div>
      </div>
    </div>

  </x-data-display.card>
</x-layouts.admin.master>

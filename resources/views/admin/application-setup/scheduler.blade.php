<div class="tab-pane fade show active" id="scheduler" role="tabpanel" aria-labelledby="scheduler-tab">
  <div class="mb-4">
    <label class="form-label fw-semibold">{{ __('Active Days') }}</label>
    <div class="d-flex flex-wrap gap-3" id="daysContainer">
      @foreach ($days as $val => $label)
        <div class="form-check">
          <input class="form-check-input day-checkbox" type="checkbox" name="schedule_days[]"
                 id="day_{{ $val }}" value="{{ $val }}"
                 {{ in_array($val, $savedDays) ? 'checked' : '' }}>
          <label class="form-check-label" for="day_{{ $val }}">{{ $label }}</label>
        </div>
      @endforeach
    </div>
    <div class="form-text text-muted">{{ __('Select days when scheduling is active') }}</div>
  </div>

  <div class="mb-3">
    <label class="form-label fw-semibold">{{ __('Time Windows') }}</label>
    <div id="schedulerWindowsWrapper" class="mb-3">
      @forelse ($savedWindows as $i => $window)
        <div class="row g-3 align-items-center scheduler-window bg-light mb-3 rounded border p-3">
          <div class="col-md-5">
            <x-data-entry.date-picker type="time" name="scheduler_windows[{{ $i }}][start]"
                                      label="Start Time" value="{{ $window['start'] }}" timeFormat="12" required />
          </div>
          <div class="col-md-5">
            <x-data-entry.date-picker type="time" name="scheduler_windows[{{ $i }}][end]"
                                      label="End Time" value="{{ $window['end'] }}" timeFormat="12" required />
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger remove-window w-100" title="{{ __('Remove window') }}"
                    {{ count($savedWindows) <= 1 ? 'disabled' : '' }}>
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
        </div>
      @empty
        <div class="row g-3 align-items-center scheduler-window bg-light mb-3 rounded border p-3">
          <div class="col-md-5">
            <x-data-entry.date-picker type="time" name="scheduler_windows[0][start]" label="Start Time"
                                      timeFormat="12" required />
          </div>
          <div class="col-md-5">
            <x-data-entry.date-picker type="time" name="scheduler_windows[0][end]" label="End Time" timeFormat="12"
                                      required />
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger remove-window w-100" title="{{ __('Remove window') }}"
                    disabled>
              <i class="ri-delete-bin-line"></i>
            </button>
          </div>
        </div>
      @endforelse
    </div>

    <button type="button" id="addSchedulerWindow" class="btn btn-outline-primary">
      <i class="ri-add-line me-1"></i> {{ __('Add Time Window') }}
    </button>
    <div class="form-text text-muted">{{ __('Define time windows for scheduled posts') }}</div>
  </div>

  <div class="mb-3">
    <x-data-entry.input type="number" name="schedule_interval_minutes" label="Interval (minutes)" placeholder="e.g. 15"
                        min="1" step="1"
                        value="{{ $applicationSetup->where('type', 'schedule_interval_minutes')->first()->value ?? 15 }}"
                        required />
    <div class="form-text text-muted">{{ __('Interval applies between posts within each time window') }}</div>
  </div>
</div>

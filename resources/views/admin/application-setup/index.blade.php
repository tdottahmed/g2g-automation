<x-layouts.admin.master>
  <x-data-display.card>
    @php
      $savedWindows = json_decode($applicationSetup->where('type', 'scheduler_windows')->first()->value ?? '[]', true);
      $savedDays = explode(
          ',',
          $applicationSetup->where('type', 'schedule_days')->first()->value ?? 'mon,tue,wed,thu,fri,sat,sun',
      );
      $days = [
          'mon' => __('Monday'),
          'tue' => __('Tuesday'),
          'wed' => __('Wednesday'),
          'thu' => __('Thursday'),
          'fri' => __('Friday'),
          'sat' => __('Saturday'),
          'sun' => __('Sunday'),
      ];
    @endphp
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title"><i class="ri-settings-3-line me-2"></i>{{ __('Scheduler & App Setup') }}</h5>
      </div>
    </x-slot>

    <x-data-entry.form action="{{ route('applicationSetup.update') }}">
      <ul class="nav nav-tabs nav-fill" id="setupTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="scheduler-tab" data-bs-toggle="tab" data-bs-target="#scheduler"
                  type="button" role="tab" aria-controls="scheduler" aria-selected="true">
            <i class="ri-timer-line me-1"></i> {{ __('Scheduler') }}
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="org-tab" data-bs-toggle="tab" data-bs-target="#org" type="button"
                  role="tab" aria-controls="org" aria-selected="false">
            <i class="ri-building-4-line me-1"></i> {{ __('Organization Info') }}
          </button>
        </li>
      </ul>

      <div class="tab-content mt-4" id="setupTabsContent">
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
                                              label="Start Time" value="{{ $window['start'] }}" timeFormat="12"
                                              required />
                  </div>
                  <div class="col-md-5">
                    <x-data-entry.date-picker type="time" name="scheduler_windows[{{ $i }}][end]"
                                              label="End Time" value="{{ $window['end'] }}" timeFormat="12" required />
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger remove-window w-100"
                            title="{{ __('Remove window') }}" {{ count($savedWindows) <= 1 ? 'disabled' : '' }}>
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
                    <x-data-entry.date-picker type="time" name="scheduler_windows[0][end]" label="End Time"
                                              timeFormat="12" required />
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger remove-window w-100"
                            title="{{ __('Remove window') }}" disabled>
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
            <x-data-entry.input type="number" name="schedule_interval_minutes" label="Interval (minutes)"
                                placeholder="e.g. 15" min="1" step="1"
                                value="{{ $applicationSetup->where('type', 'schedule_interval_minutes')->first()->value ?? 15 }}"
                                required />
            <div class="form-text text-muted">{{ __('Interval applies between posts within each time window') }}</div>
          </div>
        </div>


        <div class="tab-pane fade" id="org" role="tabpanel" aria-labelledby="org-tab">
          <x-data-entry.input type="text" name="app_name" label="Organization Name" placeholder="App Name"
                              value="{{ $applicationSetup->where('type', 'app_name')->first()->value ?? '' }}"
                              required />
          <x-data-entry.input type="email" name="app_email" label="Organization Email" placeholder="App Email"
                              value="{{ $applicationSetup->where('type', 'app_email')->first()->value ?? '' }}"
                              required />
          <x-data-entry.input type="tel" name="app_phone" label="Organization Phone" placeholder="App Phone"
                              value="{{ $applicationSetup->where('type', 'app_phone')->first()->value ?? '' }}"
                              required />
          <x-data-entry.text-area name="app_address" label="Organization Address" placeholder="App Address"
                                  rows="3"
                                  value="{{ $applicationSetup->where('type', 'app_address')->first()->value ?? '' }}">
          </x-data-entry.text-area>
          <div class="img-fluid">
            <img src="{{ $applicationSetup->where('type', 'app_logo')->first()->value ?? '' }}" alt="">
          </div>
          <img class="img-thumbnail" alt="Logo" width="200"
               src="{{ getFilePath($applicationSetup->where('type', 'app_logo')->first()->value ?? '') }}"
               data-holder-rendered="true">
          <x-data-entry.uploader-filepond name="app_logo" label="Organization Logo" />
          <img class="img-thumbnail" alt="Favicon" width="80"
               src="{{ getFilePath($applicationSetup->where('type', 'app_favicon')->first()->value ?? '') }}"
               data-holder-rendered="true">
          <x-data-entry.uploader-filepond name="app_favicon" label="Organization Favicon" />
          <img class="img-thumbnail" alt="Login Banner" width="200"
               src="{{ getFilePath($applicationSetup->where('type', 'login_banner')->first()->value ?? '') }}"
               data-holder-rendered="true">
          <x-data-entry.uploader-filepond name="login_banner" label="Organization Login Banner" />
        </div>
    </x-data-entry.form>
  </x-data-display.card>

  @include('admin.application-setup.scripts', compact('savedWindows'))
</x-layouts.admin.master>

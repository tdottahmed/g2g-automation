<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Application Setup') }}</h5>
      </div>
    </x-slot>
    <x-data-entry.form action="{{ route('applicationSetup.update') }}">
      <ul class="nav nav-tabs" id="setupTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="org-tab" data-bs-toggle="tab" data-bs-target="#org" type="button"
                  role="tab" aria-controls="org" aria-selected="true">
            {{ __('Organization Info') }}
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="scheduler-tab" data-bs-toggle="tab" data-bs-target="#scheduler" type="button"
                  role="tab" aria-controls="scheduler" aria-selected="false">
            {{ __('Scheduler') }}
          </button>
        </li>
      </ul>

      <div class="tab-content mt-3" id="setupTabsContent">
        <div class="tab-pane fade show active" id="org" role="tabpanel" aria-labelledby="org-tab">
          <x-data-entry.input type="text" name="app_name" label="Organization Name" placeholder="App Name"
                              value="{{ $applicationSetup->where('type', 'app_name')->first()->value ?? '' }}"
                              required />
          <x-data-entry.input type="text" name="app_email" label="Organization Email" placeholder="App Email"
                              value="{{ $applicationSetup->where('type', 'app_email')->first()->value ?? '' }}"
                              required />
          <x-data-entry.input type="tel" name="app_phone" label="Organization Phone" placeholder="App Phone"
                              value="{{ $applicationSetup->where('type', 'app_phone')->first()->value ?? '' }}"
                              required />
          <x-data-entry.text-area name="app_address" label="Organization Address" placeholder="App Address"
                                  value="{{ $applicationSetup->where('type', 'app_address')->first()->value ?? '' }}"></x-data-entry.text-area>

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
        <div class="tab-pane fade" id="scheduler" role="tabpanel" aria-labelledby="scheduler-tab">
          <div class="row g-3">
            <div class="col-md-4">
              <x-data-entry.date-picker type="time" name="schedule_start_time" label="Start Time"
                                        value="{{ $applicationSetup->where('type', 'schedule_start_time')->first()->value ?? '' }}"
                                        required />
            </div>
            <div class="col-md-4">
              <x-data-entry.date-picker type="time" name="schedule_end_time" label="End Time"
                                        value="{{ $applicationSetup->where('type', 'schedule_end_time')->first()->value ?? '' }}"
                                        required />
            </div>
            <div class="col-md-4">
              <x-data-entry.input type="number" name="schedule_interval_minutes" label="Interval (minutes)"
                                  placeholder="e.g. 15" min="1" step="1"
                                  value="{{ $applicationSetup->where('type', 'schedule_interval_minutes')->first()->value ?? 15 }}"
                                  required />
            </div>

            <div class="col-md-6">
              <label class="form-label">{{ __('Active Days') }}</label>
              <div class="d-flex flex-wrap gap-3">
                @php
                  $savedDays = explode(
                      ',',
                      $applicationSetup->where('type', 'schedule_days')->first()->value ??
                          'mon,tue,wed,thu,fri,sat,sun',
                  );
                  $days = [
                      'mon' => 'Mon',
                      'tue' => 'Tue',
                      'wed' => 'Wed',
                      'thu' => 'Thu',
                      'fri' => 'Fri',
                      'sat' => 'Sat',
                      'sun' => 'Sun',
                  ];
                @endphp
                @foreach ($days as $val => $label)
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="schedule_days[]"
                           id="day_{{ $val }}" value="{{ $val }}"
                           {{ in_array($val, $savedDays) ? 'checked' : '' }}>
                    <label class="form-check-label" for="day_{{ $val }}">{{ $label }}</label>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="col-12">
              <div class="form-text">
                The scheduler will generate time slots between Start and End using the given interval and days.
              </div>
            </div>
          </div>
        </div>
      </div>
    </x-data-entry.form>
  </x-data-display.card>
</x-layouts.admin.master>

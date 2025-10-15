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

    <!-- Scheduler Settings Form -->
    <div class="mb-5">
      <div class="d-flex align-items-center mb-4">
        <div class="bg-primary me-3 rounded bg-opacity-10 p-2">
          <i class="ri-timer-line text-primary fs-5"></i>
        </div>
        <h5 class="text-primary mb-0">{{ __('Scheduler Settings') }}</h5>
      </div>

      <form action="{{ route('applicationSetup.update') }}" method="POST"
            class="border-start border-primary border-4 ps-4">
        @csrf

        <div class="row">
          <div class="col-md-6">
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
          </div>

          <div class="col-md-6">
            <div class="mb-4">
              <label for="schedule_interval_minutes"
                     class="form-label fw-semibold">{{ __('Interval (minutes)') }}</label>
              <input type="number" class="form-control" id="schedule_interval_minutes" name="schedule_interval_minutes"
                     placeholder="e.g. 15" min="1" step="1"
                     value="{{ $applicationSetup->where('type', 'schedule_interval_minutes')->first()->value ?? 15 }}"
                     required>
              <div class="form-text text-muted">{{ __('Interval applies between posts within each time window') }}
              </div>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">{{ __('Time Windows') }}</label>
          <div id="schedulerWindowsWrapper" class="mb-3">
            @forelse ($savedWindows as $i => $window)
              <div class="row g-3 align-items-center scheduler-window bg-light mb-3 rounded border p-3">
                <div class="col-md-5">
                  <x-data-entry.date-picker type="time" name="scheduler_windows[{{ $i }}][start]"
                                            label="Start Time" value="{{ $window['start'] }}" required />
                </div>
                <div class="col-md-5">
                  <x-data-entry.date-picker type="time" name="scheduler_windows[{{ $i }}][end]"
                                            label="End Time" value="{{ $window['end'] }}" required />
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-outline-danger remove-window w-100 mt-4"
                          title="{{ __('Remove window') }}" {{ count($savedWindows) <= 1 ? 'disabled' : '' }}>
                    <i class="ri-delete-bin-line"></i>
                  </button>
                </div>
              </div>
            @empty
              <div class="row g-3 align-items-center scheduler-window bg-light mb-3 rounded border p-3">
                <div class="col-md-5">
                  <x-data-entry.date-picker type="time" name="scheduler_windows[0][start]" label="Start Time"
                                            value="09:00" required />
                </div>
                <div class="col-md-5">
                  <x-data-entry.date-picker type="time" name="scheduler_windows[0][end]" label="End Time"
                                            value="17:00" required />
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-outline-danger remove-window w-100 mt-4"
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

        <div class="text-end">
          <button type="submit" class="btn btn-primary">
            <i class="ri-save-line me-1"></i> {{ __('Save Scheduler Settings') }}
          </button>
        </div>
      </form>
    </div>

    <!-- Organization Settings Form -->
    <div class="border-top mt-5 pt-4">
      <div class="d-flex align-items-center mb-4">
        <div class="bg-success me-3 rounded bg-opacity-10 p-2">
          <i class="ri-building-4-line text-success fs-5"></i>
        </div>
        <h5 class="text-success mb-0">{{ __('Organization Settings') }}</h5>
      </div>

      <form action="{{ route('applicationSetup.update') }}" method="POST"
            class="border-start border-success border-4 ps-4" enctype="multipart/form-data">
        @csrf

        <div class="row">
          <div class="col-md-6">
            <x-data-entry.input type="text" name="app_name" label="Organization Name" placeholder="App Name"
                                value="{{ $applicationSetup->where('type', 'app_name')->first()->value ?? '' }}"
                                required />
          </div>
          <div class="col-md-6">
            <x-data-entry.input type="email" name="app_email" label="Organization Email" placeholder="App Email"
                                value="{{ $applicationSetup->where('type', 'app_email')->first()->value ?? '' }}"
                                required />
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <x-data-entry.input type="tel" name="app_phone" label="Organization Phone" placeholder="App Phone"
                                value="{{ $applicationSetup->where('type', 'app_phone')->first()->value ?? '' }}"
                                required />
          </div>
          <div class="col-md-6">
            <x-data-entry.text-area name="app_address" label="Organization Address" placeholder="App Address"
                                    rows="3"
                                    value="{{ $applicationSetup->where('type', 'app_address')->first()->value ?? '' }}">
            </x-data-entry.text-area>
          </div>
        </div>

        <!-- File Uploads Section -->
        <div class="row mt-3">
          <div class="col-12">
            <h6 class="fw-semibold mb-3">{{ __('Brand Assets') }}</h6>
          </div>

          <!-- Logo -->
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <div class="mb-3">
                  <img class="img-thumbnail" alt="Logo" width="120"
                       src="{{ getFilePath($applicationSetup->where('type', 'app_logo')->first()->value ?? '') }}"
                       data-holder-rendered="true" onerror="this.style.display='none'">
                </div>
                <x-data-entry.uploader-filepond name="app_logo" label="Organization Logo" />
                <div class="form-text">Recommended: 200x200px, PNG</div>
              </div>
            </div>
          </div>

          <!-- Favicon -->
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <div class="mb-3">
                  <img class="img-thumbnail" alt="Favicon" width="80"
                       src="{{ getFilePath($applicationSetup->where('type', 'app_favicon')->first()->value ?? '') }}"
                       data-holder-rendered="true" onerror="this.style.display='none'">
                </div>
                <x-data-entry.uploader-filepond name="app_favicon" label="Organization Favicon" />
                <div class="form-text">Recommended: 32x32px, ICO</div>
              </div>
            </div>
          </div>

          <!-- Login Banner -->
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <div class="mb-3">
                  <img class="img-thumbnail" alt="Login Banner" width="120"
                       src="{{ getFilePath($applicationSetup->where('type', 'login_banner')->first()->value ?? '') }}"
                       data-holder-rendered="true" onerror="this.style.display='none'">
                </div>
                <x-data-entry.uploader-filepond name="login_banner" label="Login Banner" />
                <div class="form-text">Recommended: 600x400px, JPG</div>
              </div>
            </div>
          </div>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-success">
            <i class="ri-save-line me-1"></i> {{ __('Save Organization Settings') }}
          </button>
        </div>
      </form>
    </div>
  </x-data-display.card>

  @push('scripts')
    <script>
      $(document).ready(function() {
        let windowIndex = {{ count($savedWindows) }};
        const maxWindows = 5;

        // Add new scheduler window
        $('#addSchedulerWindow').on('click', function() {
          if (windowIndex >= maxWindows) {
            showAlert('warning', `{{ __('Maximum') }} ${maxWindows} {{ __('time windows allowed') }}`);
            return;
          }

          const newWindow = `
          <div class="row g-3 align-items-center scheduler-window mb-3 p-3 border rounded bg-light">
            <div class="col-md-5">
              <x-data-entry.date-picker type="time" 
                name="scheduler_windows[${windowIndex}][start]" 
                label="Start Time" 
                value="09:00"
                required />
            </div>
            <div class="col-md-5">
              <x-data-entry.date-picker type="time" 
                name="scheduler_windows[${windowIndex}][end]" 
                label="End Time" 
                value="17:00" 
                required />
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-outline-danger remove-window w-100 mt-4" 
                      title="{{ __('Remove window') }}">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </div>
        `;

          $('#schedulerWindowsWrapper').append(newWindow);
          updateRemoveButtons();
          windowIndex++;
        });

        // Remove scheduler window
        $('#schedulerWindowsWrapper').on('click', '.remove-window', function() {
          if ($('.scheduler-window').length > 1) {
            $(this).closest('.scheduler-window').fadeOut(300, function() {
              $(this).remove();
              updateRemoveButtons();
              reindexWindows();
            });
          }
        });

        // Update remove buttons state
        function updateRemoveButtons() {
          const windows = $('.scheduler-window');
          $('.remove-window').prop('disabled', windows.length <= 1);
        }

        // Reindex windows after removal
        function reindexWindows() {
          windowIndex = 0;
          $('.scheduler-window').each(function(index) {
            $(this).find('input[name^="scheduler_windows"]').each(function() {
              const name = $(this).attr('name');
              const newName = name.replace(/scheduler_windows\[\d+\]/, `scheduler_windows[${index}]`);
              $(this).attr('name', newName);
            });
            windowIndex++;
          });
        }

        // Show alert message
        function showAlert(type, message) {
          // Remove existing alerts
          $('.alert-dismissible').remove();

          const alertClass = type === 'error' ? 'danger' : type;
          const icon = type === 'error' ? 'ri-error-warning-line' : 'ri-information-line';

          const alert = `
          <div class="alert alert-${alertClass} alert-dismissible fade show mb-4" role="alert">
            <i class="${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        `;

          $('.card-body').prepend(alert);
        }

        // Initialize on page load
        updateRemoveButtons();

        // Add form submission handlers to show loading states
        $('form').on('submit', function() {
          const btn = $(this).find('button[type="submit"]');
          btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Saving...');
        });
      });
    </script>
  @endpush
</x-layouts.admin.master>

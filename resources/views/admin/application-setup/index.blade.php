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
        @include('admin.application-setup.scheduler', compact('savedWindows', 'savedDays', 'days'))

        @include('admin.application-setup.organization')
      </div>
    </x-data-entry.form>
  </x-data-display.card>

  @include('admin.application-setup.scripts', compact('savedWindows'))
</x-layouts.admin.master>

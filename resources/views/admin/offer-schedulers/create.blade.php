<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Create Scheduler') }}</h5>
        <x-action.link href="{{ route('offer-schedulers.index') }}"
                       icon="ri-list-check">{{ __('Scheduler List') }}</x-action.link>
      </div>
    </x-slot>

    <x-data-entry.form action="{{ route('offer-schedulers.store') }}">

      {{-- Scheduler Type --}}
      <div class="alert alert-info border-0">
        <div class="d-flex align-items-center">
          <i class="ri-information-line fs-4 me-2"></i>
          <div>
            <strong>Choose Scheduler Type:</strong> Select either a User Account (applies to all their templates) OR a
            specific Template (overrides account settings).
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <x-data-entry.select name="user_account_id" label="User Account (Account-Level)" :options="$userAccounts->pluck('owner_name', 'id')"
                               placeholder="Select User Account (Optional)" />
          <small class="text-muted">Applies to all templates for this user</small>
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="offer_template_id" label="Offer Template (Template-Level)" :options="$offerTemplates->pluck('title', 'id')"
                               :selected="old('offer_template_id')" placeholder="Select Template (Optional)" />
          <small class="text-muted">Overrides account-level scheduler</small>
        </div>
      </div>

      {{-- Time Configuration --}}
      <div class="card mt-3 border-0 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="ri-time-line text-primary me-1"></i> {{ __('Time Configuration') }}
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <x-data-entry.input type="time" name="start_time" label="Start Time"
                                  value="{{ old('start_time', '09:00') }}" required />
            </div>
            <div class="col-md-4">
              <x-data-entry.input type="time" name="end_time" label="End Time" value="{{ old('end_time', '17:00') }}"
                                  required />
            </div>
            <div class="col-md-4">
              <label class="form-label">{{ __('Timezone') }} <span class="text-danger">*</span></label>
              <select name="timezone" class="form-select" required>
                <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>
                  America/New_York (EST)</option>
                <option value="America/Chicago" {{ old('timezone') == 'America/Chicago' ? 'selected' : '' }}>
                  America/Chicago (CST)</option>
                <option value="America/Denver" {{ old('timezone') == 'America/Denver' ? 'selected' : '' }}>
                  America/Denver (MST)</option>
                <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>
                  America/Los_Angeles (PST)</option>
                <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London
                  (GMT)</option>
                <option value="Europe/Paris" {{ old('timezone') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris
                  (CET)</option>
                <option value="Asia/Tokyo" {{ old('timezone') == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo (JST)
                </option>
                <option value="Asia/Dubai" {{ old('timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)
                </option>
                <option value="Asia/Dhaka" {{ old('timezone') == 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka (BST)
                </option>
              </select>
            </div>
          </div>

          {{-- Days Selection --}}
          <div class="mt-3">
            <label class="form-label">{{ __('Active Days') }}</label>
            <div class="d-flex flex-wrap gap-2">
              @php
                $days = [
                    'mon' => 'Monday',
                    'tue' => 'Tuesday',
                    'wed' => 'Wednesday',
                    'thu' => 'Thursday',
                    'fri' => 'Friday',
                    'sat' => 'Saturday',
                    'sun' => 'Sunday',
                ];
                $oldDays = old('days', ['mon', 'tue', 'wed', 'thu', 'fri']);
              @endphp
              @foreach ($days as $value => $label)
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="days[]" value="{{ $value }}"
                         id="day_{{ $value }}" {{ in_array($value, $oldDays) ? 'checked' : '' }}>
                  <label class="form-check-label" for="day_{{ $value }}">
                    {{ $label }}
                  </label>
                </div>
              @endforeach
            </div>
            <small class="text-muted">Leave all unchecked for all days</small>
          </div>
        </div>
      </div>

      {{-- Rate Limiting --}}
      <div class="card mt-3 border-0 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="ri-speed-line text-primary me-1"></i> {{ __('Rate Limiting') }}
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <x-data-entry.input type="number" name="posts_per_cycle" label="Posts Per Cycle"
                                  value="{{ old('posts_per_cycle', 2) }}" min="1" required />
              <small class="text-muted">How many offers to post each time</small>
            </div>
            <div class="col-md-4">
              <x-data-entry.input type="number" name="interval_minutes" label="Interval (Minutes)"
                                  value="{{ old('interval_minutes', 60) }}" min="1" required />
              <small class="text-muted">Wait time between posting cycles</small>
            </div>
            <div class="col-md-4">
              <x-data-entry.input type="number" name="max_posts_per_day" label="Max Posts Per Day"
                                  value="{{ old('max_posts_per_day') }}" min="1" />
              <small class="text-muted">Daily limit (leave empty for unlimited)</small>
            </div>
          </div>
        </div>
      </div>

      {{-- Status --}}
      <div class="mt-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                 {{ old('is_active', true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">
            {{ __('Activate Scheduler') }}
          </label>
        </div>
      </div>

    </x-data-entry.form>
  </x-data-display.card>

  @push('scripts')
    <script>
      $(document).ready(function() {
        // Prevent selecting both user account and template
        $('select[name="user_account_id"]').on('change', function() {
          if ($(this).val()) {
            $('select[name="offer_template_id"]').val('').prop('disabled', true);
          } else {
            $('select[name="offer_template_id"]').prop('disabled', false);
          }
        });

        $('select[name="offer_template_id"]').on('change', function() {
          if ($(this).val()) {
            $('select[name="user_account_id"]').val('').prop('disabled', true);
          } else {
            $('select[name="user_account_id"]').prop('disabled', false);
          }
        });
      });
    </script>
  @endpush
</x-layouts.admin.master>

@props([
    'name',
    'label' => null,
    'placeholder' => '',
    'value' => null,
    'type' => 'date', // date | time | date-time
    'timeFormat' => '12', // 12 or 24 hour format
])

<div>
  <label for="{{ $name }}" class="form-label">{{ __($label ?? ucfirst($name)) }}</label>
  <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ $value ?? old($name) }}"
         placeholder="{{ $label ?? ucfirst($name) }}" class="form-control flatpickr-input" autocomplete="off" />

  @error($name)
    <span class="text-danger">{{ __($message) }}</span>
  @enderror
</div>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let options = {
        altInput: true,
      };

      switch ("{{ $type }}") {
        case 'date-time':
          options.enableTime = true;
          options.dateFormat = "Y-m-d H:i";
          options.altFormat = "F j, Y h:i K"; // e.g. September 29, 2025 4:30 PM
          options.time_24hr = "{{ $timeFormat }}" === "24";
          break;

        case 'time':
          options.enableTime = true;
          options.noCalendar = true;
          options.dateFormat = "H:i";
          options.time_24hr = "{{ $timeFormat }}" === "24";
          break;

        default: // 'date'
          options.dateFormat = "Y-m-d";
          options.altFormat = "F j, Y";
          break;
      }

      flatpickr('#{{ $name }}', options);
    });
  </script>
@endpush

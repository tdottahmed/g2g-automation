@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'type' => 'date',
    'timeFormat' => '12', // Changed default to 12 for AM/PM
    'required' => false,
])

<div class="form-group">
  @if ($label)
    <label for="{{ $name }}" class="form-label">{{ __($label) }}</label>
  @endif

  <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ $value ?? old($name) }}"
         placeholder="{{ $placeholder ?: ($label ? __($label) : '') }}"
         class="form-control flatpickr-input {{ $type === 'time' ? 'time-picker' : '' }}"
         {{ $required ? 'required' : '' }} autocomplete="off" data-type="{{ $type }}"
         data-time-format="{{ $timeFormat }}" />

  @error($name)
    <div class="text-danger small mt-1">{{ __($message) }}</div>
  @enderror
</div>

@once
  @push('scripts')
    <script>
      $(document).ready(function() {
        // Initialize Flatpickr for non-dynamic elements
        $('.flatpickr-input').not('#schedulerWindowsWrapper .flatpickr-input').each(function() {
          const $element = $(this);
          const type = $element.data('type');
          const timeFormat = $element.data('time-format');

          let options = {
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            static: true
          };

          switch (type) {
            case 'date-time':
              options.enableTime = true;
              options.dateFormat = "Y-m-d H:i";
              options.altFormat = "F j, Y h:i K";
              options.time_24hr = timeFormat === "24";
              break;
            case 'time':
              options.enableTime = true;
              options.noCalendar = true;
              options.dateFormat = timeFormat === "24" ? "H:i" : "h:i K";
              options.altFormat = timeFormat === "24" ? "H:i" : "h:i K";
              options.time_24hr = timeFormat === "24";
              options.minuteIncrement = 5;
              break;
            default:
              options.dateFormat = "Y-m-d";
              options.altFormat = "F j, Y";
              break;
          }

          $element.flatpickr(options);
        });
      });
    </script>
  @endpush
@endonce

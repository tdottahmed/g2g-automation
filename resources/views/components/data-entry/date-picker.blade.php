@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'type' => 'date',
    'timeFormat' => '12',
    'required' => false,
])

<div class="form-group">
  @if ($label)
    <label for="{{ $name }}" class="form-label">{{ __($label) }}</label>
  @endif

  @if ($type === 'time')
    <!-- Use native time input for better compatibility -->
    <input type="time" id="{{ $name }}" name="{{ $name }}" value="{{ $value ?? old($name) }}"
           class="form-control" {{ $required ? 'required' : '' }} />
  @else
    <!-- Use text input for dates with Flatpickr -->
    <input type="text" id="{{ $name }}" name="{{ $name }}" value="{{ $value ?? old($name) }}"
           placeholder="{{ $placeholder ?: ($label ? __($label) : '') }}" class="form-control flatpickr-input"
           {{ $required ? 'required' : '' }} autocomplete="off" data-type="{{ $type }}" />
  @endif

  @error($name)
    <div class="text-danger small mt-1">{{ __($message) }}</div>
  @enderror
</div>

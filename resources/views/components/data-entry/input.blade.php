@props(['name', 'label' => '', 'type' => 'text', 'placeholder' => '', 'value' => '', 'showPasswordLink' => false])

<div class="mb-3">
    @if ($label && $type !== 'checkbox')
        <label for="{{ $name }}" class="form-label">{{ __($label) }}</label>
        @if ($attributes->has('required'))
            <span class="text-danger">*</span>
        @endif
    @endif

    @if ($type === 'password')
        <!-- Password Input -->
        <div class="position-relative auth-pass-inputgroup mb-3">
            <input type="password" name="{{ $name }}" id="{{ $name }}"
                class="form-control pe-5 password-input" placeholder="{{ $placeholder }}"
                value="{{ old($name, $value) }}" {{ $attributes->merge(['class' => 'form-control']) }}
                @error($name) aria-invalid="true" @enderror @if ($attributes->has('required')) required @endif>
            <button
                class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                type="button" id="password-addon">
                <i class="ri-eye-fill align-middle"></i>
            </button>
        </div>

        @if ($showPasswordLink)
            <div class="float-end">
                <a href="{{ route('password.request') }}" class="text-muted">{{ __('Forgot your password?') }}</a>
            </div>
        @endif
    @elseif ($type === 'checkbox')
        <!-- Checkbox Input -->
        <div class="form-check">
            <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1"
                {{ old($name) ? 'checked' : '' }} {{ $attributes->merge(['class' => 'form-check-input']) }}
                @if ($attributes->has('required')) required @endif>
            <label class="form-check-label" for="{{ $name }}">
                {{ __($label) }}
            </label>
        </div>
    @else
        <!-- Default Input -->
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
            placeholder="{{ $placeholder }}" value="{{ old($name, $value) }}"
            {{ $attributes->merge(['class' => 'form-control']) }} @error($name) aria-invalid="true" @enderror
            @if ($attributes->has('required')) required @endif>
    @endif

    @error($name)
        <span class="text-danger">{{ __($message) }}</span>
    @enderror
</div>

<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('Permission Details') }}</h5>
                <x-action.link href="{{ route('permissions.index') }}"
                    icon="ri-list-check">{{ __('Permission List') }}</x-action.link>
            </div>
        </x-slot>
        <h3> {{ __('Name') }} {{ $permission->name }}</h3>
        <p> {{ __('Group') }} {{ $permission->group }}</p>
        <p> {{ __('Guard') }} {{ $permission->guard }}</p>
    </x-data-display.card>
</x-layouts.admin.master>

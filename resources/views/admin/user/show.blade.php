<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('User Details') }}</h5>
                <x-action.link href="{{ route('users.index') }}"
                    icon="ri-list-check">{{ __('User List') }}</x-action.link>
            </div>
        </x-slot>
        <h3> {{ __('Name') }} {{ $user->name }}</h3>
        <p> {{ __('Email') }} {{ $user->email }}</p>
        <p> {{ __('Role') }} {{ $user->role }}</p>
    </x-data-display.card>
</x-layouts.admin.master>

<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('Edit Role') }}</h5>
                <x-action.link href="{{ route('roles.index') }}"
                    icon="ri-list-check">{{ __('Role List') }}</x-action.link>
            </div>
        </x-slot>
        <x-data-entry.form action="{{ route('roles.update', $role->id) }}" model="{{ $role }}">
            <x-data-entry.input type="text" name="name" label="Name" placeholder="Name" required
                value="{{ $role->name }}" />
            <x-data-entry.input type="text" name="guard_name" label="Guard Name" placeholder="guard_name" required
                value="{{ $role->guard_name }}" />
            <x-permission-accordion :permissionGroups="$permissionGroups" :role="$role" />
        </x-data-entry.form>
    </x-data-display.card>
</x-layouts.admin.master>

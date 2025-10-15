<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('Edit Permission') }}</h5>
                <x-action.link href="{{ route('permissions.index') }}"
                    icon="ri-add-line">{{ __('Permission List') }}</x-action.link>
            </div>
        </x-slot>
        <x-data-entry.form action="{{ route('permissions.update', $permission->id) }}" model="{{ $permission }}">
            <x-data-entry.input type="text" name="name" label="Name" placeholder="Name" required
                value="{{ $permission->name }}" />
            <x-data-entry.input type="text" name="group" label="Group" placeholder="Group" required
                value="{{ $permission->group }}" />
            <x-data-entry.input type="text" name="guard" label="Guard" placeholder="Guard" required
                value="{{ $permission->guard }}" />
        </x-data-entry.form>
    </x-data-display.card>
</x-layouts.admin.master>

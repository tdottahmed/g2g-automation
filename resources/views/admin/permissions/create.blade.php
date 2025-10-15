<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('Create Permission') }}</h5>
                <x-action.link href="{{ route('permissions.index') }}" icon="ri-list-check">
                    {{ __('Permission List') }}</x-action.link>
            </div>
        </x-slot>
        <x-data-entry.form action="{{ route('permissions.store') }}">
            <x-data-entry.input type="text" name="name" label="Name" placeholder="Name" required />
            <x-data-entry.input type="text" name="group" label="Group" placeholder="Group" required />
            <x-data-entry.input type="text" name="guard_name" label="Guard" placeholder="Guard" />
        </x-data-entry.form>
    </x-data-display.card>
</x-layouts.admin.master>

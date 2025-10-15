<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Roles</h5>
                <x-action.link href="{{ route('users.create') }}" icon="ri-add-line">Create user</x-action.link>
            </div>
        </x-slot>
        <x-data-display.data-table :rows="$users" :columnsToIgnore="['password', 'remember_token', 'email_verified_at']" />
    </x-data-display.card>
</x-layouts.admin.master>

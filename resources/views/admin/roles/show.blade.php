<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ $role->name }}</h5>
                <x-action.link href="{{ route('roles.index') }}" icon="ri-add-line">{{ __('Role List') }}</x-action.link>
            </div>
        </x-slot>
        @foreach ($role->permissions->groupBy('group') as $group => $permissions)
            <div class="card ribbon-box border shadow-none mb-lg-0 material-shadow my-2">
                <div class="card-body">
                    <div class="ribbon ribbon-primary ribbon-shape">{{ $group }}</div>
                    <div class="ribbon-content text-muted mt-4 d-flex justify-content-between">
                        <ul class="list-group list-group-horizontal-md w-100">
                            @foreach ($permissions as $permission)
                                <li class="list-group-item">
                                    {{ $permission->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </x-data-display.card>
</x-layouts.admin.master>

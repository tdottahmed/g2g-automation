<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('Edit User') }}</h5>
                <x-action.link href="{{ route('users.index') }}"
                    icon="ri-list-check">{{ __('User List') }}</x-action.link>
            </div>
        </x-slot>
        <x-data-entry.form action="{{ route('users.update', $user->id) }}" model="{{ $user }}">
            <x-data-entry.input type="text" name="name" label="Name" placeholder="Name" required
                value="{{ $user->name }}" />
            <x-data-entry.input type="email" name="email" label="Email" placeholder="Email" required
                value="{{ $user->email }}" />
            <x-data-entry.input type="password" name="password" label="Password" placeholder="Password" />
            <x-data-entry.input type="password" name="confirm_password" label="confirm password"
                placeholder="confirm_password" />
            <div class="form-group mb-3">
                <label for="role">{{ __('Role') }}</label>
                <select name="role" class="form-control" id="role">
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ $role->id == $user->role_id ? 'selected' : '' }}>
                            {{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-data-entry.form>
    </x-data-display.card>
</x-layouts.admin.master>

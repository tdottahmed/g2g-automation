<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <h5 class="card-title">Roles List</h5>
        </x-slot>
        <x-data-entry.form action="{{ route('users.store') }}" method="POST">
            <x-data-entry.input type="text" name="name" label="Name" placeholder="Name" required />
            <x-data-entry.input type="email" name="email" label="Email" placeholder="Email" required />
            <x-data-entry.input type="password" name="password" label="Password" placeholder="Password" required />
            <x-data-entry.input type="password" name="confirm_password" label="confirm password"
                placeholder="confirm_password" required />
            <div class="form-group mb-3">
                <label for="role">Role</label>
                <select name="role" class="form-control" id="role">
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-data-entry.form>
    </x-data-display.card>
</x-layouts.admin.master>

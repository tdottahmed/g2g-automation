<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Edit User Account') }}</h5>
        <x-action.link href="{{ route('user-accounts.index') }}" icon="ri-list-check">
          {{ __('User Account List') }}
        </x-action.link>
      </div>
    </x-slot>

    <x-data-entry.form :model="$userAccount" action="{{ route('user-accounts.update', $userAccount->id) }}">
      <x-data-entry.input type="text" name="email" label="Email" placeholder="Email"
                          value="{{ old('email', $userAccount->email) }}" required />

      <x-data-entry.input type="text" name="owner_name" label="Owner Name" placeholder="Owner Name"
                          value="{{ old('owner_name', $userAccount->owner_name) }}" required />

      <x-data-entry.input type="password" name="password" label="Password" placeholder="Leave blank if unchanged" />
    </x-data-entry.form>
  </x-data-display.card>
</x-layouts.admin.master>

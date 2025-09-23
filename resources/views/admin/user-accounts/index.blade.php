<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('User Accounts') }}</h5>
        <x-action.link href="{{ route('user-accounts.create') }}" icon="ri-add-line">
          {{ __('Create User Account') }}
        </x-action.link>
      </div>
    </x-slot>

    <x-data-display.table>
      <x-data-display.thead>
        <th>{{ __('Owner Name') }}</th>
        <th>{{ __('Email') }}</th>
        <th>{{ __('Generated Cookies') }}</th>
        <th>{{ __('Actions') }}</th>
      </x-data-display.thead>

      <x-data-display.tbody>
        @forelse ($userAccounts as $account)
          <tr>
            <td>{{ $account->owner_name }}</td>
            <td>{{ $account->email }}</td>
            <td>
              @if ($account->is_generated_cookies)
                <span class="badge bg-success">{{ __('Yes') }}</span>
              @else
                <span class="badge bg-secondary">{{ __('No') }}</span>
              @endif
            </td>

            <x-data-display.table-actions>
              <li>
                <a href="{{ route('user-accounts.edit', $account->id) }}" class="dropdown-item">
                  <i class="ri-edit-box-line"></i> {{ __('Edit') }}
                </a>
              </li>
              <li>
                <form method="POST" action="{{ route('user-accounts.destroy', $account->id) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger remove-item-btn"
                          onclick="return confirm('Are you sure you want to delete this account?')">
                    <i class="ri-delete-bin-line"></i> {{ __('Delete') }}
                  </button>
                </form>
              </li>
            </x-data-display.table-actions>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted text-center">
              {{ __('No user accounts found.') }}
            </td>
          </tr>
        @endforelse
      </x-data-display.tbody>
    </x-data-display.table>
  </x-data-display.card>
</x-layouts.admin.master>

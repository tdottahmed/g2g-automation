<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ __('User Accounts') }}</h5>
        <x-action.link href="{{ route('user-accounts.create') }}" icon="ri-add-line">
          {{ __('Create User Account') }}
        </x-action.link>
      </div>
    </x-slot>

    <x-data-display.table>
      <x-data-display.thead>
        <th>{{ __('Owner Name') }}</th>
        <th>{{ __('Email') }}</th>
        <th class="text-center">{{ __('Templates') }}</th>
        <th class="text-center">{{ __('Active') }}</th>
        <th class="text-center">{{ __('Cookies') }}</th>
        <th>{{ __('Actions') }}</th>
      </x-data-display.thead>

      <x-data-display.tbody>
        @forelse ($userAccounts as $account)
          <tr id="account-row-{{ $account->id }}">
            <td class="fw-medium">{{ $account->owner_name }}</td>
            <td class="text-muted small">{{ $account->email }}</td>
            <td class="text-center">
              <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                {{ $account->templates_count }}
              </span>
            </td>
            <td class="text-center">
              @if ($account->active_templates_count > 0)
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                  {{ $account->active_templates_count }}
                </span>
              @else
                <span class="text-muted small">—</span>
              @endif
            </td>
            <td class="text-center">
              @if ($account->is_generated_cookies)
                <span class="badge bg-success"><i class="ri-shield-check-line me-1"></i>{{ __('Yes') }}</span>
              @else
                <span class="badge bg-secondary">{{ __('No') }}</span>
              @endif
            </td>

            <x-data-display.table-actions>
              <li>
                <a href="{{ route('automation.dashboard') }}#user-pane-{{ $account->id }}" class="dropdown-item">
                  <i class="ri-dashboard-line"></i> {{ __('View on Dashboard') }}
                </a>
              </li>
              <li>
                <a href="{{ route('user-accounts.edit', $account->id) }}" class="dropdown-item">
                  <i class="ri-edit-box-line"></i> {{ __('Edit') }}
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <button
                  type="button"
                  class="dropdown-item text-danger btn-delete-account"
                  data-account-id="{{ $account->id }}"
                  data-account-name="{{ $account->owner_name }}"
                  data-templates="{{ $account->templates_count }}">
                  <i class="ri-delete-bin-line"></i> {{ __('Delete') }}
                </button>
              </li>
            </x-data-display.table-actions>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted text-center py-4">
              {{ __('No user accounts found.') }}
            </td>
          </tr>
        @endforelse
      </x-data-display.tbody>
    </x-data-display.table>
  </x-data-display.card>

  @push('scripts')
    <script>
      const CSRF = '{{ csrf_token() }}';

      document.querySelectorAll('.btn-delete-account').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id        = this.dataset.accountId;
          const name      = this.dataset.accountName;
          const templates = parseInt(this.dataset.templates, 10);
          const warning   = templates > 0
            ? `This will also permanently delete <strong>${templates} template${templates !== 1 ? 's' : ''}</strong> linked to this account.`
            : 'This action cannot be undone.';

          Swal.fire({
            title: `Delete "${name}"?`,
            html: warning,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: 'btn btn-danger w-xs me-2 mt-2',
              cancelButton:  'btn btn-secondary w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch(`/user-accounts/${id}`, {
              method:  'DELETE',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
              if (data.success) {
                const row = document.getElementById(`account-row-${id}`);
                if (row) row.remove();
                Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1500, showConfirmButton: false });
              }
            })
            .catch(function () {
              Swal.fire({ title: 'Error', text: 'Could not delete account.', icon: 'error' });
            });
          });
        });
      });
    </script>
  @endpush
</x-layouts.admin.master>

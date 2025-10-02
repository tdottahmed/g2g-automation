<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Offer Templates') }}</h5>
        <x-action.link href="{{ route('offer-templates.create') }}" icon="ri-add-line">
          {{ __('Create Offer Template') }}
        </x-action.link>
      </div>
    </x-slot>

    <x-data-display.table>
      <x-data-display.thead>
        <th>{{ __('Title') }}</th>
        <th>{{ __('User Account') }}</th>
        <th>{{ __('Price') }}</th>
        <th>{{ __('Currency') }}</th>
        <th>{{ __('Region') }}</th>
        <th>{{ __('Status') }}</th>
        <th>{{ __('Actions') }}</th>
      </x-data-display.thead>

      <x-data-display.tbody>
        @forelse ($offers as $offer)
          <tr>
            <td>{{ str($offer->title)->limit(50) }}
              <span
                    class="badge bg-{{ $offer->is_active ? 'success' : 'warning' }}">{{ $offer->is_active ? __('Active') : __('Inactive') }}</span>
            </td>
            <td>{{ $offer->userAccount->owner_name ?? '-' }}</td>
            <td>{{ $offer->price }}</td>
            <td>{{ $offer->currency }}</td>
            <td>{{ $offer->region }}</td>
            <td>
              <form action="{{ route('offer-templates.toggle-status', $offer->id) }}" method="POST" class="d-inline">
                @csrf
                @if ($offer->is_active)
                  <input type="hidden" name="status" value="0">
                  <button type="submit" class="btn btn-outline-danger off-offer-btn" title="Deactivate this template">
                    <i class="ri-stop-circle-line me-1"></i> {{ __('Deactivate') }}
                  </button>
                @else
                  <input type="hidden" name="status" value="1">
                  <button type="submit" class="btn btn-outline-success start-offer-btn" title="Activate this template">
                    <i class="ri-play-circle-fill me-1"></i> {{ __('Activate') }}
                  </button>
                @endif
              </form>

            </td>

            <x-data-display.table-actions>
              <li>
                <a href="{{ route('offer-templates.edit', $offer->id) }}" class="dropdown-item">
                  <i class="ri-edit-box-line"></i> {{ __('Edit') }}
                </a>
              </li>
              <li>
                <form method="POST" action="{{ route('offer-templates.destroy', $offer->id) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger remove-item-btn"
                          onclick="return confirm('{{ __('Are you sure you want to delete this offer?') }}')">
                    <i class="ri-delete-bin-line"></i> {{ __('Delete') }}
                  </button>
                </form>
              </li>
            </x-data-display.table-actions>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted text-center">
              {{ __('No offers found.') }}
            </td>
          </tr>
        @endforelse
      </x-data-display.tbody>
    </x-data-display.table>
  </x-data-display.card>

  @push('scripts')
    <script>
      document.querySelectorAll('.start-offer-btn, .off-offer-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const form = this.closest('form');
          const isActivate = this.classList.contains('start-offer-btn');

          Swal.fire({
            title: isActivate ? 'Activate this offer?' : 'Deactivate this offer?',
            text: "You can change this status anytime.",
            icon: 'question',
            showCancelButton: true,
            customClass: {
              confirmButton: 'btn btn-primary w-xs me-2 mt-2',
              cancelButton: 'btn btn-danger w-xs mt-2',
            },
            confirmButtonText: isActivate ? 'Yes, activate it!' : 'Yes, deactivate it!',
            buttonsStyling: false,
            showCloseButton: true,
          }).then((result) => {
            if (result.isConfirmed) {
              form.submit();
            }
          });
        });
      });
    </script>
  @endpush
</x-layouts.admin.master>

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
            <td>{{ $offer->title }}</td>
            <td>{{ $offer->userAccount->owner_name ?? '-' }}</td>
            <td>{{ $offer->price }}</td>
            <td>{{ $offer->currency }}</td>
            <td>{{ $offer->region }}</td>
            <td>
              @if ($offer->is_active)
                <span class="badge bg-success">{{ __('Active') }}</span>
              @else
                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
              @endif
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
</x-layouts.admin.master>

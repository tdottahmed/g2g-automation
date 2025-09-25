<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Levels') }}</h5>
        <x-action.link href="{{ route('levels.create') }}" icon="ri-add-line">
          {{ __('Create Level') }}
        </x-action.link>
      </div>
    </x-slot>

    <x-data-display.table>
      <x-data-display.thead>
        <th>{{ __('ID') }}</th>
        <th>{{ __('Type') }}</th>
        <th>{{ __('Value') }}</th>
        <th>{{ __('Status') }}</th>
        <th>{{ __('Actions') }}</th>
      </x-data-display.thead>

      <x-data-display.tbody>
        @forelse ($levels as $level)
          <tr>
            <td>{{ $level->id }}</td>
            <td>{{ ucfirst($level->type) }}</td>
            <td>{{ $level->value }}</td>
            <td>
              @if ($level->active)
                <span class="badge bg-success">{{ __('Active') }}</span>
              @else
                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
              @endif
            </td>

            <x-data-display.table-actions>
              <li>
                <a href="{{ route('levels.edit', $level->id) }}" class="dropdown-item">
                  <i class="ri-edit-box-line"></i> {{ __('Edit') }}
                </a>
              </li>
              <li>
                <form method="POST" action="{{ route('levels.destroy', $level->id) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger remove-item-btn"
                          onclick="return confirm('{{ __('Are you sure you want to delete this level?') }}')">
                    <i class="ri-delete-bin-line"></i> {{ __('Delete') }}
                  </button>
                </form>
              </li>
            </x-data-display.table-actions>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted text-center">
              {{ __('No levels found.') }}
            </td>
          </tr>
        @endforelse
      </x-data-display.tbody>
    </x-data-display.table>
  </x-data-display.card>
</x-layouts.admin.master>

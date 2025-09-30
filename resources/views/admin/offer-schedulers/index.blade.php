<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Offer Schedulers') }}</h5>
        <x-action.link href="{{ route('offer-schedulers.create') }}" icon="ri-add-line">
          {{ __('Create Scheduler') }}
        </x-action.link>
      </div>
    </x-slot>

    <x-data-display.table>
      <x-data-display.thead>
        <th>{{ __('Type') }}</th>
        <th>{{ __('For') }}</th>
        <th>{{ __('Schedule') }}</th>
        <th>{{ __('Rate Limit') }}</th>
        <th>{{ __('Today') }}</th>
        <th>{{ __('Status') }}</th>
        <th>{{ __('Actions') }}</th>
      </x-data-display.thead>

      <x-data-display.tbody>
        @forelse ($schedulers as $scheduler)
          <tr>
            <td>
              @if ($scheduler->offer_template_id)
                <span class="badge bg-primary">
                  <i class="ri-file-list-line"></i> Template
                </span>
              @else
                <span class="badge bg-info">
                  <i class="ri-user-line"></i> Account
                </span>
              @endif
            </td>
            <td>
              @if ($scheduler->offer_template_id)
                {{ $scheduler->offerTemplate->title ?? '-' }}
              @else
                {{ $scheduler->userAccount->owner_name ?? '-' }}
                <small class="text-muted d-block">{{ $scheduler->userAccount->email ?? '' }}</small>
              @endif
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <i class="ri-time-line text-primary"></i>
                <span>{{ $scheduler->start_time }} - {{ $scheduler->end_time }}</span>
              </div>
              <small class="text-muted">
                <i class="ri-map-pin-line"></i> {{ $scheduler->timezone }}
              </small>
              @if ($scheduler->days)
                <div class="mt-1">
                  @foreach ($scheduler->days as $day)
                    <span class="badge badge-soft-secondary">{{ ucfirst($day) }}</span>
                  @endforeach
                </div>
              @else
                <small class="text-muted d-block">All days</small>
              @endif
            </td>
            <td>
              <small class="d-block">
                <i class="ri-repeat-line"></i> {{ $scheduler->posts_per_cycle }} per cycle
              </small>
              <small class="d-block text-muted">
                <i class="ri-timer-line"></i> Every {{ $scheduler->interval_minutes }}min
              </small>
              @if ($scheduler->max_posts_per_day)
                <small class="d-block text-muted">
                  <i class="ri-calendar-check-line"></i> Max {{ $scheduler->max_posts_per_day }}/day
                </small>
              @endif
            </td>
            <td>
              <div class="d-flex align-items-center">
                <span
                      class="badge bg-{{ $scheduler->posts_today >= ($scheduler->max_posts_per_day ?? 999) ? 'danger' : 'success' }}">
                  {{ $scheduler->posts_today }}
                  @if ($scheduler->max_posts_per_day)
                    / {{ $scheduler->max_posts_per_day }}
                  @endif
                </span>
              </div>
              @if ($scheduler->last_run_at)
                <small class="text-muted d-block">
                  Last: {{ $scheduler->last_run_at->diffForHumans() }}
                </small>
              @endif
            </td>
            <td>
              <form action="{{ route('offer-schedulers.toggle-status', $scheduler->id) }}" method="POST"
                    class="d-inline">
                @csrf
                @if ($scheduler->is_active)
                  <button type="submit" class="btn btn-sm btn-outline-success toggle-status-btn">
                    <i class="ri-checkbox-circle-line me-1"></i> {{ __('Active') }}
                  </button>
                @else
                  <button type="submit" class="btn btn-sm btn-outline-secondary toggle-status-btn">
                    <i class="ri-close-circle-line me-1"></i> {{ __('Inactive') }}
                  </button>
                @endif
              </form>
            </td>

            <x-data-display.table-actions>
              <li>
                <a href="{{ route('offer-schedulers.show', $scheduler->id) }}" class="dropdown-item">
                  <i class="ri-eye-line"></i> {{ __('View') }}
                </a>
              </li>
              <li>
                <a href="{{ route('offer-schedulers.edit', $scheduler->id) }}" class="dropdown-item">
                  <i class="ri-edit-box-line"></i> {{ __('Edit') }}
                </a>
              </li>
              <li>
                <form method="POST" action="{{ route('offer-schedulers.reset-counter', $scheduler->id) }}"
                      class="d-inline">
                  @csrf
                  <button type="submit" class="dropdown-item text-warning reset-counter-btn">
                    <i class="ri-restart-line"></i> {{ __('Reset Counter') }}
                  </button>
                </form>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <form method="POST" action="{{ route('offer-schedulers.destroy', $scheduler->id) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item text-danger remove-item-btn"
                          onclick="return confirm('{{ __('Are you sure you want to delete this scheduler?') }}')">
                    <i class="ri-delete-bin-line"></i> {{ __('Delete') }}
                  </button>
                </form>
              </li>
            </x-data-display.table-actions>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted text-center">
              {{ __('No schedulers found.') }}
            </td>
          </tr>
        @endforelse
      </x-data-display.tbody>
    </x-data-display.table>

    @if ($schedulers->hasPages())
      <div class="mt-3">
        {{ $schedulers->links() }}
      </div>
    @endif
  </x-data-display.card>

  @push('scripts')
    <script>
      document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const form = this.closest('form');
          const isActive = this.querySelector('.ri-checkbox-circle-line');

          Swal.fire({
            title: isActive ? 'Deactivate this scheduler?' : 'Activate this scheduler?',
            text: "You can change this status anytime.",
            icon: 'question',
            showCancelButton: true,
            customClass: {
              confirmButton: 'btn btn-primary w-xs me-2 mt-2',
              cancelButton: 'btn btn-danger w-xs mt-2',
            },
            confirmButtonText: isActive ? 'Yes, deactivate it!' : 'Yes, activate it!',
            buttonsStyling: false,
            showCloseButton: true,
          }).then((result) => {
            if (result.isConfirmed) {
              form.submit();
            }
          });
        });
      });

      document.querySelectorAll('.reset-counter-btn').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const form = this.closest('form');

          Swal.fire({
            title: 'Reset daily counter?',
            text: "This will reset today's post count to zero.",
            icon: 'warning',
            showCancelButton: true,
            customClass: {
              confirmButton: 'btn btn-primary w-xs me-2 mt-2',
              cancelButton: 'btn btn-danger w-xs mt-2',
            },
            confirmButtonText: 'Yes, reset it!',
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

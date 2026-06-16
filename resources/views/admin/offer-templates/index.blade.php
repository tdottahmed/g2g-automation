<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h5 class="card-title mb-0">{{ __('Offer Templates') }}</h5>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          {{-- Filters --}}
          <form method="GET" action="{{ route('offer-templates.index') }}"
                class="d-flex align-items-center gap-2 mb-0" id="filter-form">

            <select name="account" class="form-select form-select-sm" style="min-width:150px" onchange="this.form.submit()">
              <option value="">All Accounts</option>
              @foreach ($userAccounts as $ua)
                <option value="{{ $ua->id }}" {{ request('account') == $ua->id ? 'selected' : '' }}>
                  {{ $ua->owner_name }}
                </option>
              @endforeach
            </select>

            <select name="status" class="form-select form-select-sm" style="min-width:120px" onchange="this.form.submit()">
              <option value="">All Status</option>
              <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              <option value="queued_delete" {{ request('status') === 'queued_delete' ? 'selected' : '' }}>Queued Delete</option>
            </select>

            @if (request('account') || request('status'))
              <a href="{{ route('offer-templates.index') }}" class="btn btn-sm btn-outline-secondary" title="Clear filters">
                <i class="ri-close-line"></i>
              </a>
            @endif
          </form>

          <x-action.link href="{{ route('offer-templates.create') }}" icon="ri-add-line">
            {{ __('Create Template') }}
          </x-action.link>
        </div>
      </div>
    </x-slot>

    <x-data-display.table>
      <x-data-display.thead>
        <th style="width:40px">
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="select-all" title="Select all">
          </div>
        </th>
        <th>{{ __('Title') }}</th>
        <th>{{ __('Account') }}</th>
        <th>{{ __('Price') }}</th>
        <th class="text-center">{{ __('Status') }}</th>
        <th class="text-center">{{ __('Delete Queue') }}</th>
        <th>{{ __('Actions') }}</th>
      </x-data-display.thead>

      <x-data-display.tbody>
        @forelse ($offers as $offer)
          <tr id="template-row-{{ $offer->id }}">
            <td>
              <div class="form-check mb-0">
                <input
                  class="form-check-input row-checkbox"
                  type="checkbox"
                  value="{{ $offer->id }}"
                  data-is-active="{{ $offer->is_active ? '1' : '0' }}"
                  data-queue-delete="{{ $offer->queue_delete ? '1' : '0' }}">
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-medium">{{ str($offer->title)->limit(50) }}</span>
                @if (($offer->offers_to_generate ?? 0) > 0)
                  <span class="badge bg-warning text-dark badge-post-queue"
                        title="Queued for posting: {{ $offer->offers_to_generate }} run(s)">
                    <i class="ri-send-plane-line me-1"></i>Post ×{{ $offer->offers_to_generate }}
                  </span>
                @endif
                @if ($offer->queue_delete)
                  <span class="badge bg-danger badge-del-queue"
                        title="Queued for deletion from g2g.com">
                    <i class="ri-delete-bin-line me-1"></i>Del
                  </span>
                @endif
              </div>
              <div class="text-muted small mt-1">
                TH{{ $offer->th_level }}
                @if ($offer->king_level) &middot; K{{ $offer->king_level }} @endif
                @if ($offer->queen_level) &middot; Q{{ $offer->queen_level }} @endif
                &middot; ${{ number_format($offer->price, 2) }}
              </div>
            </td>
            <td>
              <span class="text-muted small">{{ $offer->userAccount->owner_name ?? '—' }}</span>
            </td>
            <td>${{ number_format($offer->price, 2) }}</td>
            <td class="text-center">
              <form action="{{ route('offer-templates.toggle-status', $offer->id) }}" method="POST" class="d-inline">
                @csrf
                @if ($offer->is_active)
                  <input type="hidden" name="status" value="0">
                  <button type="submit" class="btn btn-sm btn-outline-danger off-offer-btn">
                    <i class="ri-stop-circle-line me-1"></i>Active
                  </button>
                @else
                  <input type="hidden" name="status" value="1">
                  <button type="submit" class="btn btn-sm btn-outline-success start-offer-btn">
                    <i class="ri-play-circle-fill me-1"></i>Inactive
                  </button>
                @endif
              </form>
            </td>
            <td class="text-center">
              @if ($offer->queue_delete)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle queue-delete-badge-{{ $offer->id }}">
                  <i class="ri-delete-bin-line me-1"></i>Queued
                </span>
              @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle queue-delete-badge-{{ $offer->id }}">
                  —
                </span>
              @endif
            </td>

            <x-data-display.table-actions>
              <li>
                <a href="{{ route('offer-templates.edit', $offer->id) }}" class="dropdown-item">
                  <i class="ri-edit-box-line"></i> {{ __('Edit') }}
                </a>
              </li>
              <li>
                <button
                  type="button"
                  class="dropdown-item btn-queue-post-single"
                  data-template-id="{{ $offer->id }}">
                  <i class="ri-add-circle-line"></i> {{ __('Queue Post +1') }}
                </button>
              </li>
              <li>
                <button
                  type="button"
                  class="dropdown-item {{ $offer->queue_delete ? 'text-secondary' : 'text-warning' }} btn-toggle-queue-delete"
                  data-template-id="{{ $offer->id }}"
                  data-queued="{{ $offer->queue_delete ? '1' : '0' }}">
                  <i class="ri-delete-bin-2-line"></i>
                  {{ $offer->queue_delete ? __('Cancel Delete Queue') : __('Queue for Delete') }}
                </button>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <button
                  type="button"
                  class="dropdown-item text-danger btn-delete-template"
                  data-template-id="{{ $offer->id }}"
                  data-template-title="{{ $offer->title }}">
                  <i class="ri-delete-bin-line"></i> {{ __('Delete from DB') }}
                </button>
              </li>
            </x-data-display.table-actions>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted text-center py-4">
              {{ __('No offers found.') }}
            </td>
          </tr>
        @endforelse
      </x-data-display.tbody>
    </x-data-display.table>
  </x-data-display.card>

  {{-- ── Sticky bulk action bar ──────────────────────────────────────────────── --}}
  <div id="bulk-bar"
       class="position-fixed bottom-0 start-0 end-0 bg-dark text-white py-3 px-4 d-none"
       style="z-index:1050; box-shadow:0 -4px 20px rgba(0,0,0,.25);">
    <div class="d-flex align-items-center gap-3 flex-wrap justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-semibold" id="bulk-count-label">0 selected</span>
        <button type="button" class="btn btn-sm btn-outline-light" id="bulk-clear-btn" title="Clear selection">
          <i class="ri-close-line"></i>
        </button>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-success" data-bulk="activate">
          <i class="ri-play-circle-fill me-1"></i>Activate
        </button>
        <button type="button" class="btn btn-sm btn-warning text-dark" data-bulk="deactivate">
          <i class="ri-stop-circle-line me-1"></i>Deactivate
        </button>
        <button type="button" class="btn btn-sm btn-info text-dark" data-bulk="queue_post">
          <i class="ri-add-circle-line me-1"></i>Queue Post +1
        </button>
        <button type="button" class="btn btn-sm btn-danger" data-bulk="queue_delete">
          <i class="ri-delete-bin-2-line me-1"></i>Queue for Delete
        </button>
        <button type="button" class="btn btn-sm btn-outline-light" data-bulk="cancel_delete">
          <i class="ri-close-circle-line me-1"></i>Cancel Delete Queue
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" data-bulk="delete">
          <i class="ri-delete-bin-line me-1"></i>Delete from DB
        </button>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      const CSRF = '{{ csrf_token() }}';

      // ── Select all / row checkboxes ──────────────────────────────────────────
      const selectAllCb  = document.getElementById('select-all');
      const bulkBar      = document.getElementById('bulk-bar');
      const bulkLabel    = document.getElementById('bulk-count-label');
      const bulkClearBtn = document.getElementById('bulk-clear-btn');

      function getChecked() {
        return Array.from(document.querySelectorAll('.row-checkbox:checked'));
      }

      function updateBulkBar() {
        const checked = getChecked();
        if (checked.length > 0) {
          bulkLabel.textContent = `${checked.length} selected`;
          bulkBar.classList.remove('d-none');
        } else {
          bulkBar.classList.add('d-none');
        }
        selectAllCb.indeterminate = checked.length > 0 && checked.length < document.querySelectorAll('.row-checkbox').length;
        selectAllCb.checked       = checked.length > 0 && checked.length === document.querySelectorAll('.row-checkbox').length;
      }

      selectAllCb.addEventListener('change', function () {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        updateBulkBar();
      });

      document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
      });

      bulkClearBtn.addEventListener('click', function () {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
        selectAllCb.checked = false;
        updateBulkBar();
      });

      // ── Bulk action buttons ──────────────────────────────────────────────────
      document.querySelectorAll('[data-bulk]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const action = this.dataset.bulk;
          const ids    = getChecked().map(cb => parseInt(cb.value, 10));

          if (ids.length === 0) return;

          const labels = {
            activate:      `Activate ${ids.length} template(s)?`,
            deactivate:    `Deactivate ${ids.length} template(s)?`,
            queue_post:    `Add +1 to post queue for ${ids.length} template(s)?`,
            queue_delete:  `Queue ${ids.length} template(s) for deletion from g2g.com?`,
            cancel_delete: `Cancel delete queue for ${ids.length} template(s)?`,
            delete:        `Permanently delete ${ids.length} template(s) from the database?`,
          };

          const icons = {
            activate:      'question',
            deactivate:    'question',
            queue_post:    'question',
            queue_delete:  'warning',
            cancel_delete: 'question',
            delete:        'warning',
          };

          Swal.fire({
            title: labels[action],
            text: action === 'delete' || action === 'queue_delete'
              ? 'This action cannot be easily undone.'
              : 'You can change this anytime.',
            icon: icons[action],
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: action === 'delete' ? 'btn btn-danger w-xs me-2 mt-2' : 'btn btn-primary w-xs me-2 mt-2',
              cancelButton:  'btn btn-secondary w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch('/offer-templates/bulk-action', {
              method:  'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
              body:    JSON.stringify({ action, ids }),
            })
            .then(r => r.json())
            .then(function (data) {
              if (data.success) {
                if (action === 'delete') {
                  ids.forEach(id => {
                    const row = document.getElementById(`template-row-${id}`);
                    if (row) row.remove();
                  });
                }
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
                updateBulkBar();

                Swal.fire({
                  title: 'Done!',
                  text: `${data.count} template(s) updated.`,
                  icon: 'success',
                  timer: 1800,
                  showConfirmButton: false,
                }).then(() => {
                  if (action !== 'delete') window.location.reload();
                });
              }
            })
            .catch(function () {
              Swal.fire({ title: 'Error', text: 'Bulk action failed.', icon: 'error' });
            });
          });
        });
      });

      // ── Toggle status (single row) ───────────────────────────────────────────
      document.querySelectorAll('.start-offer-btn, .off-offer-btn').forEach(function (button) {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          const form       = this.closest('form');
          const isActivate = this.classList.contains('start-offer-btn');

          Swal.fire({
            title: isActivate ? 'Activate this offer?' : 'Deactivate this offer?',
            text: 'You can change this status anytime.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: isActivate ? 'Yes, activate!' : 'Yes, deactivate!',
            customClass: {
              confirmButton: 'btn btn-primary w-xs me-2 mt-2',
              cancelButton:  'btn btn-danger w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(function (result) {
            if (result.isConfirmed) form.submit();
          });
        });
      });

      // ── Queue post single ────────────────────────────────────────────────────
      document.querySelectorAll('.btn-queue-post-single').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id  = this.dataset.templateId;
          const row = document.getElementById(`template-row-${id}`);

          fetch(`/offer-templates/${id}/queue-post`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(function (data) {
            if (data.success) {
              // Upsert the "Post ×N" badge in the title cell
              if (row) {
                const titleDiv = row.querySelector('.d-flex.align-items-center.gap-2');
                let postBadge  = row.querySelector('.badge-post-queue');

                if (!postBadge) {
                  postBadge = document.createElement('span');
                  postBadge.className = 'badge bg-warning text-dark badge-post-queue';
                  postBadge.title = 'Queued for posting';
                  titleDiv.appendChild(postBadge);
                }
                postBadge.innerHTML = `<i class="ri-send-plane-line me-1"></i>Post ×${data.offers_to_generate}`;
              }
              Swal.fire({ title: 'Queued!', text: `Post queue: ${data.offers_to_generate}`, icon: 'success', timer: 1200, showConfirmButton: false });
            }
          });
        });
      });

      // ── Toggle queue-delete single ───────────────────────────────────────────
      document.querySelectorAll('.btn-toggle-queue-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id      = this.dataset.templateId;
          const queued  = this.dataset.queued === '1';
          const btnEl   = this;

          const title = queued
            ? 'Cancel delete queue for this template?'
            : 'Queue this template for deletion from g2g.com?';

          Swal.fire({
            title,
            text: queued ? '' : 'The runner will delete the matching g2g.com listing on next run.',
            icon: queued ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: queued ? 'btn btn-secondary w-xs me-2 mt-2' : 'btn btn-danger w-xs me-2 mt-2',
              cancelButton:  'btn btn-outline-secondary w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch(`/offer-templates/${id}/queue-delete`, {
              method:  'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(function (data) {
              if (data.success) {
                const row = document.getElementById(`template-row-${id}`);

                // Update the column badge
                const colBadge = document.querySelector(`.queue-delete-badge-${id}`);
                if (colBadge) {
                  if (data.queue_delete) {
                    colBadge.className = `badge bg-danger-subtle text-danger border border-danger-subtle queue-delete-badge-${id}`;
                    colBadge.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Queued';
                  } else {
                    colBadge.className = `badge bg-secondary-subtle text-secondary border border-secondary-subtle queue-delete-badge-${id}`;
                    colBadge.innerHTML = '—';
                  }
                }

                // Upsert / remove the inline "Del" badge in the title cell
                if (row) {
                  const titleDiv = row.querySelector('.d-flex.align-items-center.gap-2');
                  let   delBadge = row.querySelector('.badge-del-queue');

                  if (data.queue_delete) {
                    if (!delBadge) {
                      delBadge = document.createElement('span');
                      delBadge.className = 'badge bg-danger badge-del-queue';
                      delBadge.title = 'Queued for deletion from g2g.com';
                      titleDiv.appendChild(delBadge);
                    }
                    delBadge.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Del';
                  } else if (delBadge) {
                    delBadge.remove();
                  }
                }

                // Update dropdown button text + colour
                btnEl.dataset.queued = data.queue_delete ? '1' : '0';
                btnEl.className = btnEl.className.replace(/text-(warning|secondary)/, data.queue_delete ? 'text-secondary' : 'text-warning');
                btnEl.innerHTML = `<i class="ri-delete-bin-2-line"></i> ${data.queue_delete ? 'Cancel Delete Queue' : 'Queue for Delete'}`;
              }
            })
            .catch(function () {
              Swal.fire({ title: 'Error', text: 'Could not update queue.', icon: 'error' });
            });
          });
        });
      });

      // ── Delete from DB (single) ──────────────────────────────────────────────
      document.querySelectorAll('.btn-delete-template').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id    = this.dataset.templateId;
          const title = this.dataset.templateTitle;

          Swal.fire({
            title: 'Delete from database?',
            html: `<strong>${title}</strong><br><span class="text-muted small">This only removes the record here — it does NOT delete the g2g.com listing. Use "Queue for Delete" for that.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete from DB',
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: 'btn btn-danger w-xs me-2 mt-2',
              cancelButton:  'btn btn-secondary w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch(`/offer-templates/${id}`, {
              method:  'DELETE',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(function (data) {
              if (data.success) {
                const row = document.getElementById(`template-row-${id}`);
                if (row) row.remove();
                Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1500, showConfirmButton: false });
              }
            })
            .catch(function () {
              Swal.fire({ title: 'Error', text: 'Could not delete.', icon: 'error' });
            });
          });
        });
      });
    </script>
  @endpush
</x-layouts.admin.master>

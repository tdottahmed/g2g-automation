<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <h5 class="card-title mb-0">{{ __('Offer Templates') }}</h5>

        <div class="d-flex align-items-center gap-2 flex-wrap">
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

            <select name="game" class="form-select form-select-sm" style="min-width:160px" onchange="this.form.submit()">
              <option value="">All Games</option>
              @foreach (App\Models\OfferTemplate::GAMES as $slug => $gname)
                <option value="{{ $slug }}" {{ request('game') === $slug ? 'selected' : '' }}>{{ $gname }}</option>
              @endforeach
            </select>

            <select name="status" class="form-select form-select-sm" style="min-width:140px" onchange="this.form.submit()">
              <option value="">All Templates</option>
              <option value="permanent"     {{ request('status') === 'permanent'     ? 'selected' : '' }}>Permanent</option>
              <option value="non_permanent" {{ request('status') === 'non_permanent' ? 'selected' : '' }}>Non-Permanent</option>
            </select>

            @if (request('account') || request('status') || request('game'))
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
        <th class="text-center">{{ __('Permanent') }}</th>
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
                  data-is-permanent="{{ $offer->is_permanent ? '1' : '0' }}">
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge {{ $offer->gameBadgeClass() }}" title="{{ $offer->gameLabel() }}">{{ $offer->gameLabel() }}</span>
                <span class="fw-medium">{{ str($offer->title)->limit(50) }}</span>
                @if (($offer->offers_to_generate ?? 0) > 0)
                  <span class="badge bg-warning text-dark badge-post-queue"
                        title="Queued for posting: {{ $offer->offers_to_generate }} run(s)">
                    <i class="ri-send-plane-line me-1"></i>Post ×{{ $offer->offers_to_generate }}
                  </span>
                @endif
                @if ($offer->is_permanent)
                  <span class="badge bg-success-subtle text-success border border-success-subtle badge-permanent"
                        title="Protected — skipped during delete-all">
                    <i class="ri-shield-check-line me-1"></i>Permanent
                  </span>
                @endif
              </div>
              <div class="text-muted small mt-1">
                @if ($offer->gameSummary()) {{ $offer->gameSummary() }} &middot; @endif
                ${{ number_format($offer->price, 2) }}
              </div>
            </td>
            <td>
              @if ($offer->userAccounts->isNotEmpty())
                <div class="d-flex flex-wrap gap-1">
                  @foreach ($offer->userAccounts as $ua)
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $ua->owner_name }}</span>
                  @endforeach
                </div>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>${{ number_format($offer->price, 2) }}</td>

            {{-- Permanent toggle --}}
            <td class="text-center">
              <button
                type="button"
                class="btn btn-sm {{ $offer->is_permanent ? 'btn-success' : 'btn-outline-secondary' }} btn-toggle-permanent"
                data-template-id="{{ $offer->id }}"
                data-permanent="{{ $offer->is_permanent ? '1' : '0' }}"
                title="{{ $offer->is_permanent ? 'Click to unmark as permanent' : 'Click to mark as permanent (skipped on delete-all)' }}">
                <i class="{{ $offer->is_permanent ? 'ri-shield-check-line' : 'ri-shield-line' }}"></i>
                <span class="ms-1 d-none d-md-inline">{{ $offer->is_permanent ? 'Protected' : 'Deletable' }}</span>
              </button>
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
                  class="dropdown-item btn-queue-dequeue-single"
                  data-template-id="{{ $offer->id }}">
                  <i class="ri-indeterminate-circle-line"></i> {{ __('Queue Post -1') }}
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
            <td colspan="6" class="text-muted text-center py-4">
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
        <button type="button" class="btn btn-sm btn-success" data-bulk="mark_permanent">
          <i class="ri-shield-check-line me-1"></i>Mark Permanent
        </button>
        <button type="button" class="btn btn-sm btn-outline-light" data-bulk="unmark_permanent">
          <i class="ri-shield-line me-1"></i>Unmark Permanent
        </button>
        <button type="button" class="btn btn-sm btn-info text-dark" data-bulk="queue_post">
          <i class="ri-add-circle-line me-1"></i>Queue Post +1
        </button>
        <button type="button" class="btn btn-sm btn-outline-info" data-bulk="queue_dequeue">
          <i class="ri-indeterminate-circle-line me-1"></i>Queue Post -1
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
        const allBoxes = document.querySelectorAll('.row-checkbox');
        selectAllCb.indeterminate = checked.length > 0 && checked.length < allBoxes.length;
        selectAllCb.checked       = checked.length > 0 && checked.length === allBoxes.length;
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
            mark_permanent:   `Mark ${ids.length} template(s) as permanent?`,
            unmark_permanent: `Unmark ${ids.length} template(s) as permanent?`,
            queue_post:       `Add +1 to post queue for ${ids.length} template(s)?`,
            queue_dequeue:    `Remove -1 from post queue for ${ids.length} template(s)?`,
            delete:           `Permanently delete ${ids.length} template(s) from the database?`,
          };

          Swal.fire({
            title: labels[action],
            text: action === 'delete'
              ? 'This action cannot be undone.'
              : action === 'mark_permanent'
                ? 'These offers will be skipped during delete-all runs.'
                : action === 'queue_dequeue'
                  ? 'Queue count will not go below 0.'
                  : 'You can change this anytime.',
            icon: action === 'delete' ? 'warning' : 'question',
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

      // ── Toggle permanent (single) ────────────────────────────────────────────
      document.querySelectorAll('.btn-toggle-permanent').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id        = this.dataset.templateId;
          const permanent = this.dataset.permanent === '1';
          const btnEl     = this;

          Swal.fire({
            title: permanent ? 'Remove permanent protection?' : 'Mark as permanent?',
            text:  permanent
              ? 'This offer will be included in future delete-all runs.'
              : 'This offer will be skipped during delete-all runs.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: 'btn btn-primary w-xs me-2 mt-2',
              cancelButton:  'btn btn-outline-secondary w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(function (result) {
            if (!result.isConfirmed) return;

            fetch(`/offer-templates/${id}/toggle-permanent`, {
              method:  'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(function (data) {
              if (!data.success) return;

              const isPermanent = data.is_permanent;
              const row         = document.getElementById(`template-row-${id}`);

              // Update button appearance
              btnEl.dataset.permanent = isPermanent ? '1' : '0';
              btnEl.className = btnEl.className
                .replace(/btn-(success|outline-secondary)/, isPermanent ? 'btn-success' : 'btn-outline-secondary');
              btnEl.title = isPermanent
                ? 'Click to unmark as permanent'
                : 'Click to mark as permanent (skipped on delete-all)';
              btnEl.innerHTML = `<i class="${isPermanent ? 'ri-shield-check-line' : 'ri-shield-line'}"></i>`
                              + `<span class="ms-1 d-none d-md-inline">${isPermanent ? 'Protected' : 'Deletable'}</span>`;

              // Update inline badge in title cell
              if (row) {
                const titleDiv = row.querySelector('.d-flex.align-items-center.gap-2');
                let badge      = row.querySelector('.badge-permanent');

                if (isPermanent) {
                  if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge bg-success-subtle text-success border border-success-subtle badge-permanent';
                    badge.title = 'Protected — skipped during delete-all';
                    titleDiv.appendChild(badge);
                  }
                  badge.innerHTML = '<i class="ri-shield-check-line me-1"></i>Permanent';
                } else if (badge) {
                  badge.remove();
                }
              }
            })
            .catch(function () {
              Swal.fire({ title: 'Error', text: 'Could not update permanent status.', icon: 'error' });
            });
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

      // ── Queue dequeue single (-1) ────────────────────────────────────────────
      document.querySelectorAll('.btn-queue-dequeue-single').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id  = this.dataset.templateId;
          const row = document.getElementById(`template-row-${id}`);

          fetch(`/offer-templates/${id}/queue-dequeue`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(function (data) {
            if (data.success) {
              if (row) {
                const titleDiv = row.querySelector('.d-flex.align-items-center.gap-2');
                let postBadge  = row.querySelector('.badge-post-queue');

                if (data.offers_to_generate > 0) {
                  if (!postBadge) {
                    postBadge = document.createElement('span');
                    postBadge.className = 'badge bg-warning text-dark badge-post-queue';
                    postBadge.title = 'Queued for posting';
                    titleDiv.appendChild(postBadge);
                  }
                  postBadge.innerHTML = `<i class="ri-send-plane-line me-1"></i>Post ×${data.offers_to_generate}`;
                } else if (postBadge) {
                  postBadge.remove();
                }
              }
              Swal.fire({ title: 'Updated!', text: `Post queue: ${data.offers_to_generate}`, icon: 'success', timer: 1200, showConfirmButton: false });
            }
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
            html: `<strong>${title}</strong><br><span class="text-muted small">This only removes the record here — it does NOT delete the g2g.com listing.</span>`,
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

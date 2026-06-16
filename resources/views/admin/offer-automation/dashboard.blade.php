<x-layouts.admin.master>

  {{-- ── Runner info banner ──────────────────────────────────────────────────── --}}
  <div class="alert alert-primary border-0 d-flex align-items-center gap-3 mb-4 shadow-sm">
    <i class="ri-terminal-box-line fs-3 flex-shrink-0"></i>
    <div class="flex-grow-1">
      <div class="fw-semibold mb-1">Posting is handled by the local runner — no PHP commands needed.</div>
      <div class="small text-body-secondary">
        From <code>scripts/automation/</code>, run:
        <code class="ms-1 bg-primary bg-opacity-10 px-2 py-1 rounded">node runner.js --watch</code>
        <span class="mx-1">&middot;</span>
        <code class="bg-danger bg-opacity-10 px-2 py-1 rounded">node delete-specific-offers.js --api --watch</code>
        <span class="mx-1">&middot;</span>
        <code class="bg-warning bg-opacity-10 px-2 py-1 rounded">node delete-offers.js --api --watch</code>
        — polling every {{ $intervalMinutes }} min.
      </div>
    </div>
    <a href="{{ route('offer-logs.index') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
      <i class="ri-history-line me-1"></i>All Logs
    </a>
  </div>

  {{-- ── Stats row ────────────────────────────────────────────────────────────── --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center py-4">
          <div class="fs-2 fw-bold text-primary mb-1">{{ $activeTemplates }}</div>
          <div class="text-muted small">Active Templates</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center py-4">
          <div class="fs-2 fw-bold text-warning mb-1">{{ $pendingCount }}</div>
          <div class="text-muted small">Pending Now</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center py-4">
          <div class="fs-2 fw-bold text-success mb-1">{{ $postedToday }}</div>
          <div class="text-muted small">Posted Today</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center py-4">
          <div class="fs-2 fw-bold text-danger mb-1">{{ $failedToday }}</div>
          <div class="text-muted small">Failed Today</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Accounts & Templates ─────────────────────────────────────────────────── --}}
  <x-data-display.card class="mb-4">
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="ri-account-circle-line text-primary me-2"></i>Accounts &amp; Templates
        </h5>
        <div class="d-flex gap-2">
          <a href="{{ route('user-accounts.create') }}" class="btn btn-sm btn-outline-secondary">
            <i class="ri-user-add-line me-1"></i>New Account
          </a>
          <a href="{{ route('offer-templates.create') }}" class="btn btn-sm btn-primary">
            <i class="ri-add-line me-1"></i>New Template
          </a>
        </div>
      </div>
    </x-slot>

    @if ($userAccounts->isEmpty())
      <div class="py-5 text-center text-muted">
        <i class="ri-user-search-line display-3 d-block mb-3 opacity-25"></i>
        <p>No user accounts yet.</p>
        <a href="{{ route('user-accounts.create') }}" class="btn btn-primary btn-sm">
          <i class="ri-user-add-line me-1"></i>Add User Account
        </a>
      </div>
    @else
      <div class="accordion accordion-flush" id="usersAccordion">
        @foreach ($userAccounts as $index => $user)
          <div class="accordion-item" id="account-section-{{ $user->id }}">

            {{-- ── Accordion header ──────────────────────────────────────────── --}}
            <h2 class="accordion-header">
              <button
                class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} py-3 ps-3"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#user-pane-{{ $user->id }}"
                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                <div class="d-flex align-items-center gap-3 w-100 me-3">
                  <img
                    src="https://ui-avatars.com/api/?name={{ urlencode($user->email) }}&background=7269ef&color=fff&size=40"
                    class="rounded-circle flex-shrink-0" width="40" height="40" alt="">
                  <div>
                    <div class="fw-semibold">{{ $user->email }}</div>
                    <div class="text-muted small">
                      {{ $user->total_templates }} template{{ $user->total_templates !== 1 ? 's' : '' }}
                      &middot; {{ $user->active_templates_count }} active
                    </div>
                  </div>
                  <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                    @if ($user->queue_delete_all)
                      <span class="badge bg-danger d-none d-sm-inline-flex" title="Delete-all is queued — runner will wipe all g2g offers for this account">
                        <i class="ri-delete-bin-line me-1"></i>Delete All Queued
                      </span>
                    @endif
                    @if (($user->queue_delete_count ?? 0) > 0)
                      <span class="badge bg-danger-subtle text-danger border border-danger-subtle d-none d-sm-inline-flex">
                        <i class="ri-delete-bin-2-line me-1"></i>{{ $user->queue_delete_count }} del queued
                      </span>
                    @endif
                    @if ($user->is_generated_cookies)
                      <span class="badge bg-success-subtle text-success border border-success-subtle d-none d-md-inline-flex">
                        <i class="ri-shield-check-line me-1"></i>Cookies
                      </span>
                    @else
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle d-none d-md-inline-flex">
                        <i class="ri-alert-line me-1"></i>No Cookies
                      </span>
                    @endif
                    @if ($user->active_templates_count > 0)
                      <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="ri-checkbox-circle-line me-1"></i>{{ $user->active_templates_count }} active
                      </span>
                    @else
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">No active</span>
                    @endif
                  </div>
                </div>
              </button>
            </h2>

            {{-- ── Accordion body ────────────────────────────────────────────── --}}
            <div
              id="user-pane-{{ $user->id }}"
              class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
              data-bs-parent="#usersAccordion">
              <div class="accordion-body p-0">

                {{-- Account action bar --}}
                <div class="d-flex align-items-center gap-2 px-3 py-2 bg-light border-bottom flex-wrap">
                  <span class="text-muted small fw-medium me-auto">{{ $user->owner_name }}</span>

                  <a href="{{ route('offer-templates.create') }}?account={{ $user->id }}"
                     class="btn btn-sm btn-outline-primary">
                    <i class="ri-add-line"></i> Add Template
                  </a>
                  <a href="{{ route('user-accounts.edit', $user->id) }}"
                     class="btn btn-sm btn-outline-secondary">
                    <i class="ri-edit-line"></i> Edit Account
                  </a>

                  {{-- Delete All from g2g button --}}
                  <button
                    type="button"
                    class="btn btn-sm {{ $user->queue_delete_all ? 'btn-warning' : 'btn-outline-warning' }} btn-delete-all-g2g"
                    data-account-id="{{ $user->id }}"
                    data-account-email="{{ $user->email }}"
                    data-queued="{{ $user->queue_delete_all ? '1' : '0' }}"
                    title="{{ $user->queue_delete_all ? 'Cancel: un-queue delete-all' : 'Queue: delete ALL live offers for this account from g2g.com on next runner cycle' }}">
                    <i class="ri-delete-bin-2-line me-1"></i>
                    <span class="btn-delete-all-label-{{ $user->id }}">
                      {{ $user->queue_delete_all ? 'Cancel Delete All' : 'Delete All from g2g' }}
                    </span>
                  </button>

                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger btn-delete-account"
                    data-account-id="{{ $user->id }}"
                    data-account-email="{{ $user->email }}"
                    data-templates="{{ $user->total_templates }}">
                    <i class="ri-delete-bin-line"></i> Delete Account
                  </button>
                </div>

                {{-- Per-account bulk action bar (hidden until rows selected) --}}
                <div class="d-none px-3 py-2 border-bottom bg-dark text-white" id="bulk-bar-{{ $user->id }}">
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-semibold small" id="bulk-count-{{ $user->id }}">0 selected</span>
                    <div class="vr mx-1"></div>
                    <button type="button" class="btn btn-sm btn-success bulk-action-btn"
                            data-account="{{ $user->id }}" data-action="activate">
                      <i class="ri-play-circle-fill me-1"></i>Activate
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary bulk-action-btn"
                            data-account="{{ $user->id }}" data-action="deactivate">
                      <i class="ri-stop-circle-line me-1"></i>Deactivate
                    </button>
                    <button type="button" class="btn btn-sm btn-info text-dark bulk-action-btn"
                            data-account="{{ $user->id }}" data-action="queue_post">
                      <i class="ri-add-circle-line me-1"></i>Queue Post +1
                    </button>
                    <button type="button" class="btn btn-sm btn-danger bulk-action-btn"
                            data-account="{{ $user->id }}" data-action="queue_delete">
                      <i class="ri-delete-bin-2-line me-1"></i>Queue Delete
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light bulk-action-btn"
                            data-account="{{ $user->id }}" data-action="cancel_delete">
                      <i class="ri-close-circle-line me-1"></i>Cancel Delete Queue
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger bulk-action-btn"
                            data-account="{{ $user->id }}" data-action="delete">
                      <i class="ri-delete-bin-line me-1"></i>Delete from DB
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light bulk-clear-btn ms-auto"
                            data-account="{{ $user->id }}">
                      <i class="ri-close-line"></i> Clear
                    </button>
                  </div>
                </div>

                {{-- Template table --}}
                @if ($user->offers->isEmpty())
                  <div class="py-4 text-center text-muted small">
                    No templates for this account.
                    <a href="{{ route('offer-templates.create') }}">Create one →</a>
                  </div>
                @else
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width:36px" class="ps-3">
                            <div class="form-check mb-0">
                              <input class="form-check-input tpl-select-all"
                                     type="checkbox"
                                     data-account="{{ $user->id }}"
                                     title="Select all for {{ $user->email }}">
                            </div>
                          </th>
                          <th>Template</th>
                          <th class="text-center" style="width:70px">Active</th>
                          <th class="text-center" style="width:90px">Post Q</th>
                          <th style="width:140px">Last Posted</th>
                          <th class="text-end pe-3" style="width:220px">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($user->offers as $template)
                          <tr id="template-row-{{ $template->id }}">
                            <td class="ps-3">
                              <div class="form-check mb-0">
                                <input
                                  class="form-check-input tpl-cb"
                                  type="checkbox"
                                  value="{{ $template->id }}"
                                  data-account="{{ $user->id }}">
                              </div>
                            </td>

                            <td>
                              <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-medium">{{ $template->title }}</span>
                                @if (($template->offers_to_generate ?? 0) > 0)
                                  <span class="badge bg-warning text-dark badge-post-queue-{{ $template->id }}"
                                        title="Queued for posting: {{ $template->offers_to_generate }} run(s)">
                                    <i class="ri-send-plane-line me-1"></i>Post ×{{ $template->offers_to_generate }}
                                  </span>
                                @endif
                                @if ($template->queue_delete)
                                  <span class="badge bg-danger badge-del-queue-{{ $template->id }}"
                                        title="Queued for deletion from g2g.com">
                                    <i class="ri-delete-bin-line me-1"></i>Del
                                  </span>
                                @endif
                              </div>
                              <div class="text-muted small mt-1">
                                TH{{ $template->th_level }}
                                @if ($template->king_level) &middot; K{{ $template->king_level }} @endif
                                &middot; ${{ number_format($template->price, 2) }}
                              </div>
                            </td>

                            <td class="text-center">
                              <div class="form-check form-switch d-inline-flex justify-content-center mb-0">
                                <input
                                  class="form-check-input toggle-active"
                                  type="checkbox"
                                  role="switch"
                                  data-template-id="{{ $template->id }}"
                                  {{ $template->is_active ? 'checked' : '' }}>
                              </div>
                            </td>

                            <td class="text-center">
                              @php $queued = $template->offers_to_generate ?? 0; @endphp
                              <span
                                class="badge queued-count-badge {{ $queued > 0 ? 'bg-warning text-dark' : 'bg-secondary-subtle text-secondary' }}"
                                id="queued-{{ $template->id }}">
                                {{ $queued }}
                              </span>
                            </td>

                            <td>
                              <span class="text-muted small" title="{{ $template->last_posted_at }}">
                                {{ $template->last_posted_at ? $template->last_posted_at->diffForHumans() : '—' }}
                              </span>
                            </td>

                            <td class="text-end pe-3">
                              {{-- Queue Post --}}
                              <button
                                class="btn btn-sm btn-outline-warning btn-queue-post"
                                data-template-id="{{ $template->id }}"
                                title="Add 1 forced post">
                                <i class="ri-add-circle-line"></i> Queue
                              </button>

                              {{-- Queue for g2g Delete --}}
                              <button
                                type="button"
                                class="btn btn-sm ms-1 {{ $template->queue_delete ? 'btn-danger' : 'btn-outline-secondary' }} btn-toggle-del-queue"
                                data-template-id="{{ $template->id }}"
                                data-queued="{{ $template->queue_delete ? '1' : '0' }}"
                                title="{{ $template->queue_delete ? 'Cancel g2g delete queue' : 'Queue for deletion from g2g.com' }}">
                                <i class="ri-delete-bin-2-line"></i>
                              </button>

                              {{-- Edit --}}
                              <a
                                href="{{ route('offer-templates.edit', $template->id) }}"
                                class="btn btn-sm btn-outline-secondary ms-1"
                                title="Edit template">
                                <i class="ri-edit-line"></i>
                              </a>

                              {{-- Delete from DB --}}
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-danger ms-1 btn-delete-template"
                                data-template-id="{{ $template->id }}"
                                data-template-title="{{ $template->title }}"
                                title="Delete template record from database">
                                <i class="ri-delete-bin-line"></i>
                              </button>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @endif

              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </x-data-display.card>

  {{-- ── Recent logs ──────────────────────────────────────────────────────────── --}}
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="ri-history-line text-primary me-2"></i>Recent Logs
        </h5>
        <a href="{{ route('offer-logs.index') }}" class="btn btn-sm btn-outline-secondary">
          View All →
        </a>
      </div>
    </x-slot>

    @if ($recentLogs->isEmpty())
      <div class="py-5 text-center text-muted">
        <i class="ri-file-list-3-line display-3 d-block mb-2 opacity-25"></i>
        No logs yet. Run the local runner to start posting.
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:130px">When</th>
              <th>Template</th>
              <th style="width:90px" class="text-center">Status</th>
              <th>Message</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($recentLogs as $log)
              <tr>
                <td class="text-muted small text-nowrap">
                  <span title="{{ $log->executed_at }}">{{ $log->executed_at?->diffForHumans() ?? '—' }}</span>
                </td>
                <td class="small fw-medium">
                  {{ $log->template?->title ?? '—' }}
                </td>
                <td class="text-center">
                  @if ($log->status === 'success')
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                      <i class="ri-checkbox-circle-line me-1"></i>success
                    </span>
                  @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                      <i class="ri-close-circle-line me-1"></i>failed
                    </span>
                  @endif
                </td>
                <td class="text-muted small">{{ Str::limit($log->message, 100) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </x-data-display.card>

  @push('scripts')
    <script>
      const CSRF = '{{ csrf_token() }}';

      // ── Helpers ──────────────────────────────────────────────────────────────

      function getCheckedForAccount(accountId) {
        return Array.from(document.querySelectorAll(`.tpl-cb[data-account="${accountId}"]:checked`));
      }

      function updateBulkBar(accountId) {
        const checked   = getCheckedForAccount(accountId);
        const bar       = document.getElementById(`bulk-bar-${accountId}`);
        const label     = document.getElementById(`bulk-count-${accountId}`);
        const selectAll = document.querySelector(`.tpl-select-all[data-account="${accountId}"]`);
        const total     = document.querySelectorAll(`.tpl-cb[data-account="${accountId}"]`).length;

        if (bar)   bar.classList.toggle('d-none', checked.length === 0);
        if (label) label.textContent = `${checked.length} selected`;
        if (selectAll) {
          selectAll.indeterminate = checked.length > 0 && checked.length < total;
          selectAll.checked       = checked.length === total && total > 0;
        }
      }

      // ── Select-all per account ───────────────────────────────────────────────
      document.querySelectorAll('.tpl-select-all').forEach(function (cb) {
        cb.addEventListener('change', function () {
          const accountId = this.dataset.account;
          document.querySelectorAll(`.tpl-cb[data-account="${accountId}"]`)
            .forEach(c => c.checked = this.checked);
          updateBulkBar(accountId);
        });
      });

      document.querySelectorAll('.tpl-cb').forEach(function (cb) {
        cb.addEventListener('change', function () {
          updateBulkBar(this.dataset.account);
        });
      });

      // ── Bulk clear ───────────────────────────────────────────────────────────
      document.querySelectorAll('.bulk-clear-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const accountId = this.dataset.account;
          document.querySelectorAll(`.tpl-cb[data-account="${accountId}"]`)
            .forEach(c => c.checked = false);
          updateBulkBar(accountId);
        });
      });

      // ── Bulk action buttons ──────────────────────────────────────────────────
      const bulkLabels = {
        activate:      (n) => `Activate ${n} template(s)?`,
        deactivate:    (n) => `Deactivate ${n} template(s)?`,
        queue_post:    (n) => `Add +1 post queue to ${n} template(s)?`,
        queue_delete:  (n) => `Queue ${n} template(s) for deletion from g2g.com?`,
        cancel_delete: (n) => `Cancel delete queue for ${n} template(s)?`,
        delete:        (n) => `Permanently delete ${n} template(s) from the database?`,
      };

      document.querySelectorAll('.bulk-action-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const accountId = this.dataset.account;
          const action    = this.dataset.action;
          const checked   = getCheckedForAccount(accountId);
          const ids       = checked.map(c => parseInt(c.value, 10));

          if (ids.length === 0) return;

          Swal.fire({
            title: bulkLabels[action](ids.length),
            text: (action === 'delete' || action === 'queue_delete') ? 'This cannot be easily undone.' : 'You can change this anytime.',
            icon: (action === 'delete' || action === 'queue_delete') ? 'warning' : 'question',
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
                  document.querySelectorAll(`.tpl-cb[data-account="${accountId}"]`)
                    .forEach(c => c.checked = false);
                  updateBulkBar(accountId);
                  Swal.fire({ title: 'Done!', text: `${data.count} template(s) deleted.`, icon: 'success', timer: 1800, showConfirmButton: false });
                } else {
                  Swal.fire({
                    title: 'Done!', text: `${data.count} template(s) updated.`, icon: 'success', timer: 1800, showConfirmButton: false,
                  }).then(() => window.location.reload());
                }
              }
            })
            .catch(function () {
              Swal.fire({ title: 'Error', text: 'Bulk action failed.', icon: 'error' });
            });
          });
        });
      });

      // ── Toggle active ────────────────────────────────────────────────────────
      document.querySelectorAll('.toggle-active').forEach(function (el) {
        el.addEventListener('change', function () {
          const id      = this.dataset.templateId;
          const checked = this.checked;
          const self    = this;

          fetch(`/offer-templates/toggle-status/${id}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body:    JSON.stringify({ status: checked ? 1 : 0 }),
          })
          .then(r => r.json())
          .then(function (data) {
            if (!data.success) self.checked = !checked;
          })
          .catch(function () { self.checked = !checked; });
        });
      });

      // ── Queue Post (single) ──────────────────────────────────────────────────
      document.querySelectorAll('.btn-queue-post').forEach(function (el) {
        el.addEventListener('click', function () {
          const id  = this.dataset.templateId;
          const btn = this;

          btn.disabled = true;

          fetch(`/offer-templates/${id}/queue-post`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(function (data) {
            if (data.success) {
              const n = data.offers_to_generate;

              // Update column badge
              const countBadge = document.getElementById(`queued-${id}`);
              if (countBadge) {
                countBadge.textContent = n;
                countBadge.className = 'badge queued-count-badge bg-warning text-dark';
              }

              // Update / create inline title badge
              const row = document.getElementById(`template-row-${id}`);
              if (row) {
                const titleWrap = row.querySelector('.d-flex.align-items-center.gap-2');
                let   pb = titleWrap.querySelector(`.badge-post-queue-${id}`);
                if (!pb) {
                  pb = document.createElement('span');
                  pb.className = `badge bg-warning text-dark badge-post-queue-${id}`;
                  pb.title = 'Queued for posting';
                  titleWrap.appendChild(pb);
                }
                pb.innerHTML = `<i class="ri-send-plane-line me-1"></i>Post ×${n}`;
              }
            }
          })
          .finally(function () { btn.disabled = false; });
        });
      });

      // ── Toggle Queue-Delete (single) ─────────────────────────────────────────
      document.querySelectorAll('.btn-toggle-del-queue').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id     = this.dataset.templateId;
          const queued = this.dataset.queued === '1';
          const btnEl  = this;

          Swal.fire({
            title: queued ? 'Cancel delete queue?' : 'Queue for deletion from g2g.com?',
            text:  queued ? '' : 'The runner will delete the matching g2g.com listing on its next cycle.',
            icon:  queued ? 'question' : 'warning',
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
              if (!data.success) return;

              const isQueued = data.queue_delete;

              // Update the toggle button style
              btnEl.dataset.queued = isQueued ? '1' : '0';
              btnEl.className = btnEl.className
                .replace(/btn-(outline-secondary|danger)/, isQueued ? 'btn-danger' : 'btn-outline-secondary');
              btnEl.title = isQueued ? 'Cancel g2g delete queue' : 'Queue for deletion from g2g.com';

              // Update / remove inline title badge
              const row = document.getElementById(`template-row-${id}`);
              if (row) {
                const titleWrap = row.querySelector('.d-flex.align-items-center.gap-2');
                let   db = titleWrap.querySelector(`.badge-del-queue-${id}`);

                if (isQueued) {
                  if (!db) {
                    db = document.createElement('span');
                    db.className = `badge bg-danger badge-del-queue-${id}`;
                    db.title = 'Queued for deletion from g2g.com';
                    titleWrap.appendChild(db);
                  }
                  db.innerHTML = '<i class="ri-delete-bin-line me-1"></i>Del';
                } else if (db) {
                  db.remove();
                }
              }
            })
            .catch(function () {
              Swal.fire({ title: 'Error', text: 'Could not update queue.', icon: 'error' });
            });
          });
        });
      });

      // ── Delete template from DB ──────────────────────────────────────────────
      document.querySelectorAll('.btn-delete-template').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id    = this.dataset.templateId;
          const title = this.dataset.templateTitle;

          Swal.fire({
            title: 'Delete template record?',
            html: `<strong>${title}</strong><br><span class="text-muted small">Removes the record from this database only. Use "Queue Delete" to remove the live g2g.com listing.</span>`,
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
              Swal.fire({ title: 'Error', text: 'Could not delete template.', icon: 'error' });
            });
          });
        });
      });

      // ── Delete All from g2g ──────────────────────────────────────────────────
      document.querySelectorAll('.btn-delete-all-g2g').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id      = this.dataset.accountId;
          const email   = this.dataset.accountEmail;
          const queued  = this.dataset.queued === '1';
          const btnEl   = this;

          if (queued) {
            // Cancel the queue
            Swal.fire({
              title: 'Cancel delete-all?',
              text: `The runner will no longer wipe all offers for ${email}.`,
              icon: 'question',
              showCancelButton: true,
              confirmButtonText: 'Yes, cancel it',
              cancelButtonText: 'Keep queued',
              customClass: {
                confirmButton: 'btn btn-secondary w-xs me-2 mt-2',
                cancelButton:  'btn btn-outline-secondary w-xs mt-2',
              },
              buttonsStyling: false,
            }).then(function (result) {
              if (!result.isConfirmed) return;
              toggleDeleteAll(id, email, false, btnEl);
            });
          } else {
            // Queue the delete-all
            Swal.fire({
              title: 'Delete ALL offers from g2g.com?',
              html: `<strong>${email}</strong><br>
                     <span class="text-muted small">This will queue a full wipe of <em>every live offer</em> for this account from g2g.com. The runner must be running with <code>--api --watch</code> to pick it up.</span>`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, queue delete-all',
              cancelButtonText: 'Cancel',
              customClass: {
                confirmButton: 'btn btn-danger w-xs me-2 mt-2',
                cancelButton:  'btn btn-secondary w-xs mt-2',
              },
              buttonsStyling: false,
              showCloseButton: true,
            }).then(function (result) {
              if (!result.isConfirmed) return;
              toggleDeleteAll(id, email, true, btnEl);
            });
          }
        });
      });

      function toggleDeleteAll(accountId, email, queueing, btnEl) {
        fetch(`/user-accounts/${accountId}/queue-delete-all`, {
          method:  'POST',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(function (data) {
          if (!data.success) return;

          const isQueued = data.queue_delete_all;
          btnEl.dataset.queued = isQueued ? '1' : '0';

          // Update button style and label
          const label = document.querySelector(`.btn-delete-all-label-${accountId}`);
          if (label) label.textContent = isQueued ? 'Cancel Delete All' : 'Delete All from g2g';
          btnEl.className = btnEl.className.replace(/btn-(warning|outline-warning)/, isQueued ? 'btn-warning' : 'btn-outline-warning');

          Swal.fire({
            title: isQueued ? 'Delete-all queued!' : 'Cancelled.',
            text: isQueued
              ? 'Run: node delete-offers.js --api --watch  to execute it.'
              : 'Delete-all has been cancelled.',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false,
          });
        })
        .catch(function () {
          Swal.fire({ title: 'Error', text: 'Could not update.', icon: 'error' });
        });
      }

      // ── Delete Account ───────────────────────────────────────────────────────
      document.querySelectorAll('.btn-delete-account').forEach(function (btn) {
        btn.addEventListener('click', function () {
          const id        = this.dataset.accountId;
          const email     = this.dataset.accountEmail;
          const templates = parseInt(this.dataset.templates, 10);
          const warning   = templates > 0
            ? `This will also permanently delete <strong>${templates} template${templates !== 1 ? 's' : ''}</strong> linked to this account.`
            : 'This action cannot be undone.';

          Swal.fire({
            title: 'Delete account?',
            html: `<strong>${email}</strong><br><span class="text-muted small">${warning}</span>`,
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
            .then(r => r.json())
            .then(function (data) {
              if (data.success) {
                const section = document.getElementById(`account-section-${id}`);
                if (section) section.remove();
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

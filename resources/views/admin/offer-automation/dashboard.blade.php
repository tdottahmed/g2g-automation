<x-layouts.admin.master>

  {{-- ── Runner info banner ──────────────────────────────────────────────────── --}}
  <div class="alert alert-primary border-0 d-flex align-items-center gap-3 mb-4 shadow-sm">
    <i class="ri-terminal-box-line fs-3 flex-shrink-0"></i>
    <div class="flex-grow-1">
      <div class="fw-semibold mb-1">Posting is handled by the local runner — no PHP commands needed.</div>
      <div class="small text-body-secondary">
        From <code>scripts/automation/</code>, copy <code>.env.example → .env</code>, fill in your API URL + key, then run:
        <code class="ms-1 bg-primary bg-opacity-10 px-2 py-1 rounded">node runner.js --watch</code>
        <span class="mx-1">or</span>
        <code class="bg-primary bg-opacity-10 px-2 py-1 rounded">npm run watch</code>
        for continuous polling every {{ $intervalMinutes }} min.
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

  {{-- ── User accounts + templates ────────────────────────────────────────────── --}}
  <x-data-display.card class="mb-4">
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="ri-account-circle-line text-primary me-2"></i>Accounts &amp; Templates
        </h5>
        <a href="{{ route('offer-templates.create') }}" class="btn btn-sm btn-primary">
          <i class="ri-add-line me-1"></i>New Template
        </a>
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
          <div class="accordion-item">
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
                      &middot;
                      {{ $user->active_templates_count }} active
                    </div>
                  </div>
                  <div class="ms-auto d-flex gap-2">
                    @if ($user->active_templates_count > 0)
                      <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="ri-checkbox-circle-line me-1"></i>{{ $user->active_templates_count }} active
                      </span>
                    @else
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                        No active templates
                      </span>
                    @endif
                  </div>
                </div>
              </button>
            </h2>

            <div
              id="user-pane-{{ $user->id }}"
              class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
              data-bs-parent="#usersAccordion">
              <div class="accordion-body p-0">
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
                          <th class="ps-3">Template</th>
                          <th class="text-center" style="width:80px">Active</th>
                          <th class="text-center" style="width:100px">Queued</th>
                          <th style="width:160px">Last Posted</th>
                          <th class="text-end pe-3" style="width:160px">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($user->offers as $template)
                          <tr id="template-row-{{ $template->id }}">
                            <td class="ps-3">
                              <div class="fw-medium">{{ $template->title }}</div>
                              <div class="text-muted small">
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
                                class="badge queued-badge {{ $queued > 0 ? 'bg-warning text-dark' : 'bg-secondary-subtle text-secondary' }}"
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
                              <button
                                class="btn btn-sm btn-outline-warning btn-queue-post"
                                data-template-id="{{ $template->id }}"
                                title="Add 1 forced post — runner will pick it up next run">
                                <i class="ri-add-circle-line"></i> Queue
                              </button>
                              <a
                                href="{{ route('offer-templates.edit', $template->id) }}"
                                class="btn btn-sm btn-outline-secondary ms-1"
                                title="Edit template">
                                <i class="ri-edit-line"></i>
                              </a>
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
                  <span title="{{ $log->executed_at }}">
                    {{ $log->executed_at?->diffForHumans() ?? '—' }}
                  </span>
                </td>
                <td class="small fw-medium">
                  {{ $log->template?->title ?? '<deleted>' }}
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
                <td class="text-muted small">
                  {{ Str::limit($log->message, 100) }}
                </td>
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
          .then(data => {
            if (!data.success) self.checked = !checked; // revert on failure
          })
          .catch(() => { self.checked = !checked; });
        });
      });

      // ── Queue post ───────────────────────────────────────────────────────────
      document.querySelectorAll('.btn-queue-post').forEach(function (el) {
        el.addEventListener('click', function () {
          const id   = this.dataset.templateId;
          const btn  = this;

          btn.disabled = true;

          fetch(`/offer-templates/${id}/queue-post`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              const badge = document.getElementById(`queued-${id}`);
              if (badge) {
                badge.textContent = data.offers_to_generate;
                badge.className = 'badge queued-badge bg-warning text-dark';
              }
            }
          })
          .finally(() => { btn.disabled = false; });
        });
      });
    </script>
  @endpush

</x-layouts.admin.master>

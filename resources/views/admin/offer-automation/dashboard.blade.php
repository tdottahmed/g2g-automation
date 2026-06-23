<x-layouts.admin.master>

  {{-- ── Runner info banner ──────────────────────────────────────────────────── --}}
  <div class="alert alert-primary border-0 d-flex align-items-center gap-3 mb-4 shadow-sm">
    <i class="ri-desktop-line fs-3 flex-shrink-0"></i>
    <div class="flex-grow-1">
      <div class="fw-semibold mb-1">Posting is handled by the G2G Automation Desktop App.</div>
      <div class="small text-body-secondary">
        Keep the desktop app running and connected. It polls every
        <strong>{{ $intervalMinutes }} min</strong> for pending templates and reports results back automatically.
        Permanent offers are always skipped during delete-all runs.
      </div>
    </div>
    <a href="{{ route('offer-logs.index') }}" class="btn btn-sm btn-outline-primary flex-shrink-0">
      <i class="ri-history-line me-1"></i>All Logs
    </a>
  </div>

  {{-- ── Stats row ────────────────────────────────────────────────────────────── --}}
  <div class="row g-3 mb-4">
    @php
      $stats = [
        ['icon'=>'ri-account-circle-line','color'=>'primary',  'value'=>$userAccounts->count(),'label'=>'Accounts'],
        ['icon'=>'ri-file-list-3-line',   'color'=>'info',     'value'=>$totalTemplates,        'label'=>'Total Templates'],
        ['icon'=>'ri-shield-check-line',  'color'=>'success',  'value'=>$permanentCount,        'label'=>'Permanent'],
        ['icon'=>'ri-send-plane-line',    'color'=>'warning',  'value'=>$queuedPostsCount,      'label'=>'Queued Posts'],
        ['icon'=>'ri-checkbox-circle-line','color'=>'success', 'value'=>$postedToday,           'label'=>'Posted Today'],
        ['icon'=>'ri-close-circle-line',  'color'=>'danger',   'value'=>$failedToday,           'label'=>'Failed Today'],
      ];
    @endphp
    @foreach ($stats as $s)
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center py-3 px-2">
            <div class="avatar-sm mx-auto mb-2">
              <div class="avatar-title bg-{{ $s['color'] }}-subtle text-{{ $s['color'] }} rounded-circle">
                <i class="{{ $s['icon'] }} fs-18"></i>
              </div>
            </div>
            <div class="fs-3 fw-bold text-{{ $s['color'] }} mb-0 lh-1">{{ $s['value'] }}</div>
            <div class="text-muted small mt-1">{{ $s['label'] }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- ── Quick Actions ─────────────────────────────────────────────────────────── --}}
  <x-data-display.card class="mb-4">
    <x-slot name="header">
      <h5 class="card-title mb-0">
        <i class="ri-flashlight-line text-primary me-2"></i>Quick Actions
      </h5>
    </x-slot>

    <div class="row g-3">

      {{-- 1. Template Management --}}
      <div class="col-sm-6 col-xl">
        <div class="card border-0 bg-body-tertiary h-100">
          <div class="card-body d-flex flex-column gap-2 p-3">
            <div class="avatar-sm">
              <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                <i class="ri-layout-grid-line fs-18"></i>
              </div>
            </div>
            <h6 class="fw-semibold mb-0">Template Management</h6>
            <p class="text-muted small mb-0 flex-grow-1">Browse, search, and manage offer templates for a specific account.</p>
            <button type="button" class="btn btn-sm btn-primary w-100 qa-btn" data-action="manage-templates">
              <i class="ri-layout-grid-line me-1"></i>Open Templates
            </button>
          </div>
        </div>
      </div>

      {{-- 2. Post for Account --}}
      <div class="col-sm-6 col-xl">
        <div class="card border-0 bg-body-tertiary h-100">
          <div class="card-body d-flex flex-column gap-2 p-3">
            <div class="avatar-sm">
              <div class="avatar-title bg-success-subtle text-success rounded-circle">
                <i class="ri-send-plane-line fs-18"></i>
              </div>
            </div>
            <h6 class="fw-semibold mb-0">Post for Account</h6>
            <p class="text-muted small mb-0 flex-grow-1">Queue all templates for one specific account to be posted on the next desktop app cycle.</p>
            <button type="button" class="btn btn-sm btn-success w-100 qa-btn" data-action="queue-post-all">
              <i class="ri-send-plane-line me-1"></i>Queue for Account
            </button>
          </div>
        </div>
      </div>

      {{-- 3. Delete All Except Permanent --}}
      <div class="col-sm-6 col-xl">
        <div class="card border-0 bg-body-tertiary h-100">
          <div class="card-body d-flex flex-column gap-2 p-3">
            <div class="avatar-sm">
              <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                <i class="ri-shield-line fs-18"></i>
              </div>
            </div>
            <h6 class="fw-semibold mb-0">Delete Except Permanent</h6>
            <p class="text-muted small mb-0 flex-grow-1">Queue deletion of all non-permanent offers from g2g.com. Permanent offers are kept safe.</p>
            <button type="button" class="btn btn-sm btn-warning w-100 qa-btn" data-action="delete-except-permanent">
              <i class="ri-shield-line me-1"></i>Delete Non-Permanent
            </button>
          </div>
        </div>
      </div>

      {{-- 4. Post All Accounts --}}
      <div class="col-sm-6 col-xl">
        <div class="card border-0 bg-body-tertiary h-100">
          <div class="card-body d-flex flex-column gap-2 p-3">
            <div class="avatar-sm">
              <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                <i class="ri-play-circle-line fs-18"></i>
              </div>
            </div>
            <h6 class="fw-semibold mb-0">Post All Accounts</h6>
            <p class="text-muted small mb-0 flex-grow-1">Queue all templates across every account for the next desktop app cycle at once.</p>
            <button type="button" class="btn btn-sm btn-primary w-100" id="qa-post-all-btn">
              <i class="ri-play-circle-line me-1"></i>Post All Now
            </button>
          </div>
        </div>
      </div>

      {{-- 5. Account Management --}}
      <div class="col-sm-6 col-xl">
        <div class="card border-0 bg-body-tertiary h-100">
          <div class="card-body d-flex flex-column gap-2 p-3">
            <div class="avatar-sm">
              <div class="avatar-title bg-info-subtle text-info rounded-circle">
                <i class="ri-account-circle-line fs-18"></i>
              </div>
            </div>
            <h6 class="fw-semibold mb-0">Account Management</h6>
            <p class="text-muted small mb-0 flex-grow-1">Add, edit, or remove g2g.com accounts and manage their credentials.</p>
            <a href="{{ route('user-accounts.index') }}" class="btn btn-sm btn-info text-white w-100">
              <i class="ri-account-circle-line me-1"></i>Manage Accounts
            </a>
          </div>
        </div>
      </div>

    </div>
  </x-data-display.card>

  {{-- ── Accounts section ─────────────────────────────────────────────────────── --}}
  <x-data-display.card class="mb-4">
    <x-slot name="header">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <h5 class="card-title mb-0 me-auto">
          <i class="ri-account-circle-line text-primary me-2"></i>Accounts
        </h5>

        {{-- Account search --}}
        <div class="input-group input-group-sm" style="max-width:220px">
          <span class="input-group-text bg-transparent border-end-0">
            <i class="ri-search-line text-muted"></i>
          </span>
          <input type="text" id="account-search" class="form-control border-start-0 ps-0"
                 placeholder="Search accounts…">
        </div>

        <a href="{{ route('user-accounts.create') }}" class="btn btn-sm btn-outline-secondary">
          <i class="ri-user-add-line me-1"></i>New Account
        </a>
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
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="account-table">
          <thead class="table-light">
            <tr>
              <th>Account</th>
              <th class="text-center" style="width:110px">Templates</th>
              <th class="text-center" style="width:90px">Protected</th>
              <th class="text-center" style="width:80px">Post Queue</th>
              <th class="text-center" style="width:110px">Delete All</th>
              <th class="text-end pe-3" style="width:200px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($userAccounts as $account)
              <tr class="account-row" data-email="{{ strtolower($account->email) }}" data-owner="{{ strtolower($account->owner_name ?? '') }}"
                  id="account-row-{{ $account->id }}">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($account->email) }}&background=7269ef&color=fff&size=36"
                         class="rounded-circle flex-shrink-0" width="36" height="36" alt="">
                    <div>
                      <div class="fw-semibold lh-sm">{{ $account->email }}</div>
                      @if ($account->owner_name)
                        <div class="text-muted small">{{ $account->owner_name }}</div>
                      @endif
                    </div>
                    @if (!$account->is_generated_cookies)
                      <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1"
                            title="No saved cookies — desktop app needs to log in">
                        <i class="ri-alert-line"></i>
                      </span>
                    @endif
                  </div>
                </td>
                <td class="text-center">
                  <span class="fw-semibold">{{ $account->total_templates }}</span>
                </td>
                <td class="text-center">
                  @if ($account->permanent_templates_count > 0)
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                      <i class="ri-shield-check-line me-1"></i>{{ $account->permanent_templates_count }}
                    </span>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
                <td class="text-center">
                  @if ($account->queued_posts_count > 0)
                    <span class="badge bg-warning text-dark">
                      <i class="ri-send-plane-line me-1"></i>{{ $account->queued_posts_count }}
                    </span>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
                <td class="text-center">
                  @if ($account->queue_delete_all)
                    <span class="badge bg-danger">
                      <i class="ri-delete-bin-line me-1"></i>Queued
                    </span>
                  @else
                    <span class="text-muted small">—</span>
                  @endif
                </td>
                <td class="text-end pe-3">
                  {{-- Manage Templates --}}
                  <button type="button"
                          class="btn btn-sm btn-outline-primary btn-manage-templates"
                          data-account-id="{{ $account->id }}"
                          data-account-email="{{ $account->email }}"
                          data-total="{{ $account->total_templates }}"
                          data-permanent="{{ $account->permanent_templates_count }}"
                          title="Browse and manage templates for this account">
                    <i class="ri-layout-grid-line me-1"></i>Templates
                  </button>

                  {{-- Delete All from g2g --}}
                  <button type="button"
                          class="btn btn-sm ms-1 {{ $account->queue_delete_all ? 'btn-warning' : 'btn-outline-warning' }} btn-delete-all-g2g"
                          data-account-id="{{ $account->id }}"
                          data-account-email="{{ $account->email }}"
                          data-permanent-count="{{ $account->permanent_templates_count }}"
                          data-queued="{{ $account->queue_delete_all ? '1' : '0' }}"
                          title="{{ $account->queue_delete_all ? 'Cancel delete-all' : 'Queue: delete all g2g offers (skips permanent)' }}">
                    <i class="ri-delete-bin-2-line"></i>
                  </button>

                  {{-- More actions dropdown --}}
                  <div class="dropdown d-inline-block ms-1">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split px-2"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" href="{{ route('user-accounts.edit', $account->id) }}">
                          <i class="ri-edit-line me-2"></i>Edit Account
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('offer-templates.create') }}?account={{ $account->id }}">
                          <i class="ri-add-circle-line me-2"></i>Add Template
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <button type="button" class="dropdown-item text-danger btn-delete-account"
                                data-account-id="{{ $account->id }}"
                                data-account-email="{{ $account->email }}"
                                data-templates="{{ $account->total_templates }}">
                          <i class="ri-delete-bin-line me-2"></i>Delete Account
                        </button>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div id="account-no-results" class="py-4 text-center text-muted d-none">
        <i class="ri-search-line fs-2 opacity-50"></i>
        <p class="mt-2 mb-0">No accounts match your search.</p>
      </div>
    @endif
  </x-data-display.card>

  {{-- ══════════════════════════════════════════════════════════════════════════ --}}
  {{-- Template Management Offcanvas                                             --}}
  {{-- ══════════════════════════════════════════════════════════════════════════ --}}
  <div class="offcanvas offcanvas-end" tabindex="-1" id="templatesOffcanvas"
       style="width:min(760px,100vw)" aria-labelledby="templatesOffcanvasLabel">

    {{-- Header --}}
    <div class="offcanvas-header border-bottom pb-3">
      <div>
        <h5 class="offcanvas-title mb-0" id="templatesOffcanvasLabel">Templates</h5>
        <div class="text-muted small mt-1" id="oc-account-meta"></div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Toolbar --}}
    <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center gap-2 flex-wrap">
      <div class="input-group input-group-sm flex-grow-1" style="min-width:180px;max-width:300px">
        <span class="input-group-text bg-white"><i class="ri-search-line text-muted"></i></span>
        <input type="text" id="oc-search" class="form-control border-start-0 ps-0" placeholder="Search templates…">
      </div>
      <select id="oc-perm-filter" class="form-select form-select-sm" style="max-width:160px">
        <option value="">All templates</option>
        <option value="permanent">Permanent only</option>
        <option value="non_permanent">Non-permanent</option>
      </select>
      <a id="oc-add-template-btn" href="#" class="btn btn-sm btn-primary ms-auto">
        <i class="ri-add-line me-1"></i>Add Template
      </a>
    </div>

    {{-- Bulk action bar (hidden until rows selected) --}}
    <div id="oc-bulk-bar" class="d-none px-3 py-2 bg-dark text-white border-bottom">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="fw-semibold small" id="oc-bulk-count">0 selected</span>
        <div class="vr mx-1"></div>
        <button type="button" class="btn btn-sm btn-success oc-bulk-btn" data-action="mark_permanent">
          <i class="ri-shield-check-line me-1"></i>Mark Permanent
        </button>
        <button type="button" class="btn btn-sm btn-outline-light oc-bulk-btn" data-action="unmark_permanent">
          <i class="ri-shield-line me-1"></i>Unmark
        </button>
        <button type="button" class="btn btn-sm btn-info text-dark oc-bulk-btn" data-action="queue_post">
          <i class="ri-add-circle-line me-1"></i>Queue +1
        </button>
        <button type="button" class="btn btn-sm btn-outline-info text-light border-info oc-bulk-btn" data-action="queue_dequeue">
          <i class="ri-indeterminate-circle-line me-1"></i>Queue -1
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger oc-bulk-btn" data-action="delete">
          <i class="ri-delete-bin-line me-1"></i>Delete
        </button>
        <button type="button" class="btn btn-sm btn-outline-light ms-auto" id="oc-bulk-clear">
          <i class="ri-close-line"></i>
        </button>
      </div>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-0">
      <div id="oc-loading" class="py-5 text-center text-muted d-none">
        <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
        <p class="small mb-0">Loading templates…</p>
      </div>
      <div id="oc-empty" class="py-5 text-center text-muted d-none">
        <i class="ri-file-list-3-line display-3 d-block mb-2 opacity-25"></i>
        <p class="mb-0">No templates match your filter.</p>
      </div>
      <div class="table-responsive d-none" id="oc-table-wrap">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light sticky-top">
            <tr>
              <th style="width:36px" class="ps-3">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="oc-select-all">
                </div>
              </th>
              <th>Template</th>
              <th class="text-center" style="width:95px">Permanent</th>
              <th class="text-center" style="width:70px">Queue</th>
              <th class="d-none d-md-table-cell" style="width:110px">Last Posted</th>
              <th class="text-end pe-3" style="width:110px">Actions</th>
            </tr>
          </thead>
          <tbody id="oc-tbody"></tbody>
        </table>
      </div>
      <div id="oc-pagination" class="d-none px-3 py-3 border-top d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <div class="text-muted small" id="oc-page-info"></div>
        <div class="d-flex gap-1">
          <button class="btn btn-sm btn-outline-secondary" id="oc-prev" disabled>
            <i class="ri-arrow-left-s-line"></i> Prev
          </button>
          <button class="btn btn-sm btn-outline-secondary" id="oc-next" disabled>
            Next <i class="ri-arrow-right-s-line"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════════════════════ --}}
  {{-- Quick Action Account Picker Modal                                         --}}
  {{-- ══════════════════════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="quickActionModal" tabindex="-1" aria-labelledby="qaModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:500px">
      <div class="modal-content">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm flex-shrink-0">
              <div class="avatar-title rounded-circle" id="qa-icon-wrap">
                <i id="qa-icon" class="fs-18"></i>
              </div>
            </div>
            <div>
              <h5 class="modal-title mb-0" id="qaModalTitle">Action</h5>
              <p class="text-muted small mb-0" id="qa-subtitle"></p>
            </div>
          </div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
        </div>

        {{-- Step indicator (only visible for 2-step actions) --}}
        <div id="qa-step-bar" class="d-none border-bottom px-4 py-2 bg-body-tertiary">
          <div class="d-flex align-items-center gap-2 small">
            <span class="badge rounded-pill bg-primary" id="qa-pill-1">1</span>
            <span id="qa-step-lbl-1" class="fw-medium">Select Account</span>
            <i class="ri-arrow-right-s-line text-muted"></i>
            <span class="badge rounded-pill border text-muted" id="qa-pill-2">2</span>
            <span id="qa-step-lbl-2" class="text-muted">Select Game</span>
          </div>
        </div>

        <div class="modal-body">
          {{-- Contextual warning --}}
          <div class="alert d-none mb-3" id="qa-warning" role="alert">
            <i class="me-1" id="qa-warn-icon"></i>
            <span id="qa-warn-text"></span>
          </div>

          {{-- Step 1: Account list --}}
          <div id="qa-step-1-body">
          @if ($userAccounts->isEmpty())
            <div class="py-4 text-center text-muted">
              <i class="ri-user-search-line display-5 opacity-25 d-block mb-2"></i>
              <p class="mb-0 small">No accounts found. Add one first.</p>
            </div>
          @else
            <p class="text-muted small fw-semibold mb-2 text-uppercase" style="letter-spacing:.05em">Select an account</p>
            <div class="d-flex flex-column gap-2" id="qa-account-list">
              @foreach ($userAccounts as $account)
                @php
                  $acctGames = ($accountGameCounts[$account->id] ?? collect())->map(fn($g) => [
                    'game'  => $g->game,
                    'label' => \App\Models\OfferTemplate::GAMES[$g->game] ?? $g->game,
                    'count' => (int) $g->game_count,
                  ])->sortByDesc('count')->values()->toArray();
                @endphp
                <div class="qa-account-card d-flex align-items-center gap-3 p-2 px-3 border rounded-2"
                     role="button"
                     data-id="{{ $account->id }}"
                     data-email="{{ $account->email }}"
                     data-owner="{{ $account->owner_name ?? '' }}"
                     data-total="{{ $account->total_templates }}"
                     data-permanent="{{ $account->permanent_templates_count }}"
                     data-games="{{ json_encode($acctGames) }}">
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($account->email) }}&background=7269ef&color=fff&size=32"
                       class="rounded-circle flex-shrink-0" width="32" height="32" alt="">
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate small">{{ $account->email }}</div>
                    @if ($account->owner_name)
                      <div class="text-muted" style="font-size:.75rem">{{ $account->owner_name }}</div>
                    @endif
                  </div>
                  <div class="text-muted small flex-shrink-0">
                    {{ $account->total_templates }} tpl
                  </div>
                  <div class="form-check mb-0 flex-shrink-0">
                    <input class="form-check-input qa-radio" type="radio" name="qa-account-pick" style="pointer-events:none">
                  </div>
                </div>
              @endforeach
            </div>
          @endif
          </div>{{-- /qa-step-1-body --}}

          {{-- Step 2: Game selection (only for delete-except-permanent) --}}
          <div id="qa-step-2-body" class="d-none">
            <div class="alert alert-warning border border-warning-subtle d-flex align-items-start gap-2 mb-3">
              <i class="ri-alert-line text-warning mt-1 flex-shrink-0"></i>
              <span>All <strong>non-permanent</strong> live offers for the selected game will be deleted from g2g.com on the next desktop app run. <strong>Permanent offers are protected.</strong></span>
            </div>
            <p class="text-muted small fw-semibold mb-2 text-uppercase" style="letter-spacing:.05em">Select a game</p>
            <div class="d-flex flex-column gap-2" id="qa-game-list"></div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-outline-secondary d-none" id="qa-back">
            <i class="ri-arrow-left-s-line me-1"></i>Back
          </button>
          <button type="button" class="btn btn-primary d-none" id="qa-next" disabled>
            Next <i class="ri-arrow-right-s-line ms-1"></i>
          </button>
          <button type="button" class="btn btn-primary" id="qa-confirm" disabled>Confirm</button>
        </div>

      </div>
    </div>
  </div>

  @push('scripts')
  <script>
    const CSRF = '{{ csrf_token() }}';

    // ── State ──────────────────────────────────────────────────────────────────
    let currentAccountId    = null;
    let currentPage         = 1;
    let lastMeta            = {};
    let searchDebounceTimer = null;

    // ── Account search (client-side) ───────────────────────────────────────────
    document.getElementById('account-search')?.addEventListener('input', function () {
      const q       = this.value.toLowerCase().trim();
      const rows    = document.querySelectorAll('.account-row');
      let   visible = 0;

      rows.forEach(row => {
        const match = !q
          || row.dataset.email.includes(q)
          || row.dataset.owner.includes(q);
        row.classList.toggle('d-none', !match);
        if (match) visible++;
      });

      document.getElementById('account-no-results')?.classList.toggle('d-none', visible > 0);
    });

    // ── Open template offcanvas ────────────────────────────────────────────────
    function openTemplateOffcanvas(accountId, accountEmail, total, permanent) {
      currentAccountId = accountId;
      currentPage      = 1;

      document.getElementById('templatesOffcanvasLabel').textContent = accountEmail;
      document.getElementById('oc-account-meta').textContent =
        `${total} template(s) · ${permanent} permanent`;
      document.getElementById('oc-add-template-btn').href =
        `/offer-templates/create?account=${accountId}`;

      document.getElementById('oc-search').value      = '';
      document.getElementById('oc-perm-filter').value = '';
      clearOcBulkSelection();

      const oc = new bootstrap.Offcanvas(document.getElementById('templatesOffcanvas'));
      oc.show();
      loadTemplates();
    }

    document.querySelectorAll('.btn-manage-templates').forEach(btn => {
      btn.addEventListener('click', function () {
        openTemplateOffcanvas(
          this.dataset.accountId,
          this.dataset.accountEmail,
          this.dataset.total,
          this.dataset.permanent
        );
      });
    });

    // ── Search / filter inside offcanvas ───────────────────────────────────────
    document.getElementById('oc-search').addEventListener('input', function () {
      clearTimeout(searchDebounceTimer);
      searchDebounceTimer = setTimeout(() => { currentPage = 1; loadTemplates(); }, 380);
    });

    document.getElementById('oc-perm-filter').addEventListener('change', function () {
      currentPage = 1;
      loadTemplates();
    });

    // ── Load templates via AJAX ────────────────────────────────────────────────
    function loadTemplates() {
      if (!currentAccountId) return;

      const search    = document.getElementById('oc-search').value.trim();
      const permanent = document.getElementById('oc-perm-filter').value;
      const params    = new URLSearchParams({ page: currentPage });
      if (search)    params.set('search', search);
      if (permanent) params.set('permanent', permanent);

      showOcState('loading');

      fetch(`/automation/user/${currentAccountId}/templates?${params}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
      })
      .then(r => r.json())
      .then(data => {
        lastMeta = data;
        renderTemplates(data.data ?? []);
        renderPagination(data);
      })
      .catch(() => showOcState('empty'));
    }

    // ── Render template rows ───────────────────────────────────────────────────
    function renderTemplates(templates) {
      if (!templates.length) { showOcState('empty'); return; }

      showOcState('table');
      const tbody = document.getElementById('oc-tbody');
      tbody.innerHTML = templates.map(t => {
        const lastPosted = t.last_posted_at
          ? `<span title="${t.last_posted_at}">${relativeTime(t.last_posted_at)}</span>`
          : '<span class="text-muted">—</span>';

        const levels = [
          t.th_level    ? `TH${t.th_level}`    : null,
          t.king_level  ? `K${t.king_level}`   : null,
          t.queen_level ? `Q${t.queen_level}`  : null,
        ].filter(Boolean).join(' · ');

        const queueBadge = (t.offers_to_generate ?? 0) > 0
          ? `<span class="badge bg-warning text-dark" id="oc-queue-${t.id}" title="Queued posts">
               <i class="ri-send-plane-line me-1"></i>${t.offers_to_generate}
             </span>`
          : `<span class="badge bg-secondary-subtle text-secondary" id="oc-queue-${t.id}">—</span>`;

        const permBtn = t.is_permanent
          ? `<button class="btn btn-sm btn-success oc-perm-btn" data-id="${t.id}" data-permanent="1" title="Protected — click to unmark">
               <i class="ri-shield-check-line"></i>
             </button>`
          : `<button class="btn btn-sm btn-outline-secondary oc-perm-btn" data-id="${t.id}" data-permanent="0" title="Deletable — click to protect">
               <i class="ri-shield-line"></i>
             </button>`;

        return `
          <tr id="oc-row-${t.id}">
            <td class="ps-3">
              <div class="form-check mb-0">
                <input class="form-check-input oc-cb" type="checkbox" value="${t.id}">
              </div>
            </td>
            <td>
              <div class="fw-medium lh-sm" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${escHtml(t.title)}">
                ${escHtml(t.title)}
              </div>
              <div class="text-muted small">${levels}${t.price ? ` · $${parseFloat(t.price).toFixed(2)}` : ''}</div>
            </td>
            <td class="text-center">${permBtn}</td>
            <td class="text-center">${queueBadge}</td>
            <td class="text-muted small d-none d-md-table-cell">${lastPosted}</td>
            <td class="text-end pe-3">
              <button class="btn btn-sm btn-outline-warning oc-queue-btn" data-id="${t.id}" title="Queue +1 forced post">
                <i class="ri-add-circle-line"></i>
              </button>
              <a href="/offer-templates/${t.id}/edit" class="btn btn-sm btn-outline-secondary ms-1" title="Edit">
                <i class="ri-edit-line"></i>
              </a>
              <button class="btn btn-sm btn-outline-danger ms-1 oc-delete-btn" data-id="${t.id}" data-title="${escHtml(t.title)}" title="Delete from DB">
                <i class="ri-delete-bin-line"></i>
              </button>
            </td>
          </tr>`;
      }).join('');

      bindOcRowActions();
      updateOcSelectAll();
    }

    // ── Bind row-level actions ─────────────────────────────────────────────────
    function bindOcRowActions() {
      document.querySelectorAll('.oc-cb').forEach(cb => {
        cb.addEventListener('change', updateOcBulkBar);
      });

      document.getElementById('oc-select-all').addEventListener('change', function () {
        document.querySelectorAll('.oc-cb').forEach(c => c.checked = this.checked);
        updateOcBulkBar();
      });

      document.querySelectorAll('.oc-perm-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          const id          = this.dataset.id;
          const isPermanent = this.dataset.permanent === '1';

          Swal.fire({
            title: isPermanent ? 'Remove protection?' : 'Mark as permanent?',
            text:  isPermanent
              ? 'This offer will be included in future delete-all runs.'
              : 'This offer will be skipped during delete-all runs.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-primary w-xs me-2 mt-2', cancelButton: 'btn btn-outline-secondary w-xs mt-2' },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`/offer-templates/${id}/toggle-permanent`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
              if (!data.success) return;
              const p   = data.is_permanent;
              this.dataset.permanent = p ? '1' : '0';
              this.className = p ? 'btn btn-sm btn-success oc-perm-btn' : 'btn btn-sm btn-outline-secondary oc-perm-btn';
              this.innerHTML = p ? '<i class="ri-shield-check-line"></i>' : '<i class="ri-shield-line"></i>';
              this.title     = p ? 'Protected — click to unmark' : 'Deletable — click to protect';
            });
          });
        });
      });

      document.querySelectorAll('.oc-queue-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          const id = this.dataset.id;
          this.disabled = true;
          fetch(`/offer-templates/${id}/queue-post`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              const badge = document.getElementById(`oc-queue-${id}`);
              if (badge) {
                badge.className = 'badge bg-warning text-dark';
                badge.innerHTML = `<i class="ri-send-plane-line me-1"></i>${data.offers_to_generate}`;
              }
              Swal.fire({ title: 'Queued!', text: `Post queue: ${data.offers_to_generate}`, icon: 'success', timer: 1000, showConfirmButton: false });
            }
          })
          .finally(() => { this.disabled = false; });
        });
      });

      document.querySelectorAll('.oc-delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
          const id    = this.dataset.id;
          const title = this.dataset.title;
          Swal.fire({
            title: 'Delete from database?',
            html: `<strong>${title}</strong><br><span class="text-muted small">Removes the record only — does not touch g2g.com.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger w-xs me-2 mt-2', cancelButton: 'btn btn-secondary w-xs mt-2' },
            buttonsStyling: false,
            showCloseButton: true,
          }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`/offer-templates/${id}`, {
              method: 'DELETE',
              headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
              if (data.success) {
                document.getElementById(`oc-row-${id}`)?.remove();
                Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1200, showConfirmButton: false });
              }
            });
          });
        });
      });
    }

    // ── Bulk selection helpers ─────────────────────────────────────────────────
    function getOcChecked() {
      return Array.from(document.querySelectorAll('.oc-cb:checked'));
    }

    function updateOcBulkBar() {
      const checked = getOcChecked();
      const bar     = document.getElementById('oc-bulk-bar');
      const label   = document.getElementById('oc-bulk-count');
      bar.classList.toggle('d-none', checked.length === 0);
      label.textContent = `${checked.length} selected`;
      updateOcSelectAll();
    }

    function updateOcSelectAll() {
      const all     = document.querySelectorAll('.oc-cb');
      const checked = getOcChecked();
      const sa      = document.getElementById('oc-select-all');
      if (!sa) return;
      sa.indeterminate = checked.length > 0 && checked.length < all.length;
      sa.checked       = all.length > 0 && checked.length === all.length;
    }

    function clearOcBulkSelection() {
      document.querySelectorAll('.oc-cb').forEach(c => c.checked = false);
      document.getElementById('oc-select-all').checked = false;
      document.getElementById('oc-bulk-bar').classList.add('d-none');
    }

    document.getElementById('oc-bulk-clear').addEventListener('click', clearOcBulkSelection);

    // ── Bulk actions ───────────────────────────────────────────────────────────
    document.querySelectorAll('.oc-bulk-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const action  = this.dataset.action;
        const checked = getOcChecked();
        const ids     = checked.map(c => parseInt(c.value, 10));
        if (!ids.length) return;

        const labels = {
          mark_permanent:   `Mark ${ids.length} template(s) as permanent?`,
          unmark_permanent: `Unmark ${ids.length} template(s)?`,
          queue_post:       `Add +1 post queue to ${ids.length} template(s)?`,
          queue_dequeue:    `Subtract -1 post queue from ${ids.length} template(s)?`,
          delete:           `Permanently delete ${ids.length} template(s) from the database?`,
        };

        Swal.fire({
          title: labels[action],
          text: action === 'delete'
            ? 'This cannot be undone.'
            : action === 'mark_permanent' ? 'These offers will be skipped during delete-all runs.' : '',
          icon: action === 'delete' ? 'warning' : 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, proceed',
          cancelButtonText: 'Cancel',
          customClass: {
            confirmButton: action === 'delete' ? 'btn btn-danger w-xs me-2 mt-2' : 'btn btn-primary w-xs me-2 mt-2',
            cancelButton: 'btn btn-secondary w-xs mt-2',
          },
          buttonsStyling: false,
          showCloseButton: true,
        }).then(result => {
          if (!result.isConfirmed) return;
          fetch('/offer-templates/bulk-action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ action, ids }),
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              if (action === 'delete') {
                ids.forEach(id => document.getElementById(`oc-row-${id}`)?.remove());
                clearOcBulkSelection();
                Swal.fire({ title: 'Done!', text: `${data.count} template(s) deleted.`, icon: 'success', timer: 1500, showConfirmButton: false });
              } else {
                Swal.fire({ title: 'Done!', text: `${data.count} template(s) updated.`, icon: 'success', timer: 1500, showConfirmButton: false })
                  .then(() => { currentPage = 1; loadTemplates(); });
              }
            }
          })
          .catch(() => Swal.fire({ title: 'Error', text: 'Action failed.', icon: 'error' }));
        });
      });
    });

    // ── Pagination ─────────────────────────────────────────────────────────────
    function renderPagination(data) {
      const wrap = document.getElementById('oc-pagination');
      const info = document.getElementById('oc-page-info');
      const prev = document.getElementById('oc-prev');
      const next = document.getElementById('oc-next');

      if ((data.last_page ?? 1) <= 1) { wrap.classList.add('d-none'); return; }

      wrap.classList.remove('d-none');
      info.textContent = `Showing ${data.from}–${data.to} of ${data.total}`;
      prev.disabled = data.current_page <= 1;
      next.disabled = data.current_page >= data.last_page;
    }

    document.getElementById('oc-prev').addEventListener('click', function () {
      if (currentPage > 1) { currentPage--; loadTemplates(); }
    });
    document.getElementById('oc-next').addEventListener('click', function () {
      if (currentPage < (lastMeta.last_page ?? 1)) { currentPage++; loadTemplates(); }
    });

    // ── Delete All from g2g (per-row button) ───────────────────────────────────
    document.querySelectorAll('.btn-delete-all-g2g').forEach(btn => {
      btn.addEventListener('click', function () {
        const id           = this.dataset.accountId;
        const email        = this.dataset.accountEmail;
        const permanentCnt = parseInt(this.dataset.permanentCount, 10);
        const queued       = this.dataset.queued === '1';
        const btnEl        = this;

        if (queued) {
          Swal.fire({
            title: 'Cancel delete-all?',
            text: `The desktop app will no longer wipe offers for ${email}.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'Keep queued',
            customClass: { confirmButton: 'btn btn-secondary w-xs me-2 mt-2', cancelButton: 'btn btn-outline-secondary w-xs mt-2' },
            buttonsStyling: false,
          }).then(result => { if (result.isConfirmed) doToggleDeleteAll(id, email, false, permanentCnt, btnEl); });
        } else {
          openQaModal('delete-except-permanent');
          const accountCard = document.querySelector(`.qa-account-card[data-id="${id}"]`);
          if (accountCard) {
            accountCard.click();
            qaNext.click();
            qaBack.classList.add('d-none');
          }
        }
      });
    });

    function doToggleDeleteAll(accountId, email, queueing, permanentCnt, btnEl) {
      fetch(`/user-accounts/${accountId}/queue-delete-all`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
      })
      .then(r => r.json())
      .then(data => {
        if (!data.success) return;
        const isQueued = data.queue_delete_all;
        btnEl.dataset.queued = isQueued ? '1' : '0';
        btnEl.className = btnEl.className.replace(/btn-(warning|outline-warning)/, isQueued ? 'btn-warning' : 'btn-outline-warning');
        btnEl.title = isQueued ? 'Cancel delete-all' : 'Queue: delete all g2g offers (skips permanent)';

        const row        = document.getElementById(`account-row-${accountId}`);
        const statusCell = row?.querySelector('td:nth-child(5)');
        if (statusCell) {
          statusCell.innerHTML = isQueued
            ? `<span class="badge bg-danger"><i class="ri-delete-bin-line me-1"></i>Queued</span>`
            : `<span class="text-muted small">—</span>`;
        }

        Swal.fire({
          title: isQueued ? 'Delete-all queued!' : 'Cancelled.',
          text: isQueued
            ? `The desktop app will delete all${permanentCnt > 0 ? ` non-permanent` : ''} offers for ${email} on next run.`
            : 'Delete-all has been cancelled.',
          icon: 'success',
          timer: 2500,
          showConfirmButton: false,
        });
      })
      .catch(() => Swal.fire({ title: 'Error', text: 'Could not update.', icon: 'error' }));
    }

    // ── Delete Account ─────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-delete-account').forEach(btn => {
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
          customClass: { confirmButton: 'btn btn-danger w-xs me-2 mt-2', cancelButton: 'btn btn-secondary w-xs mt-2' },
          buttonsStyling: false,
          showCloseButton: true,
        }).then(result => {
          if (!result.isConfirmed) return;
          fetch(`/user-accounts/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              document.getElementById(`account-row-${id}`)?.remove();
              Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1200, showConfirmButton: false });
            }
          })
          .catch(() => Swal.fire({ title: 'Error', text: 'Could not delete account.', icon: 'error' }));
        });
      });
    });

    // ══════════════════════════════════════════════════════════════════════════
    // Quick Action Modal
    // ══════════════════════════════════════════════════════════════════════════

    const QA_CONFIGS = {
      'manage-templates': {
        icon:        'ri-layout-grid-line',
        iconBg:      'bg-primary-subtle text-primary',
        title:       'Template Management',
        subtitle:    'Select an account to open its templates',
        confirmCls:  'btn-primary',
        confirmHtml: '<i class="ri-layout-grid-line me-1"></i>Open Templates',
        warning:     null,
      },
      'queue-post-all': {
        icon:        'ri-send-plane-line',
        iconBg:      'bg-success-subtle text-success',
        title:       'Post for Account',
        subtitle:    'Select an account to queue all its templates',
        confirmCls:  'btn-success',
        confirmHtml: '<i class="ri-send-plane-line me-1"></i>Queue for Account',
        warning: {
          cls:  'alert-info border-info-subtle',
          icon: 'ri-information-line text-info',
          html: 'All templates for the selected account will be queued for posting. The desktop app will process them on the next cycle.',
        },
      },
      'delete-except-permanent': {
        icon:        'ri-shield-line',
        iconBg:      'bg-warning-subtle text-warning',
        title:       'Delete All Except Permanent',
        subtitle:    'Select an account, then choose which game to target',
        confirmCls:  'btn-danger',
        confirmHtml: '<i class="ri-delete-bin-line me-1"></i>Queue Delete',
        warning:     null,
      },
    };

    let qaAction       = null;
    let qaAccountId    = null;
    let qaEmail        = null;
    let qaSelectedGame = null;
    let qaGameLabel    = null;
    const QA_TWO_STEP  = new Set(['delete-except-permanent']);
    const qaModalEl    = document.getElementById('quickActionModal');
    const qaModal      = new bootstrap.Modal(qaModalEl);
    const qaConfirm    = document.getElementById('qa-confirm');
    const qaNext       = document.getElementById('qa-next');
    const qaBack       = document.getElementById('qa-back');

    function openQaModal(action) {
      qaAction       = action;
      qaAccountId    = null;
      qaEmail        = null;
      qaSelectedGame = null;
      qaGameLabel    = null;

      const cfg      = QA_CONFIGS[action];
      const twoStep  = QA_TWO_STEP.has(action);

      document.getElementById('qa-icon').className        = `${cfg.icon} fs-18`;
      document.getElementById('qa-icon-wrap').className   = `avatar-title rounded-circle ${cfg.iconBg}`;
      document.getElementById('qaModalTitle').textContent = cfg.title;
      document.getElementById('qa-subtitle').textContent  = cfg.subtitle;
      qaConfirm.innerHTML  = cfg.confirmHtml;
      qaConfirm.className  = `btn ${cfg.confirmCls}`;
      qaConfirm.disabled   = true;

      const warnEl = document.getElementById('qa-warning');
      if (cfg.warning) {
        warnEl.className = `alert ${cfg.warning.cls} d-flex align-items-start gap-2 mb-3`;
        document.getElementById('qa-warn-icon').className = cfg.warning.icon;
        document.getElementById('qa-warn-text').innerHTML = cfg.warning.html;
        warnEl.classList.remove('d-none');
      } else {
        warnEl.classList.add('d-none');
      }

      // Step bar + button visibility
      document.getElementById('qa-step-bar').classList.toggle('d-none', !twoStep);
      document.getElementById('qa-step-1-body').classList.remove('d-none');
      document.getElementById('qa-step-2-body').classList.add('d-none');
      qaNext.classList.toggle('d-none', !twoStep);
      qaNext.disabled = true;
      qaBack.classList.add('d-none');
      qaConfirm.classList.toggle('d-none', twoStep);

      if (twoStep) updateStepIndicator(1);

      // Clear account selection
      document.querySelectorAll('.qa-account-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary-subtle');
        card.querySelector('.qa-radio').checked = false;
      });

      qaModal.show();
    }

    function updateStepIndicator(step) {
      const pill1 = document.getElementById('qa-pill-1');
      const lbl1  = document.getElementById('qa-step-lbl-1');
      const pill2 = document.getElementById('qa-pill-2');
      const lbl2  = document.getElementById('qa-step-lbl-2');
      if (step === 1) {
        pill1.className   = 'badge rounded-pill bg-primary';
        pill1.innerHTML   = '1';
        lbl1.className    = 'fw-medium';
        pill2.className   = 'badge rounded-pill border text-muted';
        pill2.textContent = '2';
        lbl2.className    = 'text-muted';
      } else {
        pill1.className = 'badge rounded-pill bg-success';
        pill1.innerHTML = '<i class="ri-check-line"></i>';
        lbl1.className  = 'text-muted';
        pill2.className   = 'badge rounded-pill bg-primary';
        pill2.textContent = '2';
        lbl2.className    = 'fw-medium';
      }
    }

    function populateGameList() {
      const card     = document.querySelector(`.qa-account-card[data-id="${qaAccountId}"]`);
      const games    = JSON.parse(card?.dataset.games || '[]');
      const total    = games.reduce((s, g) => s + g.count, 0);
      const gameMeta = {
        clash_of_clans:      { icon: 'ri-shield-line',   cls: 'bg-primary' },
        brawl_stars:         { icon: 'ri-star-line',     cls: 'bg-warning' },
        clash_royale:        { icon: 'ri-medal-line',    cls: 'bg-info' },
        hay_day:             { icon: 'ri-seedling-line', cls: 'bg-success' },
        mobile_legends:      { icon: 'ri-sword-line',    cls: 'bg-danger' },
        call_of_duty_mobile: { icon: 'ri-crosshair-2-line', cls: 'bg-dark' },
      };

      function gameCard(game, label, count, subtitle) {
        const meta = gameMeta[game] || { icon: 'ri-gamepad-line', cls: 'bg-secondary' };
        const colorCls = game ? meta.cls : 'bg-secondary';
        const iconCls  = game ? meta.icon : 'ri-apps-2-line';
        return `
          <div class="qa-game-card d-flex align-items-center gap-3 p-2 px-3 border rounded-2" role="button"
               data-game="${escHtml(game)}" data-label="${escHtml(label)}">
            <div class="avatar-title rounded-circle ${colorCls} text-white flex-shrink-0"
                 style="width:32px;height:32px;font-size:16px">
              <i class="${iconCls}"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="fw-semibold small">${escHtml(label)}</div>
              <div class="text-muted" style="font-size:.75rem">${subtitle}</div>
            </div>
            <span class="badge bg-secondary-subtle text-secondary flex-shrink-0">${count}</span>
            <div class="form-check mb-0 flex-shrink-0">
              <input class="form-check-input qa-game-radio" type="radio" name="qa-game-pick" style="pointer-events:none">
            </div>
          </div>`;
      }

      const rows = [
        gameCard('', 'All Games', total, `${total} non-permanent template${total !== 1 ? 's' : ''} across all games`),
        ...games.map(g => gameCard(g.game, g.label, g.count, `${g.count} non-permanent template${g.count !== 1 ? 's' : ''}`)),
      ];

      const list = document.getElementById('qa-game-list');
      list.innerHTML = rows.join('');

      list.querySelectorAll('.qa-game-card').forEach(c => {
        c.addEventListener('click', () => {
          list.querySelectorAll('.qa-game-card').forEach(x => {
            x.classList.remove('border-primary', 'bg-primary-subtle');
            x.querySelector('.qa-game-radio').checked = false;
          });
          c.classList.add('border-primary', 'bg-primary-subtle');
          c.querySelector('.qa-game-radio').checked = true;
          qaSelectedGame = c.dataset.game || null;
          qaGameLabel    = c.dataset.label;
          qaConfirm.disabled = false;
        });
      });
    }

    // Account card selection
    document.querySelectorAll('.qa-account-card').forEach(card => {
      card.addEventListener('click', () => {
        document.querySelectorAll('.qa-account-card').forEach(c => {
          c.classList.remove('border-primary', 'bg-primary-subtle');
          c.querySelector('.qa-radio').checked = false;
        });
        card.classList.add('border-primary', 'bg-primary-subtle');
        card.querySelector('.qa-radio').checked = true;
        qaAccountId = card.dataset.id;
        qaEmail     = card.dataset.email;
        if (QA_TWO_STEP.has(qaAction)) {
          qaNext.disabled = false;
        } else {
          qaConfirm.disabled = false;
        }
      });
    });

    // Next button: advance to step 2
    qaNext.addEventListener('click', () => {
      if (!qaAccountId) return;
      updateStepIndicator(2);
      document.getElementById('qa-step-1-body').classList.add('d-none');
      document.getElementById('qa-step-2-body').classList.remove('d-none');
      qaNext.classList.add('d-none');
      qaBack.classList.remove('d-none');
      qaConfirm.classList.remove('d-none');
      qaConfirm.disabled = true;
      qaSelectedGame = null;
      qaGameLabel    = null;
      // Clear any prior game selection
      document.getElementById('qa-game-list').querySelectorAll('.qa-game-card').forEach(c => {
        c.classList.remove('border-primary', 'bg-primary-subtle');
      });
      populateGameList();
    });

    // Back button: return to step 1
    qaBack.addEventListener('click', () => {
      updateStepIndicator(1);
      document.getElementById('qa-step-1-body').classList.remove('d-none');
      document.getElementById('qa-step-2-body').classList.add('d-none');
      qaBack.classList.add('d-none');
      qaNext.classList.remove('d-none');
      qaNext.disabled = !qaAccountId;
      qaConfirm.classList.add('d-none');
      qaSelectedGame = null;
      qaGameLabel    = null;
    });

    // Quick action buttons → open modal
    document.querySelectorAll('.qa-btn').forEach(btn => {
      btn.addEventListener('click', () => openQaModal(btn.dataset.action));
    });

    // ── Post All Accounts ──────────────────────────────────────────────────────
    document.getElementById('qa-post-all-btn')?.addEventListener('click', () => {
      Swal.fire({
        title: 'Post All Accounts?',
        html: 'This will queue <strong>all templates across every account</strong> to be posted on the next desktop app cycle. Are you sure?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="ri-play-circle-line me-1"></i>Post All Now',
        cancelButtonText: 'Cancel',
        customClass: {
          confirmButton: 'btn btn-primary w-xs me-2 mt-2',
          cancelButton:  'btn btn-secondary w-xs mt-2',
        },
        buttonsStyling: false,
        showCloseButton: true,
      }).then(async result => {
        if (!result.isConfirmed) return;

        try {
          const res  = await fetch('/offer-templates/queue-post-all-accounts', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          });
          const data = await res.json();

          if (data.success) {
            Swal.fire({
              title: 'All Queued!',
              html:  `<strong>${data.queued}</strong> template(s) queued across all accounts. The desktop app will post them on the next cycle.`,
              icon:  'success',
              timer: 3000,
              showConfirmButton: false,
            });
          } else {
            Swal.fire({ title: 'Error', text: data.message ?? 'Action failed.', icon: 'error' });
          }
        } catch {
          Swal.fire({ title: 'Error', text: 'Request failed. Please try again.', icon: 'error' });
        }
      });
    });

    // Confirm handler
    qaConfirm.addEventListener('click', async () => {
      if (!qaAccountId) return;

      if (qaAction === 'manage-templates') {
        qaModal.hide();
        const card = document.querySelector(`.qa-account-card[data-id="${qaAccountId}"]`);
        openTemplateOffcanvas(
          qaAccountId,
          qaEmail,
          card?.dataset.total ?? 0,
          card?.dataset.permanent ?? 0
        );
        return;
      }

      const ENDPOINTS = {
        'queue-post-all':          { url: '/offer-templates/queue-post-by-account', body: { user_account_id: qaAccountId } },
        'delete-except-permanent': { url: `/user-accounts/${qaAccountId}/queue-delete-non-permanent`, body: qaSelectedGame ? { game: qaSelectedGame } : {} },
      };

      const ep = ENDPOINTS[qaAction];
      if (!ep) return;

      qaConfirm.disabled = true;
      qaConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

      try {
        const opts = {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        };
        opts.body = JSON.stringify(ep.body ?? {});

        const res  = await fetch(ep.url, opts);
        const data = await res.json();

        qaModal.hide();

        if (data.success) {
          const SUCCESS_MSGS = {
            'queue-post-all': {
              title: 'Posts Queued!',
              text:  `All templates for <strong>${escHtml(qaEmail)}</strong> have been queued. The desktop app will process them on the next cycle.`,
            },
            'delete-except-permanent': {
              title: 'Deletion Queued!',
              text:  qaSelectedGame
                ? `Non-permanent <strong>${escHtml(qaGameLabel)}</strong> offers for <strong>${escHtml(qaEmail)}</strong> will be deleted on the next desktop app run.`
                : `All non-permanent offers for <strong>${escHtml(qaEmail)}</strong> will be deleted on the next desktop app run.`,
            },
          };
          const msg = SUCCESS_MSGS[qaAction];
          Swal.fire({
            title: msg.title,
            html:  msg.text,
            icon:  'success',
            timer: 3000,
            showConfirmButton: false,
          });

          // Update the "Delete All" badge in the accounts table for delete actions
          if (qaAction === 'delete-except-permanent') {
            const row        = document.getElementById(`account-row-${qaAccountId}`);
            const statusCell = row?.querySelector('td:nth-child(5)');
            if (statusCell) {
              const gameTag = qaSelectedGame ? ` · ${escHtml(qaGameLabel)}` : '';
              statusCell.innerHTML = `<span class="badge bg-danger"><i class="ri-delete-bin-line me-1"></i>Queued${gameTag}</span>`;
            }
            // Update the per-row delete button state
            const deleteBtn = row?.querySelector('.btn-delete-all-g2g');
            if (deleteBtn) {
              deleteBtn.dataset.queued = '1';
              deleteBtn.className = deleteBtn.className.replace(/btn-(warning|outline-warning)/, 'btn-warning');
              deleteBtn.title = 'Cancel delete-all';
            }
          }
        } else {
          Swal.fire({ title: 'Error', text: data.message ?? 'Action failed.', icon: 'error' });
        }
      } catch (err) {
        qaModal.hide();
        Swal.fire({ title: 'Error', text: 'Request failed. Please try again.', icon: 'error' });
      }
    });

    // Reset modal state when it closes
    qaModalEl.addEventListener('hidden.bs.modal', () => {
      const cfg = qaAction ? QA_CONFIGS[qaAction] : null;
      if (cfg) {
        qaConfirm.innerHTML = cfg.confirmHtml;
        qaConfirm.className = `btn ${cfg.confirmCls}`;
        qaConfirm.disabled  = true;
      }
      // Reset 2-step state
      document.getElementById('qa-step-bar').classList.add('d-none');
      document.getElementById('qa-step-1-body').classList.remove('d-none');
      document.getElementById('qa-step-2-body').classList.add('d-none');
      qaNext.classList.add('d-none');
      qaNext.disabled = true;
      qaBack.classList.add('d-none');
      qaConfirm.classList.remove('d-none');
      qaSelectedGame = null;
      qaGameLabel    = null;
    });

    // ── Helpers ────────────────────────────────────────────────────────────────
    function showOcState(state) {
      document.getElementById('oc-loading').classList.toggle('d-none',    state !== 'loading');
      document.getElementById('oc-empty').classList.toggle('d-none',      state !== 'empty');
      document.getElementById('oc-table-wrap').classList.toggle('d-none', state !== 'table');
      if (state !== 'table') document.getElementById('oc-pagination').classList.add('d-none');
    }

    function escHtml(str) {
      return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function relativeTime(isoStr) {
      const diff = Math.floor((Date.now() - new Date(isoStr)) / 1000);
      if (diff < 60)    return `${diff}s ago`;
      if (diff < 3600)  return `${Math.floor(diff/60)}m ago`;
      if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
      return `${Math.floor(diff/86400)}d ago`;
    }
  </script>
  @endpush
</x-layouts.admin.master>

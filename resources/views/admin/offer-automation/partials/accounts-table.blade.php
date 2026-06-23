<x-data-display.card class="mb-4">
  <x-slot name="header">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <h5 class="card-title mb-0 me-auto">
        <i class="ri-account-circle-line text-primary me-2"></i>Accounts
      </h5>

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
                <button type="button"
                        class="btn btn-sm btn-outline-primary btn-manage-templates"
                        data-account-id="{{ $account->id }}"
                        data-account-email="{{ $account->email }}"
                        data-total="{{ $account->total_templates }}"
                        data-permanent="{{ $account->permanent_templates_count }}"
                        title="Browse and manage templates for this account">
                  <i class="ri-layout-grid-line me-1"></i>Templates
                </button>

                <button type="button"
                        class="btn btn-sm ms-1 {{ $account->queue_delete_all ? 'btn-warning' : 'btn-outline-warning' }} btn-delete-all-g2g"
                        data-account-id="{{ $account->id }}"
                        data-account-email="{{ $account->email }}"
                        data-permanent-count="{{ $account->permanent_templates_count }}"
                        data-queued="{{ $account->queue_delete_all ? '1' : '0' }}"
                        title="{{ $account->queue_delete_all ? 'Cancel delete-all' : 'Queue: delete all g2g offers (skips permanent)' }}">
                  <i class="ri-delete-bin-2-line"></i>
                </button>

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

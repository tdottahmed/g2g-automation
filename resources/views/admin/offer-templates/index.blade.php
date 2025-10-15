<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Offer Templates') }}</h5>
        <div>
          <!-- Bulk Actions Dropdown -->
          <div class="btn-group me-2" id="bulkActionsGroup" style="display: none;">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
              <i class="ri-settings-3-line me-1"></i> {{ __('Bulk Actions') }}
              <span class="badge bg-primary ms-1" id="selectedCountBadge">0</span>
            </button>
            <ul class="dropdown-menu">
              <li>
                <a class="dropdown-item bulk-action-btn" href="#" data-action="activate">
                  <i class="ri-play-circle-fill text-success me-2"></i> {{ __('Activate Selected') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item bulk-action-btn" href="#" data-action="deactivate">
                  <i class="ri-stop-circle-line text-warning me-2"></i> {{ __('Deactivate Selected') }}
                </a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-item bulk-action-btn text-danger" href="#" data-action="delete">
                  <i class="ri-delete-bin-line me-2"></i> {{ __('Delete Selected') }}
                </a>
              </li>
            </ul>
          </div>

          <x-action.link href="{{ route('offer-templates.create') }}" icon="ri-add-line">
            {{ __('Create Offer Template') }}
          </x-action.link>
        </div>
      </div>
    </x-slot>

    <!-- Filter Section -->
    <div class="card-body border-bottom">
      <form id="filterForm">
        <div class="row g-3">
          <div class="col-md-4">
            <label for="statusFilter" class="form-label">{{ __('Status') }}</label>
            <select class="form-select" id="statusFilter" name="status">
              <option value="">{{ __('All Status') }}</option>
              <option value="1">{{ __('Active') }}</option>
              <option value="0">{{ __('Inactive') }}</option>
            </select>
          </div>

          <div class="col-md-6">
            <label for="userAccountFilter" class="form-label">{{ __('User Account') }}</label>
            <select class="form-select" id="userAccountFilter" name="user_account">
              <option value="">{{ __('All Accounts') }}</option>
              @foreach ($userAccounts as $account)
                <option value="{{ $account->id }}">{{ $account->owner_name }} ({{ $account->email }})</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label d-block">&nbsp;</label>
            <div class="d-flex gap-2">
              <button type="button" id="applyFilters" class="btn btn-primary">
                <i class="ri-filter-line me-1"></i> {{ __('Filter') }}
              </button>
              <button type="button" id="resetFilters" class="btn btn-outline-secondary">
                <i class="ri-refresh-line me-1"></i> {{ __('Reset') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Active Filter Badges -->
        <div class="row mt-3">
          <div class="col-12">
            <div id="activeFilters" class="d-flex flex-wrap gap-2" style="display: none !important;">
            </div>
          </div>
        </div>
      </form>
    </div>

    <table id="offer-template" class="nowrap table align-middle" style="width:100%">
      <x-data-display.thead>
        <th width="50">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAll">
          </div>
        </th>
        <th>{{ __('Title') }}</th>
        <th>{{ __('User Account') }}</th>
        <th>{{ __('Price') }}</th>
        <th>{{ __('Status') }}</th>
        <th>{{ __('Last Posted') }}</th>
        <th>{{ __('Actions') }}</th>
      </x-data-display.thead>

      <x-data-display.tbody>
        @forelse ($offers as $offer)
          <tr data-status="{{ $offer->is_active ? '1' : '0' }}" data-user-account="{{ $offer->user_account_id }}">
            <td>
              <div class="form-check">
                <input class="form-check-input row-checkbox" type="checkbox" value="{{ $offer->id }}">
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center">
                {{ str($offer->title)->limit(50) }}
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center">
                @if ($offer->userAccount->profile_photo ?? false)
                  <img src="{{ asset('storage/' . $offer->userAccount->profile_photo) }}"
                       alt="{{ $offer->userAccount->owner_name }}" class="rounded-circle me-2" width="24"
                       height="24">
                @else
                  <div class="avatar-sm me-2">
                    <span class="avatar-title bg-primary rounded-circle">
                      {{ substr($offer->userAccount->owner_name ?? 'U', 0, 1) }}
                    </span>
                  </div>
                @endif
                <div>
                  <div class="fw-medium">{{ $offer->userAccount->owner_name ?? '-' }}</div>
                  <small class="text-muted">{{ $offer->userAccount->email ?? '' }}</small>
                </div>
              </div>
            </td>
            <td>
              <span class="fw-bold text-success">{{ number_format($offer->price, 2) }}</span>
            </td>
            <td>
              <span class="badge bg-{{ $offer->is_active ? 'success' : 'warning' }}">
                {{ $offer->is_active ? __('Active') : __('Inactive') }}
              </span>
            </td>
            <td>
              @if ($offer->last_posted_at)
                <small class="text-muted" title="{{ $offer->last_posted_at->format('M j, Y g:i A') }}">
                  {{ $offer->last_posted_at->diffForHumans() }}
                </small>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              <div class="d-flex gap-1">
                <form action="{{ route('offer-templates.toggle-status', $offer->id) }}" method="POST"
                      class="d-inline">
                  @csrf
                  @if ($offer->is_active)
                    <input type="hidden" name="status" value="0">
                    <button type="submit" class="btn btn-sm btn-outline-danger off-offer-btn"
                            title="Deactivate this template">
                      <i class="ri-stop-circle-line"></i>
                    </button>
                  @else
                    <input type="hidden" name="status" value="1">
                    <button type="submit" class="btn btn-sm btn-outline-success start-offer-btn"
                            title="Activate this template">
                      <i class="ri-play-circle-fill"></i>
                    </button>
                  @endif
                </form>

                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                          data-bs-toggle="dropdown">
                    <i class="ri-more-2-fill"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a href="{{ route('offer-templates.edit', $offer->id) }}" class="dropdown-item">
                        <i class="ri-edit-box-line me-2"></i> {{ __('Edit') }}
                      </a>
                    </li>
                    <li>
                      <a href="{{ route('offer-templates.show', $offer->id) }}" class="dropdown-item">
                        <i class="ri-eye-line me-2"></i> {{ __('View') }}
                      </a>
                    </li>
                    <li>
                      <hr class="dropdown-divider">
                    </li>
                    <li>
                      <form method="POST" action="{{ route('offer-templates.destroy', $offer->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger remove-item-btn"
                                onclick="return confirm('{{ __('Are you sure you want to delete this offer?') }}')">
                          <i class="ri-delete-bin-line me-2"></i> {{ __('Delete') }}
                        </button>
                      </form>
                    </li>
                  </ul>
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-muted py-4 text-center">
              <div class="py-5">
                <i class="ri-inbox-line display-4 text-muted"></i>
                <h5 class="mt-3">{{ __('No offers found.') }}</h5>
                <p class="text-muted">{{ __('Try adjusting your filters or create a new offer template.') }}</p>
                <x-action.link href="{{ route('offer-templates.create') }}" icon="ri-add-line" class="btn-primary">
                  {{ __('Create Your First Offer Template') }}
                </x-action.link>
              </div>
            </td>
          </tr>
        @endforelse
      </x-data-display.tbody>
    </table>
  </x-data-display.card>

  @push('scripts')
    <script src="{{ asset('assets/admin/libs/jquery.dataTable.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/responsive.bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/admin/libs/datetime.js') }}"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Global variables
        let dataTable;
        let selectedOfferIds = new Set();
        let activeFilters = {};

        // DOM Elements
        const filterForm = document.getElementById('filterForm');
        const applyFiltersBtn = document.getElementById('applyFilters');
        const resetFiltersBtn = document.getElementById('resetFilters');
        const activeFiltersContainer = document.getElementById('activeFilters');
        const selectAll = document.getElementById('selectAll');
        const bulkActionsGroup = document.getElementById('bulkActionsGroup');
        const selectedCountBadge = document.getElementById('selectedCountBadge');

        // Initialize DataTable
        function initializeDataTable() {
          dataTable = new DataTable("#offer-template", {
            responsive: true,
            scrollX: true,
            scrollY: false,
            scrollCollapse: false,
            paging: true,
            fixedColumns: false,
            autoWidth: false,
            language: {
              paginate: {
                previous: "<i class='ri-arrow-left-s-line'>",
                next: "<i class='ri-arrow-right-s-line'>"
              },
              search: "_INPUT_",
              searchPlaceholder: "Search offers...",
              lengthMenu: "_MENU_",
              info: "Showing _START_ to _END_ of _TOTAL_ offers",
              infoEmpty: "No offers available",
              infoFiltered: "(filtered from _MAX_ total offers)"
            },
            columnDefs: [{
                orderable: false,
                targets: [0, 6]
              },
              {
                searchable: false,
                targets: [0, 6]
              },
              {
                type: "currency",
                targets: 3
              },
              {
                type: "date",
                targets: 5
              }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            drawCallback: function(settings) {
              updateCheckboxesAfterDraw();
            }
          });
        }

        // Initialize DataTable on page load
        initializeDataTable();

        // Update checkboxes after DataTable redraw
        function updateCheckboxesAfterDraw() {
          if (!dataTable) return;

          // Update all visible checkboxes based on selectedOfferIds
          document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            const offerId = checkbox.value;
            checkbox.checked = selectedOfferIds.has(offerId);
          });

          updateSelectAllCheckbox();
          toggleBulkActions();
        }

        // Update select all checkbox state
        function updateSelectAllCheckbox() {
          if (!dataTable) return;

          const totalFilteredRows = dataTable.rows({
            search: 'applied'
          }).count();
          const selectedCount = selectedOfferIds.size;

          if (selectedCount === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
          } else if (selectedCount === totalFilteredRows) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
          } else {
            selectAll.checked = false;
            selectAll.indeterminate = true;
          }

          selectedCountBadge.textContent = selectedCount;
        }

        // Toggle bulk actions visibility
        function toggleBulkActions() {
          const selectedCount = selectedOfferIds.size;
          bulkActionsGroup.style.display = selectedCount > 0 ? 'inline-block' : 'none';
        }

        // Apply filters to DataTable
        function applyDataTableFilters() {
          if (!dataTable) return;

          dataTable.columns().every(function() {
            this.search('');
          });

          $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
            const row = dataTable.row(dataIndex).node();
            let showRow = true;

            if (activeFilters.status !== undefined) {
              const rowStatus = $(row).data('status').toString();
              if (rowStatus !== activeFilters.status) {
                showRow = false;
              }
            }

            if (activeFilters.user_account !== undefined) {
              const rowUserAccount = $(row).data('user-account').toString();
              if (rowUserAccount !== activeFilters.user_account) {
                showRow = false;
              }
            }

            return showRow;
          });

          dataTable.draw();
          $.fn.dataTable.ext.search.pop();
        }

        // Update active filter badges
        function updateActiveFilterBadges() {
          activeFiltersContainer.innerHTML = '';

          if (Object.keys(activeFilters).length === 0) {
            activeFiltersContainer.style.display = 'none';
            return;
          }

          const filterLabels = {
            status: {
              '1': 'Active',
              '0': 'Inactive'
            },
            user_account: Object.fromEntries(
              Array.from(document.getElementById('userAccountFilter').options).map(opt => [opt.value, opt.text])
            )
          };

          for (const [key, value] of Object.entries(activeFilters)) {
            let label = filterLabels[key]?.[value] || value;

            const badge = document.createElement('span');
            badge.className = 'badge bg-primary d-flex align-items-center';
            badge.innerHTML = `
              ${label}
              <button type="button" class="btn-close btn-close-white ms-2" data-filter="${key}" aria-label="Remove"></button>
            `;
            activeFiltersContainer.appendChild(badge);
          }

          activeFiltersContainer.style.display = 'flex';

          activeFiltersContainer.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
              const filterToRemove = this.dataset.filter;
              delete activeFilters[filterToRemove];
              document.querySelector(`[name="${filterToRemove}"]`).value = '';
              applyDataTableFilters();
              updateActiveFilterBadges();
            });
          });
        }

        // Apply filters
        applyFiltersBtn.addEventListener('click', function() {
          const formData = new FormData(filterForm);
          activeFilters = {};

          for (let [key, value] of formData.entries()) {
            if (value) {
              activeFilters[key] = value;
            }
          }

          applyDataTableFilters();
          updateActiveFilterBadges();
        });

        // Reset filters
        resetFiltersBtn.addEventListener('click', function() {
          filterForm.reset();
          activeFilters = {};
          if (dataTable) {
            dataTable.columns().search('').draw();
          }
          activeFiltersContainer.style.display = 'none';
          activeFiltersContainer.innerHTML = '';
          selectedOfferIds.clear();
          updateCheckboxesAfterDraw();
        });

        // Select All checkbox
        selectAll.addEventListener('change', function() {
          const isChecked = this.checked;

          if (isChecked && dataTable) {
            dataTable.rows({
              search: 'applied'
            }).every(function() {
              const row = this.node();
              const checkbox = $(row).find('.row-checkbox');
              const offerId = checkbox.val();
              selectedOfferIds.add(offerId);
              checkbox.prop('checked', true);
            });
          } else {
            selectedOfferIds.clear();
            document.querySelectorAll('.row-checkbox').forEach(checkbox => {
              checkbox.checked = false;
            });
          }

          updateSelectAllCheckbox();
          toggleBulkActions();
        });

        // Individual checkbox change - use event delegation
        document.addEventListener('change', function(e) {
          if (e.target.classList.contains('row-checkbox')) {
            const checkbox = e.target;
            const offerId = checkbox.value;

            if (checkbox.checked) {
              selectedOfferIds.add(offerId);
            } else {
              selectedOfferIds.delete(offerId);
            }

            updateSelectAllCheckbox();
            toggleBulkActions();
          }
        });

        // Bulk actions
        document.querySelectorAll('.bulk-action-btn').forEach(button => {
          button.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const selectedIds = Array.from(selectedOfferIds);

            if (selectedIds.length === 0) {
              Swal.fire({
                title: 'No items selected',
                text: 'Please select at least one offer template to perform this action.',
                icon: 'warning',
                confirmButtonText: 'OK'
              });
              return;
            }

            performBulkAction(action, selectedIds);
          });
        });

        // Perform bulk action
        function performBulkAction(action, selectedIds) {
          const actionTexts = {
            'activate': 'activate',
            'deactivate': 'deactivate',
            'delete': 'delete'
          };

          const actionTitles = {
            'activate': 'Activate Selected Offers',
            'deactivate': 'Deactivate Selected Offers',
            'delete': 'Delete Selected Offers'
          };

          const actionIcons = {
            'activate': 'question',
            'deactivate': 'question',
            'delete': 'warning'
          };

          const confirmButtonTexts = {
            'activate': 'Yes, activate them!',
            'deactivate': 'Yes, deactivate them!',
            'delete': 'Yes, delete them!'
          };

          Swal.fire({
            title: actionTitles[action],
            html: `You are about to ${actionTexts[action]} <strong>${selectedIds.length}</strong> offer template(s). This action cannot be undone.`,
            icon: actionIcons[action],
            showCancelButton: true,
            confirmButtonText: confirmButtonTexts[action],
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: 'btn btn-primary w-xs me-2 mt-2',
              cancelButton: 'btn btn-danger w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
          }).then((result) => {
            if (result.isConfirmed) {
              Swal.fire({
                title: 'Processing...',
                text: `Please wait while we ${actionTexts[action]} the selected offers.`,
                allowOutsideClick: false,
                didOpen: () => {
                  Swal.showLoading();
                }
              });

              fetch(`/offer-templates/bulk-action`, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                  },
                  body: JSON.stringify({
                    action: action,
                    ids: selectedIds
                  })
                })
                .then(response => response.json())
                .then(data => {
                  Swal.close();

                  if (data.success) {
                    Swal.fire({
                      title: 'Success!',
                      text: data.message ||
                        `Successfully ${actionTexts[action]}d ${selectedIds.length} offer template(s).`,
                      icon: 'success',
                      confirmButtonText: 'OK'
                    }).then(() => {
                      selectedOfferIds.clear();
                      window.location.reload();
                    });
                  } else {
                    Swal.fire({
                      title: 'Error!',
                      text: data.message || `Failed to ${actionTexts[action]} the selected offers.`,
                      icon: 'error',
                      confirmButtonText: 'OK'
                    });
                  }
                })
                .catch(error => {
                  Swal.close();
                  Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while processing your request.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                  });
                  console.error('Error:', error);
                });
            }
          });
        }

        // Individual toggle buttons
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

        // Auto-apply filters from URL parameters
        function applyUrlFilters() {
          const urlParams = new URLSearchParams(window.location.search);
          const filters = ['status', 'user_account'];

          filters.forEach(filter => {
            const value = urlParams.get(filter);
            if (value) {
              const element = document.querySelector(`[name="${filter}"]`);
              if (element) {
                element.value = value;
                activeFilters[filter] = value;
              }
            }
          });

          if (Object.keys(activeFilters).length > 0) {
            applyDataTableFilters();
            updateActiveFilterBadges();
          }
        }

        // Apply URL filters on page load
        applyUrlFilters();
      });
    </script>

    <style>
      .form-check-input:indeterminate {
        background-color: #0d6efd;
        border-color: #0d6efd;
      }

      .bulk-action-btn:hover {
        background-color: #f8f9fa;
      }

      #bulkActionsGroup {
        transition: all 0.3s ease;
      }

      .avatar-sm {
        width: 24px;
        height: 24px;
      }

      .avatar-title {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
      }

      #activeFilters .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
      }

      #activeFilters .btn-close {
        font-size: 0.6rem;
        padding: 0.5rem;
      }

      .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-bottom: 1rem;
      }

      .dataTables_wrapper .dataTables_length {
        float: left;
        margin-bottom: 1rem;
      }

      #selectedCountBadge {
        font-size: 0.7rem;
        padding: 0.2em 0.4em;
      }
    </style>
  @endpush
</x-layouts.admin.master>

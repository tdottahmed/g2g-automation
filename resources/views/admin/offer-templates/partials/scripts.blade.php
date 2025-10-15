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

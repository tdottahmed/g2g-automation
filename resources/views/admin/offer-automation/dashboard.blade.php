<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-1">
            <i class="ri-robot-line text-primary me-2"></i>Offer Automation Dashboard
          </h5>
          <p class="text-muted mb-0">Manage and execute automated offer posting</p>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-primary btn-refresh">
            <i class="ri-refresh-line me-1"></i> Refresh
          </button>
          <button type="button" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> New Template
          </button>
        </div>
      </div>
    </x-slot>

    <!-- Stats Overview -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-primary border-0 bg-opacity-10">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <h5 class="text-muted fw-normal mb-3">Total Users</h5>
                <h2 class="text-primary mb-0">{{ $userAccounts->count() }}</h2>
              </div>
              <div class="flex-shrink-0">
                <i class="ri-user-line display-4 text-primary opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-success border-0 bg-opacity-10">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <h5 class="text-muted fw-normal mb-3">Active Templates</h5>
                <h2 class="text-success mb-0">{{ $userAccounts->sum('active_templates_count') }}</h2>
              </div>
              <div class="flex-shrink-0">
                <i class="ri-file-text-line display-4 text-success opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-info border-0 bg-opacity-10">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <h5 class="text-muted fw-normal mb-3">Ready to Post</h5>
                <h2 class="text-info mb-0">{{ $userAccounts->where('active_templates_count', '>', 0)->count() }}</h2>
              </div>
              <div class="flex-shrink-0">
                <i class="ri-play-circle-line display-4 text-info opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-warning border-0 bg-opacity-10">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <h5 class="text-muted fw-normal mb-3">Total Templates</h5>
                <h2 class="text-warning mb-0">{{ $userAccounts->sum('total_templates') }}</h2>
              </div>
              <div class="flex-shrink-0">
                <i class="ri-stack-line display-4 text-warning opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Accounts Grid -->
    <div class="row">
      @foreach ($userAccounts as $user)
        <div class="col-xl-4 col-lg-6">
          <div class="card user-card card-hover border-0 shadow-sm">
            <div class="card-body position-relative">
              <!-- Status Indicator -->
              <div class="position-absolute end-0 top-0 m-3">
                <span class="badge bg-{{ $user->active_templates_count > 0 ? 'success' : 'secondary' }} rounded-pill">
                  <i class="ri-circle-fill me-1" style="font-size: 6px;"></i>
                  {{ $user->active_templates_count > 0 ? 'Ready' : 'No Templates' }}
                </span>
              </div>

              <!-- User Header -->
              <div class="d-flex align-items-center mb-4">
                <div class="flex-shrink-0">
                  <div class="avatar-lg position-relative">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->email) }}&background=7269ef&color=fff&bold=true&size=128"
                         alt="user-image" class="rounded-circle img-thumbnail">
                    <span class="position-absolute bg-success border-3 rounded-circle bottom-0 end-0 border border-white"
                          style="width: 12px; height: 12px;"></span>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h5 class="card-title text-dark mb-1">{{ $user->email }}</h5>
                  <p class="text-muted mb-0">
                    <i class="ri-user-id-line me-1"></i>ID: {{ $user->id }}
                  </p>
                </div>
              </div>

              <!-- Template Stats -->
              <div class="row mb-4 text-center">
                <div class="col-6">
                  <div class="border-end border-2">
                    <div class="text-center">
                      <h3 class="fw-bold text-primary mb-1">{{ $user->total_templates }}</h3>
                      <small class="text-muted text-uppercase">Total</small>
                    </div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="text-center">
                    <h3 class="fw-bold text-success mb-1">{{ $user->active_templates_count }}</h3>
                    <small class="text-muted text-uppercase">Active</small>
                  </div>
                </div>
              </div>

              <!-- Automation Controls -->
              <div class="automation-controls" data-user-id="{{ $user->id }}">
                <div class="row g-2">
                  <div class="col-12">
                    <label class="form-label small text-uppercase fw-semibold text-muted">Execution Mode</label>
                    <select class="mode-selector form-select">
                      <option value="direct">🚀 Direct Mode</option>
                      <option value="relation">🔗 Relation Mode</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <div class="form-check form-switch">
                      <input class="form-check-input force-post" type="checkbox" id="forceSwitch{{ $user->id }}">
                      <label class="form-check-label small" for="forceSwitch{{ $user->id }}">
                        <i class="ri-flashlight-line me-1"></i>Force Post Inactive
                      </label>
                    </div>
                  </div>
                  <div class="col-12 mt-2">
                    <button type="button" class="btn btn-primary w-100 run-automation"
                            {{ $user->active_templates_count == 0 ? 'disabled' : '' }}>
                      <i class="ri-play-circle-line me-2"></i>
                      Run Automation
                      <div class="spinner-border spinner-border-sm d-none ms-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Templates Preview -->
              <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="text-muted mb-0">
                    <i class="ri-file-list-line me-1"></i>Templates
                  </h6>
                  <button type="button" class="btn btn-sm btn-outline-light text-muted btn-load-templates"
                          data-user-id="{{ $user->id }}">
                    <i class="ri-arrow-down-s-line"></i>
                  </button>
                </div>
                <div id="templates-list-{{ $user->id }}" class="templates-list">
                  <div class="text-muted py-2 text-center">
                    <small>Click load button to view templates</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Empty State -->
    @if ($userAccounts->isEmpty())
      <div class="py-5 text-center">
        <div class="mb-4">
          <i class="ri-robot-line display-1 text-muted opacity-25"></i>
        </div>
        <h4 class="text-muted">No User Accounts Found</h4>
        <p class="text-muted mb-4">Get started by adding your first user account</p>
        <button type="button" class="btn btn-primary">
          <i class="ri-user-add-line me-2"></i>Add User Account
        </button>
      </div>
    @endif
  </x-data-display.card>

  <!-- Output Modal -->
  <div class="modal fade" id="outputModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="ri-terminal-line me-2"></i>Automation Output
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0">
          <pre id="outputText" class="bg-dark text-success mb-0 p-4"
               style="min-height: 300px; max-height: 500px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 0.875rem;"></pre>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="ri-close-line me-1"></i>Close
          </button>
          <button type="button" class="btn btn-primary btn-copy-output">
            <i class="ri-file-copy-line me-1"></i>Copy Output
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('styles')
    <style>
      .user-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
      }

      .user-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
      }

      .card-animate {
        transition: all 0.3s ease;
      }

      .card-animate:hover {
        transform: translateY(-3px);
      }

      .avatar-lg {
        width: 80px;
        height: 80px;
      }

      .templates-list {
        max-height: 200px;
        overflow-y: auto;
      }

      .templates-list::-webkit-scrollbar {
        width: 4px;
      }

      .templates-list::-webkit-scrollbar-track {
        background: #f8f9fa;
      }

      .templates-list::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 10px;
      }

      .bg-opacity-10 {
        background-opacity: 0.1 !important;
      }

      .btn-load-templates {
        transition: all 0.2s ease;
      }

      .btn-load-templates:hover {
        background-color: #7269ef !important;
        color: white !important;
      }

      .form-switch .form-check-input:checked {
        background-color: #7269ef;
        border-color: #7269ef;
      }

      #outputText {
        background: #1a1a1a;
        color: #00ff00;
        border-radius: 0;
      }

      .border-2 {
        border-width: 2px !important;
      }
    </style>
  @endpush

  @push('scripts')
    <script>
      $(document).ready(function() {
        // Load templates when clicking load button
        $('.btn-load-templates').on('click', function(e) {
          e.stopPropagation();
          const userId = $(this).data('user-id');
          loadUserTemplates(userId);
        });

        // Run automation
        $('.run-automation').click(function() {
          const $card = $(this).closest('.card');
          const userId = $card.find('.automation-controls').data('user-id');
          const mode = $card.find('.mode-selector').val();
          const force = $card.find('.force-post').is(':checked');

          runAutomation(userId, mode, force, $(this));
        });

        // Refresh button
        $('.btn-refresh').click(function() {
          const $btn = $(this);
          $btn.prop('disabled', true).html('<i class="ri-refresh-line me-1"></i> Refreshing...');

          setTimeout(() => {
            location.reload();
          }, 1000);
        });

        // Copy output
        $('.btn-copy-output').click(function() {
          const outputText = $('#outputText').text();
          navigator.clipboard.writeText(outputText).then(() => {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.html('<i class="ri-check-line me-1"></i>Copied!');
            setTimeout(() => {
              $btn.html(originalHtml);
            }, 2000);
          });
        });

        function loadUserTemplates(userId) {
          const $templatesList = $(`#templates-list-${userId}`);
          $templatesList.html(`
          <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
            <small class="text-muted">Loading templates...</small>
          </div>
        `);

          $.get(`/automation/user/${userId}/templates`, function(templates) {
            let html = '';
            if (templates.length > 0) {
              templates.forEach(template => {
                const lastPosted = template.last_posted_at ?
                  new Date(template.last_posted_at).toLocaleDateString() : 'Never';

                html += `
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                  <div class="flex-grow-1">
                    <h6 class="mb-1 text-dark">${template.title}</h6>
                    <small class="text-muted">
                      <i class="ri-time-line me-1"></i>Last: ${lastPosted}
                    </small>
                  </div>
                  <span class="badge bg-${template.is_active ? 'success' : 'secondary'} rounded-pill">
                    <i class="ri-${template.is_active ? 'check' : 'close'}-circle-line me-1"></i>
                    ${template.is_active ? 'Active' : 'Inactive'}
                  </span>
                </div>`;
              });
            } else {
              html = '<div class="text-center text-muted py-3"><small>No templates found</small></div>';
            }
            $templatesList.html(html);
          }).fail(function() {
            $templatesList.html(
              '<div class="text-center text-danger py-3"><small>Failed to load templates</small></div>');
          });
        }

        function runAutomation(userId, mode, force, $button) {
          const $modal = $('#outputModal');
          const $output = $('#outputText');
          const $spinner = $button.find('.spinner-border');

          // Show loading state
          $button.prop('disabled', true);
          $spinner.removeClass('d-none');

          $output.text('🚀 Starting automation process...\n\n');
          $modal.modal('show');

          $.ajax({
            url: `/automation/run/user/${userId}`,
            method: 'POST',
            data: {
              mode: mode,
              force: force,
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              $output.text(response.output);
              $button.prop('disabled', false);
              $spinner.addClass('d-none');

              // Reload templates after successful automation
              loadUserTemplates(userId);
            },
            error: function(xhr) {
              $output.text('❌ Error: ' + (xhr.responseJSON?.message || 'Request failed'));
              $button.prop('disabled', false);
              $spinner.addClass('d-none');
            }
          });
        }

        // Load templates for first user by default
        @if ($userAccounts->isNotEmpty())
          loadUserTemplates({{ $userAccounts->first()->id }});
        @endif
      });
    </script>
  @endpush
</x-layouts.admin.master>

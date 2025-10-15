<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-1">
            <i class="ri-send-plane-line text-primary me-2"></i>Offer Posting
          </h5>
          <p class="text-muted mb-0">Start posting offers for your accounts</p>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-primary btn-refresh">
            <i class="ri-refresh-line me-1"></i> Refresh
          </button>
          <button type="button" class="btn btn-success btn-post-all">
            <i class="ri-play-large-line me-1"></i> Start All Posting
          </button>
        </div>
      </div>
    </x-slot>

    <!-- Quick Stats -->
    <div class="row mb-4">
      <div class="col-md-3 col-6">
        <div class="bg-light rounded border p-3 text-center">
          <h3 class="text-primary mb-1">{{ $userAccounts->count() }}</h3>
          <small class="text-muted">Total Users</small>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="bg-light rounded border p-3 text-center">
          <h3 class="text-success mb-1">{{ $userAccounts->sum('active_templates_count') }}</h3>
          <small class="text-muted">Active Templates</small>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="bg-light rounded border p-3 text-center">
          <h3 class="text-info mb-1">{{ $userAccounts->where('active_templates_count', '>', 0)->count() }}</h3>
          <small class="text-muted">Ready Accounts</small>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="bg-light rounded border p-3 text-center">
          <h3 class="text-warning mb-1">{{ $userAccounts->sum('total_templates') }}</h3>
          <small class="text-muted">Total Templates</small>
        </div>
      </div>
    </div>

    <!-- Output Panel (Fixed at top) -->
    <div class="alert alert-info output-panel mb-4" style="display: none;">
      <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
          <h6 class="alert-heading mb-2">
            <i class="ri-information-line me-1"></i>Posting Status
          </h6>
          <pre class="output-text small mb-0"
               style="white-space: pre-wrap; background: transparent; border: none; padding: 0; margin: 0;"></pre>
        </div>
        <button type="button" class="btn-close ms-3" onclick="hideOutput()"></button>
      </div>
    </div>

    <!-- User Accounts List -->
    <div class="row">
      @foreach ($userAccounts as $user)
        <div class="col-xl-6 col-lg-12">
          <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->email) }}&background=7269ef&color=fff&size=64"
                         alt="user-image" class="rounded-circle">
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="fw-semibold mb-1">{{ $user->email }}</h6>
                    <div class="d-flex text-muted small gap-3">
                      <span><i class="ri-file-text-line me-1"></i>{{ $user->total_templates }} total</span>
                      <span><i class="ri-checkbox-circle-line me-1"></i>{{ $user->active_templates_count }}
                        active</span>
                    </div>
                  </div>
                </div>

                <div class="text-end">
                  <span class="badge bg-{{ $user->active_templates_count > 0 ? 'success' : 'secondary' }} mb-2">
                    {{ $user->active_templates_count > 0 ? 'Ready' : 'No Templates' }}
                  </span>
                  <br>
                  <button type="button" class="btn btn-primary btn-sm btn-start-posting mt-1"
                          data-user-id="{{ $user->id }}"
                          {{ $user->active_templates_count == 0 ? 'disabled' : '' }}>
                    <i class="ri-play-circle-line me-1"></i>
                    Start Posting
                  </button>
                </div>
              </div>

              <!-- Templates Preview (Collapsible) -->
              <div class="mt-3">
                <button class="btn btn-outline-light btn-sm w-100 text-muted btn-toggle-templates" type="button"
                        data-bs-toggle="collapse" data-bs-target="#templates-{{ $user->id }}"
                        aria-expanded="false">
                  <i class="ri-arrow-down-s-line me-1"></i>
                  View Templates
                </button>

                <div class="collapse mt-2" id="templates-{{ $user->id }}">
                  <div class="templates-container" id="templates-list-{{ $user->id }}">
                    <div class="text-muted small py-2 text-center">
                      Loading templates...
                    </div>
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
          <i class="ri-user-search-line display-1 text-muted opacity-25"></i>
        </div>
        <h4 class="text-muted">No User Accounts</h4>
        <p class="text-muted mb-4">Add user accounts to start posting offers</p>
        <button type="button" class="btn btn-primary">
          <i class="ri-user-add-line me-2"></i>Add User Account
        </button>
      </div>
    @endif

    <!-- Progress Bar (for all posting) -->
    <div class="progress mb-3" id="allPostingProgress" style="display: none; height: 6px;">
      <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
    </div>
  </x-data-display.card>

  @push('styles')
    <style>
      .output-panel {
        border-left: 4px solid #0dcaf0;
      }

      .templates-container {
        max-height: 200px;
        overflow-y: auto;
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 0.75rem;
      }

      .templates-container::-webkit-scrollbar {
        width: 4px;
      }

      .templates-container::-webkit-scrollbar-track {
        background: #f1f1f1;
      }

      .templates-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
      }

      .btn-start-posting {
        min-width: 120px;
      }

      .btn-post-all {
        min-width: 140px;
      }
    </style>
  @endpush

  @push('scripts')
    <script>
      function hideOutput() {
        $('.output-panel').fadeOut();
      }

      function showOutput(message, type = 'info') {
        const $panel = $('.output-panel');
        const $text = $('.output-text');

        // Update panel style based on type
        $panel.removeClass('alert-info alert-success alert-danger')
          .addClass(`alert-${type}`);

        $text.text(message);
        $panel.slideDown();

        // Auto-hide success messages after 10 seconds
        if (type === 'success') {
          setTimeout(hideOutput, 10000);
        }
      }

      $(document).ready(function() {
        // Load templates when toggle button is clicked
        $('.btn-toggle-templates').on('click', function() {
          const $button = $(this);
          const target = $button.data('bs-target');
          const userId = target.split('-')[1];

          // Only load if not already loaded
          const $container = $(`#templates-list-${userId}`);
          if ($container.children().length === 1 && $container.text().includes('Loading')) {
            loadUserTemplates(userId);
          }
        });

        // Start posting for individual user
        $('.btn-start-posting').click(function() {
          const $button = $(this);
          const userId = $button.data('user-id');

          $button.prop('disabled', true).html(`
          <div class="spinner-border spinner-border-sm me-1" role="status"></div>
          Posting...
        `);

          showOutput(`🚀 Starting posting for user ${userId}...`, 'info');

          $.ajax({
            url: `/automation/run/user/${userId}`,
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              showOutput(response.output, 'success');
              $button.html('<i class="ri-play-circle-line me-1"></i>Start Posting').prop('disabled', false);

              // Refresh templates for this user
              loadUserTemplates(userId);
            },
            error: function(xhr) {
              showOutput('❌ Error: ' + (xhr.responseJSON?.message || 'Posting failed'), 'danger');
              $button.html('<i class="ri-play-circle-line me-1"></i>Start Posting').prop('disabled', false);
            }
          });
        });

        // Start all posting
        $('.btn-post-all').click(function() {
          const $button = $(this);
          const $progress = $('#allPostingProgress');

          $button.prop('disabled', true).html(`
          <div class="spinner-border spinner-border-sm me-1" role="status"></div>
          Starting All...
        `);
          $progress.show();

          showOutput('🚀 Starting posting for all users...', 'info');

          $.ajax({
            url: '{{ route('automation.run.all-users') }}',
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              showOutput(response.output, 'success');
              $button.html('<i class="ri-play-large-line me-1"></i>Start All Posting').prop('disabled',
                false);
              $progress.hide();

              // Refresh the page to update counts
              setTimeout(() => {
                location.reload();
              }, 3000);
            },
            error: function(xhr) {
              showOutput('❌ Error: ' + (xhr.responseJSON?.message || 'All posting failed'), 'danger');
              $button.html('<i class="ri-play-large-line me-1"></i>Start All Posting').prop('disabled',
                false);
              $progress.hide();
            }
          });
        });

        // Refresh button
        $('.btn-refresh').click(function() {
          const $btn = $(this);
          $btn.prop('disabled', true).html('<i class="ri-refresh-line me-1"></i> Refreshing...');
          setTimeout(() => location.reload(), 1000);
        });

        function loadUserTemplates(userId) {
          const $container = $(`#templates-list-${userId}`);

          $.get(`/automation/user/${userId}/templates`, function(templates) {
            let html = '';

            if (templates.length > 0) {
              templates.forEach(template => {
                const lastPosted = template.last_posted_at ?
                  new Date(template.last_posted_at).toLocaleDateString() : 'Never';

                html += `
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                  <div>
                    <strong class="small">${template.title}</strong>
                    <br>
                    <small class="text-muted">Last: ${lastPosted}</small>
                  </div>
                  <span class="badge bg-${template.is_active ? 'success' : 'secondary'}">
                    ${template.is_active ? 'Active' : 'Inactive'}
                  </span>
                </div>
              `;
              });
            } else {
              html = '<div class="text-center text-muted small">No templates found</div>';
            }

            $container.html(html);
          }).fail(function() {
            $container.html('<div class="text-center text-danger small">Failed to load templates</div>');
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

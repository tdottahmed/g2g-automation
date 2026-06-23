<x-data-display.card class="mb-4">
  <x-slot name="header">
    <h5 class="card-title mb-0">
      <i class="ri-flashlight-line text-primary me-2"></i>Quick Actions
    </h5>
  </x-slot>

  <div class="row g-3">

    {{-- Template Management --}}
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

    {{-- Post for Account --}}
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

    {{-- Delete All Except Permanent --}}
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

    {{-- Post All Accounts --}}
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

  </div>
</x-data-display.card>

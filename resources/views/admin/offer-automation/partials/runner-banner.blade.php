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

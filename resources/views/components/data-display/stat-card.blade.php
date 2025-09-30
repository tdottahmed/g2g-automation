@props([
    'icon',
    'label',
    'value' => 0,
    'subtitle' => null,
    'trend' => null,
    'trendType' => 'up', // 'up' or 'down'
    'bgClass' => 'bg-primary',
])

<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center">
      <div class="me-3 flex-shrink-0">
        <div class="avatar-sm">
          <span class="avatar-title {{ $bgClass }} rounded-circle text-white">
            <i class="{{ $icon }} fs-4"></i>
          </span>
        </div>
      </div>
      <div class="flex-grow-1">
        <h4 class="mb-1">{{ $value }}</h4>
        <p class="text-muted mb-0">{{ $label }}</p>
        @if ($subtitle)
          <small class="text-muted">{{ $subtitle }}</small>
        @endif
        @if ($trend !== null)
          <div class="d-flex align-items-center mt-1">
            <span class="text-{{ $trendType === 'up' ? 'success' : 'danger' }}">
              <i class="ri-arrow-{{ $trendType === 'up' ? 'up' : 'down' }}-line me-1"></i>
              {{ $trend }}%
            </span>
            <span class="text-muted ms-1">vs previous</span>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

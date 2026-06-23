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
<div class="row g-3 mb-4">
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

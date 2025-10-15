@props([
    'header' => null,
    'footer' => null,
])

<div class="card">
    @if ($header || isset($headerSlot))
        <div class="card-header">
            {{ $header ?? $headerSlot }}
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if ($footer || isset($footerSlot))
        <div class="card-footer">
            {{ $footer ?? $footerSlot }}
        </div>
    @endif
</div>

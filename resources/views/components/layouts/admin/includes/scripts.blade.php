<!-- JAVASCRIPT -->
<script src="/assets/admin/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/assets/admin/libs/simplebar/simplebar.min.js"></script>
<script src="/assets/admin/libs/node-waves/waves.min.js"></script>
<script src="/assets/admin/libs/feather-icons/feather.min.js"></script>
<script src="/assets/admin/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script src="/assets/admin/js/plugins.js"></script>


<!-- apexcharts -->
{{-- <script src="/assets/admin/libs/apexcharts/apexcharts.min.js"></script> --}}

<!-- Vector map-->
<script src="/assets/admin/libs/jsvectormap/jsvectormap.min.js"></script>
<script src="/assets/admin/libs/jsvectormap/maps/world-merc.js"></script>

<!--Swiper slider js-->
<script src="/assets/admin/libs/swiper/swiper-bundle.min.js"></script>

<!-- Dashboard init -->
<script src="/assets/admin/js/pages/dashboard-ecommerce.init.js"></script>

<!-- App js -->
<script src="/assets/admin/js/app.js"></script>
<script src="/assets/admin/js/pages/password-addon.init.js"></script>
<script src="/assets/admin/js/jquery.min.js"></script>
<script src="/assets/admin/libs/sweetalert2/sweetalert2.min.js"></script>
<script src="/assets/admin/libs/select2/js/select2.min.js"></script>
<script src="{{ asset('/assets/admin/libs/tinymce/tinymce.min.js') }}"></script>
<script type='text/javascript' src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<x-data-display.sweet-alert />

{{-- ── Page refresh widget ──────────────────────────────────────────────────── --}}
<script>
(function () {
    const STORAGE_KEY   = 'pageAutoRefreshSeconds';
    const icon          = document.getElementById('refresh-icon');
    const dot           = document.getElementById('refresh-active-dot');
    const countdown     = document.getElementById('refresh-countdown');
    const nowBtn        = document.getElementById('refresh-now-btn');

    let autoTimer       = null;
    let tickTimer       = null;
    let targetTime      = 0;

    // ── Spin icon and reload ──
    function triggerRefresh() {
        if (!icon) return;
        icon.style.transition = 'transform 0.45s cubic-bezier(.4,0,.2,1)';
        icon.style.transform  = 'rotate(360deg)';
        setTimeout(() => window.location.reload(), 420);
    }

    // ── Clear any running auto-refresh ──
    function clearAuto() {
        clearInterval(autoTimer);
        clearInterval(tickTimer);
        autoTimer = tickTimer = null;
        if (dot)      dot.classList.add('d-none');
        if (countdown) countdown.textContent = '';
    }

    // ── Start auto-refresh at given interval (seconds) ──
    function startAuto(seconds) {
        clearAuto();
        if (seconds <= 0) { localStorage.removeItem(STORAGE_KEY); return; }

        localStorage.setItem(STORAGE_KEY, seconds);
        if (dot) dot.classList.remove('d-none');

        targetTime = Date.now() + seconds * 1000;

        function tick() {
            const rem = Math.max(0, Math.ceil((targetTime - Date.now()) / 1000));
            if (countdown) countdown.textContent = rem + 's';
        }
        tick();
        tickTimer = setInterval(tick, 500);

        autoTimer = setInterval(() => {
            targetTime = Date.now() + seconds * 1000;
            window.location.reload();
        }, seconds * 1000);
    }

    // ── Highlight the active interval option ──
    function markActive(seconds) {
        document.querySelectorAll('.refresh-interval-opt').forEach(btn => {
            const active = parseInt(btn.dataset.seconds, 10) === seconds;
            btn.classList.toggle('active', active);
            btn.classList.toggle('fw-semibold', active);
        });
    }

    // ── Interval option clicks ──
    document.querySelectorAll('.refresh-interval-opt').forEach(btn => {
        btn.addEventListener('click', () => {
            const seconds = parseInt(btn.dataset.seconds, 10);
            startAuto(seconds);
            markActive(seconds);
            // Close dropdown
            const dd = btn.closest('.dropdown-menu');
            const toggle = document.querySelector('[data-bs-toggle="dropdown"]');
            if (toggle) bootstrap.Dropdown.getInstance(toggle)?.hide();
        });
    });

    // ── Refresh now button ──
    if (nowBtn) nowBtn.addEventListener('click', triggerRefresh);

    // ── Restore saved setting on page load ──
    const saved = parseInt(localStorage.getItem(STORAGE_KEY) ?? '0', 10);
    if (saved > 0) { startAuto(saved); markActive(saved); }
    else { markActive(0); }
})();
</script>

@stack('scripts')

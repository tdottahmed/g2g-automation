<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="/" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ getFilePath(getSetting('app_logo')) }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ getFilePath(getSetting('app_logo')) }}" alt="" height="17">
                        </span>
                    </a>

                    <a href="/" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ getFilePath(getSetting('app_logo')) }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ getFilePath(getSetting('app_logo')) }}" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button"
                    class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <!-- App Search-->
                <form action="#" class="app-search d-none d-md-block" method="get">

                    <div class="position-relative">
                        <div class="d-flex">
                            <input type="text" class="form-control" name="search" placeholder="Search..."
                                autocomplete="off" id="search-options" value="">
                            <span class="mdi mdi-magnify search-widget-icon"></span>
                            <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                        </div>
                        <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                            id="search-close-options"></span>
                    </div>
                </form>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown d-none topbar-head-dropdown header-item">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-search-dropdown">
                        <form action="#" class="p-3" method="get">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search ..."
                                        aria-label="Recipient's username">
                                    <button class="btn btn-primary" type="submit"><i
                                            class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
                        data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                {{-- ── Page Refresh ── --}}
                <div class="ms-1 header-item d-flex">
                    <div class="btn-group" id="refresh-group">
                        {{-- Refresh now --}}
                        <button type="button" id="refresh-now-btn"
                            class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary"
                            title="Refresh page">
                            <span class="position-relative d-inline-flex">
                                <i class="ri-refresh-line fs-22" id="refresh-icon"></i>
                                {{-- Pulse dot when auto-refresh is active --}}
                                <span id="refresh-active-dot"
                                    class="position-absolute top-0 start-100 translate-middle p-1 bg-success rounded-circle d-none"
                                    style="width:8px;height:8px;margin-top:2px;margin-left:-4px">
                                </span>
                            </span>
                        </button>
                        {{-- Interval dropdown toggle --}}
                        <button type="button"
                            class="btn btn-topbar material-shadow-none btn-ghost-secondary dropdown-toggle dropdown-toggle-split px-1"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside"
                            aria-expanded="false" title="Auto-refresh options"
                            style="font-size:11px;min-width:0">
                            <span class="visually-hidden">Refresh options</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width:210px">
                            <div class="d-flex align-items-center justify-content-between px-2 py-1 mb-2 border-bottom pb-2">
                                <span class="fw-semibold small">Auto-Refresh</span>
                                <span class="text-muted small" id="refresh-countdown" style="font-variant-numeric:tabular-nums"></span>
                            </div>
                            <button class="dropdown-item rounded-1 py-1 refresh-interval-opt" data-seconds="0">
                                <i class="ri-close-line me-2 text-muted"></i>Off
                            </button>
                            <button class="dropdown-item rounded-1 py-1 refresh-interval-opt" data-seconds="30">
                                <i class="ri-timer-line me-2 text-success"></i>Every 30 seconds
                            </button>
                            <button class="dropdown-item rounded-1 py-1 refresh-interval-opt" data-seconds="60">
                                <i class="ri-timer-line me-2 text-success"></i>Every 1 minute
                            </button>
                            <button class="dropdown-item rounded-1 py-1 refresh-interval-opt" data-seconds="120">
                                <i class="ri-timer-line me-2 text-success"></i>Every 2 minutes
                            </button>
                            <button class="dropdown-item rounded-1 py-1 refresh-interval-opt" data-seconds="300">
                                <i class="ri-timer-line me-2 text-success"></i>Every 5 minutes
                            </button>
                        </div>
                    </div>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button"
                        class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user"
                                src="{{ getFilePath(auth()->user()->image) }}" alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span
                                    class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ Auth::user()->name }}</span>

                            </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <a class="dropdown-item" href="{{ route('profile.edit') }}"><i
                                class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                                class="align-middle">Profile</span></a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit" class="dropdown-item"><i
                                    class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle" data-key="t-logout">Logout</span></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

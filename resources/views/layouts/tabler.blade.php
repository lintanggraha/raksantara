<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Risk Management System'))</title>

    <link rel="stylesheet" href="{{ asset('vendor/tabler/css/tabler.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2-bootstrap-5-theme.min.css') }}">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .page-wrapper {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }
        .page-header {
            padding: 1rem 0 0.5rem;
        }
        .page-body {
            flex: 1 1 auto;
            padding: 0.5rem 0 1.5rem;
        }
        .container-xl {
            width: 100%;
            max-width: 1320px;
            margin-right: auto;
            margin-left: auto;
            padding-right: 15px;
            padding-left: 15px;
        }
        .navbar-brand-autodark {
            color: #212529;
            text-decoration: none;
            font-weight: 600;
        }
        .avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            color: #495057;
            font-weight: 600;
        }
        .avatar-sm {
            width: 2rem;
            height: 2rem;
            font-size: 0.875rem;
        }
        .form-label {
            display: inline-block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-hint {
            display: block;
            margin-top: 0.25rem;
            color: #6c757d;
            font-size: 0.8125rem;
        }
        .table-vcenter td,
        .table-vcenter th {
            vertical-align: middle !important;
        }
        .fw-bold {
            font-weight: 700 !important;
        }
        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1.2;
        }
        .text-end {
            text-align: right !important;
        }
        .text-start {
            text-align: left !important;
        }
        .lh-1 {
            line-height: 1 !important;
        }
        .ps-2 {
            padding-left: 0.5rem !important;
        }
        .me-2 {
            margin-right: 0.5rem !important;
        }
        .dropdown-menu-end {
            right: 0;
            left: auto;
        }
        .gap-1 {
            gap: 0.25rem !important;
        }
        .gap-2 {
            gap: 0.5rem !important;
        }
        .gap-3 {
            gap: 1rem !important;
        }
        .g-2 {
            margin-right: -0.5rem;
            margin-left: -0.5rem;
        }
        .g-2 > * {
            padding-right: 0.5rem;
            padding-left: 0.5rem;
        }
        .g-3 {
            margin-right: -1rem;
            margin-left: -1rem;
        }
        .g-3 > * {
            padding-right: 1rem;
            padding-left: 1rem;
        }
        .bg-green-lt {
            background-color: #d1f0dd;
            color: #186a3b;
        }
        .bg-yellow-lt {
            background-color: #fff3cd;
            color: #856404;
        }
        .bg-blue-lt {
            background-color: #d7ebff;
            color: #0f4c81;
        }
        .bg-secondary-lt {
            background-color: #e2e3e5;
            color: #41464b;
        }
        .small-box {
            position: relative;
            border-radius: 8px;
            color: #fff;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }
        .small-box .inner h3 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
        }
        .small-box .inner p {
            margin: 4px 0 0;
            font-size: 0.85rem;
            opacity: 0.95;
        }
        .small-box .icon {
            position: absolute;
            right: 12px;
            top: 10px;
            font-size: 2rem;
            opacity: 0.25;
        }
        .small-box .small-box-footer {
            display: block;
            margin: 12px -16px -16px;
            padding: 8px 16px;
            color: rgba(255, 255, 255, 0.9);
            background: rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
        .btn-xs {
            padding: 0.1rem 0.4rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .float-right {
            float: right !important;
        }
        .mr-2 {
            margin-right: 0.5rem !important;
        }
        .ml-2 {
            margin-left: 0.5rem !important;
        }
        .form-inline {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
        }
        .form-inline .form-control {
            width: auto;
        }
        .form-inline label {
            margin-bottom: 0;
        }
        .btn.with-icon {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn.with-icon i {
            margin: 0;
            line-height: 1;
        }
        .navbar,
        .navbar .container-xl,
        .navbar .navbar-nav,
        .navbar .nav-item.dropdown {
            overflow: visible;
        }
        .navbar-toggler-icon {
            display: inline-block;
            width: 1.5em;
            height: 1.5em;
            vertical-align: middle;
            background: no-repeat center center;
            background-size: 100% 100%;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='rgba%2833,37,41,0.75%29' stroke-width='2' stroke-linecap='round' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .navbar .nav-item.dropdown > .nav-link {
            cursor: pointer;
        }
        .navbar .dropdown-menu {
            min-width: 12rem;
            z-index: 2000;
        }
        .navbar .navbar-nav .nav-link.nav-link-main {
            border-radius: 0;
            background-color: transparent !important;
            transition: color 0.15s ease;
        }
        .navbar .navbar-nav .nav-link.nav-link-main:hover,
        .navbar .navbar-nav .nav-link.nav-link-main:focus {
            background-color: transparent !important;
            color: #0b57d0 !important;
        }
        .navbar .navbar-nav .nav-item.active > .nav-link.nav-link-main,
        .navbar .navbar-nav .nav-item.show > .nav-link.nav-link-main {
            background-color: transparent !important;
            border-color: transparent !important;
            box-shadow: none !important;
            color: #0b57d0 !important;
            font-weight: 600;
        }
        .navbar .navbar-nav .nav-link.nav-link-main.dropdown-toggle::after {
            margin-left: 0.4rem;
        }
        [data-compliance-menu] .dropdown-item,
        [data-risk-menu] .dropdown-item {
            border-radius: 0;
            margin: 0;
            padding: 0.5rem 0.75rem;
            background-color: transparent !important;
            transition: color 0.15s ease;
        }
        [data-compliance-menu] .dropdown-item:hover,
        [data-compliance-menu] .dropdown-item:focus,
        [data-risk-menu] .dropdown-item:hover,
        [data-risk-menu] .dropdown-item:focus {
            background-color: transparent !important;
            color: #0b57d0 !important;
        }
        [data-compliance-menu] .dropdown-item.active,
        [data-compliance-menu] .dropdown-item.active:hover,
        [data-compliance-menu] .dropdown-item.active:focus,
        [data-risk-menu] .dropdown-item.active,
        [data-risk-menu] .dropdown-item.active:hover,
        [data-risk-menu] .dropdown-item.active:focus {
            background-color: transparent !important;
            color: #0b57d0 !important;
            font-weight: 600;
            box-shadow: none;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            min-height: calc(1.4285714286em + 0.875rem + 2px);
            padding: 0.4375rem 0.75rem;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 1.4285714286em;
        }
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: calc(1.4285714286em + 0.875rem);
        }
        .select2-container .select2-dropdown .select2-search__field {
            min-height: 2rem;
        }
    </style>

    @yield('css')
    @stack('styles')
</head>
<body>
<div class="page">
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#main-navbar"
                data-toggle="collapse"
                data-target="#main-navbar"
                aria-controls="main-navbar"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark pe-0 pe-md-3">
                <a href="{{ route('risks.index') }}">Risk Management System</a>
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                @auth
                    <div class="nav-item dropdown">
                        <a
                            href="#"
                            class="nav-link d-flex lh-1 text-reset p-0 dropdown-toggle"
                            id="user-menu-toggle"
                            data-bs-toggle="dropdown"
                            data-toggle="dropdown"
                            data-user-menu-toggle
                            role="button"
                            aria-expanded="false"
                            aria-label="Open user menu"
                        >
                            <span class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <div class="d-none d-xl-block ps-2">
                                <div>{{ auth()->user()->name }}</div>
                                <div class="mt-1 small text-secondary">
                                    {{ auth()->user()->isSuperadmin() ? 'Superadmin' : 'Authenticated User' }}
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow" data-user-menu aria-labelledby="user-menu-toggle">
                            <a href="{{ route('profile.index') }}" class="dropdown-item">Profile</a>
                            <a href="{{ route('change.password') }}" class="dropdown-item">Change Password</a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
            <div class="collapse navbar-collapse" id="main-navbar">
                <ul class="navbar-nav">
                    @auth
                        @if(auth()->user()->isSuperadmin())
                            <li class="nav-item {{ request()->routeIs('risks.monitoring') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('risks.monitoring') }}">Dashboard</a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('users.index') }}">User Management</a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('risk-status-approvals.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('risk-status-approvals.index') }}">Status Approvals</a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('risk-alerts.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('risk-alerts.index') }}">Alerts</a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('audit-logs.index') }}">Audit Trail</a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('threat-intelligence.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('threat-intelligence.index') }}">Threat Intel</a>
                            </li>
                        @elseif(auth()->user()->isDivisionVerified())
                                <li
                                class="nav-item dropdown {{
                                    (request()->routeIs('risks.monitoring')
                                    || (request()->routeIs('risks.*') && !request()->routeIs('risks.dashboard-summary'))
                                    || request()->routeIs('trends.analysis')
                                    || request()->routeIs('kris.*')
                                    || request()->routeIs('treatments.kanban')
                                    || request()->routeIs('incidents.*')) ? 'active' : ''
                                }}"
                                data-risk-nav
                            >
                                <a
                                    class="nav-link nav-link-main dropdown-toggle"
                                    href="#"
                                    data-bs-toggle="dropdown"
                                    data-toggle="dropdown"
                                    data-risk-toggle
                                    role="button"
                                    aria-expanded="false"
                                >
                                    Risk
                                </a>
                                <div class="dropdown-menu" data-risk-menu>
                                    <a class="dropdown-item {{ request()->routeIs('risks.monitoring') ? 'active' : '' }}" href="{{ route('risks.monitoring') }}">
                                        Risk Monitoring
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('risks.*') && !request()->routeIs('risks.monitoring') && !request()->routeIs('risks.dashboard-summary') ? 'active' : '' }}" href="{{ route('risks.index') }}">
                                        Risk Register
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('trends.analysis') ? 'active' : '' }}" href="{{ route('trends.analysis') }}">
                                        Risk Trend Analysis
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('kris.*') ? 'active' : '' }}" href="{{ route('kris.index') }}">
                                        Key Risk Indicators 
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('treatments.kanban') ? 'active' : '' }}" href="{{ route('treatments.kanban') }}">
                                        Mitigation Kanban
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('incidents.*') ? 'active' : '' }}" href="{{ route('incidents.index') }}">
                                        Incident Management
                                    </a>
                                </div>
                            </li>
                            @if(auth()->user()->isComplianceDivision())
                                <li class="nav-item {{ request()->routeIs('audit.placeholder') ? 'active' : '' }}">
                                    <a class="nav-link nav-link-main" href="{{ route('audit.placeholder') }}">Audit</a>
                                </li>
                            @endif
                            <li class="nav-item {{ request()->routeIs('threat-intelligence.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('threat-intelligence.index') }}">Threat Intel</a>
                            </li>
                            @if(auth()->user()->isComplianceDivision())
                                <li class="nav-item {{ request()->routeIs('ojk-mitigation.*') ? 'active' : '' }}">
                                    <a class="nav-link nav-link-main" href="{{ route('ojk-mitigation.index') }}">Mitigasi OJK</a>
                                </li>
                            @endif
                            <li class="nav-item {{ request()->routeIs('howto') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('howto') }}">User Guide</a>
                            </li>
                            @if(auth()->user()->isComplianceDivision())
                                <li class="nav-item dropdown {{ request()->routeIs('compliance.*') ? 'active' : '' }}" data-compliance-nav>
                                    <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-compliance-toggle role="button" aria-expanded="false">
                                        Compliance
                                    </a>
                                    <div class="dropdown-menu" data-compliance-menu>
                                        <a class="dropdown-item {{ request()->routeIs('compliance.internal-audit*') ? 'active' : '' }}" href="{{ route('compliance.internal-audit') }}">
                                            Internal Audit
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('compliance.risk-management*') ? 'active' : '' }}" href="{{ route('compliance.risk-management') }}">
                                            Risk Management
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('compliance.compliance*') ? 'active' : '' }}" href="{{ route('compliance.compliance') }}">
                                            Governance &amp; Compliance
                                        </a>
                                    </div>
                                </li>
                            @endif
                        @else
                            <li class="nav-item {{ request()->routeIs('account.pending') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('account.pending') }}">Pending Approval</a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        @if (trim($__env->yieldContent('content_header')))
            <div class="page-header d-print-none">
                <div class="container-xl">
                    @yield('content_header')
                </div>
            </div>
        @endif

        <div class="page-body">
            <div class="container-xl">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/tabler/js/tabler.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
<script>
    document.querySelectorAll('[data-toggle]').forEach(function (el) {
        if (!el.hasAttribute('data-bs-toggle')) {
            el.setAttribute('data-bs-toggle', el.getAttribute('data-toggle'));
        }
    });
    document.querySelectorAll('[data-bs-toggle]').forEach(function (el) {
        if (!el.hasAttribute('data-toggle')) {
            el.setAttribute('data-toggle', el.getAttribute('data-bs-toggle'));
        }
    });
    document.querySelectorAll('[data-target]').forEach(function (el) {
        if (!el.hasAttribute('data-bs-target')) {
            el.setAttribute('data-bs-target', el.getAttribute('data-target'));
        }
    });
    document.querySelectorAll('[data-bs-target]').forEach(function (el) {
        if (!el.hasAttribute('data-target')) {
            el.setAttribute('data-target', el.getAttribute('data-bs-target'));
        }
    });
    document.querySelectorAll('[data-dismiss]').forEach(function (el) {
        if (!el.hasAttribute('data-bs-dismiss')) {
            el.setAttribute('data-bs-dismiss', el.getAttribute('data-dismiss'));
        }
    });
    document.querySelectorAll('[data-bs-dismiss]').forEach(function (el) {
        if (!el.hasAttribute('data-dismiss')) {
            el.setAttribute('data-dismiss', el.getAttribute('data-bs-dismiss'));
        }
    });

    const hasBootstrap5 = window.bootstrap && typeof window.bootstrap.Dropdown !== 'undefined';
    if (hasBootstrap5) {
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
            bootstrap.Dropdown.getOrCreateInstance(el);
        });

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    } else if (window.jQuery) {
        if ($.fn.dropdown) {
            $('[data-toggle="dropdown"]').dropdown();
        }
        if ($.fn.tooltip) {
            $('[data-toggle="tooltip"]').tooltip();
        }
    }

    // Keep user menu reliable across browsers (including Firefox).
    const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
    const userMenu = document.querySelector('[data-user-menu]');
    if (userMenuToggle && userMenu) {
        const dropdownInstance = (window.bootstrap && window.bootstrap.Dropdown)
            ? bootstrap.Dropdown.getOrCreateInstance(userMenuToggle)
            : null;
        const dropdownFallback = (window.jQuery && $.fn.dropdown) ? $(userMenuToggle) : null;

        function openUserMenu() {
            if (dropdownInstance) {
                dropdownInstance.show();
            } else if (dropdownFallback) {
                dropdownFallback.dropdown('show');
            }
            userMenu.classList.add('show');
            userMenuToggle.setAttribute('aria-expanded', 'true');
        }

        function closeUserMenu() {
            if (dropdownInstance) {
                dropdownInstance.hide();
            } else if (dropdownFallback) {
                dropdownFallback.dropdown('hide');
            }
            userMenu.classList.remove('show');
            userMenuToggle.setAttribute('aria-expanded', 'false');
        }

        function toggleUserMenu() {
            if (userMenu.classList.contains('show')) {
                closeUserMenu();
            } else {
                openUserMenu();
            }
        }

        userMenuToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleUserMenu();
        });

        userMenuToggle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleUserMenu();
            } else if (event.key === 'Escape') {
                closeUserMenu();
            }
        });

        document.addEventListener('click', function (event) {
            if (!userMenu.contains(event.target) && !userMenuToggle.contains(event.target)) {
                closeUserMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeUserMenu();
            }
        });
    }

    function initSearchableSelects(context) {
        if (!window.jQuery || !$.fn.select2) {
            return;
        }

        const $scope = context ? $(context) : $(document);

        $scope.find('select').each(function () {
            const $select = $(this);

            if ($select.hasClass('no-search-select') || $select.data('noSearch') === true) {
                return;
            }

            if ($select.data('select2')) {
                return;
            }

            const placeholder = $select.attr('placeholder') || ($select.find('option[value=""]').first().text() || '');
            const insideModal = $select.closest('.modal').first();

            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: placeholder,
                allowClear: $select.find('option[value=""]').length > 0,
                minimumResultsForSearch: 0,
                dropdownParent: insideModal.length ? insideModal : $(document.body),
            });
        });
    }

    function normalizeSelect2Width(scope) {
        const $scope = scope ? $(scope) : $(document);

        $scope.find('select').each(function () {
            const instance = $(this).data('select2');
            if (!instance) {
                return;
            }

            instance.$container.css('width', '100%');
        });
    }

    $(document).ready(function () {
        initSearchableSelects(document);
    });

    document.addEventListener('shown.bs.tab', function (event) {
        const targetSelector = event.target.getAttribute('data-bs-target') || event.target.getAttribute('href');
        const tabPane = targetSelector ? document.querySelector(targetSelector) : null;

        if (tabPane) {
            initSearchableSelects(tabPane);
            normalizeSelect2Width(tabPane);
        }
    });

    const selectObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (!(node instanceof Element)) {
                    return;
                }

                if (node.matches('select')) {
                    initSearchableSelects(node.parentElement || document);
                    normalizeSelect2Width(node.parentElement || document);
                    return;
                }

                if (node.querySelector('select')) {
                    initSearchableSelects(node);
                    normalizeSelect2Width(node);
                }
            });
        });
    });

    selectObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });

    // Navbar dropdown helper: reliable click + hover (desktop) using Bootstrap instance.
    const hasBootstrapDropdown = window.bootstrap && window.bootstrap.Dropdown;
    const hasBootstrap4Dropdown = window.jQuery && $.fn.dropdown;

    function setupManagedNavbarDropdown(navSelector, toggleSelector) {
        const nav = document.querySelector(navSelector);
        const toggle = document.querySelector(toggleSelector);
        if (!nav || !toggle || (!hasBootstrapDropdown && !hasBootstrap4Dropdown)) {
            return;
        }

        const dropdownInstance = hasBootstrapDropdown
            ? bootstrap.Dropdown.getOrCreateInstance(toggle)
            : null;
        const dropdownFallback = hasBootstrap4Dropdown ? $(toggle) : null;
        let hideTimer = null;

        function openMenu() {
            if (hideTimer) {
                clearTimeout(hideTimer);
                hideTimer = null;
            }
            if (dropdownInstance) {
                dropdownInstance.show();
            } else if (dropdownFallback) {
                dropdownFallback.dropdown('show');
            }
        }

        function closeMenu() {
            hideTimer = setTimeout(function () {
                if (dropdownInstance) {
                    dropdownInstance.hide();
                } else if (dropdownFallback) {
                    dropdownFallback.dropdown('hide');
                }
                hideTimer = null;
            }, 120);
        }

        nav.addEventListener('mouseenter', function () {
            if (!window.matchMedia('(hover: hover)').matches) {
                return;
            }
            openMenu();
        });

        nav.addEventListener('mouseleave', function () {
            if (!window.matchMedia('(hover: hover)').matches) {
                return;
            }
            closeMenu();
        });

        nav.addEventListener('focusin', function () {
            openMenu();
        });

        nav.addEventListener('focusout', function () {
            closeMenu();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                if (dropdownInstance) {
                    dropdownInstance.hide();
                } else if (dropdownFallback) {
                    dropdownFallback.dropdown('hide');
                }
            }
        });
    }

    setupManagedNavbarDropdown('[data-compliance-nav]', '[data-compliance-toggle]');
    setupManagedNavbarDropdown('[data-risk-nav]', '[data-risk-toggle]');
</script>

@yield('js')
@stack('scripts')
@stack('scripts_body')
@stack('script_ckeditor')
</body>
</html>

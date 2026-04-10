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
        .avatar-superadmin {
            background: linear-gradient(135deg, #dbeafe, #e0f2fe);
            color: #0b57d0;
            border: 1px solid rgba(11, 87, 208, 0.14);
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
        [data-risk-menu] {
            width: 31rem;
            min-width: 31rem;
            padding: 0.35rem 0.5rem;
        }
        .risk-menu-columns {
            position: relative;
        }

        /* LinkedIn-style navbar */
        .navbar-nav > .nav-item > .nav-link-main {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0.5rem 0.75rem !important;
            gap: 4px !important;
            min-width: 80px;
            border-bottom: 2px solid transparent;
        }
        .navbar-nav > .nav-item > .nav-link-main .nav-link-icon {
            margin: 0 !important;
            width: auto !important;
            height: auto !important;
            line-height: 1 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .navbar-nav > .nav-item > .nav-link-main .nav-link-icon i {
            font-size: 1.4rem !important;
            color: #6c757d;
            transition: color 0.15s ease-in-out;
            margin: 0 !important;
        }
        .navbar-nav > .nav-item > .nav-link-main > span:not(.nav-link-icon) {
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            line-height: 1.2 !important;
            color: #6c757d;
            transition: color 0.15s ease-in-out;
            text-align: center;
            white-space: normal;
        }
        
        /* Active state */
        .navbar-nav > .nav-item.active > .nav-link-main .nav-link-icon i,
        .navbar-nav > .nav-item.active > .nav-link-main > span:not(.nav-link-icon),
        .navbar-nav > .nav-item > .nav-link-main:hover .nav-link-icon i,
        .navbar-nav > .nav-item > .nav-link-main:hover > span:not(.nav-link-icon),
        .navbar-nav > .nav-item.dropdown.show > .nav-link.dropdown-toggle > span:not(.nav-link-icon),
        .navbar-nav > .nav-item.dropdown.show > .nav-link.dropdown-toggle .nav-link-icon i {
            color: #1e293b !important;
        }
        .navbar-nav > .nav-item.active > .nav-link-main {
            border-bottom: 2px solid #1e293b !important;
            padding-bottom: 0.5rem !important;
        }

        /* Hide dropdown caret in main nav */
        .navbar-nav > .nav-item > .nav-link-main.dropdown-toggle::after {
            display: none !important;
        }

        /* Override mobile view */
        @media (max-width: 991.98px) {
            .navbar-nav > .nav-item > .nav-link-main {
                flex-direction: row !important;
                justify-content: flex-start !important;
                padding: 0.75rem 1rem !important;
                gap: 12px !important;
                border-bottom: none !important;
            }
            .navbar-nav > .nav-item.active > .nav-link-main {
                border-left: 3px solid #1e293b !important;
                border-bottom: none !important;
                padding-bottom: 0.75rem !important;
            }
            .navbar-nav > .nav-item > .nav-link-main .nav-link-icon i {
                font-size: 1.25rem !important;
            }
            .navbar-nav > .nav-item > .nav-link-main > span:not(.nav-link-icon) {
                font-size: 0.875rem !important;
                text-align: left;
            }
        }

        .risk-menu-columns {
            position: relative;
            display: grid;
            grid-template-columns: repeat(2, 15rem);
            width: 30rem;
            column-gap: 0;
        }
        .risk-menu-columns::before {
            content: "";
            position: absolute;
            top: 0.35rem;
            bottom: 0.35rem;
            left: 50%;
            width: 1px;
            background: #e5e7eb;
            transform: translateX(-0.5px);
        }
        .risk-menu-column {
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            width: 100%;
            min-width: 0;
            padding: 0 0.5rem;
        }
        .navbar .navbar-nav .nav-link.nav-link-main {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 0;
            background-color: transparent !important;
            transition: color 0.15s ease;
        }
        .navbar .navbar-nav .nav-link-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            color: #5f6b7a;
            font-size: 0.9rem;
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
        .navbar .navbar-nav .nav-item.active > .nav-link .nav-link-icon,
        .navbar .navbar-nav .nav-item.show > .nav-link .nav-link-icon,
        .navbar .navbar-nav .nav-link.nav-link-main:hover .nav-link-icon,
        .navbar .navbar-nav .nav-link.nav-link-main:focus .nav-link-icon {
            color: #0b57d0;
        }
        .navbar .navbar-nav .nav-link.nav-link-main.dropdown-toggle::after {
            margin-left: 0.4rem;
        }
        .navbar .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }
        .navbar .dropdown-menu .dropdown-item i {
            width: 1rem;
            text-align: center;
            color: #5f6b7a;
        }
        [data-compliance-menu] .dropdown-item,
        [data-risk-menu] .dropdown-item,
        [data-app-management-menu] .dropdown-item {
            border-radius: 0;
            margin: 0;
            padding: 0.5rem 0.75rem;
            background-color: transparent !important;
            transition: color 0.15s ease;
        }
        [data-risk-menu] .dropdown-item {
            display: block;
            width: 100%;
            line-height: 1.45;
            white-space: normal;
            word-break: normal;
            overflow-wrap: break-word;
        }
        [data-compliance-menu] .dropdown-item:hover,
        [data-compliance-menu] .dropdown-item:focus,
        [data-risk-menu] .dropdown-item:hover,
        [data-risk-menu] .dropdown-item:focus,
        [data-app-management-menu] .dropdown-item:hover,
        [data-app-management-menu] .dropdown-item:focus {
            background-color: transparent !important;
            color: #0b57d0 !important;
        }
        [data-compliance-menu] .dropdown-item.active,
        [data-compliance-menu] .dropdown-item.active:hover,
        [data-compliance-menu] .dropdown-item.active:focus,
        [data-risk-menu] .dropdown-item.active,
        [data-risk-menu] .dropdown-item.active:hover,
        [data-risk-menu] .dropdown-item.active:focus,
        [data-app-management-menu] .dropdown-item.active,
        [data-app-management-menu] .dropdown-item.active:hover,
        [data-app-management-menu] .dropdown-item.active:focus {
            background-color: transparent !important;
            color: #0b57d0 !important;
            font-weight: 600;
            box-shadow: none;
        }
        [data-compliance-menu] .dropdown-item:hover i,
        [data-compliance-menu] .dropdown-item:focus i,
        [data-compliance-menu] .dropdown-item.active i,
        [data-risk-menu] .dropdown-item:hover i,
        [data-risk-menu] .dropdown-item:focus i,
        [data-risk-menu] .dropdown-item.active i,
        [data-app-management-menu] .dropdown-item:hover i,
        [data-app-management-menu] .dropdown-item:focus i,
        [data-app-management-menu] .dropdown-item.active i {
            color: #0b57d0;
        }
        @media (max-width: 991.98px) {
            [data-risk-menu] {
                width: auto;
                min-width: 12rem;
                padding: 0;
            }
            .risk-menu-columns {
                display: block;
            }
            .risk-menu-columns::before {
                display: none;
            }
            .risk-menu-column {
                width: auto;
                min-width: 0;
                padding: 0;
            }
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
                        @php($viewer = auth()->user())
                        @php($viewerDisplayName = $viewer->username ?: $viewer->name)
                        @php($viewerSubtitle = $viewer->isSuperadmin() ? 'superadmin' : ($viewer->isComplianceDivision() ? 'Compliance' : \App\Support\RiskOwnerAccess::displayLabel($viewer->division, 'User')))
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
                            <span class="avatar avatar-sm {{ $viewer->isSuperadmin() ? 'avatar-superadmin' : '' }}">
                                {{ strtoupper(substr($viewerDisplayName, 0, 1)) }}
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div>{{ $viewerDisplayName }}</div>
                                <div class="mt-1 small text-secondary">{{ $viewerSubtitle }}</div>
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
                            @php($isAppManagementActive = request()->routeIs('parameters.*') || request()->routeIs('users.*') || request()->routeIs('risk-appetites.*'))
                            <li class="nav-item {{ request()->routeIs('risks.monitoring') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('risks.monitoring') }}">
                                    <span class="nav-link-icon"><i class="fas fa-home"></i></span>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            
                            {{-- Framework Explorer Menu --}}
                            <li class="nav-item dropdown {{ request()->routeIs('frameworks.*') ? 'active' : '' }}" data-framework-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-framework-toggle role="button" aria-expanded="false">
                                    <span class="nav-link-icon"><i class="fas fa-layer-group"></i></span>
                                    <span>Frameworks</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.iso27001') ? 'active' : '' }}" href="{{ route('frameworks.iso27001') }}">
                                        ISO 27001
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.nist') ? 'active' : '' }}" href="{{ route('frameworks.nist') }}">
                                        NIST CSF 2.0
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.seojk') ? 'active' : '' }}" href="{{ route('frameworks.seojk') }}">
                                        SEOJK 29 SEOJK.03 2022
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.cis') ? 'active' : '' }}" href="{{ route('frameworks.cis') }}">
                                        Panduan Resiliensi OJK
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.pbi') ? 'active' : '' }}" href="{{ route('frameworks.pbi') }}">
                                        PBI 02/2024
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item dropdown {{ $isAppManagementActive ? 'active show' : '' }}" data-app-management-nav>
                                <a
                                    class="nav-link nav-link-main dropdown-toggle"
                                    href="#"
                                    data-bs-toggle="dropdown"
                                    data-toggle="dropdown"
                                    data-app-management-toggle
                                    role="button"
                                    aria-expanded="{{ $isAppManagementActive ? 'true' : 'false' }}"
                                >
                                    <span class="nav-link-icon"><i class="fas fa-cogs"></i></span>
                                    <span>App Management</span>
                                </a>
                                <div class="dropdown-menu" data-app-management-menu>
                                    <a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                        <i class="fas fa-users-cog"></i>
                                        <span>User Management</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('parameters.*') ? 'active' : '' }}" href="{{ route('parameters.index') }}">
                                        <i class="fas fa-sliders-h"></i>
                                        <span>Parameter</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('risk-appetites.*') ? 'active' : '' }}" href="{{ route('risk-appetites.index') }}">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Corporate Risk Appetite</span>
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item {{ request()->routeIs('risk-status-approvals.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('risk-status-approvals.index') }}">
                                    <span class="nav-link-icon"><i class="fas fa-tasks"></i></span>
                                    <span>Status Approvals</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('risk-alerts.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('risk-alerts.index') }}">
                                    <span class="nav-link-icon"><i class="far fa-bell"></i></span>
                                    <span>Alerts</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('audit-logs.index') }}">
                                    <span class="nav-link-icon"><i class="far fa-file-alt"></i></span>
                                    <span>Audit Trail</span>
                                </a>
                            </li>
                            <li class="nav-item {{ request()->routeIs('threat-intelligence.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('threat-intelligence.index') }}">
                                    <span class="nav-link-icon"><i class="fas fa-globe"></i></span>
                                    <span>Threat Intel</span>
                                </a>
                            </li>
                        @elseif(auth()->user()->isDivisionVerified())
                            @php($riskMenuItems = [
                                ['label' => 'Risk Monitoring', 'href' => route('risks.monitoring'), 'active' => request()->routeIs('risks.monitoring')],
                                ['label' => 'Risk Register', 'href' => route('risks.index'), 'active' => request()->routeIs('risks.*') && !request()->routeIs('risks.monitoring') && !request()->routeIs('risks.dashboard-summary')],
                                ['label' => 'Risk Trend Analysis', 'href' => route('trends.analysis'), 'active' => request()->routeIs('trends.analysis')],
                                ['label' => 'Key Risk Indicators', 'href' => route('kris.index'), 'active' => request()->routeIs('kris.*')],
                                ['label' => 'Mitigation Kanban', 'href' => route('treatments.kanban'), 'active' => request()->routeIs('treatments.kanban')],
                                ['label' => 'Owner Workload', 'href' => route('owners.workload'), 'active' => request()->routeIs('owners.workload')],
                                ['label' => 'Mitigation Playbooks', 'href' => route('treatment-templates.index'), 'active' => request()->routeIs('treatment-templates.*')],
                                ['label' => 'Incident Management', 'href' => route('incidents.index'), 'active' => request()->routeIs('incidents.*')],
                                ['label' => 'Business Continuity (BCM)', 'href' => route('bcm.index'), 'active' => request()->routeIs('bcm.*')],
                                ['label' => 'Corporate Risk Appetite', 'href' => route('risk-appetites.index'), 'active' => request()->routeIs('risk-appetites.*')],
                            ])
                                <li
                                class="nav-item dropdown {{
                                    (request()->routeIs('risks.monitoring')
                                    || (request()->routeIs('risks.*') && !request()->routeIs('risks.dashboard-summary'))
                                    || request()->routeIs('trends.analysis')
                                    || request()->routeIs('kris.*')
                                    || request()->routeIs('treatments.kanban')
                                    || request()->routeIs('owners.workload')
                                    || request()->routeIs('treatment-templates.*')
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
                                    <span class="nav-link-icon"><i class="fas fa-shield-alt"></i></span>
                                    <span>Risk</span>
                                </a>
                                <div class="dropdown-menu" data-risk-menu>
                                    <div class="risk-menu-columns">
                                        @foreach(array_chunk($riskMenuItems, 5) as $riskMenuChunk)
                                            <div class="risk-menu-column">
                                                @foreach($riskMenuChunk as $riskMenuItem)
                                                    <a class="dropdown-item {{ $riskMenuItem['active'] ? 'active' : '' }}" href="{{ $riskMenuItem['href'] }}">
                                                        {{ $riskMenuItem['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                            {{-- Framework Explorer Menu --}}
                            <li class="nav-item dropdown {{ request()->routeIs('frameworks.*') ? 'active' : '' }}" data-framework-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-framework-toggle role="button" aria-expanded="false">
                                    <span class="nav-link-icon"><i class="fas fa-layer-group"></i></span>
                                    <span>Frameworks</span>
                                </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.iso27001') ? 'active' : '' }}" href="{{ route('frameworks.iso27001') }}">
                                        ISO 27001
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.nist') ? 'active' : '' }}" href="{{ route('frameworks.nist') }}">
                                        NIST CSF 2.0
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.seojk') ? 'active' : '' }}" href="{{ route('frameworks.seojk') }}">
                                        SEOJK 29 SEOJK.03 2022
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.cis') ? 'active' : '' }}" href="{{ route('frameworks.cis') }}">
                                        Panduan Resiliensi OJK
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.pbi') ? 'active' : '' }}" href="{{ route('frameworks.pbi') }}">
                                        PBI 02/2024
                                    </a>
                                </div>
                            </li>

                            <li class="nav-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('vendors.index') }}">
                                    <span class="nav-link-icon"><i class="fas fa-handshake"></i></span>
                                    <span>Third-Party Vendors</span>
                                </a>
                            </li>

                            @if(auth()->user()->isComplianceDivision())
                                <li class="nav-item {{ request()->routeIs('audit.placeholder') ? 'active' : '' }}">
                                    <a class="nav-link nav-link-main" href="{{ route('audit.placeholder') }}">
                                        <span class="nav-link-icon"><i class="fas fa-clipboard-check"></i></span>
                                        <span>Audit</span>
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item {{ request()->routeIs('threat-intelligence.*') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('threat-intelligence.index') }}">
                                    <span class="nav-link-icon"><i class="fas fa-globe"></i></span>
                                    <span>Threat Intel</span>
                                </a>
                            </li>
                            @if(auth()->user()->isComplianceDivision() && config('features.show_ojk_mitigation', false))
                                <li class="nav-item {{ request()->routeIs('ojk-mitigation.*') ? 'active' : '' }}">
                                    <a class="nav-link nav-link-main" href="{{ route('ojk-mitigation.index') }}">
                                        <span class="nav-link-icon"><i class="fas fa-file-invoice"></i></span>
                                        <span>Mitigasi OJK</span>
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item {{ request()->routeIs('howto') ? 'active' : '' }}">
                                <a class="nav-link nav-link-main" href="{{ route('howto') }}">
                                    <span class="nav-link-icon"><i class="fas fa-book"></i></span>
                                    <span>User Guide</span>
                                </a>
                            </li>
                            @if(auth()->user()->isComplianceDivision())
                                <li class="nav-item dropdown {{ request()->routeIs('compliance.*') ? 'active' : '' }}" data-compliance-nav>
                                    <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-compliance-toggle role="button" aria-expanded="false">
                                        <span class="nav-link-icon"><i class="fas fa-balance-scale"></i></span>
                                        <span>Compliance</span>
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
                                <a class="nav-link nav-link-main" href="{{ route('account.pending') }}">
                                    <span class="nav-link-icon"><i class="fas fa-hourglass-half"></i></span>
                                    <span>Pending Approval</span>
                                </a>
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

    setupManagedNavbarDropdown('[data-app-management-nav]', '[data-app-management-toggle]');
    setupManagedNavbarDropdown('[data-compliance-nav]', '[data-compliance-toggle]');
    setupManagedNavbarDropdown('[data-risk-nav]', '[data-risk-toggle]');
    setupManagedNavbarDropdown('[data-framework-nav]', '[data-framework-toggle]');
</script>

@yield('js')
@stack('scripts')
@stack('scripts_body')
@stack('script_ckeditor')
</body>
</html>

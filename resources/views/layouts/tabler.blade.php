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

        /* Application sidebar */
        :root {
            --app-sidebar-width: 272px;
            --app-sidebar-collapsed-width: 76px;
            --app-sidebar-bg: #fff;
            --app-sidebar-border: #e5e7eb;
            --app-accent: #0b57d0;
        }
        .app-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1040;
            display: block;
            width: var(--app-sidebar-width);
            height: 100vh;
            padding: 0;
            overflow-y: auto;
            background: var(--app-sidebar-bg);
            border-right: 1px solid var(--app-sidebar-border);
            box-shadow: 2px 0 14px rgba(15, 23, 42, 0.04);
        }
        .app-sidebar .container-xl {
            display: flex;
            flex-direction: column;
            min-height: 100%;
            padding: 1rem 0.75rem;
        }
        .app-sidebar .navbar-brand {
            width: 100%;
            margin: 0 0 1.25rem;
            padding: 0 0.75rem;
            font-size: 1.05rem;
        }
        .app-sidebar .navbar-brand a {
            color: #182433;
            text-decoration: none;
        }
        .app-brand-short {
            display: none;
        }
        .sidebar-mini-fallback {
            display: none;
        }
        .sidebar-mini-svg {
            display: none;
        }
        .sidebar-collapse-toggle {
            position: absolute;
            top: 0.85rem;
            right: 0.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            padding: 0;
            color: #5f6b7a;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 0.4rem;
        }
        .sidebar-collapse-toggle:hover,
        .sidebar-collapse-toggle:focus {
            color: var(--app-accent);
            background: #eaf2ff;
            border-color: #d7e6ff;
        }
        .app-sidebar .navbar-collapse {
            display: block !important;
            width: 100%;
            flex: 1 1 auto;
        }
        .app-sidebar .navbar-nav {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 0.2rem;
        }
        .app-sidebar .navbar-nav > .nav-item {
            width: 100%;
        }
        .app-sidebar .navbar-nav > .nav-item > .nav-link-main,
        .app-sidebar .navbar .navbar-nav .nav-link.nav-link-main {
            display: flex !important;
            flex-direction: row !important;
            justify-content: flex-start !important;
            width: 100%;
            min-width: 0;
            padding: 0.7rem 0.75rem !important;
            gap: 0.75rem !important;
            border: 0 !important;
            border-radius: 0.45rem;
            color: #5f6b7a;
            text-align: left;
        }
        .app-sidebar .navbar-nav > .nav-item > .nav-link-main > span:not(.nav-link-icon) {
            color: inherit;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            text-align: left;
        }
        .app-sidebar .navbar-nav > .nav-item > .nav-link-main .nav-link-icon {
            width: 1.25rem !important;
        }
        .app-sidebar .navbar-nav > .nav-item > .nav-link-main .nav-link-icon i {
            color: inherit;
            font-size: 1rem !important;
        }
        .app-sidebar .navbar-nav > .nav-item.active > .nav-link-main,
        .app-sidebar .navbar-nav > .nav-item.show > .nav-link-main,
        .app-sidebar .navbar .navbar-nav .nav-link.nav-link-main:hover,
        .app-sidebar .navbar .navbar-nav .nav-link.nav-link-main:focus {
            background: #eaf2ff !important;
            color: var(--app-accent) !important;
            font-weight: 600;
        }
        .app-sidebar .navbar-nav > .nav-item > .nav-link-main.dropdown-toggle::after {
            display: block !important;
            margin-left: auto;
        }
        /* Keep sidebar submenus in the navigation flow (accordion), not as flyout popups. */
        .app-sidebar .navbar-collapse .nav-item.dropdown > .dropdown-menu {
            position: static !important;
            float: none;
            width: 100%;
            min-width: 0;
            margin: 0.2rem 0;
            padding: 0.2rem 0 0.35rem 2.75rem;
            border-color: var(--app-sidebar-border);
            border: 0;
            box-shadow: none;
            background: transparent;
        }
        .app-sidebar .navbar-collapse .nav-item.dropdown > .dropdown-menu.show {
            display: block !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.48rem 0.65rem;
            border-radius: 0.35rem;
            color: #5f6b7a;
            font-size: 0.8125rem;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover,
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus,
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active {
            background: #eaf2ff !important;
            color: var(--app-accent) !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item i {
            width: 1rem;
            color: currentColor;
            text-align: center;
        }
        .app-sidebar .navbar-collapse [data-risk-menu] {
            width: 100%;
        }
        .app-sidebar .risk-menu-columns {
            display: block;
            width: 100%;
        }
        .app-sidebar .risk-menu-columns::before {
            display: none;
        }
        .app-sidebar .risk-menu-column {
            width: 100%;
            padding: 0;
        }
        .app-sidebar .navbar-nav.flex-row {
            order: 3;
            flex: 0 0 auto;
            margin-top: auto;
            padding: 1rem 0.75rem 0.25rem;
            border-top: 1px solid var(--app-sidebar-border);
        }
        .app-sidebar .navbar-nav.flex-row .nav-item {
            width: 100%;
        }
        .app-sidebar .navbar-nav.flex-row .nav-link {
            justify-content: flex-start;
            gap: 0.65rem;
        }
        .app-sidebar .navbar-toggler {
            display: none;
        }
        .page-wrapper {
            min-height: 100vh;
            margin-left: var(--app-sidebar-width);
            padding-top: 0 !important;
        }
        .page-wrapper > .page-header {
            padding-top: 0;
        }
        .page-wrapper > .page-body {
            padding-top: 0;
        }
        .page-wrapper .container-xl {
            max-width: 1440px;
        }
        body.sidebar-collapsed .app-sidebar {
            width: var(--app-sidebar-collapsed-width);
            overflow-x: visible;
        }
        body.sidebar-collapsed .page-wrapper {
            margin-left: var(--app-sidebar-collapsed-width);
        }
        body.sidebar-collapsed .app-sidebar .container-xl {
            padding-right: 0.5rem;
            padding-left: 0.5rem;
        }
        body.sidebar-collapsed .app-sidebar .navbar-brand {
            padding: 0 !important;
            text-align: center;
        }
        body.sidebar-collapsed .sidebar-collapse-toggle {
            top: 3rem;
            right: auto;
            left: 50%;
            z-index: 2;
            width: 2rem;
            height: 1.75rem;
            transform: translateX(-50%);
            background: #f8fafc;
            border-color: var(--app-sidebar-border);
            border-radius: 0.4rem;
            box-shadow: none;
        }
        body.sidebar-collapsed .app-brand-full,
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main > span:not(.nav-link-icon),
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main.dropdown-toggle::after,
        body.sidebar-collapsed .app-sidebar .navbar-nav.flex-row .d-xl-block {
            display: none !important;
        }
        body.sidebar-collapsed .app-brand-short {
            display: inline;
        }
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main {
            justify-content: center !important;
            padding-right: 0.45rem !important;
            padding-left: 0.45rem !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse {
            padding-top: 1.75rem;
        }
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main .nav-link-icon {
            width: auto !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-nav.flex-row {
            padding-right: 0;
            padding-left: 0;
        }
        body.sidebar-collapsed .sidebar-collapse-toggle i {
            transform: rotate(180deg);
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .nav-item.dropdown > .dropdown-menu.show {
            display: block !important;
            width: 100%;
            min-width: 0;
            margin: 0.15rem 0 0.3rem;
            padding: 0.1rem 0;
            border: 0;
            background: transparent;
            box-shadow: none;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item {
            display: flex !important;
            justify-content: center;
            min-height: 1.8rem;
            padding: 0.35rem !important;
            color: #9aa8b8;
            background: transparent !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item::before {
            display: none !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item span {
            display: none;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item .sidebar-mini-fallback {
            display: inline-block !important;
            width: 1rem;
            color: #718096;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item i {
            display: inline-block !important;
            width: 1rem !important;
            font-family: "Font Awesome 5 Free" !important;
            font-size: 0.875rem !important;
            font-style: normal;
            font-weight: 900 !important;
            line-height: 1;
            color: currentColor !important;
            visibility: visible !important;
            opacity: 1 !important;
            text-align: center;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item i::before {
            display: block !important;
            color: #718096 !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover i::before,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus i::before,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active i::before {
            color: var(--app-accent) !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item > i {
            display: none !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item .sidebar-mini-svg {
            display: block !important;
            width: 0.95rem;
            height: 0.95rem;
            color: #718096;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover .sidebar-mini-fallback,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus .sidebar-mini-fallback,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active .sidebar-mini-fallback {
            color: var(--app-accent);
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active {
            color: var(--app-accent) !important;
            background: #eaf2ff !important;
            border-radius: 0.35rem;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover .sidebar-mini-svg,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus .sidebar-mini-svg,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active .sidebar-mini-svg {
            color: var(--app-accent);
        }
        @media (max-width: 767.98px) {
            body.sidebar-collapsed .app-sidebar {
                width: 100%;
            }
            body.sidebar-collapsed .page-wrapper {
                margin-left: 0;
            }
            body.sidebar-collapsed .app-brand-full,
            body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main > span:not(.nav-link-icon) {
                display: inline !important;
            }
            body.sidebar-collapsed .app-brand-short {
                display: none;
            }
            body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main {
                justify-content: flex-start !important;
                padding-right: 0.75rem !important;
                padding-left: 0.75rem !important;
            }
            body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu {
                display: none;
            }
            body.sidebar-collapsed .app-sidebar .navbar-collapse .nav-item.dropdown > .dropdown-menu.show {
                display: block !important;
            }
            .app-sidebar {
                position: sticky;
                top: 0;
                width: 100%;
                height: auto;
                max-height: 100vh;
                overflow: visible;
                border-right: 0;
                border-bottom: 1px solid var(--app-sidebar-border);
            }
            .app-sidebar .container-xl {
                display: grid;
                grid-template-columns: auto 1fr auto;
                min-height: auto;
                padding: 0.65rem 0.75rem;
                align-items: center;
            }
            .app-sidebar .navbar-toggler {
                display: inline-flex;
                grid-column: 1;
                grid-row: 1;
                margin-right: 0.75rem;
            }
            .sidebar-collapse-toggle {
                display: none;
            }
            .app-sidebar .navbar-brand {
                grid-column: 2;
                grid-row: 1;
                margin: 0;
                padding: 0;
                font-size: 0.95rem;
            }
            .app-sidebar .navbar-nav.flex-row {
                grid-column: 3;
                grid-row: 1;
                margin: 0;
                padding: 0;
                border: 0;
            }
            .app-sidebar .navbar-collapse {
                display: none !important;
                grid-column: 1 / -1;
                grid-row: 2;
                max-height: calc(100vh - 60px);
                margin: 0.75rem -0.75rem -0.65rem;
                padding: 0.5rem 0.75rem 0.75rem;
                overflow-y: auto;
                border-top: 1px solid var(--app-sidebar-border);
            }
            .app-sidebar .navbar-collapse.show {
                display: block !important;
            }
            .app-sidebar .navbar-collapse .dropdown-menu,
            .app-sidebar .navbar-collapse [data-risk-menu] {
                width: 100%;
                min-width: 0;
            }
            .page-wrapper {
                min-height: calc(100vh - 60px);
                margin-left: 0;
            }
        }
    </style>

    @yield('css')
    @yield('styles')
    @stack('styles')
</head>
<body>
<div class="page">
    <header class="navbar navbar-expand-md d-print-none app-sidebar" aria-label="Main navigation">
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
                <a href="{{ route('risks.index') }}">
                    <span class="app-brand-full">Enterprise RMS</span>
                    <span class="app-brand-short">ERMS</span>
                </a>
            </h1>
            <button class="sidebar-collapse-toggle" type="button" data-sidebar-toggle aria-label="Collapse sidebar" aria-expanded="true" title="Collapse sidebar">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
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
                            @php($isAppManagementActive = request()->routeIs('parameters.*') || request()->routeIs('users.*') || request()->routeIs('risk-appetites.*') || request()->routeIs('access-control.*') || request()->routeIs('data-retention.*') || request()->routeIs('reports.scheduled.*'))
                            <li class="nav-item dropdown {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'active show' : '' }}" data-dashboard-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-dashboard-toggle role="button" aria-expanded="{{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'true' : 'false' }}">
                                    <span class="nav-link-icon"><i class="fas fa-chart-pie"></i></span>
                                    <span>Dashboard</span>
                                </a>
                                <div class="dropdown-menu {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'show' : '' }}" data-dashboard-menu>
                                    <a class="dropdown-item {{ (request()->query('view') === 'strategic' || (request()->routeIs('dashboard') && !request()->query('view'))) ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'strategic']) }}">
                                        <i class="fas fa-chart-line me-2 text-primary"></i>
                                        <span>1. Strategic (Board View)</span>
                                    </a>
                                    <a class="dropdown-item {{ (request()->routeIs('risks.monitoring') || request()->routeIs('management.dashboard') || request()->query('view') === 'operational' || request()->query('view') === 'monitoring') ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'operational']) }}">
                                        <i class="fas fa-tachometer-alt me-2 text-success"></i>
                                        <span>2. Operational (Action View)</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->query('view') === 'analytical' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'analytical']) }}">
                                        <i class="fas fa-th-large me-2 text-warning"></i>
                                        <span>3. Analytical (Matrix &amp; Intelligence)</span>
                                    </a>
                                </div>
                            </li>
                            
                            {{-- Framework Explorer Menu --}}
                            <li class="nav-item dropdown {{ (request()->routeIs('frameworks.*') || request()->routeIs('control-framework-mappings.*')) ? 'active show' : '' }}" data-framework-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-framework-toggle role="button" aria-expanded="{{ (request()->routeIs('frameworks.*') || request()->routeIs('control-framework-mappings.*')) ? 'true' : 'false' }}">
                                    <span class="nav-link-icon"><i class="fas fa-layer-group"></i></span>
                                    <span>Frameworks</span>
                                </a>
                                <div class="dropdown-menu {{ (request()->routeIs('frameworks.*') || request()->routeIs('control-framework-mappings.*')) ? 'show' : '' }}">
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.iso27001') ? 'active' : '' }}" href="{{ route('frameworks.iso27001') }}">
                                        <i class="fas fa-shield-alt"></i><span>ISO 27001</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.nist') ? 'active' : '' }}" href="{{ route('frameworks.nist') }}">
                                        <i class="fas fa-sitemap"></i><span>NIST CSF 2.0</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.seojk') ? 'active' : '' }}" href="{{ route('frameworks.seojk') }}">
                                        <i class="fas fa-file-contract"></i><span>SEOJK 29 SEOJK.03 2022</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.cis') ? 'active' : '' }}" href="{{ route('frameworks.cis') }}">
                                        <i class="fas fa-landmark"></i><span>Panduan Resiliensi OJK</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.pbi') ? 'active' : '' }}" href="{{ route('frameworks.pbi') }}">
                                        <i class="fas fa-gavel"></i><span>PBI 02/2024</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item {{ request()->routeIs('control-framework-mappings.*') ? 'active' : '' }}" href="{{ route('control-framework-mappings.index') }}">
                                        <i class="fas fa-project-diagram"></i><span>Control Framework Mappings</span>
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
                                <div class="dropdown-menu {{ $isAppManagementActive ? 'show' : '' }}" data-app-management-menu>
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
                                    <a class="dropdown-item {{ request()->routeIs('access-control.*') ? 'active' : '' }}" href="{{ route('access-control.index') }}">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Role &amp; Access Control</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('data-retention.*') ? 'active' : '' }}" href="{{ route('data-retention.index') }}">
                                        <i class="fas fa-trash-restore"></i>
                                        <span>Data Retention &amp; Purge</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('reports.scheduled.*') ? 'active' : '' }}" href="{{ route('reports.scheduled.index') }}">
                                        <i class="fas fa-clock"></i>
                                        <span>Scheduled Report Engine</span>
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
                            <li class="nav-item dropdown {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'active show' : '' }}" data-dashboard-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-dashboard-toggle role="button" aria-expanded="{{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'true' : 'false' }}">
                                    <span class="nav-link-icon"><i class="fas fa-chart-pie"></i></span>
                                    <span>Dashboard</span>
                                </a>
                                <div class="dropdown-menu {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'show' : '' }}" data-dashboard-menu>
                                    <a class="dropdown-item {{ (request()->query('view') === 'strategic' || (request()->routeIs('dashboard') && !request()->query('view'))) ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'strategic']) }}">
                                        <i class="fas fa-chart-line me-2 text-primary"></i>
                                        <span>1. Strategic (Board View)</span>
                                    </a>
                                    <a class="dropdown-item {{ (request()->routeIs('risks.monitoring') || request()->routeIs('management.dashboard') || request()->query('view') === 'operational' || request()->query('view') === 'monitoring') ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'operational']) }}">
                                        <i class="fas fa-tachometer-alt me-2 text-success"></i>
                                        <span>2. Operational (Action View)</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->query('view') === 'analytical' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'analytical']) }}">
                                        <i class="fas fa-th-large me-2 text-warning"></i>
                                        <span>3. Analytical (Matrix &amp; Intelligence)</span>
                                    </a>
                                </div>
                            </li>
                            @php($isRiskMenuActive = (request()->routeIs('risks.*') && !request()->routeIs('risks.monitoring') && !request()->routeIs('risks.dashboard-summary'))
                                || request()->routeIs('trends.analysis')
                                || request()->routeIs('kris.*')
                                || request()->routeIs('treatments.kanban')
                                || request()->routeIs('owners.workload')
                                || request()->routeIs('treatment-templates.*')
                                || request()->routeIs('incidents.*')
                                || request()->routeIs('enterprise-risk-profile.*')
                                || request()->routeIs('strategic-risk-reviews.*')
                                || request()->routeIs('operational-resilience.*'))
                            @php($riskMenuItems = [
                                ['label' => 'Risk Register', 'icon' => 'fas fa-clipboard-list', 'mini' => '≡', 'href' => route('risks.index'), 'active' => request()->routeIs('risks.*') && !request()->routeIs('risks.monitoring') && !request()->routeIs('risks.dashboard-summary')],
                                ['label' => 'Risk Trend Analysis', 'icon' => 'fas fa-chart-area', 'mini' => '⌁', 'href' => route('trends.analysis'), 'active' => request()->routeIs('trends.analysis')],
                                ['label' => 'Key Risk Indicators', 'icon' => 'fas fa-tachometer-alt', 'mini' => '◷', 'href' => route('kris.index'), 'active' => request()->routeIs('kris.*')],
                                ['label' => 'Mitigation Kanban', 'icon' => 'fas fa-columns', 'mini' => '▦', 'href' => route('treatments.kanban'), 'active' => request()->routeIs('treatments.kanban')],
                                ['label' => 'Owner Workload', 'icon' => 'fas fa-users', 'mini' => '♙', 'href' => route('owners.workload'), 'active' => request()->routeIs('owners.workload')],
                                ['label' => 'Mitigation Playbooks', 'icon' => 'fas fa-book-open', 'mini' => '▤', 'href' => route('treatment-templates.index'), 'active' => request()->routeIs('treatment-templates.*')],
                                ['label' => 'Incident Management', 'icon' => 'fas fa-exclamation-triangle', 'mini' => '!', 'href' => route('incidents.index'), 'active' => request()->routeIs('incidents.*')],
                                ['label' => 'Business Continuity (BCM)', 'icon' => 'fas fa-life-ring', 'mini' => '⊕', 'href' => route('bcm.index'), 'active' => request()->routeIs('bcm.*')],
                                ['label' => 'Corporate Risk Appetite', 'icon' => 'fas fa-bullseye', 'mini' => '◎', 'href' => route('risk-appetites.index'), 'active' => request()->routeIs('risk-appetites.*')],
                                ['label' => 'Risk Profile & Decisions', 'icon' => 'fas fa-gavel', 'mini' => '⚖', 'href' => route('enterprise-risk-profile.index'), 'active' => request()->routeIs('enterprise-risk-profile.*')],
                                ['label' => 'Strategic Reviews & Scenarios', 'icon' => 'fas fa-chess', 'mini' => '♟', 'href' => route('strategic-risk-reviews.index'), 'active' => request()->routeIs('strategic-risk-reviews.*')],
                                ['label' => 'Operational Resilience (DR)', 'icon' => 'fas fa-database', 'mini' => '⛁', 'href' => route('operational-resilience.index'), 'active' => request()->routeIs('operational-resilience.*')],
                            ])
                                <li
                                class="nav-item dropdown {{ $isRiskMenuActive ? 'active show' : '' }}"
                                data-risk-nav
                            >
                                <a
                                    class="nav-link nav-link-main dropdown-toggle"
                                    href="#"
                                    data-bs-toggle="dropdown"
                                    data-toggle="dropdown"
                                    data-risk-toggle
                                    role="button"
                                    aria-expanded="{{ $isRiskMenuActive ? 'true' : 'false' }}"
                                >
                                    <span class="nav-link-icon"><i class="fas fa-shield-alt"></i></span>
                                    <span>Risk</span>
                                </a>
                                <div class="dropdown-menu {{ $isRiskMenuActive ? 'show' : '' }}" data-risk-menu>
                                    <div class="risk-menu-columns">
                                        @foreach(array_chunk($riskMenuItems, 6) as $riskMenuChunk)
                                            <div class="risk-menu-column">
                                                @foreach($riskMenuChunk as $riskMenuItem)
                                                    <a class="dropdown-item {{ $riskMenuItem['active'] ? 'active' : '' }}" href="{{ $riskMenuItem['href'] }}" data-mini-icon="{{ $riskMenuItem['mini'] }}">
                                                        <svg class="sidebar-mini-svg" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"></circle><path d="m10 8 4 4-4 4"></path></svg><span class="sidebar-mini-fallback" aria-hidden="true">{{ $riskMenuItem['mini'] }}</span><i class="{{ $riskMenuItem['icon'] }}"></i><span>{{ $riskMenuItem['label'] }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                            {{-- Framework Explorer Menu --}}
                            <li class="nav-item dropdown {{ (request()->routeIs('frameworks.*') || request()->routeIs('control-framework-mappings.*')) ? 'active show' : '' }}" data-framework-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-framework-toggle role="button" aria-expanded="{{ (request()->routeIs('frameworks.*') || request()->routeIs('control-framework-mappings.*')) ? 'true' : 'false' }}">
                                    <span class="nav-link-icon"><i class="fas fa-layer-group"></i></span>
                                    <span>Frameworks</span>
                                </a>
                                <div class="dropdown-menu {{ (request()->routeIs('frameworks.*') || request()->routeIs('control-framework-mappings.*')) ? 'show' : '' }}">
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.iso27001') ? 'active' : '' }}" href="{{ route('frameworks.iso27001') }}">
                                        <i class="fas fa-shield-alt"></i><span>ISO 27001</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.nist') ? 'active' : '' }}" href="{{ route('frameworks.nist') }}">
                                        <i class="fas fa-sitemap"></i><span>NIST CSF 2.0</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.seojk') ? 'active' : '' }}" href="{{ route('frameworks.seojk') }}">
                                        <i class="fas fa-file-contract"></i><span>SEOJK 29 SEOJK.03 2022</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.cis') ? 'active' : '' }}" href="{{ route('frameworks.cis') }}">
                                        <i class="fas fa-landmark"></i><span>Panduan Resiliensi OJK</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->routeIs('frameworks.pbi') ? 'active' : '' }}" href="{{ route('frameworks.pbi') }}">
                                        <i class="fas fa-gavel"></i><span>PBI 02/2024</span>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item {{ request()->routeIs('control-framework-mappings.*') ? 'active' : '' }}" href="{{ route('control-framework-mappings.index') }}">
                                        <i class="fas fa-project-diagram"></i><span>Control Framework Mappings</span>
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
                                @php($isComplianceMenuActive = request()->routeIs('compliance.*'))
                                <li class="nav-item dropdown {{ $isComplianceMenuActive ? 'active show' : '' }}" data-compliance-nav>
                                    <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-compliance-toggle role="button" aria-expanded="{{ $isComplianceMenuActive ? 'true' : 'false' }}">
                                        <span class="nav-link-icon"><i class="fas fa-balance-scale"></i></span>
                                        <span>Compliance</span>
                                    </a>
                                    <div class="dropdown-menu {{ $isComplianceMenuActive ? 'show' : '' }}" data-compliance-menu>
                                        <a class="dropdown-item {{ request()->routeIs('compliance.internal-audit*') ? 'active' : '' }}" href="{{ route('compliance.internal-audit') }}">
                                            <i class="fas fa-clipboard-check"></i><span>Internal Audit</span>
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('compliance.risk-management*') ? 'active' : '' }}" href="{{ route('compliance.risk-management') }}">
                                            <i class="fas fa-shield-alt"></i><span>Risk Management</span>
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('compliance.compliance*') ? 'active' : '' }}" href="{{ route('compliance.compliance') }}">
                                            <i class="fas fa-balance-scale"></i><span>Governance &amp; Compliance</span>
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

    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebarStorageKey = 'enterprise-rms-sidebar-collapsed';

    function setSidebarCollapsed(collapsed) {
        document.body.classList.toggle('sidebar-collapsed', collapsed);

        if (sidebarToggle) {
            sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
            sidebarToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            sidebarToggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }
    }

    try {
        setSidebarCollapsed(window.localStorage.getItem(sidebarStorageKey) === 'true');
    } catch (error) {
        setSidebarCollapsed(false);
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            const collapsed = !document.body.classList.contains('sidebar-collapsed');
            setSidebarCollapsed(collapsed);

            try {
                window.localStorage.setItem(sidebarStorageKey, String(collapsed));
            } catch (error) {
                // The sidebar remains usable when browser storage is unavailable.
            }
        });
    }

    // Sidebar submenu accordion. Capture the click before Bootstrap tries to create a flyout.
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest(
            '.app-sidebar .navbar-collapse [data-dashboard-toggle], ' +
            '.app-sidebar .navbar-collapse [data-risk-toggle], ' +
            '.app-sidebar .navbar-collapse [data-framework-toggle], ' +
            '.app-sidebar .navbar-collapse [data-compliance-toggle], ' +
            '.app-sidebar .navbar-collapse [data-app-management-toggle]'
        );

        if (!toggle) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        if (document.body.classList.contains('sidebar-collapsed')) {
            setSidebarCollapsed(false);
            try {
                window.localStorage.setItem(sidebarStorageKey, 'false');
            } catch (error) {
                // The sidebar still expands without persistent browser storage.
            }
            return;
        }

        const item = toggle.closest('.nav-item.dropdown');
        const menu = item ? item.querySelector(':scope > .dropdown-menu') : null;
        if (!item || !menu) {
            return;
        }

        const willOpen = !menu.classList.contains('show');

        item.parentElement.querySelectorAll(':scope > .nav-item.dropdown > .dropdown-menu.show').forEach(function (openMenu) {
            openMenu.classList.remove('show');
            openMenu.parentElement.classList.remove('show');
            const openToggle = openMenu.parentElement.querySelector(':scope > [data-bs-toggle="dropdown"]');
            if (openToggle) {
                openToggle.setAttribute('aria-expanded', 'false');
            }
        });

        if (willOpen) {
            menu.classList.add('show');
            item.classList.add('show');
            toggle.setAttribute('aria-expanded', 'true');
        }
    }, true);
</script>

@yield('js')
@yield('scripts')
@stack('scripts')
@stack('scripts_body')
@stack('script_ckeditor')
</body>
</html>

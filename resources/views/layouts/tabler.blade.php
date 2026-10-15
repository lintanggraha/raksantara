<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Raksantara'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('raksantara-mark.svg') }}">

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
            padding: 0 2.5rem 0 0.75rem;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
        }
        .app-sidebar .navbar-brand a {
            color: #182433;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            white-space: nowrap;
        }
        .app-brand-mark {
            flex: 0 0 auto;
            display: block;
            filter: drop-shadow(0 1px 2px rgba(30, 58, 138, 0.25));
        }
        .app-brand-full {
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: -0.01em;
        }
        .app-brand-short {
            display: none;
        }
        .sidebar-collapse-toggle {
            position: absolute;
            top: 0.85rem;
            right: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 0;
            color: #94a3b8;
            background: transparent;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            transition: color 0.15s ease, background-color 0.15s ease;
            outline: none !important;
        }
        .sidebar-collapse-toggle:hover {
            color: #0f172a;
            background: #f1f5f9;
        }
        .sidebar-collapse-toggle:focus-visible {
            color: #0f172a;
            box-shadow: 0 0 0 2px rgba(11, 87, 208, 0.35);
        }
        .sidebar-collapse-toggle:active {
            background: #e2e8f0;
        }
        .sidebar-collapse-toggle .sidebar-toggle-icon {
            display: block;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
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
            float: none !important;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0.15rem 0 0.25rem 0 !important;
            padding: 0.15rem 0 0.25rem 0 !important;
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .app-sidebar .navbar-collapse .nav-item.dropdown > .dropdown-menu.show {
            display: block !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: flex-start !important;
            width: 100% !important;
            min-width: 0 !important;
            padding: 0.45rem 0.75rem !important;
            gap: 0.75rem !important;
            border-radius: 0.45rem !important;
            border: 0 !important;
            margin: 0.05rem 0 !important;
            color: #5f6b7a !important;
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            line-height: 1.35 !important;
            white-space: normal !important;
            word-break: normal !important;
            overflow-wrap: break-word !important;
            text-align: left !important;
            background: transparent !important;
            transition: background-color 0.15s ease, color 0.15s ease !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover,
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus {
            background: #f1f5f9 !important;
            color: var(--app-accent, #0b57d0) !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active,
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active:hover,
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active:focus {
            background: #eaf2ff !important;
            color: var(--app-accent, #0b57d0) !important;
            font-weight: 600 !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item i {
            width: 1.25rem !important;
            min-width: 1.25rem !important;
            margin-right: 0 !important;
            font-size: 0.75rem !important;
            color: #64748b !important;
            text-align: center !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover i,
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus i,
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active i {
            color: var(--app-accent, #0b57d0) !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item span {
            display: inline !important;
            font-size: 0.8125rem !important;
            font-weight: inherit !important;
            color: inherit !important;
            text-align: left !important;
        }
        .app-sidebar .navbar-collapse .dropdown-menu .dropdown-divider {
            margin: 0.35rem 0.75rem !important;
            border-top: 1px solid var(--app-sidebar-border, #e5e7eb) !important;
            opacity: 1 !important;
        }
        .app-sidebar .navbar-collapse [data-risk-menu] {
            width: 100% !important;
            padding: 0.15rem 0 0.25rem 0 !important;
        }
        .app-sidebar .risk-menu-columns {
            display: block !important;
            width: 100% !important;
        }
        .app-sidebar .risk-menu-columns::before {
            display: none !important;
        }
        .app-sidebar .risk-menu-column {
            width: 100% !important;
            padding: 0 !important;
        }
        .app-sidebar .navbar-nav.flex-row {
            order: 3;
            flex: 0 0 auto;
            margin-top: auto;
            padding: 1rem 0.75rem 0.25rem;
            border-top: 1px solid var(--app-sidebar-border);
        }
        .app-sidebar .navbar-nav.flex-row .nav-item {
            position: relative;
            width: 100%;
        }
        .app-sidebar .navbar-nav.flex-row .nav-link {
            justify-content: flex-start;
            gap: 0.65rem;
        }
        .app-sidebar .navbar-nav.flex-row .dropup,
        .app-sidebar .navbar-nav.flex-row .nav-item.dropup {
            position: relative;
        }
        .app-sidebar .navbar-nav.flex-row .dropup .dropdown-menu,
        .app-sidebar .navbar-nav.flex-row [data-user-menu] {
            top: auto !important;
            bottom: calc(100% + 0.5rem) !important;
            left: 0.5rem !important;
            right: 0.5rem !important;
            width: calc(100% - 1rem) !important;
            min-width: 13.5rem !important;
            transform: none !important;
            margin: 0 !important;
            border-radius: 0.5rem !important;
            border: 1px solid var(--app-sidebar-border, #e5e7eb) !important;
            box-shadow: 0 -10px 25px -5px rgba(15, 23, 42, 0.12), 0 -4px 6px -4px rgba(15, 23, 42, 0.08) !important;
            background-color: #ffffff !important;
            z-index: 1060 !important;
        }
        .app-sidebar .navbar-nav.flex-row [data-user-menu].dropdown-menu-arrow::before {
            top: auto !important;
            bottom: -6px !important;
            right: 1.5rem !important;
            left: auto !important;
            border-top: 0 !important;
            border-left: 0 !important;
            border-right: 1px solid var(--app-sidebar-border, #e5e7eb) !important;
            border-bottom: 1px solid var(--app-sidebar-border, #e5e7eb) !important;
            background: #ffffff !important;
            transform: rotate(45deg) !important;
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
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto 0.75rem auto !important;
            padding: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-brand a {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: 100% !important;
            text-align: center !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body.sidebar-collapsed .sidebar-collapse-toggle {
            top: 3.25rem;
            right: auto;
            left: 50%;
            z-index: 2;
            transform: translateX(-50%);
        }
        body.sidebar-collapsed .sidebar-collapse-toggle .sidebar-toggle-icon {
            transform: rotate(180deg);
        }
        body.sidebar-collapsed .app-brand-full,
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main > span:not(.nav-link-icon),
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main.dropdown-toggle::after,
        body.sidebar-collapsed .app-sidebar .navbar-nav.flex-row .d-xl-block {
            display: none !important;
        }
        body.sidebar-collapsed .app-brand-short {
            display: block !important;
            text-align: center !important;
            width: 100% !important;
            margin: 0 auto !important;
            font-size: 1.05rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.05em !important;
            line-height: 1.2 !important;
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
        /* Collapsed Sidebar: Active top-level nav items (e.g. Risk, Dashboard) */
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item.active > .nav-link-main,
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item.show > .nav-link-main {
            background: #eaf2ff !important;
            color: var(--app-accent, #0b57d0) !important;
            border-radius: 0.45rem;
        }
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item.active > .nav-link-main .nav-link-icon,
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item.show > .nav-link-main .nav-link-icon {
            color: var(--app-accent, #0b57d0) !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-nav > .nav-item > .nav-link-main:hover {
            background: #f1f5f9 !important;
            border-radius: 0.45rem;
        }

        /* Collapsed Sidebar: Submenus accordion display - Icons only */
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu {
            display: none !important;
            position: static !important;
            float: none !important;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0.2rem 0 !important;
            padding: 0.25rem 0 !important;
            border: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .nav-item.dropdown > .dropdown-menu.show {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            background: rgba(241, 245, 249, 0.45) !important;
            border-radius: 0.5rem !important;
            padding: 0.3rem 0 !important;
            margin: 0.2rem 0 !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 2.35rem !important;
            height: 2.2rem !important;
            min-width: 2.35rem !important;
            max-width: 2.35rem !important;
            padding: 0 !important;
            margin: 0.125rem auto !important;
            border-radius: 0.4rem !important;
            text-align: center !important;
            font-size: 0 !important;
            line-height: 0 !important;
            gap: 0 !important;
            color: #64748b !important;
            background: transparent !important;
            transition: all 0.15s ease !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus {
            background: #e2e8f0 !important;
            color: var(--app-accent, #0b57d0) !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active:hover,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active:focus {
            background: #eaf2ff !important;
            color: var(--app-accent, #0b57d0) !important;
            font-weight: 600 !important;
            box-shadow: inset 0 0 0 1px rgba(11, 87, 208, 0.25) !important;
        }
        /* ALL SUBMENUS IN COLLAPSED SIDEBAR: HIDE TEXT, SHOW ONLY ICON */
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item span {
            display: none !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item i {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 1.25rem !important;
            min-width: 1.25rem !important;
            height: 1.25rem !important;
            font-size: 0.95rem !important;
            line-height: 1 !important;
            margin: 0 !important;
            margin-right: 0 !important;
            padding: 0 !important;
            color: inherit !important;
            text-align: center !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:hover i,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item:focus i,
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item.active i {
            color: var(--app-accent, #0b57d0) !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-divider {
            width: 1.75rem !important;
            margin: 0.35rem auto !important;
            border-top: 1px solid var(--app-sidebar-border, #cbd5e1) !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse [data-risk-menu] {
            width: 100% !important;
            padding: 0 !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .risk-menu-columns {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            width: 100% !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-collapse .risk-menu-column {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            width: 100% !important;
            padding: 0 !important;
        }

        /* Collapsed Sidebar: User dropup to the side */
        body.sidebar-collapsed .app-sidebar .navbar-nav.flex-row [data-user-menu] {
            left: calc(var(--app-sidebar-collapsed-width) + 0.25rem) !important;
            right: auto !important;
            bottom: 0.5rem !important;
            width: 13.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1) !important;
        }
        body.sidebar-collapsed .app-sidebar .navbar-nav.flex-row [data-user-menu].dropdown-menu-arrow::before {
            top: auto !important;
            bottom: 0.75rem !important;
            left: -6px !important;
            right: auto !important;
            border-right: 0 !important;
            border-bottom: 0 !important;
            border-left: 1px solid var(--app-sidebar-border, #e5e7eb) !important;
            border-top: 1px solid var(--app-sidebar-border, #e5e7eb) !important;
            transform: rotate(-45deg) !important;
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
                background: transparent !important;
                padding: 0.15rem 0 0.25rem 0 !important;
            }
            body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item {
                display: flex !important;
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
                justify-content: flex-start !important;
                padding: 0.45rem 0.75rem !important;
                gap: 0.75rem !important;
            }
            body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item span {
                display: inline !important;
            }
            body.sidebar-collapsed .app-sidebar .navbar-collapse .dropdown-menu .dropdown-item i {
                margin-right: 0 !important;
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

        /* Click-to-sort table headers (public/js/table-sort.js) */
        th.is-sortable { cursor: pointer; user-select: none; }
        th.is-sortable::after { content: "\2195"; margin-left: .3rem; font-size: .8em; opacity: .25; }
        th.is-sortable:hover::after { opacity: .6; }
        th.is-sortable[aria-sort="ascending"]::after { content: "\2191"; opacity: .9; }
        th.is-sortable[aria-sort="descending"]::after { content: "\2193"; opacity: .9; }
        th.is-sortable:focus-visible { outline: 2px solid var(--tblr-primary, #206bc4); outline-offset: -2px; }
        table.dataTable th.is-sortable::after { content: none; }
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
            <h1 class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('dashboard') }}" class="app-brand-link" title="Raksantara: Dashboard">
                    @include('partials.brand-mark', ['size' => 28])
                    <span class="app-brand-full">Raksantara</span>
                </a>
            </h1>
            <button class="sidebar-collapse-toggle" type="button" data-sidebar-toggle aria-label="Sembunyikan sidebar" aria-expanded="true" title="Sembunyikan sidebar">
                <svg class="sidebar-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 17l-5-5 5-5"></path>
                    <path d="M18 17l-5-5 5-5"></path>
                </svg>
            </button>
            <div class="navbar-nav flex-row order-md-last">
                @auth
                    <div class="nav-item dropup">
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
                            data-bs-display="static"
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
                                    <a class="dropdown-item {{ ((request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) && request()->query('view') !== 'analytical') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                        <i class="fas fa-chart-line me-2 text-primary"></i>
                                        <span>Ringkasan</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->query('view') === 'analytical' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'analytical']) }}">
                                        <i class="fas fa-th-large me-2 text-warning"></i>
                                        <span>Analisis</span>
                                    </a>
                                </div>
                            </li>
                            
                            {{-- Framework Explorer Menu --}}
                            <li class="nav-item dropdown {{ (request()->routeIs('frameworks.*')) ? 'active show' : '' }}" data-framework-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-framework-toggle role="button" aria-expanded="{{ (request()->routeIs('frameworks.*')) ? 'true' : 'false' }}">
                                    <span class="nav-link-icon"><i class="fas fa-layer-group"></i></span>
                                    <span>Frameworks</span>
                                </a>
                                <div class="dropdown-menu {{ (request()->routeIs('frameworks.*')) ? 'show' : '' }}" data-framework-menu>
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
                            @includeIf('local.sidebar-extra', ['variant' => 'superadmin'])
                        @elseif(auth()->user()->isDivisionVerified())
                            <li class="nav-item dropdown {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'active show' : '' }}" data-dashboard-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-dashboard-toggle role="button" aria-expanded="{{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'true' : 'false' }}">
                                    <span class="nav-link-icon"><i class="fas fa-chart-pie"></i></span>
                                    <span>Dashboard</span>
                                </a>
                                <div class="dropdown-menu {{ (request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) ? 'show' : '' }}" data-dashboard-menu>
                                    <a class="dropdown-item {{ ((request()->routeIs('dashboard') || request()->routeIs('management.dashboard') || request()->routeIs('risks.monitoring')) && request()->query('view') !== 'analytical') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                        <i class="fas fa-chart-line me-2 text-primary"></i>
                                        <span>Ringkasan</span>
                                    </a>
                                    <a class="dropdown-item {{ request()->query('view') === 'analytical' ? 'active' : '' }}" href="{{ route('dashboard', ['view' => 'analytical']) }}">
                                        <i class="fas fa-th-large me-2 text-warning"></i>
                                        <span>Analisis</span>
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
                                || request()->routeIs('bcm.*')
                                || request()->routeIs('risk-appetites.*')
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
                                                        <i class="{{ $riskMenuItem['icon'] }} me-2"></i><span>{{ $riskMenuItem['label'] }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                            {{-- Framework Explorer Menu --}}
                            <li class="nav-item dropdown {{ (request()->routeIs('frameworks.*')) ? 'active show' : '' }}" data-framework-nav>
                                <a class="nav-link nav-link-main dropdown-toggle" href="#" data-bs-toggle="dropdown" data-toggle="dropdown" data-framework-toggle role="button" aria-expanded="{{ (request()->routeIs('frameworks.*')) ? 'true' : 'false' }}">
                                    <span class="nav-link-icon"><i class="fas fa-layer-group"></i></span>
                                    <span>Frameworks</span>
                                </a>
                                <div class="dropdown-menu {{ (request()->routeIs('frameworks.*')) ? 'show' : '' }}" data-framework-menu>
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
                            @includeIf('local.sidebar-extra', ['variant' => 'division'])
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
                                @php($isComplianceMenuActive = request()->routeIs('compliance.*') || request()->routeIs('risk-status-approvals.*') || request()->routeIs('parameters.*'))
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
                                        <a class="dropdown-item {{ request()->routeIs('risk-status-approvals.*') ? 'active' : '' }}" href="{{ route('risk-status-approvals.index') }}">
                                            <i class="fas fa-check-double"></i><span>Persetujuan Status Risk</span>
                                        </a>
                                        <a class="dropdown-item {{ request()->routeIs('parameters.*') ? 'active' : '' }}" href="{{ route('parameters.index') }}">
                                            <i class="fas fa-sliders-h"></i><span>Parameter Risk</span>
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
<script src="{{ asset('js/table-sort.js') }}" defer></script>
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
            sidebarToggle.setAttribute('aria-label', collapsed ? 'Lebarkan sidebar' : 'Sembunyikan sidebar');
            sidebarToggle.setAttribute('title', collapsed ? 'Lebarkan sidebar' : 'Sembunyikan sidebar');
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

    // Auto-populate title attributes for collapsed sidebar tooltips
    function initSidebarTooltips() {
        document.querySelectorAll('.app-sidebar .navbar-collapse .dropdown-item').forEach(function(item) {
            if (!item.getAttribute('title')) {
                const span = item.querySelector('span');
                const text = span ? span.textContent.trim() : item.textContent.trim();
                if (text) {
                    item.setAttribute('title', text);
                }
            }
        });
        document.querySelectorAll('.app-sidebar .navbar-nav > .nav-item > .nav-link-main').forEach(function(link) {
            if (!link.getAttribute('title')) {
                const span = link.querySelector('span:not(.nav-link-icon)');
                const text = span ? span.textContent.trim() : link.textContent.trim();
                if (text) {
                    link.setAttribute('title', text);
                }
            }
        });
    }
    initSidebarTooltips();

    // Sidebar submenu accordion. Capture the click before Bootstrap tries to create a flyout.
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest(
            '.app-sidebar .navbar-collapse [data-dashboard-toggle], ' +
            '.app-sidebar .navbar-collapse [data-risk-toggle], ' +
            '.app-sidebar .navbar-collapse [data-framework-toggle], ' +
            '.app-sidebar .navbar-collapse [data-compliance-toggle], ' +
            '.app-sidebar .navbar-collapse [data-assessment-toggle], ' +
            '.app-sidebar .navbar-collapse [data-app-management-toggle]'
        );

        if (!toggle) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

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

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
        .page-center {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .container-tight {
            width: 100%;
            max-width: 480px;
        }
        .card-md {
            width: 100%;
        }
        .navbar-brand-autodark {
            color: #212529;
            text-decoration: none;
            font-weight: 600;
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
    </style>
    {{-- Design system layer; remove this line to return to the previous look. --}}
    <link rel="stylesheet" href="{{ asset('css/raksantara-ui.css') }}?v={{ @filemtime(public_path('css/raksantara-ui.css')) }}">
    @yield('css')
</head>
<body>
<div class="rk-auth">
    <aside class="rk-auth-brand" aria-hidden="true">
        <span class="rk-auth-logo">
            @include('partials.brand-mark', ['size' => 32])
            <strong>Raksantara</strong>
        </span>
        <div>
            <h1>Tata kelola risiko, kepatuhan, dan insiden dalam satu tempat.</h1>
            <p>Pantau profil risiko seluruh divisi, tindak lanjut temuan, dan jejak audit secara terukur.</p>
            <ul class="rk-auth-points">
                <li><i class="fas fa-shield-alt"></i><span>Risk register, KRI, dan appetite yang selaras dengan kebijakan</span></li>
                <li><i class="fas fa-exclamation-triangle"></i><span>Insiden dan loss event per divisi dan per client</span></li>
                <li><i class="fas fa-history"></i><span>Audit trail lengkap: siapa, apa, kapan, dan siapa yang menyetujui</span></li>
            </ul>
        </div>
        <span class="rk-auth-foot">&copy; {{ date('Y') }} Raksantara</span>
    </aside>
    <main class="rk-auth-main">
    <div class="rk-auth-panel">
        <a href="{{ route('login') }}" class="rk-auth-logo">
            @include('partials.brand-mark', ['size' => 32])
            <strong>Raksantara</strong>
        </a>

        <div class="card card-md">
            <div class="card-body">
                @if (trim($__env->yieldContent('auth_header')))
                    <h2 class="card-title text-center mb-4">@yield('auth_header')</h2>
                @endif

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
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

                @yield('auth_body')
            </div>

            @if (trim($__env->yieldContent('auth_footer')))
                <div class="card-footer text-center">
                    @yield('auth_footer')
                </div>
            @endif
        </div>
    </div>
    </main>
</div>

<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap5/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/tabler/js/tabler.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
<script>
    function initSearchableSelectsAuth(context) {
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

            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: placeholder,
                allowClear: $select.find('option[value=""]').length > 0,
                minimumResultsForSearch: 0,
            });
        });
    }

    $(document).ready(function () {
        initSearchableSelectsAuth(document);
    });
</script>
@yield('js')
</body>
</html>

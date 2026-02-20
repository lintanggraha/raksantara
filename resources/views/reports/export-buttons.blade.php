@php
    $scopeLabel = $scopeLabel ?? 'This Menu';
    $overallLabel = $overallLabel ?? 'Overall';
    $reportQuery = isset($reportQuery) && is_array($reportQuery) ? $reportQuery : request()->query();
    $overallQuery = isset($overallQuery) && is_array($overallQuery) ? $overallQuery : $reportQuery;
@endphp

@once
    @push('styles')
        <style>
            .report-export-dropdown {
                position: relative;
            }

            .report-export-toggle {
                display: inline-flex;
                align-items: center;
                gap: 0.42rem;
            }

            .report-export-menu {
                display: none;
                min-width: 16rem;
                position: absolute;
                top: calc(100% + 0.3rem);
                right: 0;
                left: auto;
                z-index: 2100;
            }

            .report-export-menu.show {
                display: block;
            }

            .report-export-menu .dropdown-item i {
                width: 1rem;
            }
        </style>
    @endpush

    @push('scripts_body')
        <script>
            (function () {
                function closeAllReportDropdowns(exceptEl) {
                    document.querySelectorAll('[data-report-export]').forEach(function (wrapper) {
                        if (exceptEl && wrapper === exceptEl) {
                            return;
                        }
                        const menu = wrapper.querySelector('[data-report-export-menu]');
                        const toggle = wrapper.querySelector('[data-report-export-toggle]');
                        if (menu) {
                            menu.classList.remove('show');
                        }
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                }

                document.addEventListener('click', function (event) {
                    const toggle = event.target.closest('[data-report-export-toggle]');
                    if (toggle) {
                        event.preventDefault();
                        event.stopPropagation();

                        const wrapper = toggle.closest('[data-report-export]');
                        const menu = wrapper ? wrapper.querySelector('[data-report-export-menu]') : null;
                        if (!wrapper || !menu) {
                            return;
                        }

                        const willOpen = !menu.classList.contains('show');
                        closeAllReportDropdowns(wrapper);
                        menu.classList.toggle('show', willOpen);
                        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                        return;
                    }

                    if (!event.target.closest('[data-report-export]')) {
                        closeAllReportDropdowns();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeAllReportDropdowns();
                    }
                });
            })();
        </script>
    @endpush
@endonce

<div class="dropdown report-export-dropdown" data-report-export>
    <button
        class="btn btn-outline-primary btn-sm report-export-toggle"
        type="button"
        data-report-export-toggle
        aria-expanded="false"
    >
        <i class="fas fa-file-export"></i>
        <span>Export Report</span>
        <i class="fas fa-chevron-down"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end report-export-menu" data-report-export-menu>
        <a class="dropdown-item" href="{{ route('reports.export', array_merge(['scope' => $scope, 'format' => 'xls'], $reportQuery)) }}">
            <i class="fas fa-file-excel me-2 text-success"></i>{{ $scopeLabel }} - XLS
        </a>
        <a class="dropdown-item" href="{{ route('reports.export', array_merge(['scope' => $scope, 'format' => 'pdf'], $reportQuery)) }}">
            <i class="fas fa-file-pdf me-2 text-danger"></i>{{ $scopeLabel }} - PDF
        </a>
        <a class="dropdown-item" href="{{ route('reports.export', array_merge(['scope' => $scope, 'format' => 'pptx'], $reportQuery)) }}">
            <i class="fas fa-file-powerpoint me-2 text-warning"></i>{{ $scopeLabel }} - PPT
        </a>

        <a class="dropdown-item" href="{{ route('reports.export', array_merge(['scope' => 'overall', 'format' => 'xls'], $overallQuery)) }}">
            <i class="fas fa-file-excel me-2 text-success"></i>{{ $overallLabel }} - XLS
        </a>
        <a class="dropdown-item" href="{{ route('reports.export', array_merge(['scope' => 'overall', 'format' => 'pdf'], $overallQuery)) }}">
            <i class="fas fa-file-pdf me-2 text-danger"></i>{{ $overallLabel }} - PDF
        </a>
        <a class="dropdown-item" href="{{ route('reports.export', array_merge(['scope' => 'overall', 'format' => 'pptx'], $overallQuery)) }}">
            <i class="fas fa-file-powerpoint me-2 text-warning"></i>{{ $overallLabel }} - PPT
        </a>
    </div>
</div>

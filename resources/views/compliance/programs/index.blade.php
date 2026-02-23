@extends('layouts.tabler')

@section('title', $sectionConfig['title'] . ' - Compliance')

@section('content')
    @php
        $tableTitleMap = [
            'Internal Audit' => 'Internal Audit Plan',
            'Risk Management' => 'Risk Management Plan',
            'Governance & Compliance' => 'Governance & Compliance Plan',
        ];
        $tableTitle = $tableTitleMap[$sectionConfig['title']] ?? ($sectionConfig['title'] . ' Plan');
    @endphp

    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap5.min.css') }}">

    <style>
        #complianceProgramTable th {
            text-align: center;
            vertical-align: middle !important;
            font-size: 13px;
            white-space: nowrap;
        }

        #complianceProgramTable td {
            vertical-align: middle;
            font-size: 13px;
            line-height: 1.35;
        }

        .btn-xs {
            padding: 0.1rem 0.4rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .program-toolbar .btn.with-icon {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            border-radius: 0.45rem;
            padding: 0.36rem 0.72rem;
            font-weight: 600;
        }

        .program-toolbar .btn.with-icon i {
            margin: 0;
            line-height: 1;
        }

        .program-title {
            margin: 0;
            font-size: 1.12rem;
            font-weight: 700;
            color: #0f172a;
        }

        .program-action-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
        }

        #complianceProgramTable_wrapper .dataTables_filter {
            text-align: right;
        }

        #complianceProgramTable_wrapper .dataTables_filter label,
        #complianceProgramTable_wrapper .dataTables_length label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        #complianceProgramTable_wrapper .dataTables_filter input {
            margin-left: 0 !important;
            min-width: 220px;
        }

        #complianceProgramTable_wrapper .dataTables_length select {
            width: auto;
            min-width: 82px;
        }

        #complianceProgramTable_wrapper .dataTables_paginate {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        #complianceProgramTable_wrapper .dataTables_paginate .pagination {
            display: inline-flex !important;
            align-items: center;
            gap: 0.35rem;
            margin: 0;
            padding-left: 0;
        }

        @media (max-width: 768px) {
            #complianceProgramTable_wrapper .dataTables_filter {
                text-align: left;
            }

            #complianceProgramTable_wrapper .dataTables_filter input {
                min-width: 160px;
            }
        }
    </style>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="program-title">{{ $tableTitle }}</h3>
            <div class="program-toolbar">
                <a href="{{ route($sectionConfig['route_base'] . '.create') }}" class="btn btn-primary btn-sm with-icon">
                    <i class="fas fa-plus"></i> Add Program Kerja
                </a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table id="complianceProgramTable" class="table table-hover table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Actions</th>
                        <th>ID</th>
                        <th>Program / Key Initiative</th>
                        <th>Description & Rationales</th>
                        <th>Deliverables</th>
                        <th>How to Measure</th>
                        <th>Budget</th>
                        <th>Updated</th>
                        <th>Suport Needed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programs as $program)
                        <tr>
                            <td class="text-center">
                                <div class="program-action-stack">
                                    <a
                                        href="{{ route($sectionConfig['route_base'] . '.edit', $program->id) }}"
                                        class="btn btn-warning btn-xs"
                                        title="Edit"
                                        data-toggle="tooltip"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-danger btn-xs"
                                        title="Delete"
                                        data-toggle="tooltip"
                                        onclick="if(confirm('Delete data ini?')) { document.getElementById('delete-program-{{ $program->id }}').submit(); }"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <form
                                    id="delete-program-{{ $program->id }}"
                                    action="{{ route($sectionConfig['route_base'] . '.destroy', $program->id) }}"
                                    method="POST"
                                    class="d-none"
                                >
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                            <td class="text-nowrap">{{ $program->work_id }}</td>
                            <td>{{ $program->program_kerja }}</td>
                            <td title="{{ $program->deskripsi }}">{{ \Illuminate\Support\Str::limit($program->deskripsi, 120) }}</td>
                            <td title="{{ $program->target_sasaran_kerja }}">{{ \Illuminate\Support\Str::limit($program->target_sasaran_kerja, 120) }}</td>
                            <td title="{{ $program->alignment_champion_program }}">{{ \Illuminate\Support\Str::limit($program->alignment_champion_program, 120) }}</td>
                            <td class="text-nowrap">{{ $program->alignment_risk_profile_2025 }}</td>
                            <td class="text-nowrap text-center" data-order="{{ optional($program->updated_at)->timestamp ?? 0 }}">
                                {{ optional($program->updated_at)->format('d-m-Y H:i') }}
                            </td>
                            <td title="{{ $program->support_needed }}">{{ \Illuminate\Support\Str::limit($program->support_needed ?? '-', 120) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            const table = $('#complianceProgramTable').DataTable({
                responsive: false,
                autoWidth: false,
                scrollX: true,
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                order: [[7, 'desc']],
                dom:
                    "<'row mb-2 align-items-center'<'col-md-6'l><'col-md-6 text-end'f>>" +
                    "rt" +
                    "<'row mt-2'<'col-md-5'i><'col-md-7 text-end'p>>",
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false }
                ],
                language: {
                    paginate: {
                        previous: "&laquo; Previous",
                        next: "Next &raquo;"
                    }
                }
            });

            function enforcePagerSpacing() {
                const paginate = $('#complianceProgramTable_wrapper .dataTables_paginate');
                paginate.find('a.paginate_button').css({
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    marginLeft: '0.35rem',
                    marginRight: '0.35rem'
                });
                paginate.find('a.paginate_button.previous').css({
                    marginLeft: '0',
                    marginRight: '0.45rem'
                });
                paginate.find('a.paginate_button.next').css({
                    marginLeft: '0.45rem',
                    marginRight: '0'
                });
                paginate.find('span').css({
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '0.35rem',
                    margin: '0 0.1rem'
                });
            }

            table.on('draw.dt', function () {
                enforcePagerSpacing();
            });
            enforcePagerSpacing();
        });
    </script>
@endsection

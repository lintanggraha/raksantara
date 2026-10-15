@extends('layouts.tabler')

@section('title', 'Audit Log')

@section('css')
<style>
    .audit-table td { vertical-align: top; font-size: .82rem; }
    .audit-table .when { white-space: nowrap; }
    .audit-table .when b { display: block; font-variant-numeric: tabular-nums; }
    .audit-table .muted { color: #667382; font-size: .75rem; }
    .audit-table .module { display: inline-block; font-size: .7rem; padding: .05rem .45rem; border-radius: 999px; background: #f1f3f6; color: #4b5563; margin-bottom: .2rem; }
    .audit-table .approval { margin-top: .3rem; font-size: .75rem; color: #1e3a8a; }
    .audit-table details summary { cursor: pointer; color: #1e3a8a; font-size: .78rem; }
    .audit-diff { margin-top: .4rem; width: 100%; border-collapse: collapse; font-size: .76rem; }
    .audit-diff th, .audit-diff td { border: 1px solid #e6e8ec; padding: .25rem .4rem; vertical-align: top; text-align: left; }
    .audit-diff th { background: #f6f8fb; font-weight: 600; }
    .audit-diff .old { background: #fdf2f2; color: #7f1d1d; word-break: break-word; }
    .audit-diff .new { background: #effaf2; color: #14532d; word-break: break-word; }
    .audit-diff .field { white-space: nowrap; color: #374151; }
</style>
@endsection

@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="mb-0">Audit Log</h1>
        <div class="text-muted small">Siapa melakukan apa, kapan, dan apa saja yang berubah. Waktu dalam {{ $timezoneLabel }}.</div>
    </div>
    <a href="{{ route('audit-logs.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
        <i class="fas fa-file-csv me-1"></i> Export CSV ({{ number_format($logs->total(), 0, ',', '.') }} baris)
    </a>
</div>
@stop

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small" for="auditFrom">Dari tanggal</label>
                <input type="date" id="auditFrom" name="from" value="{{ $filters['from'] }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="auditTo">Sampai tanggal</label>
                <input type="date" id="auditTo" name="to" value="{{ $filters['to'] }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="auditActor">Pengguna</label>
                <select id="auditActor" name="actor" class="form-select form-select-sm">
                    <option value="">Semua pengguna</option>
                    @foreach($actors as $actor)
                        <option value="{{ $actor->id }}" @selected($filters['actor'] === $actor->id)>{{ $actor->name }} ({{ '@' . $actor->username }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="auditModule">Modul</label>
                <select id="auditModule" name="module" class="form-select form-select-sm no-search-select">
                    <option value="">Semua modul</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}" @selected($filters['module'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="auditEvent">Aktivitas</label>
                <select id="auditEvent" name="event" class="form-select form-select-sm">
                    <option value="">Semua aktivitas</option>
                    @foreach($events as $key => $label)
                        <option value="{{ $key }}" @selected($filters['event'] === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="auditQ">Cari</label>
                <input type="search" id="auditQ" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm" placeholder="Kode risk, nama, isi…">
            </div>
            <div class="col-md-2">
                <label class="form-label small" for="auditIp">Alamat IP</label>
                <input type="text" id="auditIp" name="ip" value="{{ $filters['ip'] }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                @if(array_filter($filters))
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-link btn-sm">Hapus filter</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-vcenter card-table audit-table" data-no-sort>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aktivitas</th>
                    <th>Objek</th>
                    <th style="min-width: 320px;">Rincian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="when">
                            {{ $row['day'] }}
                            <b>{{ $row['clock'] }}</b>
                            @if($row['ip'])<div class="muted" title="{{ $row['user_agent'] }}">IP {{ $row['ip'] }}@if($row['browser']) · {{ $row['browser'] }}@endif</div>@endif
                        </td>
                        <td>
                            <div class="fw-medium">{{ $row['actor'] }}</div>
                            <div class="muted">{{ $row['actor_detail'] }}</div>
                        </td>
                        <td>
                            <span class="module">{{ $row['module'] }}</span>
                            <div class="fw-medium">{{ $row['activity'] }}</div>
                        </td>
                        <td>{{ $row['object'] ?: '–' }}</td>
                        <td>
                            @if($row['approval'])
                                <div class="approval"><i class="fas fa-user-check me-1"></i>{{ $row['approval'] }}</div>
                            @endif
                            @if(count($row['changes']))
                                <details @if(count($row['changes']) <= 3) open @endif>
                                    <summary>
                                        @switch($row['change_kind'])
                                            @case('updated') {{ count($row['changes']) }} field diubah @break
                                            @case('created') Data yang dibuat ({{ count($row['changes']) }} field) @break
                                            @case('deleted') Data yang dihapus ({{ count($row['changes']) }} field) @break
                                            @default Detail
                                        @endswitch
                                    </summary>
                                    <table class="audit-diff">
                                        <thead>
                                            <tr>
                                                <th>Field</th>
                                                @if($row['change_kind'] !== 'created')<th>Sebelum</th>@endif
                                                @if($row['change_kind'] !== 'deleted')<th>Sesudah</th>@endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($row['changes'] as $change)
                                                <tr>
                                                    <td class="field">{{ $change['field'] }}</td>
                                                    @if($row['change_kind'] !== 'created')<td class="old">{{ $change['before'] ?? '–' }}</td>@endif
                                                    @if($row['change_kind'] !== 'deleted')<td class="new">{{ $change['after'] ?? '–' }}</td>@endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </details>
                            @elseif(!$row['approval'])
                                <span class="muted">Tidak ada data tambahan.</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada catatan untuk filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer">{{ $logs->links() }}</div>
    @endif
</div>
@stop

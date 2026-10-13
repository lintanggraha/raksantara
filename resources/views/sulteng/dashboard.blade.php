@extends('layouts.tabler')

@section('title', 'Executive Dashboard Monitoring - Bank Sulteng')

@section('content_header')
<div class="row align-items-center">
    <div class="col">
        <div class="page-pretitle text-muted text-uppercase fw-bold">
            <i class="fas fa-shield-alt me-1 text-primary"></i> Executive Management Summary
        </div>
        <h2 class="page-title d-flex align-items-center gap-2">
            <span>Monitoring Assessment</span>
            <span class="badge bg-primary-lt fs-6 fw-semibold">Bank Sulteng</span>
        </h2>
        <div class="text-muted mt-1 small">
            Ringkasan grafis interaktif pemenuhan evidence forensik, akuntabilitas divisi PIC, dan rincian status penyerahan data untuk manajemen.
        </div>
    </div>
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <a href="{{ route('sulteng.items') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="fas fa-file-upload"></i>
                <span>Upload Evidence</span>
            </a>
            <a href="{{ route('sulteng.export-excel') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="fas fa-file-excel"></i>
                <span>Export Excel</span>
            </a>
            @if(auth()->user() && auth()->user()->isSuperadmin())
            <form action="{{ route('sulteng.sync-excel') }}" method="POST" class="d-inline" onsubmit="return confirm('Sinkronisasi ulang item checklist dari file Excel sumber?');">
                @csrf
                <button type="submit" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" title="Sinkronkan data dari file Excel sumber">
                    <i class="fas fa-sync-alt"></i>
                    <span>Sync Excel</span>
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-xl px-0">

    {{-- Sub Navigation Tabs --}}
    <ul class="nav nav-tabs border-bottom mb-4">
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="{{ route('sulteng.dashboard') }}">
                <i class="fas fa-chart-pie me-1 text-primary"></i> Dashboard Grafik Manajemen
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-muted" href="{{ route('sulteng.items') }}">
                <i class="fas fa-clipboard-list me-1"></i> Rincian Checklist &amp; Upload
                <span class="badge bg-secondary-lt ms-1">{{ $totalItems }}</span>
            </a>
        </li>
    </ul>

    {{-- Executive Summary Hero Banner --}}
    <div class="card border-0 shadow-sm mb-4 bg-gradient text-dark position-relative overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border-left: 5px solid #206bc4 !important;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-green text-white px-2 py-1 fs-6 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="fas fa-check-circle"></i> FASE REKONSTRUKSI &amp; ANALISIS FORENSIK
                        </span>
                        <span class="badge bg-primary-lt fw-bold">Periode: 11 – 20 September 2026</span>
                    </div>
                    <h3 class="fw-bold text-dark mb-1 fs-2">Progres Pemenuhan Evidence: {{ $progressPercent }}% Selesai</h3>
                    <p class="text-secondary mb-3 fs-5" style="max-width: 780px;">
                        Data log transaksi inti, log sistem operasi Linux server middleware, database PostgreSQL, dan gateway non-kartu (QRIS) <strong>telah berhasil dihimpun dan diverifikasi 100%</strong> di penyimpanan lokal. Fokus tindak lanjut manajemen saat ini adalah eskalasi dokumen arsitektur dan parameter rule dari 3 unit kerja.
                    </p>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-success text-white rounded-circle shadow-sm">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            <div>
                                <div class="small text-muted">Kesiapan Log Transaksi</div>
                                <div class="fw-bold text-success">100% Siap Ditelusuri</div>
                            </div>
                        </div>
                        <div class="vr mx-2 d-none d-sm-block"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-danger text-white rounded-circle shadow-sm">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            <div>
                                <div class="small text-muted">Belum Diterima (Merah)</div>
                                <a href="javascript:void(0)" onclick="openDrilldown('Belum Diterima')" class="fw-bold text-danger text-decoration-none" title="Klik untuk lihat 9 item merah">
                                    {{ $pendingCount }} Point Belum Lengkap <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i>
                                </a>
                            </div>
                        </div>
                        <div class="vr mx-2 d-none d-sm-block"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-azure text-white rounded-circle shadow-sm">
                                <i class="fas fa-database"></i>
                            </span>
                            <div>
                                <div class="small text-muted">Total Arsip Evidence</div>
                                <div class="fw-bold text-azure">{{ $totalEvidences }} File ({{ $formattedTotalSize }})</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-3 bg-white rounded-3 shadow-sm border text-center">
                        <div class="small text-uppercase fw-bold text-muted mb-2">Total Item Checklist: {{ $totalItems }} Point</div>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="fs-1 fw-bold text-primary" style="font-size: 3.2rem !important; line-height: 1;">{{ $progressPercent }}%</div>
                        </div>
                        <div class="progress mt-2 mb-2" style="height: 12px; cursor: pointer;" onclick="openDrilldown('Belum Diterima')" title="Klik progress bar untuk melihat rincian item">
                            <div class="progress-bar bg-success" style="width: {{ $progressPercent }}%" title="Diterima (Hijau): {{ $receivedCount }} item ({{ $progressPercent }}%)"></div>
                            <div class="progress-bar bg-danger" style="width: {{ $totalItems > 0 ? round(($pendingCount / $totalItems) * 100, 1) : 0 }}%" title="Belum Diterima (Merah): {{ $pendingCount }} item ({{ $totalItems > 0 ? round(($pendingCount / $totalItems) * 100, 1) : 0 }}%)"></div>
                        </div>
                        <div class="d-flex justify-content-between small px-1 mt-1">
                            <a href="javascript:void(0)" onclick="openDrilldown('Diterima')" class="badge bg-success-lt text-success text-decoration-none fw-bold px-2 py-1 hover-shadow" title="Klik untuk lihat {{ $receivedCount }} item Diterima (Hijau)">
                                <i class="fas fa-check-circle me-1"></i> {{ $receivedCount }} Diterima ({{ $progressPercent }}%)
                            </a>
                            <a href="javascript:void(0)" onclick="openDrilldown('Belum Diterima')" class="badge bg-danger-lt text-danger text-decoration-none fw-bold px-2 py-1 hover-shadow" title="Klik untuk lihat {{ $pendingCount }} item Belum Diterima (Merah)">
                                <i class="fas fa-times-circle me-1"></i> {{ $pendingCount }} Belum Diterima ({{ $totalItems > 0 ? round(($pendingCount / $totalItems) * 100, 1) : 0 }}%)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Executive Graphic Charts Row --}}
    <div class="row row-cards mb-4">
        {{-- Chart 1: Donut Chart Status (Interactive Clickable) --}}
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-chart-pie text-primary"></i> Status Pemenuhan Evidence
                        </h3>
                        <div class="text-muted small mt-1">Klik bagian lingkaran untuk melihat rincian item</div>
                    </div>
                    <span class="badge bg-light text-muted">{{ $totalItems }} Point</span>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center py-3">
                    <div style="position: relative; width: 100%; max-width: 250px; height: 230px; cursor: pointer;" title="Klik irisan grafik untuk melihat rincian item">
                        <canvas id="statusDonutChart"></canvas>
                        <div class="d-flex flex-column align-items-center justify-content-center" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                            <div class="fs-1 fw-bold text-dark" style="line-height: 1;">{{ $progressPercent }}%</div>
                            <div class="small text-muted fw-semibold">Terpenuhi</div>
                        </div>
                    </div>

                    {{-- Clickable Legend Pills (Binary: Hijau & Merah) --}}
                    <div class="w-100 mt-3 pt-2 border-top">
                        {{-- Hijau (Diterima) --}}
                        <a href="javascript:void(0)" onclick="openDrilldown('Diterima')" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark hover-shadow mb-2 bg-success-lt border border-success" style="transition: all 0.2s;" title="Klik untuk rincian 22 item Diterima (Hijau)">
                            <span class="d-flex align-items-center gap-2">
                                <span class="badge bg-green rounded-circle p-1"></span>
                                <span class="fw-bold text-dark">Diterima / Lengkap (Hijau)</span>
                            </span>
                            <span class="badge bg-success text-white fw-bold shadow-sm">
                                <i class="fas fa-search me-1"></i> {{ $receivedCount }} item ({{ $progressPercent }}%) &raquo;
                            </span>
                        </a>

                        {{-- Merah (Belum Diterima) --}}
                        <a href="javascript:void(0)" onclick="openDrilldown('Belum Diterima')" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark hover-shadow bg-danger-lt border border-danger" style="transition: all 0.2s;" title="Klik untuk rincian 9 item Belum Diterima (Merah)">
                            <span class="d-flex align-items-center gap-2">
                                <span class="badge bg-danger rounded-circle p-1"></span>
                                <span class="fw-bold text-dark">Belum Diterima (Merah)</span>
                            </span>
                            <span class="badge bg-danger text-white fw-bold shadow-sm">
                                <i class="fas fa-search me-1"></i> {{ $pendingCount }} item ({{ $totalItems > 0 ? round(($pendingCount / $totalItems) * 100, 1) : 0 }}%) &raquo;
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Stacked Bar Chart Categories (Interactive Clickable) --}}
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-layer-group text-azure"></i> Kesiapan per Kategori Sistem &amp; Komponen
                        </h3>
                        <div class="text-muted small mt-1">Klik batang grafik untuk membuka rincian kategori &amp; status di lembar checklist</div>
                    </div>
                    <span class="badge bg-azure-lt">{{ count($categories) }} Kategori Utama</span>
                </div>
                <div class="card-body p-3">
                    <div style="position: relative; width: 100%; height: 290px;">
                        <canvas id="categoryBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DEDICATED CARD: Penjelasan Status Forensik (Item Diterima dengan Catatan Rekomendasi Penguat) --}}
    <div class="card shadow-sm border-0 mb-4" style="border-left: 5px solid #2fb344 !important;">
        <div class="card-header bg-green-lt py-3 d-flex align-items-center justify-content-between">
            <div>
                <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2 fs-3">
                    <i class="fas fa-check-double text-success fa-lg"></i>
                    Penjelasan Status Forensik: Item Diterima dengan Catatan Rekomendasi Penguat (B.5, E.2, E.1)
                </h3>
                <div class="text-secondary small mt-1">
                    Log <strong>Messaging Middleware (B.5)</strong> dan <strong>Log/Event WAF SIEM (E.2)</strong> telah berstatus <strong>Diterima (Hijau)</strong> berdasarkan arsip log transaksi dan event harian yang tersedia. Untuk meningkatkan kedalaman audit pembuktian forensik, tim investigasi menyertakan <strong>catatan rekomendasi data pendukung</strong>.
                </div>
            </div>
            <button class="btn btn-sm btn-outline-danger fw-bold shadow-sm d-flex align-items-center gap-1" onclick="openDrilldown('Belum Diterima')">
                <i class="fas fa-list-check"></i>
                <span>Lihat {{ $pendingCount }} Item Belum Diterima</span>
            </button>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                {{-- B.5 (Diterima dengan Rekomendasi Message Broker) --}}
                <div class="col-lg-4">
                    <div class="card h-100 border border-success bg-white shadow-sm">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-success text-white fw-bold fs-6">B.5</span>
                                    <span class="badge bg-blue-lt">PIC: ITOM</span>
                                </div>
                                <div class="fw-bold text-dark fs-4 mb-2">Log Messaging Middleware</div>
                                
                                <div class="mb-2 p-2 rounded bg-success-lt border border-success small">
                                    <div class="text-success fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-check-circle"></i> Status: Lengkap Diterima
                                    </div>
                                    <div class="text-dark mt-1">
                                        Terpenuhi melalui log orchestrator transaksi messaging digital banking (<code>orchestrator-cbsbe</code> &amp; <code>orchestrator-webolibs</code>) periode 11–20 September 2026.
                                    </div>
                                </div>

                                <div class="p-2 rounded bg-warning-lt border border-warning mb-2 small">
                                    <div class="text-warning-emphasis fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-lightbulb"></i> Rekomendasi Tambahan Penguat:
                                    </div>
                                    <ul class="mb-0 ps-3 mt-1 text-dark" style="font-size: 0.76rem;">
                                        <li><strong>Raw Dedicated Broker Logs:</strong> Log server/partisi Kafka, RabbitMQ, atau ActiveMQ jika terdapat broker mandiri.</li>
                                        <li><strong>Queue Depth &amp; Latency Metrics:</strong> Metrik antrean dan keterlambatan pesan saat lonjakan transaksi insiden.</li>
                                        <li><strong>ACK &amp; Dead-Letter Queue (DLQ):</strong> Log konfirmasi pemrosesan pesan serta rekaman pesan gagal (*dropped/retry*).</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="small text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Status: Diterima (Ada Catatan)</span>
                                <a href="{{ route('sulteng.items', ['search' => 'B.5']) }}" class="btn btn-xs btn-outline-success fw-bold">
                                    Buka Item &amp; Lihat File &raquo;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- E.2 (Diterima dengan Rekomendasi WAF SIEM & Payload) --}}
                <div class="col-lg-4">
                    <div class="card h-100 border border-success bg-white shadow-sm">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-success text-white fw-bold fs-6">E.2</span>
                                    <span class="badge bg-blue-lt">PIC: Infra / SOC</span>
                                </div>
                                <div class="fw-bold text-dark fs-4 mb-2">Log/Event dari WAF (SIEM)</div>
                                
                                <div class="mb-2 p-2 rounded bg-success-lt border border-success small">
                                    <div class="text-success fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-check-circle"></i> Status: Lengkap Diterima
                                    </div>
                                    <div class="text-dark mt-1">
                                        Terpenuhi melalui 11 bundle log harian proteksi WAF Sangfor periode 10–20 September 2026 (arsip <code>Log-Event dari WAF.zip</code>).
                                    </div>
                                </div>

                                <div class="p-2 rounded bg-warning-lt border border-warning mb-2 small">
                                    <div class="text-warning-emphasis fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-lightbulb"></i> Rekomendasi Tambahan Penguat:
                                    </div>
                                    <ul class="mb-0 ps-3 mt-1 text-dark" style="font-size: 0.76rem;">
                                        <li><strong>Indeks Terpusat SIEM:</strong> Integrasi stream event WAF langsung di indeks platform Wazuh/Elasticsearch.</li>
                                        <li><strong>WAF DC Palu (Multi-Site):</strong> Pelengkap log WAF DC Palu untuk korelasi lalu lintas serangan multi-lokasi (saat ini DRC SBY).</li>
                                        <li><strong>Full HTTP Request Body:</strong> Tangkapan payload mentah (*raw body*) untuk alert level High/Critical.</li>
                                        <li><strong>Header X-Forwarded-For:</strong> Pemetaan IP asli penyerang yang melewati reverse proxy.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="small text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Status: Diterima (Ada Catatan)</span>
                                <a href="{{ route('sulteng.items', ['search' => 'E.2']) }}" class="btn btn-xs btn-outline-success fw-bold">
                                    Buka Item &amp; Lihat File &raquo;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- E.1 (Dihijaukan dengan Catatan) --}}
                <div class="col-lg-4">
                    <div class="card h-100 border border-success bg-white shadow-sm">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-success text-white fw-bold fs-6">E.1</span>
                                    <span class="badge bg-blue-lt">PIC: Infra / SOC</span>
                                </div>
                                <div class="fw-bold text-dark fs-4 mb-2">Log/Event dari Firewall (SIEM)</div>
                                
                                <div class="mb-2 p-2 rounded bg-success-lt border border-success small">
                                    <div class="text-success fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-check-circle"></i> Status: Lengkap Diterima
                                    </div>
                                    <div class="text-dark mt-1">
                                        File log firewall Sangfor DRC Surabaya telah diserahkan khusus di folder kategori E (<code>Log-Event dari Firewall.xlsx</code>).
                                    </div>
                                </div>

                                <div class="p-2 rounded bg-warning-lt border border-warning mb-2 small">
                                    <div class="text-warning-emphasis fw-bold d-flex align-items-center gap-1">
                                        <i class="fas fa-lightbulb"></i> Rekomendasi Tambahan Penguat:
                                    </div>
                                    <ul class="mb-0 ps-3 mt-1 text-dark" style="font-size: 0.76rem;">
                                        <li><strong>Log Stream DC Palu:</strong> Penyerahan log firewall DC Palu untuk melengkapi DRC Surabaya.</li>
                                        <li><strong>Indeks Wazuh SIEM:</strong> Event stream terindeks di Wazuh untuk korelasi alert perimeter inter-zone.</li>
                                        <li><strong>NAT Session Table:</strong> Tabel sesi terjemahan IP lokal ke publik pada saat insiden berlangsung.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <span class="small text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Status: Diterima (Ada Catatan)</span>
                                <a href="{{ route('sulteng.items', ['search' => 'E.1']) }}" class="btn btn-xs btn-outline-success fw-bold">
                                    Buka Item &amp; Lihat File &raquo;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bundled Notes Strip --}}
            <div class="mt-3 p-2 px-3 rounded bg-light border d-flex flex-wrap align-items-center justify-content-between gap-2 small">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white">Item Hijau Lainnya</span>
                    <span class="text-secondary">Point <strong>B.6</strong> (Web Server Olibs &gt;2 GB), <strong>B.7</strong>, <strong>C.5</strong>, <strong>C.6</strong>, <strong>C.7</strong>, dan <strong>D.3</strong> berstatus <strong>Diterima</strong> lengkap dari ekstraksi data ITOM &amp; bundle log terpadu.</span>
                </div>
                <a href="javascript:void(0)" onclick="openDrilldown('Diterima')" class="btn btn-xs btn-outline-success fw-semibold">
                    Lihat Catatan Lengkap di Modal &raquo;
                </a>
            </div>
        </div>
    </div>

    {{-- Second Graphic Row: Accountability by PIC & Management Action Items --}}
    <div class="row row-cards mb-4">
        {{-- Chart 3: PIC Accountability Chart (Clickable) --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-users-cog text-info"></i> Akuntabilitas &amp; Progres Penyerahan per PIC Divisi
                        </h3>
                        <div class="text-muted small mt-1">Klik batang grafik untuk memfilter daftar checklist berdasarkan PIC Divisi</div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div style="position: relative; width: 100%; height: 280px; cursor: pointer;">
                        <canvas id="picAccountabilityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Management Action Items & Escalation Board --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100 border-top border-danger border-3">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-bullhorn text-danger"></i> Daftar Item Belum Diterima (Merah)
                    </h3>
                    <a href="javascript:void(0)" onclick="openDrilldown('Belum Diterima')" class="badge bg-danger text-white text-decoration-none fw-bold" title="Lihat {{ $pendingCount }} item merah">
                        {{ $pendingCount }} Item Merah &raquo;
                    </a>
                </div>
                <div class="card-body p-3">
                    <p class="text-muted small mb-3">
                        Berikut adalah <strong>{{ $pendingCount }} poin item berstatus merah</strong> yang membutuhkan <strong>surat penugasan / arahan pimpinan</strong>:
                    </p>

                    <div class="list-group list-group-flush gap-2" style="max-height: 230px; overflow-y: auto;">
                        @foreach($pendingItems as $pItem)
                        <div class="p-2 rounded border bg-light d-flex align-items-start gap-2 hover-shadow">
                            <span class="badge bg-red text-white mt-1">{{ $pItem->code }}</span>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-bold text-dark small text-truncate">{{ $pItem->title }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    <span class="badge bg-blue-lt me-1">{{ $pItem->pic_division ?: 'PIC Belum Ada' }}</span>
                                    <span>{{ Str::limit($pItem->notes ?: 'Belum diserahkan oleh unit kerja terkait.', 55) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('sulteng.items', ['search' => $pItem->code]) }}" class="btn btn-xs btn-outline-danger" title="Buka Point {{ $pItem->code }}">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-3 text-center">
                        <button class="btn btn-sm btn-outline-danger w-100 fw-bold" onclick="openDrilldown('Belum Diterima')">
                            <i class="fas fa-search me-1"></i> Buka Rincian Lengkap {{ $pendingCount }} Item Merah di Modal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Investigation Roadmap / Pipeline Tracker --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-tasks text-primary"></i> Roadmap &amp; Tahapan Pelaksanaan Investigasi Forensik
            </h3>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 text-center">
                {{-- Step 1 --}}
                <div class="col-md">
                    <div class="p-3 rounded-3 bg-light border border-success h-100 position-relative">
                        <span class="badge bg-success text-white position-absolute top-0 start-50 translate-middle rounded-pill px-3">
                            <i class="fas fa-check me-1"></i> Selesai
                        </span>
                        <div class="fs-4 text-success fw-bold mt-2">Tahap 1</div>
                        <div class="fw-bold text-dark mt-1">Inventarisasi Kebutuhan Data</div>
                        <div class="text-muted small mt-1">Penyusunan 28 point checklist audit forensik</div>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="col-md">
                    <div class="p-3 rounded-3 bg-light border border-success h-100 position-relative">
                        <span class="badge bg-success text-white position-absolute top-0 start-50 translate-middle rounded-pill px-3">
                            <i class="fas fa-check me-1"></i> Selesai
                        </span>
                        <div class="fs-4 text-success fw-bold mt-2">Tahap 2</div>
                        <div class="fw-bold text-dark mt-1">Akuisisi Log &amp; Database Inti</div>
                        <div class="text-muted small mt-1">OS Linux, PostgreSQL DB, Log Transaksi eMware &amp; QRIS</div>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="col-md">
                    <div class="p-3 rounded-3 bg-warning-lt border border-warning h-100 position-relative">
                        <span class="badge bg-warning text-white position-absolute top-0 start-50 translate-middle rounded-pill px-3">
                            <i class="fas fa-spinner fa-spin me-1"></i> Berjalan
                        </span>
                        <div class="fs-4 text-warning fw-bold mt-2">Tahap 3</div>
                        <div class="fw-bold text-dark mt-1">Spesifikasi Arsitektur &amp; Rule</div>
                        <div class="text-muted small mt-1">Eskalasi dokumen teknis, rule FDS, dan output CAT-SCALE</div>
                    </div>
                </div>

                {{-- Step 4 --}}
                <div class="col-md">
                    <div class="p-3 rounded-3 bg-light border h-100 position-relative opacity-75">
                        <span class="badge bg-secondary text-white position-absolute top-0 start-50 translate-middle rounded-pill px-3">
                            Antrean
                        </span>
                        <div class="fs-4 text-secondary fw-bold mt-2">Tahap 4</div>
                        <div class="fw-bold text-dark mt-1">Korelasi &amp; Timeline Forensik</div>
                        <div class="text-muted small mt-1">Analisis anomali lonjakan transaksi 18–20 Sept</div>
                    </div>
                </div>

                {{-- Step 5 --}}
                <div class="col-md">
                    <div class="p-3 rounded-3 bg-light border h-100 position-relative opacity-75">
                        <span class="badge bg-secondary text-white position-absolute top-0 start-50 translate-middle rounded-pill px-3">
                            Antrean
                        </span>
                        <div class="fs-4 text-secondary fw-bold mt-2">Tahap 5</div>
                        <div class="fw-bold text-dark mt-1">Laporan Investigasi &amp; Rekomendasi</div>
                        <div class="text-muted small mt-1">Penyusunan Berita Acara &amp; Rencana Remediasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Collapsible Technical Breakdown for Analyst & Technical Staff --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h3 class="card-title fw-bold text-secondary mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-table text-secondary"></i> Rincian Teknis &amp; Rekap Data Lengkap
            </h3>
            <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#technicalBreakdownCollapse" aria-expanded="false" aria-controls="technicalBreakdownCollapse">
                <i class="fas fa-chevron-down"></i>
                <span>Tampilkan / Sembunyikan Detail Teknis</span>
            </button>
        </div>
        <div class="collapse" id="technicalBreakdownCollapse">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover card-table">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px;">Kode</th>
                                <th>Kategori Sistem</th>
                                <th class="text-center" style="width: 100px;">Total Item</th>
                                <th class="text-center" style="width: 120px;">Diterima (Hijau)</th>
                                <th class="text-center" style="width: 140px;">Belum Diterima (Merah)</th>
                                <th class="text-center" style="width: 110px;">File Evidence</th>
                                <th style="width: 200px;">Progress</th>
                                <th class="text-end" style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $cat)
                            <tr>
                                <td>
                                    <span class="badge bg-primary text-white fw-bold fs-6">{{ $cat['code'] }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $cat['name'] }}</div>
                                    <div class="small text-muted">Section {{ $cat['code'] }} dalam lembar Checklist</div>
                                </td>
                                <td class="text-center fw-bold">{{ $cat['total'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-green-lt text-green fw-bold">{{ $cat['diterima'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-red-lt text-red fw-bold">{{ $cat['belum'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-azure-lt text-azure fw-bold">
                                        <i class="fas fa-paperclip me-1"></i>{{ $cat['evidences_count'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: {{ $cat['percent'] }}%" role="progressbar"></div>
                                        </div>
                                        <span class="small fw-bold text-muted" style="min-width: 35px;">{{ $cat['percent'] }}%</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('sulteng.items', ['category' => $cat['code']]) }}" class="btn btn-sm btn-ghost-primary" title="Buka Kategori {{ $cat['code'] }}">
                                        <i class="fas fa-folder-open me-1"></i> Buka
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- INTERACTIVE DRILLDOWN MODAL (Binary: Belum Diterima & Diterima) --}}
<div class="modal fade" id="drilldownModal" tabindex="-1" aria-labelledby="drilldownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <span id="drilldownModalIcon" class="avatar avatar-sm bg-danger text-white rounded-circle">
                        <i class="fas fa-times-circle"></i>
                    </span>
                    <div>
                        <h4 class="modal-title fw-bold text-dark mb-0" id="drilldownModalLabel">Rincian Item Checklist (Merah &amp; Hijau)</h4>
                        <div class="small text-muted" id="drilldownModalSubtitle">Daftar item berdasarkan status pemenuhan evidence forensik</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Filter Pills inside Modal (Binary: Belum Diterima & Diterima) --}}
            <div class="p-3 bg-white border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-danger active fw-bold" id="btnFilterPending" onclick="switchModalFilter('Belum Diterima')">
                            🔴 Belum Diterima ({{ $pendingCount }})
                        </button>
                        <button type="button" class="btn btn-outline-success fw-bold" id="btnFilterDiterima" onclick="switchModalFilter('Diterima')">
                            🟢 Diterima ({{ $receivedCount }})
                        </button>
                    </div>

                    <a href="{{ route('sulteng.items') }}" id="btnOpenFullChecklist" class="btn btn-sm btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Buka di Lembar Checklist Lengkap
                    </a>
                </div>
            </div>

            <div class="modal-body p-3">
                {{-- Group 1: Belum Diterima (Red) --}}
                <div id="drilldownContainerPending" class="drilldown-group">
                    <div class="alert alert-danger py-2 px-3 small mb-3 border-danger d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                        <div>
                            <strong>Status Merah (Belum Diterima):</strong> <strong>{{ $pendingCount }} item</strong> berikut belum diserahkan oleh unit/divisi PIC terkait dan memerlukan eskalasi audit investigasi.
                        </div>
                    </div>

                    @foreach($pendingItems as $item)
                    <div class="card mb-3 border shadow-none" style="border-left: 4px solid #d63939 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <span class="badge bg-danger text-white fw-bold fs-6">Point {{ $item->code }}</span>
                                        <span class="badge bg-primary-lt">{{ $item->category_name }}</span>
                                        <span class="badge bg-blue-lt"><i class="fas fa-user-shield me-1"></i> PIC: {{ $item->pic_division ?: 'Belum Ada' }}</span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-2">{{ $item->title }}</h4>
                                </div>
                                <span class="badge bg-danger-lt text-danger fw-bold fs-6 px-2 py-1">
                                    <i class="fas fa-times-circle me-1"></i> Belum Diterima
                                </span>
                            </div>

                            <div class="p-2 rounded bg-danger-lt border border-danger small mt-2">
                                <div class="fw-bold text-danger mb-1">
                                    <i class="fas fa-bullhorn me-1"></i> Alasan Belum Diterima &amp; Catatan Forensik:
                                </div>
                                <div class="text-dark">{{ $item->notes ?: 'Belum ada data/file evidence yang diserahkan oleh unit PIC terkait.' }}</div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                                <span class="small text-muted">
                                    Periode yang diminta: <strong>{{ $item->period ?: '-' }}</strong>
                                </span>
                                <a href="{{ route('sulteng.items', ['search' => $item->code]) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                    <i class="fas fa-upload me-1"></i> Buka Item &amp; Upload Evidence
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Group 2: Diterima (Green) --}}
                <div id="drilldownContainerDiterima" class="drilldown-group d-none">
                    <div class="alert alert-success py-2 px-3 small mb-3 border-success d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle fa-lg"></i>
                        <div>
                            <strong>Status Hijau (Diterima / Lengkap):</strong> <strong>{{ $receivedCount }} item</strong> berikut telah terverifikasi lengkap beserta arsip evidence fisiknya di folder penyimpanan evidence.
                        </div>
                    </div>

                    @foreach($receivedItems as $item)
                    <div class="card mb-2 border shadow-none" style="border-left: 4px solid #2fb344 !important;">
                        <div class="card-body p-2 px-3">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-green text-white fw-bold mt-1">{{ $item->code }}</span>
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $item->title }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            {{ $item->category_name }} &bull; PIC: {{ $item->pic_division ?: '-' }}
                                        </div>

                                        @if(!empty($item->notes))
                                        <div class="mt-1 p-1 px-2 rounded bg-info-lt border border-info small text-dark" style="font-size: 0.75rem;">
                                            <i class="fas fa-sticky-note text-info me-1"></i> <strong>Catatan Forensik:</strong> {{ $item->notes }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <span class="badge bg-azure-lt small">
                                        <i class="fas fa-paperclip me-1"></i>{{ $item->evidences->count() }} file
                                    </span>
                                    <a href="{{ route('sulteng.items', ['search' => $item->code]) }}" class="btn btn-xs btn-ghost-primary">
                                        Lihat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Modal Drilldown Filter Switcher (Binary: Belum Diterima & Diterima)
function switchModalFilter(status) {
    const groupPending = document.getElementById('drilldownContainerPending');
    const groupDiterima = document.getElementById('drilldownContainerDiterima');

    const btnPending = document.getElementById('btnFilterPending');
    const btnDiterima = document.getElementById('btnFilterDiterima');

    const modalIcon = document.getElementById('drilldownModalIcon');
    const modalTitle = document.getElementById('drilldownModalLabel');
    const btnFullChecklist = document.getElementById('btnOpenFullChecklist');

    if (groupPending) groupPending.classList.add('d-none');
    if (groupDiterima) groupDiterima.classList.add('d-none');

    if (btnPending) btnPending.classList.remove('active');
    if (btnDiterima) btnDiterima.classList.remove('active');

    if (status === 'Diterima') {
        if (groupDiterima) groupDiterima.classList.remove('d-none');
        if (btnDiterima) btnDiterima.classList.add('active');
        if (modalIcon) {
            modalIcon.className = 'avatar avatar-sm bg-success text-white rounded-circle';
            modalIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
        }
        if (modalTitle) modalTitle.textContent = 'Daftar {{ $receivedCount }} Item Diterima / Lengkap (Status Hijau)';
        if (btnFullChecklist) btnFullChecklist.href = "{{ route('sulteng.items', ['status' => 'Diterima']) }}";
    } else {
        // Default to Belum Diterima (Merah)
        if (groupPending) groupPending.classList.remove('d-none');
        if (btnPending) btnPending.classList.add('active');
        if (modalIcon) {
            modalIcon.className = 'avatar avatar-sm bg-danger text-white rounded-circle';
            modalIcon.innerHTML = '<i class="fas fa-times-circle"></i>';
        }
        if (modalTitle) modalTitle.textContent = 'Daftar {{ $pendingCount }} Item Belum Diterima (Status Merah)';
        if (btnFullChecklist) btnFullChecklist.href = "{{ route('sulteng.items', ['status' => 'Belum Diterima']) }}";
    }
}

function openDrilldown(status) {
    switchModalFilter(status);
    const modalEl = document.getElementById('drilldownModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js gagal dimuat.');
        return;
    }

    // Set Default Chart Font
    Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif';
    Chart.defaults.color = '#64748b';

    // -------------------------------------------------------------
    // Chart 1: Status Pemenuhan Donut Chart (Binary: Hijau & Merah)
    // -------------------------------------------------------------
    const statusCtx = document.getElementById('statusDonutChart');
    if (statusCtx) {
        const statusData = @json($statusChart);
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.data,
                    backgroundColor: [
                        '#2fb344', // Green - Diterima
                        '#d63939'  // Red - Belum Diterima
                    ],
                    hoverBackgroundColor: [
                        '#279639',
                        '#b82c2c'
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                onClick: function(event, elements) {
                    if (elements && elements.length > 0) {
                        const index = elements[0].index;
                        const statuses = ['Diterima', 'Belum Diterima'];
                        openDrilldown(statuses[index]);
                    }
                },
                onHover: function(event, elements) {
                    event.native.target.style.cursor = elements && elements.length ? 'pointer' : 'default';
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function (context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.parsed;
                                const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                return ' ' + context.label + ': ' + value + ' item (' + pct + '%) - Klik untuk rincian';
                            }
                        }
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // Chart 2: Category Kesiapan Stacked Bar Chart (Binary: Hijau & Merah)
    // -------------------------------------------------------------
    const catCtx = document.getElementById('categoryBarChart');
    if (catCtx) {
        const catData = @json($categoryChart);
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: catData.labels,
                datasets: [
                    {
                        label: 'Diterima (Hijau)',
                        data: catData.diterima,
                        backgroundColor: '#2fb344',
                        borderRadius: 4,
                        barPercentage: 0.65,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Belum Diterima (Merah)',
                        data: catData.belum,
                        backgroundColor: '#d63939',
                        borderRadius: 4,
                        barPercentage: 0.65,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                onClick: function(event, elements) {
                    if (elements && elements.length > 0) {
                        const elem = elements[0];
                        const code = catData.codes[elem.index];
                        const statuses = ['Diterima', 'Belum Diterima'];
                        const selectedStatus = statuses[elem.datasetIndex];
                        window.location.href = "{{ route('sulteng.items') }}?category=" + encodeURIComponent(code) + "&status=" + encodeURIComponent(selectedStatus);
                    }
                },
                onHover: function(event, elements) {
                    event.native.target.style.cursor = elements && elements.length ? 'pointer' : 'default';
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            stepSize: 2,
                            font: { size: 11 }
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Item Checklist (Klik batang untuk buka checklist)',
                            font: { size: 11, weight: '600' }
                        }
                    },
                    y: {
                        stacked: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11, weight: '600' },
                            color: '#334155'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { size: 12, weight: 'bold' },
                        callbacks: {
                            afterLabel: function() {
                                return 'Klik untuk filter ke lembar checklist';
                            }
                        }
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // Chart 3: PIC Division Accountability Bar Chart (Binary: Hijau & Merah)
    // -------------------------------------------------------------
    const picCtx = document.getElementById('picAccountabilityChart');
    if (picCtx) {
        const picData = @json($picChart);
        new Chart(picCtx, {
            type: 'bar',
            data: {
                labels: picData.labels,
                datasets: [
                    {
                        label: 'Diterima (Hijau)',
                        data: picData.diterima,
                        backgroundColor: '#2fb344',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    },
                    {
                        label: 'Belum Diterima (Merah)',
                        data: picData.belum,
                        backgroundColor: '#d63939',
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                onClick: function(event, elements) {
                    if (elements && elements.length > 0) {
                        const elem = elements[0];
                        const picName = picData.labels[elem.index];
                        window.location.href = "{{ route('sulteng.items') }}?pic=" + encodeURIComponent(picName);
                    }
                },
                onHover: function(event, elements) {
                    event.native.target.style.cursor = elements && elements.length ? 'pointer' : 'default';
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: { size: 11, weight: '600' },
                            color: '#334155'
                        }
                    },
                    y: {
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            stepSize: 2,
                            font: { size: 11 }
                        },
                        title: {
                            display: true,
                            text: 'Jumlah Item Tugas PIC (Klik batang untuk filter PIC)',
                            font: { size: 11, weight: '600' }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            afterBody: function (context) {
                                const idx = context[0].dataIndex;
                                return 'Tingkat Penyelesaian: ' + picData.percent[idx] + '%\nKlik untuk filter checklist PIC';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection

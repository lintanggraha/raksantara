@extends('layouts.tabler')

@section('title', 'Checklist Evaluasi Mandiri Kepatuhan (Self-Assessment) - Raksantara')

@push('styles')
<style>
.chk-page {
    --ink: #0f172a;
    --muted: #475569;
    --line: rgba(15, 23, 42, 0.1);
    --shell: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    color: var(--ink);
    padding: .25rem;
    border-radius: 32px;
    background: var(--shell);
}
.chk-shell { display: grid; gap: 1rem; }
.chk-hero {
    display: grid; grid-template-columns: 1.6fr .9fr; gap: 1.25rem;
    align-items: stretch; min-height: 280px;
    padding: 1.45rem; border-radius: 28px; overflow: hidden; position: relative;
    background:
        radial-gradient(circle at top right, rgba(147, 197, 253, .85), transparent 32%),
        radial-gradient(circle at bottom left, rgba(191, 219, 254, .6), transparent 28%),
        linear-gradient(135deg, #1e3a8a 0%, #2563eb 45%, #3b82f6 100%);
    box-shadow: 0 20px 44px rgba(37, 99, 235, .15);
}
.chk-hero > * { position: relative; z-index: 1; }
.chk-kicker {
    display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .7rem;
    border-radius: 999px; background: rgba(255, 255, 255, .18); color: #eff6ff;
    font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
}
.chk-title { margin: 1rem 0 .7rem; color: #ffffff; font-size: clamp(2rem, 3.8vw, 2.75rem); font-weight: 800; line-height: 1.05; }
.chk-lede { margin: 0; max-width: 680px; color: rgba(239, 246, 255, .9); line-height: 1.65; font-size: .95rem; }

.chk-panel {
    padding: 1.15rem; border-radius: 20px; border: 1px solid var(--line);
    background: #ffffff; box-shadow: 0 14px 28px rgba(15, 23, 42, .04);
}
.chk-panel-head { display: flex; align-items: center; justify-content: space-between; gap: .85rem; margin-bottom: .85rem; }
.chk-panel-head h3 { margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; }

.fw-card {
    padding: .85rem 1rem; border-radius: 16px; border: 1.5px solid var(--line);
    background: #ffffff; cursor: pointer; transition: all .15s ease; text-align: left;
    width: 100%; display: flex; align-items: center; gap: .85rem;
}
.fw-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(15, 23, 42, .06); border-color: #2563eb; }
.fw-card.active { border-color: #2563eb; background: #eff6ff; box-shadow: 0 8px 20px rgba(37, 99, 235, .12); }
.fw-icon {
    width: 2.5rem; height: 2.5rem; border-radius: 12px; display: inline-flex;
    align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}

.status-btn-group .btn-check:checked + .btn-outline-success { background: #dcfce7; color: #166534; border-color: #16a34a; font-weight: 700; }
.status-btn-group .btn-check:checked + .btn-outline-warning { background: #fef3c7; color: #92400e; border-color: #d97706; font-weight: 700; }
.status-btn-group .btn-check:checked + .btn-outline-danger { background: #fee2e2; color: #991b1b; border-color: #dc2626; font-weight: 700; }

.chk-score-pill {
    padding: .5rem 1rem; border-radius: 14px; font-weight: 800; font-size: 1.1rem;
    display: inline-flex; align-items: center; gap: .5rem;
}
</style>
@endpush

@section('content')
<div class="chk-page">
    <div class="chk-shell">
        <!-- Hero Header -->
        <div class="chk-hero">
            <div>
                <span class="chk-kicker"><i class="fas fa-clipboard-check me-1"></i> Analisis Kepatuhan • Audit Mandiri</span>
                <h1 class="chk-title">Checklist Evaluasi Mandiri Kepatuhan</h1>
                <p class="chk-lede">
                    Alat asesmen mandiri (Self-Assessment Checklist) untuk mengevaluasi tingkat pemenuhan kontrol keamanan informasi, tata kelola TI, dan regulasi perbankan. Mendukung penyimpanan status di browser, ekspor laporan spreadsheet XLSX, serta impor data evaluasi offline.
                </p>
            </div>
            <div style="background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .25); border-radius: 20px; padding: 1.25rem; backdrop-filter: blur(8px); display: flex; flex-direction: column; justify-content: center;">
                <label style="font-size: .72rem; font-weight: 700; color: #bfdbfe; text-transform: uppercase;">Kemudahan Audit Offline</label>
                <h3 style="color: #ffffff; font-size: 1.1rem; margin: .4rem 0 .6rem;">Ekspor & Impor Excel</h3>
                <p style="color: rgba(255, 255, 255, .82); font-size: .84rem; line-height: 1.5; margin: 0;">
                    Unduh checklist ke spreadsheet (.xlsx), distribusikan kepada tim operasional / auditor internal, lalu unggah kembali untuk menyinkronkan hasil asesmen secara otomatis.
                </p>
            </div>
        </div>

        <!-- Framework Picker Carousel/Grid -->
        <div class="chk-panel">
            <div class="chk-panel-head">
                <h3><i class="fas fa-layer-group me-2 text-primary"></i> Pilih Standar / Regulasi yang Akan Dievaluasi (13 Framework)</h3>
                <span class="badge bg-blue-lt">Interaktif</span>
            </div>

            <div class="row g-2" id="fw-picker-grid">
                @foreach($availableFrameworks as $index => $fw)
                    <div class="col-md-4 col-lg-3">
                        <button class="fw-card {{ $index === 0 ? 'active' : '' }}" 
                                data-fw-id="{{ $fw['id'] }}" 
                                data-fw-file="{{ $fw['file'] }}"
                                data-fw-name="{{ $fw['name'] }}"
                                data-fw-color="{{ $fw['color'] }}">
                            <div class="fw-icon" style="background: {{ $fw['color'] }}20; color: {{ $fw['color'] }};">
                                <i class="fas {{ $fw['icon'] }}"></i>
                            </div>
                            <div style="min-width: 0;">
                                <strong style="font-size: .88rem; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">{{ $fw['name'] }}</strong>
                                <span style="font-size: .74rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">{{ $fw['desc'] }}</span>
                            </div>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Scoreboard & Action Controls -->
        <div class="chk-panel">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3 class="mb-1" id="current-eval-title"><i class="fas fa-shield-alt text-primary me-2"></i> Evaluasi: ISO 27001:2022</h3>
                    <p class="text-muted mb-0 small" id="current-eval-meta">Memuat persyaratan...</p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button class="btn btn-outline-success" id="btn-export-xlsx">
                        <i class="fas fa-file-excel me-1"></i> Ekspor Excel (.xlsx)
                    </button>
                    <label class="btn btn-outline-primary mb-0" for="file-import-xlsx" style="cursor: pointer;">
                        <i class="fas fa-file-import me-1"></i> Impor Excel (.xlsx)
                        <input type="file" id="file-import-xlsx" accept=".xlsx, .xls" style="display: none;">
                    </label>
                    <button class="btn btn-outline-secondary" id="btn-reset-eval">
                        <i class="fas fa-rotate me-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-bold text-muted text-uppercase">Tingkat Kepatuhan (Compliance Progress)</span>
                    <strong class="text-primary" id="progress-text">0% Selesai</strong>
                </div>
                <div class="progress rounded-pill" style="height: 24px; font-weight: 800; font-size: .8rem;">
                    <div class="progress-bar bg-success" id="bar-sudah" style="width: 0%;">0%</div>
                    <div class="progress-bar bg-warning text-dark" id="bar-parsial" style="width: 0%;">0%</div>
                    <div class="progress-bar bg-danger" id="bar-belum" style="width: 0%;">0%</div>
                </div>

                <div class="row g-2 mt-2 text-center">
                    <div class="col-4">
                        <small class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Sudah: <span id="count-sudah">0</span></small>
                    </div>
                    <div class="col-4">
                        <small class="text-warning fw-bold"><i class="fas fa-triangle-exclamation me-1"></i> Parsial: <span id="count-parsial">0</span></small>
                    </div>
                    <div class="col-4">
                        <small class="text-danger fw-bold"><i class="fas fa-circle-xmark me-1"></i> Belum: <span id="count-belum">0</span></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checklist Table -->
        <div class="chk-panel p-0 overflow-hidden">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
                <div class="input-icon" style="width: 320px;">
                    <span class="input-icon-addon"><i class="fas fa-search"></i></span>
                    <input type="text" id="chk-search" class="form-control" placeholder="Cari id kontrol, nama, atau bukti...">
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary status-filter active" data-filter="all">Semua</button>
                    <button class="btn btn-sm btn-outline-success status-filter" data-filter="Sudah">Sudah</button>
                    <button class="btn btn-sm btn-outline-warning status-filter" data-filter="Parsial">Parsial</button>
                    <button class="btn btn-sm btn-outline-danger status-filter" data-filter="Belum">Belum</button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-top mb-0" id="chk-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%;">Persyaratan / Kontrol</th>
                            <th style="width: 25%;" class="text-center">Status Implementasi</th>
                            <th style="width: 40%;">Catatan & Bukti Pelaksanaan (Audit Evidence)</th>
                        </tr>
                    </thead>
                    <tbody id="chk-tbody">
                        <!-- Rendered via JS -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<!-- SheetJS CDN for high-performance in-browser Excel import/export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
let currentFwId = 'iso27001';
let currentFwFile = 'iso27001.json';
let currentFwName = 'ISO 27001:2022';
let currentItems = [];
let evaluationState = {}; // { id: { status: 'Sudah'|'Parsial'|'Belum', evidence: '...' } }
let currentFilter = 'all';

function getStorageKey(fwId) {
    return 'raksantara_checklist_' + fwId;
}

function loadState() {
    try {
        const raw = localStorage.getItem(getStorageKey(currentFwId));
        evaluationState = raw ? JSON.parse(raw) : {};
    } catch(e) {
        evaluationState = {};
    }
}

function saveState() {
    try {
        localStorage.setItem(getStorageKey(currentFwId), JSON.stringify(evaluationState));
    } catch(e) {}
    updateProgress();
}

async function loadFrameworkData(fwId, fwFile, fwName) {
    currentFwId = fwId;
    currentFwFile = fwFile;
    currentFwName = fwName;

    document.getElementById('current-eval-title').innerHTML = `<i class="fas fa-shield-alt text-primary me-2"></i> Evaluasi: ${fwName}`;
    document.getElementById('current-eval-meta').textContent = 'Mengambil data persyaratan...';

    loadState();

    try {
        const res = await fetch(`/data/${fwFile}`);
        if (!res.ok) throw new Error('File tidak ditemukan');
        let data = await res.json();

        // Normalize if wrapped in an object or array
        if (!Array.isArray(data)) {
            if (data.requirements) data = data.requirements;
            else if (data.controls) data = data.controls;
            else if (data.items) data = data.items;
        }

        currentItems = Array.isArray(data) ? data.map(item => {
            return {
                id: item.id || item.code || item.number || 'REQ',
                name: item.title || item.name || item.domain || 'Persyaratan Kontrol',
                description: item.summary || item.description || item.focus || ''
            };
        }) : [];

        document.getElementById('current-eval-meta').textContent = `${currentItems.length} Persyaratan Kontrol Terdaftar`;
        renderTable();
        updateProgress();
    } catch(err) {
        document.getElementById('current-eval-meta').textContent = 'Gagal memuat file persyaratan.';
        document.getElementById('chk-tbody').innerHTML = `
            <tr>
                <td colspan="3" class="text-center p-4 text-danger">Gagal memuat dataset untuk framework ini.</td>
            </tr>
        `;
    }
}

function renderTable() {
    const tbody = document.getElementById('chk-tbody');
    tbody.innerHTML = '';

    const query = (document.getElementById('chk-search')?.value || '').toLowerCase().trim();

    currentItems.forEach((item, index) => {
        const state = evaluationState[item.id] || { status: 'Belum', evidence: '' };

        if (currentFilter !== 'all' && state.status !== currentFilter) return;

        const matchText = (item.id + ' ' + item.name + ' ' + item.description + ' ' + state.evidence).toLowerCase();
        if (query && !matchText.includes(query)) return;

        const tr = document.createElement('tr');

        // Col 1: Requirement
        const td1 = document.createElement('td');
        td1.className = 'p-3';
        td1.innerHTML = `
            <span class="badge bg-secondary-lt font-monospace mb-1">${item.id}</span>
            <strong class="d-block text-dark" style="font-size: .92rem;">${item.name}</strong>
            <p class="mb-0 text-muted" style="font-size: .8rem; line-height: 1.45;">${item.description}</p>
        `;

        // Col 2: Status radio group
        const td2 = document.createElement('td');
        td2.className = 'p-3 text-center';
        td2.innerHTML = `
            <div class="btn-group status-btn-group" role="group">
                <input type="radio" class="btn-check" name="status-${item.id}" id="sudah-${item.id}" value="Sudah" ${state.status === 'Sudah' ? 'checked' : ''}>
                <label class="btn btn-sm btn-outline-success" for="sudah-${item.id}">Sudah</label>

                <input type="radio" class="btn-check" name="status-${item.id}" id="parsial-${item.id}" value="Parsial" ${state.status === 'Parsial' ? 'checked' : ''}>
                <label class="btn btn-sm btn-outline-warning" for="parsial-${item.id}">Parsial</label>

                <input type="radio" class="btn-check" name="status-${item.id}" id="belum-${item.id}" value="Belum" ${state.status === 'Belum' || !state.status ? 'checked' : ''}>
                <label class="btn btn-sm btn-outline-danger" for="belum-${item.id}">Belum</label>
            </div>
        `;

        // Col 3: Evidence textarea
        const td3 = document.createElement('td');
        td3.className = 'p-3';
        td3.innerHTML = `
            <textarea class="form-control form-control-sm" rows="2" placeholder="Tuliskan catatan implementasi, link dokumen, atau nomor SOP...">${state.evidence || ''}</textarea>
        `;

        // Radio change listener
        td2.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (!evaluationState[item.id]) evaluationState[item.id] = {};
                evaluationState[item.id].status = e.target.value;
                saveState();
            });
        });

        // Textarea input listener
        const txtArea = td3.querySelector('textarea');
        txtArea.addEventListener('input', (e) => {
            if (!evaluationState[item.id]) evaluationState[item.id] = { status: 'Belum' };
            evaluationState[item.id].evidence = e.target.value;
            saveState();
        });

        tr.appendChild(td1);
        tr.appendChild(td2);
        tr.appendChild(td3);
        tbody.appendChild(tr);
    });
}

function updateProgress() {
    let sudah = 0;
    let parsial = 0;
    let belum = 0;

    currentItems.forEach(item => {
        const state = evaluationState[item.id];
        if (state && state.status === 'Sudah') sudah++;
        else if (state && state.status === 'Parsial') parsial++;
        else belum++;
    });

    const total = currentItems.length || 1;
    const sudahPct = Math.round((sudah / total) * 100);
    const parsialPct = Math.round((parsial / total) * 100);
    const belumPct = 100 - sudahPct - parsialPct;

    document.getElementById('bar-sudah').style.width = sudahPct + '%';
    document.getElementById('bar-sudah').textContent = sudahPct > 0 ? sudahPct + '%' : '';
    document.getElementById('bar-parsial').style.width = parsialPct + '%';
    document.getElementById('bar-parsial').textContent = parsialPct > 0 ? parsialPct + '%' : '';
    document.getElementById('bar-belum').style.width = belumPct + '%';
    document.getElementById('bar-belum').textContent = belumPct > 0 ? belumPct + '%' : '';

    document.getElementById('count-sudah').textContent = sudah;
    document.getElementById('count-parsial').textContent = parsial;
    document.getElementById('count-belum').textContent = belum;
    document.getElementById('progress-text').textContent = `${sudahPct}% Terpenuhi (${sudah}/${currentItems.length})`;
}

// Export to XLSX
document.getElementById('btn-export-xlsx')?.addEventListener('click', () => {
    if (!currentItems || currentItems.length === 0) {
        alert('Tidak ada data persyaratan untuk diekspor.');
        return;
    }

    const rows = [
        ['Sumber', 'ID Kontrol', 'Nama Kontrol', 'Deskripsi Persyaratan', 'Status Implementasi', 'Catatan / Bukti']
    ];

    currentItems.forEach(item => {
        const state = evaluationState[item.id] || { status: 'Belum', evidence: '' };
        rows.push([
            currentFwName,
            item.id,
            item.name,
            item.description,
            state.status || 'Belum',
            state.evidence || ''
        ]);
    });

    const ws = XLSX.utils.aoa_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Checklist Kepatuhan");
    XLSX.writeFile(wb, `Checklist_${currentFwId}_${new Date().toISOString().slice(0,10)}.xlsx`);
});

// Import from XLSX
document.getElementById('file-import-xlsx')?.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(evt) {
        try {
            const data = new Uint8Array(evt.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheet];
            const jsonRows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

            if (jsonRows.length < 2) {
                alert('File Excel kosong atau format tidak sesuai.');
                return;
            }

            const headerRow = jsonRows[0].map(h => String(h || '').trim().toLowerCase());
            const idCol = headerRow.findIndex(h => h.includes('id'));
            const statusCol = headerRow.findIndex(h => h.includes('status'));
            const evidenceCol = headerRow.findIndex(h => h.includes('bukti') || h.includes('catatan') || h.includes('evidence'));

            if (idCol === -1 || statusCol === -1) {
                alert('Kolom "ID Kontrol" dan "Status Implementasi" tidak ditemukan dalam file.');
                return;
            }

            let importedCount = 0;
            for (let i = 1; i < jsonRows.length; i++) {
                const row = jsonRows[i];
                const id = String(row[idCol] || '').trim();
                let status = String(row[statusCol] || '').trim();
                const evidence = evidenceCol !== -1 ? String(row[evidenceCol] || '').trim() : '';

                if (id) {
                    if (['sudah', 'complete', 'yes', 'compliant'].includes(status.toLowerCase())) status = 'Sudah';
                    else if (['parsial', 'partial', 'in progress'].includes(status.toLowerCase())) status = 'Parsial';
                    else status = 'Belum';

                    evaluationState[id] = { status, evidence };
                    importedCount++;
                }
            }

            saveState();
            renderTable();
            alert(`Berhasil mengimpor ${importedCount} status kontrol dari Excel.`);
        } catch(err) {
            alert('Gagal memproses file Excel: ' + err.message);
        }
    };
    reader.readAsArrayBuffer(file);
});

// Reset
document.getElementById('btn-reset-eval')?.addEventListener('click', () => {
    if (confirm('Yakin ingin mereset seluruh status evaluasi untuk framework ini?')) {
        evaluationState = {};
        saveState();
        renderTable();
    }
});

// Search & Filter
document.getElementById('chk-search')?.addEventListener('input', renderTable);
document.querySelectorAll('.status-filter').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;
        renderTable();
    });
});

// Framework cards click
document.querySelectorAll('.fw-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.fw-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        loadFrameworkData(card.dataset.fwId, card.dataset.fwFile, card.dataset.fwName);
    });
});

// Initial
loadFrameworkData(currentFwId, currentFwFile, currentFwName);
</script>
@endpush

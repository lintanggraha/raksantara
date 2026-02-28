<?php

namespace App\Services;

use App\Risk;
use App\Support\RiskOwnerAccess;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AuditDashboardDataService
{
    private AuditDashboardImportAdapter $importAdapter;

    public function __construct(?AuditDashboardImportAdapter $importAdapter = null)
    {
        $this->importAdapter = $importAdapter ?: new AuditDashboardImportAdapter();
    }

    public function build(array $filters = []): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);

        return $this->buildFromPptSnapshot($normalizedFilters);
    }

    /**
     * Import-ready pipeline. Parser file akan ditambahkan nanti,
     * tapi hasil parser cukup kirim array rows ke method ini.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function buildFromImportedRows(array $rows, array $filters = []): array
    {
        $normalizedFilters = $this->normalizeFilters($filters);
        $normalizedRows = collect($this->importAdapter->normalize($rows));

        return $this->buildFromRows(
            $normalizedRows,
            $normalizedFilters,
            $this->availableYearsFromRows($normalizedRows),
            'import'
        );
    }

    /**
     * Default dashboard source: fixed PPT snapshot.
     * Risk register tidak dipakai untuk angka dashboard audit saat ini.
     *
     * @param  array{year: int|null, bank: string|null}  $filters
     */
    private function buildFromPptSnapshot(array $filters): array
    {
        $statusColors = $this->statusColors();

        $summaryRows = [
            ['label' => 'Closed', 'count' => 30],
            ['label' => 'Domain Bank', 'count' => 5],
            ['label' => 'On Progress', 'count' => 41],
        ];

        $overdueRows = [
            ['label' => 'Closed', 'count' => 24],
            ['label' => 'Domain Bank', 'count' => 3],
            ['label' => 'On Progress', 'count' => 2],
        ];

        $rekapRows = [
            ['region' => 'Internal Collega', 'bank' => 'COLLEGA', 'year' => '2025', 'auditee' => 'Jaringan Komunikasi Data', 'total' => 2, 'closed' => 0, 'on_progress' => 2, 'domain_bank' => 0],
            ['region' => 'Internal Collega', 'bank' => 'COLLEGA', 'year' => '2025', 'auditee' => 'Operasional', 'total' => 2, 'closed' => 0, 'on_progress' => 2, 'domain_bank' => 0],
            ['region' => 'Internal Collega', 'bank' => 'COLLEGA', 'year' => '2025', 'auditee' => 'Operasional DC & DRC', 'total' => 23, 'closed' => 0, 'on_progress' => 23, 'domain_bank' => 0],
            ['region' => 'Internal Collega', 'bank' => 'COLLEGA', 'year' => '2025', 'auditee' => 'Organisasi & Manajemen', 'total' => 7, 'closed' => 0, 'on_progress' => 7, 'domain_bank' => 0],
            ['region' => 'Internal Collega', 'bank' => 'COLLEGA', 'year' => '2025', 'auditee' => 'Pengembangan Aplikasi', 'total' => 2, 'closed' => 0, 'on_progress' => 2, 'domain_bank' => 0],
            ['region' => 'Internal Collega', 'bank' => 'COLLEGA', 'year' => '2025', 'auditee' => 'Service Desk', 'total' => 3, 'closed' => 0, 'on_progress' => 3, 'domain_bank' => 0],
            ['region' => 'Region 1', 'bank' => 'BANK ACEH', 'year' => '2024', 'auditee' => 'Pentest CBS', 'total' => 7, 'closed' => 7, 'on_progress' => 0, 'domain_bank' => 0],
            ['region' => 'Region 1', 'bank' => 'BANK BENGKULU', 'year' => '2025', 'auditee' => 'Audit Pentest SCV', 'total' => 8, 'closed' => 8, 'on_progress' => 0, 'domain_bank' => 0],
            ['region' => 'Region 1', 'bank' => 'BANK BENGKULU', 'year' => '2025', 'auditee' => 'Pemenuhan KHP TI Bank Bengkulu', 'total' => 5, 'closed' => 3, 'on_progress' => 2, 'domain_bank' => 0],
            ['region' => 'Region 1', 'bank' => 'BANK SUMUT', 'year' => '2024', 'auditee' => 'Audit DC & DRC 2024', 'total' => 8, 'closed' => 6, 'on_progress' => 0, 'domain_bank' => 2],
            ['region' => 'Region 2', 'bank' => 'BANK SULTENG', 'year' => '2024', 'auditee' => 'Audit SCV', 'total' => 9, 'closed' => 6, 'on_progress' => 0, 'domain_bank' => 3],
        ];

        $progressByBankRows = [
            [
                'group' => 'BANK ACEH',
                'total' => 7,
                'counts' => ['Closed' => 7, 'Domain Bank' => 0, 'On Progress' => 0],
                'percentages' => ['Closed' => 100.00, 'Domain Bank' => 0.00, 'On Progress' => 0.00],
            ],
            [
                'group' => 'BANK BENGKULU',
                'total' => 13,
                'counts' => ['Closed' => 11, 'Domain Bank' => 0, 'On Progress' => 2],
                'percentages' => ['Closed' => 84.62, 'Domain Bank' => 0.00, 'On Progress' => 15.38],
            ],
            [
                'group' => 'BANK SULTENG',
                'total' => 9,
                'counts' => ['Closed' => 6, 'Domain Bank' => 3, 'On Progress' => 0],
                'percentages' => ['Closed' => 66.67, 'Domain Bank' => 33.33, 'On Progress' => 0.00],
            ],
            [
                'group' => 'BANK SUMUT',
                'total' => 8,
                'counts' => ['Closed' => 6, 'Domain Bank' => 2, 'On Progress' => 0],
                'percentages' => ['Closed' => 75.00, 'Domain Bank' => 25.00, 'On Progress' => 0.00],
            ],
            [
                'group' => 'COLLEGA',
                'total' => 39,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 39],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
        ];

        $progressByRegionRows = [
            [
                'group' => 'Region',
                'total' => 76,
                'counts' => ['Closed' => 30, 'Domain Bank' => 5, 'On Progress' => 41],
                'percentages' => ['Closed' => 39.47, 'Domain Bank' => 6.58, 'On Progress' => 53.95],
            ],
        ];

        $progressByProductRows = [
            [
                'group' => 'ALL DIVISI',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'CBS CONVEN, CBS SYARIAH',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'CBS SYARIAH',
                'total' => 7,
                'counts' => ['Closed' => 7, 'Domain Bank' => 0, 'On Progress' => 0],
                'percentages' => ['Closed' => 100.00, 'Domain Bank' => 0.00, 'On Progress' => 0.00],
            ],
            [
                'group' => 'CR',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'CR, ITOM',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'DC',
                'total' => 1,
                'counts' => ['Closed' => 1, 'Domain Bank' => 0, 'On Progress' => 0],
                'percentages' => ['Closed' => 100.00, 'Domain Bank' => 0.00, 'On Progress' => 0.00],
            ],
            [
                'group' => 'HCFL',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'HCFL, CR',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'HCFL, INFRA',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'INFRA',
                'total' => 29,
                'counts' => ['Closed' => 3, 'Domain Bank' => 0, 'On Progress' => 26],
                'percentages' => ['Closed' => 10.34, 'Domain Bank' => 0.00, 'On Progress' => 89.66],
            ],
            [
                'group' => 'INFRA & ITOM',
                'total' => 4,
                'counts' => ['Closed' => 3, 'Domain Bank' => 1, 'On Progress' => 0],
                'percentages' => ['Closed' => 75.00, 'Domain Bank' => 25.00, 'On Progress' => 0.00],
            ],
            [
                'group' => 'INFRA, BUSINESS SUPPORT',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'ITOM',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'RFR',
                'total' => 16,
                'counts' => ['Closed' => 13, 'Domain Bank' => 3, 'On Progress' => 0],
                'percentages' => ['Closed' => 81.25, 'Domain Bank' => 18.75, 'On Progress' => 0.00],
            ],
            [
                'group' => 'SURROUNDING',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
            [
                'group' => 'SURROUNDING, INFRA',
                'total' => 1,
                'counts' => ['Closed' => 0, 'Domain Bank' => 0, 'On Progress' => 1],
                'percentages' => ['Closed' => 0.00, 'Domain Bank' => 0.00, 'On Progress' => 100.00],
            ],
        ];

        $detailRows = [
            ['bank' => 'BANK ACEH', 'date' => '28', 'month' => 'March', 'year' => '2024', 'product' => 'CBS SYARIAH', 'product_name' => 'OLIBS 724 BE', 'status_detail' => 'Closed 100%', 'pic' => 'CBS SYARIAH', 'log_css' => 'DK202402055', 'status_summary' => 'Closed', 'task' => 'Evaluasi konfigurasi keamanan web service Back End Core Banking.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK ACEH', 'date' => '29', 'month' => 'February', 'year' => '2024', 'product' => 'CBS SYARIAH', 'product_name' => 'OLIBS 724 BE', 'status_detail' => 'Closed 100%', 'pic' => 'CBS SYARIAH', 'log_css' => 'DK202402053', 'status_summary' => 'Closed', 'task' => 'Perbaikan kontrol HTTP method dan hardening policy aplikasi.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK BENGKULU', 'date' => '9', 'month' => 'September', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'CBS', 'status_detail' => 'Closed 100%', 'pic' => 'INFRA', 'log_css' => 'GP202507058', 'status_summary' => 'Closed', 'task' => 'Penerapan firewall policy dan perbaikan konfigurasi jaringan.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK BENGKULU', 'date' => '9', 'month' => 'September', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'FDS', 'status_detail' => 'On Progress', 'pic' => 'INFRA', 'log_css' => 'GP202507063', 'status_summary' => 'On Progress', 'task' => 'Monitoring security incident dan tuning alert di FDS.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK BENGKULU', 'date' => '9', 'month' => 'September', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'ISO', 'status_detail' => 'Closed 100%', 'pic' => 'INFRA', 'log_css' => 'GP202507060', 'status_summary' => 'Closed', 'task' => 'Penyesuaian dokumen ISO sesuai hasil audit terbaru.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK BENGKULU', 'date' => '9', 'month' => 'September', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'OS', 'status_detail' => 'On Progress', 'pic' => 'INFRA', 'log_css' => 'GP202507062', 'status_summary' => 'On Progress', 'task' => 'Hardening OS dan patching berkala server kritikal.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK BENGKULU', 'date' => '9', 'month' => 'September', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'SOP', 'status_detail' => 'Closed 100%', 'pic' => 'INFRA', 'log_css' => 'GP202507059', 'status_summary' => 'Closed', 'task' => 'Pembaruan SOP operasional sesuai catatan audit.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK BENGKULU', 'date' => '17', 'month' => 'July', 'year' => '2024', 'product' => 'RFR', 'product_name' => 'SCV', 'status_detail' => 'Closed 100%', 'pic' => 'RFR', 'log_css' => '0', 'status_summary' => 'Closed', 'task' => 'Penutupan celah input validation dan dependency vulnerability.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK SULTENG', 'date' => '31', 'month' => 'May', 'year' => '2025', 'product' => 'RFR', 'product_name' => 'SCV', 'status_detail' => 'Closed 100%', 'pic' => 'RFR', 'log_css' => '0', 'status_summary' => 'Closed', 'task' => 'Perbaikan kontrol kredensial dan enkripsi data.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK SULTENG', 'date' => '12', 'month' => 'June', 'year' => '2024', 'product' => 'DC', 'product_name' => 'SCV', 'status_detail' => 'Closed 100%', 'pic' => 'ITOM', 'log_css' => 'CP202405052', 'status_summary' => 'Closed', 'task' => 'Perbaikan keandalan storage dan backup data center.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK SULTENG', 'date' => '12', 'month' => 'June', 'year' => '2024', 'product' => 'RFR', 'product_name' => 'SCV', 'status_detail' => 'Closed 100%', 'pic' => 'REGION 2', 'log_css' => 'CK202405005', 'status_summary' => 'Domain Bank', 'task' => 'Penyelesaian temuan yang memerlukan aksi domain bank.', 'assigned_to' => 'Null', 'is_overdue' => true],
            ['bank' => 'BANK SUMUT', 'date' => '-', 'month' => 'Null', 'year' => '2024', 'product' => 'INFRA & ITOM', 'product_name' => 'INFRA & ITOM', 'status_detail' => 'Closed 100%', 'pic' => 'INFRA & ITOM', 'log_css' => '0', 'status_summary' => 'Closed', 'task' => 'Improvement kapasitas DRC dan sinkronisasi backup.', 'assigned_to' => 'Null', 'is_overdue' => false],
            ['bank' => 'COLLEGA', 'date' => '-', 'month' => 'Null', 'year' => '2025', 'product' => 'ALL DIVISI', 'product_name' => 'ALL DIVISI', 'status_detail' => 'On Progress', 'pic' => 'ALL DIVISI', 'log_css' => '0', 'status_summary' => 'On Progress', 'task' => 'Persiapan pembaruan struktur organisasi dan dokumen internal.', 'assigned_to' => 'Null', 'is_overdue' => false],
            ['bank' => 'COLLEGA', 'date' => '-', 'month' => 'Null', 'year' => '2025', 'product' => 'CR', 'product_name' => 'CR, ITOM', 'status_detail' => 'On Progress', 'pic' => 'CR, ITOM', 'log_css' => '0', 'status_summary' => 'On Progress', 'task' => 'Penguatan proses incident response dan monitoring akses.', 'assigned_to' => 'Null', 'is_overdue' => false],
            ['bank' => 'COLLEGA', 'date' => '-', 'month' => 'Null', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'INFRA', 'status_detail' => 'On Progress', 'pic' => 'INFRA', 'log_css' => '0', 'status_summary' => 'On Progress', 'task' => 'Peningkatan reliability DRC dan konfigurasi server.', 'assigned_to' => 'Null', 'is_overdue' => false],
            ['bank' => 'COLLEGA', 'date' => '-', 'month' => 'Null', 'year' => '2025', 'product' => 'SURROUNDING', 'product_name' => 'SURROUNDING', 'status_detail' => 'On Progress', 'pic' => 'SURROUNDING', 'log_css' => '0', 'status_summary' => 'On Progress', 'task' => 'Perbaikan integrasi aplikasi surrounding dengan CBS.', 'assigned_to' => 'Null', 'is_overdue' => false],
        ];

        $mapPoints = [
            ['bank' => 'BANK ACEH', 'lat' => (float) $this->coordinatesByBank('BANK ACEH')['lat'], 'lng' => (float) $this->coordinatesByBank('BANK ACEH')['lng'], 'total' => 7, 'closed' => 7, 'domain_bank' => 0, 'on_progress' => 0],
            ['bank' => 'BANK BENGKULU', 'lat' => (float) $this->coordinatesByBank('BANK BENGKULU')['lat'], 'lng' => (float) $this->coordinatesByBank('BANK BENGKULU')['lng'], 'total' => 13, 'closed' => 11, 'domain_bank' => 0, 'on_progress' => 2],
            ['bank' => 'BANK SULTENG', 'lat' => (float) $this->coordinatesByBank('BANK SULTENG')['lat'], 'lng' => (float) $this->coordinatesByBank('BANK SULTENG')['lng'], 'total' => 9, 'closed' => 6, 'domain_bank' => 3, 'on_progress' => 0],
            ['bank' => 'BANK SUMUT', 'lat' => (float) $this->coordinatesByBank('BANK SUMUT')['lat'], 'lng' => (float) $this->coordinatesByBank('BANK SUMUT')['lng'], 'total' => 8, 'closed' => 6, 'domain_bank' => 2, 'on_progress' => 0],
            ['bank' => 'COLLEGA', 'lat' => (float) $this->coordinatesByBank('COLLEGA')['lat'], 'lng' => (float) $this->coordinatesByBank('COLLEGA')['lng'], 'total' => 39, 'closed' => 0, 'domain_bank' => 0, 'on_progress' => 39],
        ];

        $dueByBank = [
            ['group' => 'BANK ACEH', '<=6 Bulan' => 0, '<=9 Bulan' => 0, '>9 Bulan' => 7, 'grand_total' => 7],
            ['group' => 'BANK BENGKULU', '<=6 Bulan' => 5, '<=9 Bulan' => 1, '>9 Bulan' => 7, 'grand_total' => 13],
            ['group' => 'BANK SULTENG', '<=6 Bulan' => 0, '<=9 Bulan' => 0, '>9 Bulan' => 9, 'grand_total' => 9],
            ['group' => 'Grand Total', '<=6 Bulan' => 5, '<=9 Bulan' => 1, '>9 Bulan' => 23, 'grand_total' => 29, 'is_total' => true],
        ];

        $dueByDivision = [
            ['group' => 'CBS SYARIAH', '<=6 Bulan' => 0, '<=9 Bulan' => 0, '>9 Bulan' => 7, 'grand_total' => 7],
            ['group' => 'INFRA', '<=6 Bulan' => 5, '<=9 Bulan' => 0, '>9 Bulan' => 0, 'grand_total' => 5],
            ['group' => 'ITOM', '<=6 Bulan' => 0, '<=9 Bulan' => 0, '>9 Bulan' => 1, 'grand_total' => 1],
            ['group' => 'REGION 2', '<=6 Bulan' => 0, '<=9 Bulan' => 0, '>9 Bulan' => 8, 'grand_total' => 8],
            ['group' => 'RFR', '<=6 Bulan' => 0, '<=9 Bulan' => 1, '>9 Bulan' => 7, 'grand_total' => 8],
            ['group' => 'Grand Total', '<=6 Bulan' => 5, '<=9 Bulan' => 1, '>9 Bulan' => 23, 'grand_total' => 29, 'is_total' => true],
        ];

        $dueByProduct = [
            ['group' => 'CBS SYARIAH', '<=6 Bulan' => 0, '<=9 Bulan' => 0, '>9 Bulan' => 7, 'grand_total' => 7],
            ['group' => 'DC', '<=6 Bulan' => 0, '<=9 Bulan' => 0, '>9 Bulan' => 1, 'grand_total' => 1],
            ['group' => 'INFRA', '<=6 Bulan' => 5, '<=9 Bulan' => 0, '>9 Bulan' => 0, 'grand_total' => 5],
            ['group' => 'RFR', '<=6 Bulan' => 0, '<=9 Bulan' => 1, '>9 Bulan' => 15, 'grand_total' => 16],
            ['group' => 'Grand Total', '<=6 Bulan' => 5, '<=9 Bulan' => 1, '>9 Bulan' => 23, 'grand_total' => 29, 'is_total' => true],
        ];

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'source' => 'snapshot',
            'period' => [
                'month_label' => 'Feb 2026',
                'week_label' => 'W2',
                'overdue_until' => '2/6/2026',
            ],
            'filters' => [
                'selected_year' => null,
                'selected_bank' => null,
                'years' => [],
                'banks' => [],
            ],
            'summary_cards' => [
                'total_temuan' => [
                    'title' => 'Total Temuan',
                    'rows' => $summaryRows,
                    'grand_total' => $this->summaryTotal($summaryRows),
                ],
                'resume_temuan' => [
                    'title' => 'Resume Temuan',
                    'rows' => $summaryRows,
                    'grand_total' => $this->summaryTotal($summaryRows),
                ],
                'temuan_jatuh_tempo' => [
                    'title' => 'Temuan Jatuh Tempo',
                    'rows' => $overdueRows,
                    'grand_total' => $this->summaryTotal($overdueRows),
                ],
            ],
            'executive_actions' => $this->executiveActions(),
            'rekap' => [
                'rows' => $rekapRows,
                'bar_chart' => $this->buildRekapBarChart($rekapRows),
            ],
            'maps' => [
                'points' => $mapPoints,
            ],
            'progress' => [
                'by_bank' => ['rows' => $progressByBankRows],
                'by_region' => ['rows' => $progressByRegionRows],
                'by_product' => ['rows' => $progressByProductRows],
                'status_by_bank_chart' => $this->buildStatusByGroupChart($progressByBankRows, 'group'),
                'region_percentage_chart' => $this->buildStatusPercentageChart($progressByRegionRows),
            ],
            'trend' => [
                'created' => [
                    'labels' => ['BANK ACEH', 'BANK BENGKULU', 'BANK SULTENG', 'BANK SUMUT', 'COLLEGA'],
                    'datasets' => [
                        [
                            'label' => 'Closed',
                            'borderColor' => $statusColors['Closed'] ?? '#f59e0b',
                            'backgroundColor' => $statusColors['Closed'] ?? '#f59e0b',
                            'data' => [7, 11, 6, 6, 0],
                        ],
                        [
                            'label' => 'Domain Bank',
                            'borderColor' => $statusColors['Domain Bank'] ?? '#ef4444',
                            'backgroundColor' => $statusColors['Domain Bank'] ?? '#ef4444',
                            'data' => [0, 0, 3, 2, 0],
                        ],
                        [
                            'label' => 'On Progress',
                            'borderColor' => $statusColors['On Progress'] ?? '#f9a8d4',
                            'backgroundColor' => $statusColors['On Progress'] ?? '#f9a8d4',
                            'data' => [0, 2, 0, 0, 39],
                        ],
                    ],
                ],
                'closed' => [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'Closed',
                            'borderColor' => $statusColors['Closed'] ?? '#f59e0b',
                            'backgroundColor' => $statusColors['Closed'] ?? '#f59e0b',
                            'data' => [],
                        ],
                    ],
                ],
            ],
            'detail_outstanding' => [
                'rows' => $detailRows,
            ],
            'duedate_maturity' => [
                'by_bank' => $dueByBank,
                'by_division' => $dueByDivision,
                'by_product' => $dueByProduct,
                'pie_by_bank' => [
                    ['label' => 'BANK SULTENG >9 Bulan', 'count' => 9],
                    ['label' => 'BANK BENGKULU >9 Bulan', 'count' => 7],
                    ['label' => 'BANK ACEH >9 Bulan', 'count' => 7],
                    ['label' => 'BANK BENGKULU <=6 Bulan', 'count' => 5],
                    ['label' => 'BANK BENGKULU <=9 Bulan', 'count' => 1],
                ],
                'pie_by_division' => [
                    ['label' => 'REGION 2 >9 Bulan', 'count' => 8],
                    ['label' => 'RFR >9 Bulan', 'count' => 7],
                    ['label' => 'CBS SYARIAH >9 Bulan', 'count' => 7],
                    ['label' => 'INFRA <=6 Bulan', 'count' => 5],
                    ['label' => 'RFR <=9 Bulan', 'count' => 1],
                    ['label' => 'ITOM >9 Bulan', 'count' => 1],
                ],
                'pie_by_product' => [
                    ['label' => 'SCV >9 Bulan', 'count' => 16],
                    ['label' => 'OLIBS 724 BE >9 Bulan', 'count' => 7],
                    ['label' => 'SOP <=6 Bulan', 'count' => 3],
                    ['label' => 'SCV <=9 Bulan', 'count' => 1],
                    ['label' => 'CBS <=6 Bulan', 'count' => 1],
                    ['label' => 'ISO <=6 Bulan', 'count' => 1],
                ],
                'grand_total' => 29,
            ],
            'status_colors' => $statusColors,
        ];
    }

    /**
     * @param  array{year: int|null, bank: string|null}  $filters
     * @param  array<int, int>  $years
     */
    private function buildFromRows(Collection $rows, array $filters, array $years, string $source): array
    {
        $rows = $rows->map(function ($row) {
            return $this->sanitizePreparedRow($row);
        })->filter(function ($row) {
            return is_array($row);
        })->values();

        $selectedYear = $filters['year'];
        $selectedBank = $this->normalizeSelectedBank($filters['bank']);

        if (!is_null($selectedYear)) {
            $rows = $rows->filter(function (array $row) use ($selectedYear) {
                return (int) ($row['year'] ?? 0) === (int) $selectedYear;
            })->values();
        }

        if (!is_null($selectedBank)) {
            $rows = $rows->filter(function (array $row) use ($selectedBank) {
                return strtoupper((string) ($row['bank'] ?? '')) === $selectedBank;
            })->values();
        }

        $statusSummary = $this->buildStatusSummary($rows);
        $overdueSummary = $this->buildOverdueSummary($rows);
        $rekapRows = $this->buildRekapRows($rows);
        $progressByBank = $this->buildPercentageMatrix($rows, 'bank');
        $progressByRegion = $this->buildPercentageMatrix($rows, 'region');
        $progressByProduct = $this->buildPercentageMatrix($rows, 'product');
        $trendCreated = $this->buildTrendData($rows, $selectedYear, false);
        $trendClosed = $this->buildTrendData($rows, $selectedYear, true);

        $today = Carbon::today();
        $activeMonthLabel = is_null($selectedYear)
            ? $today->isoFormat('MMMM YYYY')
            : Carbon::create($selectedYear, (int) $today->format('m'), 1)->isoFormat('MMMM YYYY');

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'source' => $source,
            'period' => [
                'month_label' => $activeMonthLabel,
                'week_label' => 'W' . $today->weekOfMonth,
                'overdue_until' => $today->format('j/n/Y'),
            ],
            'filters' => [
                'selected_year' => $selectedYear,
                'selected_bank' => $selectedBank,
                'years' => $years,
                'banks' => $this->availableBanks($rows),
            ],
            'summary_cards' => [
                'total_temuan' => [
                    'title' => 'Total Temuan',
                    'rows' => $statusSummary,
                    'grand_total' => $this->summaryTotal($statusSummary),
                ],
                'resume_temuan' => [
                    'title' => 'Resume Temuan',
                    'rows' => $statusSummary,
                    'grand_total' => $this->summaryTotal($statusSummary),
                ],
                'temuan_jatuh_tempo' => [
                    'title' => 'Temuan Jatuh Tempo',
                    'rows' => $overdueSummary,
                    'grand_total' => $this->summaryTotal($overdueSummary),
                ],
            ],
            'executive_actions' => $this->executiveActions(),
            'rekap' => [
                'rows' => $rekapRows,
                'bar_chart' => $this->buildRekapBarChart($rekapRows),
            ],
            'maps' => [
                'points' => $this->buildMapPoints($rows),
            ],
            'progress' => [
                'by_bank' => $progressByBank,
                'by_region' => $progressByRegion,
                'by_product' => $progressByProduct,
                'status_by_bank_chart' => $this->buildStatusByGroupChart($progressByBank['rows'], 'group'),
                'region_percentage_chart' => $this->buildStatusPercentageChart($progressByRegion['rows']),
            ],
            'trend' => [
                'created' => $trendCreated,
                'closed' => $trendClosed,
            ],
            'detail_outstanding' => [
                'rows' => $this->buildDetailRows($rows),
            ],
            'duedate_maturity' => $this->buildDueMaturityData($rows),
            'status_colors' => $this->statusColors(),
        ];
    }

    /**
     * @return array{year: int|null, bank: string|null}
     */
    private function normalizeFilters(array $filters): array
    {
        $year = null;
        $bank = null;

        if (array_key_exists('year', $filters) && is_numeric($filters['year'])) {
            $candidate = (int) $filters['year'];
            if ($candidate >= 2000 && $candidate <= 2100) {
                $year = $candidate;
            }
        }

        if (array_key_exists('bank', $filters)) {
            $candidate = strtoupper(trim((string) $filters['bank']));
            if ($candidate !== '' && $candidate !== 'ALL') {
                $bank = $candidate;
            }
        }

        return [
            'year' => $year,
            'bank' => $bank,
        ];
    }

    private function normalizeSelectedBank(?string $bank): ?string
    {
        if (is_null($bank)) {
            return null;
        }

        $candidate = strtoupper(trim($bank));

        return ($candidate === '' || $candidate === 'ALL') ? null : $candidate;
    }

    private function loadRisks(?int $year): Collection
    {
        $query = Risk::with(['category', 'creator', 'treatments'])->orderByDesc('created_at');

        if (!is_null($year)) {
            $query->where(function ($nested) use ($year) {
                $nested->where('year', $year)
                    ->orWhereYear('created_at', $year);
            });
        }

        return $query->get();
    }

    private function transformRisk(Risk $risk): array
    {
        $createdAt = $this->safeCarbon(optional($risk)->created_at);
        $updatedAt = $this->safeCarbon(optional($risk)->updated_at);
        $year = (int) ($risk->year ?: optional($createdAt)->format('Y') ?: now()->format('Y'));
        $division = RiskOwnerAccess::displayLabel($risk->risk_owner, 'Unknown');

        $bank = $this->resolveBankLabel([
            (string) $risk->risk_title,
            (string) $risk->risk_description,
            (string) $risk->risk_owner,
            (string) optional($risk->category)->name,
        ]);

        $statusLabel = $this->normalizeStatus((string) $risk->status, (string) $risk->risk_description);
        $statusDetail = $statusLabel === 'Closed' ? 'Closed 100%' : $statusLabel;

        $product = $this->resolveProductLabel((string) $risk->risk_owner, (string) $risk->risk_title, (string) $risk->risk_description);

        $orderedTreatments = collect($risk->treatments ?? [])->sortBy(function ($treatment) {
            $deadline = $this->safeCarbon(optional($treatment)->deadline);

            return is_null($deadline) ? PHP_INT_MAX : $deadline->timestamp;
        })->values();

        $primaryTreatment = $orderedTreatments->first();
        $taskText = trim((string) (optional($primaryTreatment)->description ?: $risk->risk_description ?: $risk->risk_title ?: '-'));
        $assignedTo = trim((string) (optional($primaryTreatment)->pic ?: optional($risk->creator)->name ?: '-'));

        $overdueItems = [];
        foreach ($orderedTreatments as $treatment) {
            $deadline = $this->safeCarbon(optional($treatment)->deadline);
            if (is_null($deadline) || $deadline->greaterThanOrEqualTo(Carbon::today())) {
                continue;
            }

            $months = (int) floor($deadline->diffInDays(Carbon::today()) / 30);
            $overdueItems[] = [
                'bucket' => $this->resolveAgingBucket($months),
                'months_overdue' => $months,
                'deadline' => $deadline->format('Y-m-d'),
            ];
        }

        $nearestDeadline = collect($overdueItems)->pluck('deadline')->filter()->sort()->first();

        return [
            'risk_db_id' => (int) $risk->id,
            'risk_id' => (string) ($risk->risk_id ?: ('RISK-' . $risk->id)),
            'bank' => $bank,
            'region' => $this->resolveRegionFromBank($bank),
            'year' => $year,
            'auditee' => $division,
            'division' => $division,
            'product' => $product,
            'product_name' => trim((string) ($risk->risk_title ?: '-')),
            'status_label' => $statusLabel,
            'status_detail' => $statusDetail,
            'task' => $taskText === '' ? '-' : $taskText,
            'assigned_to' => $assignedTo === '' ? '-' : $assignedTo,
            'pic' => trim((string) (optional($primaryTreatment)->pic ?: '-')) ?: '-',
            'created_at_obj' => $createdAt,
            'created_day' => is_null($createdAt) ? '-' : $createdAt->format('d'),
            'created_month' => is_null($createdAt) ? '-' : $createdAt->isoFormat('MMMM'),
            'created_year' => is_null($createdAt) ? $year : (int) $createdAt->format('Y'),
            'closed_reference_at_obj' => $statusLabel === 'Closed' ? $updatedAt : null,
            'is_overdue' => !empty($overdueItems),
            'overdue_items' => $overdueItems,
            'overdue_until' => $nearestDeadline,
            'total' => 1,
            '_source' => 'database',
        ];
    }

    /**
     * @param  mixed  $row
     * @return array<string, mixed>|null
     */
    private function sanitizePreparedRow($row): ?array
    {
        if (!is_array($row)) {
            return null;
        }

        $division = trim((string) ($row['division'] ?? $row['auditee'] ?? 'Unknown'));
        if ($division === '') {
            $division = 'Unknown';
        }

        $title = trim((string) ($row['product_name'] ?? ''));
        $taskText = trim((string) ($row['task'] ?? ''));
        $ownerSource = trim((string) ($row['auditee'] ?? $row['division'] ?? ''));

        $bankRaw = trim((string) ($row['bank'] ?? ''));
        $bank = $this->resolveBankLabel([$bankRaw, $title, $taskText, $ownerSource]);

        $status = $this->normalizeStatus(
            (string) ($row['status_label'] ?? $row['status'] ?? ''),
            (string) ($row['status_detail'] ?? $taskText)
        );

        $createdAt = $this->safeCarbon($row['created_at_obj'] ?? $row['created_at'] ?? null);
        $closedAt = $this->safeCarbon($row['closed_reference_at_obj'] ?? $row['closed_at'] ?? null);
        $year = (int) ($row['year'] ?? (optional($createdAt)->format('Y') ?: now()->format('Y')));

        $productRaw = trim((string) ($row['product'] ?? ''));
        $product = $productRaw === ''
            ? $this->resolveProductLabel($ownerSource, $title, $taskText)
            : $productRaw;

        $statusDetail = trim((string) ($row['status_detail'] ?? ''));
        if ($statusDetail === '') {
            $statusDetail = $status === 'Closed' ? 'Closed 100%' : $status;
        }

        $overdueItems = $this->normalizeOverdueItemsArray($row['overdue_items'] ?? []);
        $nearestDeadline = collect($overdueItems)->pluck('deadline')->filter()->sort()->first();

        return [
            'risk_db_id' => (int) ($row['risk_db_id'] ?? 0),
            'risk_id' => (string) ($row['risk_id'] ?? $row['log_css'] ?? '-'),
            'bank' => $bank,
            'region' => trim((string) ($row['region'] ?? '')) ?: $this->resolveRegionFromBank($bank),
            'year' => $year,
            'auditee' => trim((string) ($row['auditee'] ?? $division)) ?: 'Unknown',
            'division' => $division,
            'product' => $product,
            'product_name' => $title !== '' ? $title : '-',
            'status_label' => $status,
            'status_detail' => $statusDetail,
            'task' => $taskText !== '' ? $taskText : '-',
            'assigned_to' => trim((string) ($row['assigned_to'] ?? '-')) ?: '-',
            'pic' => trim((string) ($row['pic'] ?? '-')) ?: '-',
            'created_at_obj' => $createdAt,
            'created_day' => is_null($createdAt) ? '-' : $createdAt->format('d'),
            'created_month' => is_null($createdAt) ? '-' : $createdAt->isoFormat('MMMM'),
            'created_year' => is_null($createdAt) ? $year : (int) $createdAt->format('Y'),
            'closed_reference_at_obj' => $status === 'Closed' ? $closedAt : null,
            'is_overdue' => !empty($overdueItems),
            'overdue_items' => $overdueItems,
            'overdue_until' => $nearestDeadline,
            'total' => (int) ($row['total'] ?? 1) > 0 ? (int) ($row['total'] ?? 1) : 1,
            '_source' => (string) ($row['_source'] ?? 'normalized'),
        ];
    }

    /**
     * @param  mixed  $items
     * @return array<int, array{bucket: string, months_overdue: int, deadline: string}>
     */
    private function normalizeOverdueItemsArray($items): array
    {
        if (!is_array($items)) {
            return [];
        }

        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $deadlineRaw = trim((string) ($item['deadline'] ?? ''));
            $months = is_numeric($item['months_overdue'] ?? null) ? (int) $item['months_overdue'] : 0;
            $bucket = trim((string) ($item['bucket'] ?? ''));

            if ($bucket === '' && $months <= 0 && $deadlineRaw === '') {
                continue;
            }

            if ($bucket === '') {
                $bucket = $this->resolveAgingBucket($months);
            }

            if ($deadlineRaw !== '') {
                $deadline = $this->safeCarbon($deadlineRaw);
                if ($deadline instanceof Carbon) {
                    $deadlineRaw = $deadline->format('Y-m-d');
                    if ($months <= 0 && $deadline->lessThan(Carbon::today())) {
                        $months = (int) floor($deadline->diffInDays(Carbon::today()) / 30);
                    }
                }
            }

            $result[] = [
                'bucket' => $bucket,
                'months_overdue' => $months,
                'deadline' => $deadlineRaw,
            ];
        }

        return $result;
    }

    private function buildStatusSummary(Collection $rows): array
    {
        $summary = [];
        foreach ($this->statusOrder() as $status) {
            $summary[] = [
                'label' => $status,
                'count' => (int) $rows->where('status_label', $status)->count(),
            ];
        }

        return $summary;
    }

    private function buildOverdueSummary(Collection $rows): array
    {
        $overdueRows = $rows->filter(function ($row) {
            return (bool) ($row['is_overdue'] ?? false);
        });

        $summary = [];
        foreach ($this->statusOrder() as $status) {
            $summary[] = [
                'label' => $status,
                'count' => (int) $overdueRows->where('status_label', $status)->count(),
            ];
        }

        return $summary;
    }

    private function buildRekapRows(Collection $rows): array
    {
        $grouped = $rows->groupBy(function ($row) {
            return implode('|', [
                (string) ($row['region'] ?? '-'),
                (string) ($row['bank'] ?? '-'),
                (string) ($row['year'] ?? '-'),
                (string) ($row['auditee'] ?? '-'),
            ]);
        });

        $result = [];
        foreach ($grouped as $groupRows) {
            $first = $groupRows->first();
            $result[] = [
                'region' => (string) ($first['region'] ?? '-'),
                'bank' => (string) ($first['bank'] ?? '-'),
                'year' => (string) ($first['year'] ?? '-'),
                'auditee' => (string) ($first['auditee'] ?? '-'),
                'total' => (int) $groupRows->count(),
                'closed' => (int) $groupRows->where('status_label', 'Closed')->count(),
                'on_progress' => (int) $groupRows->where('status_label', 'On Progress')->count(),
                'domain_bank' => (int) $groupRows->where('status_label', 'Domain Bank')->count(),
            ];
        }

        usort($result, function ($a, $b) {
            return strcmp(
                strtoupper((string) ($a['region'] . '|' . $a['bank'] . '|' . $a['auditee'])),
                strtoupper((string) ($b['region'] . '|' . $b['bank'] . '|' . $b['auditee']))
            );
        });

        return $result;
    }

    private function buildRekapBarChart(array $rekapRows): array
    {
        return [
            'labels' => array_map(function ($row) {
                return (string) ($row['bank'] . ' - ' . Str::limit((string) $row['auditee'], 24));
            }, $rekapRows),
            'datasets' => [
                [
                    'label' => 'Total',
                    'backgroundColor' => '#3b82f6',
                    'data' => array_map(fn ($row) => (int) ($row['total'] ?? 0), $rekapRows),
                ],
                [
                    'label' => 'Closed',
                    'backgroundColor' => $this->statusColors()['Closed'] ?? '#f59e0b',
                    'data' => array_map(fn ($row) => (int) ($row['closed'] ?? 0), $rekapRows),
                ],
                [
                    'label' => 'On Progress',
                    'backgroundColor' => $this->statusColors()['On Progress'] ?? '#f9a8d4',
                    'data' => array_map(fn ($row) => (int) ($row['on_progress'] ?? 0), $rekapRows),
                ],
                [
                    'label' => 'Domain Bank',
                    'backgroundColor' => $this->statusColors()['Domain Bank'] ?? '#ef4444',
                    'data' => array_map(fn ($row) => (int) ($row['domain_bank'] ?? 0), $rekapRows),
                ],
            ],
        ];
    }

    private function buildMapPoints(Collection $rows): array
    {
        $points = [];
        foreach ($rows->groupBy('bank') as $bank => $bankRows) {
            $coords = $this->coordinatesByBank((string) $bank);
            $points[] = [
                'bank' => (string) $bank,
                'lat' => (float) $coords['lat'],
                'lng' => (float) $coords['lng'],
                'total' => (int) $bankRows->count(),
                'closed' => (int) $bankRows->where('status_label', 'Closed')->count(),
                'domain_bank' => (int) $bankRows->where('status_label', 'Domain Bank')->count(),
                'on_progress' => (int) $bankRows->where('status_label', 'On Progress')->count(),
            ];
        }

        usort($points, fn ($a, $b) => strcmp($a['bank'], $b['bank']));

        return $points;
    }

    private function buildPercentageMatrix(Collection $rows, string $groupKey): array
    {
        $groups = $rows->groupBy($groupKey)->map(function (Collection $groupRows, $group) {
            $total = (int) $groupRows->count();
            $closed = (int) $groupRows->where('status_label', 'Closed')->count();
            $domain = (int) $groupRows->where('status_label', 'Domain Bank')->count();
            $progress = (int) $groupRows->where('status_label', 'On Progress')->count();

            return [
                'group' => (string) $group,
                'total' => $total,
                'counts' => [
                    'Closed' => $closed,
                    'Domain Bank' => $domain,
                    'On Progress' => $progress,
                ],
                'percentages' => [
                    'Closed' => $total > 0 ? round(($closed / $total) * 100, 2) : 0.0,
                    'Domain Bank' => $total > 0 ? round(($domain / $total) * 100, 2) : 0.0,
                    'On Progress' => $total > 0 ? round(($progress / $total) * 100, 2) : 0.0,
                ],
            ];
        })->values()->all();

        usort($groups, function ($a, $b) {
            if ((int) $a['total'] !== (int) $b['total']) {
                return ((int) $b['total']) <=> ((int) $a['total']);
            }

            return strcmp(strtoupper((string) $a['group']), strtoupper((string) $b['group']));
        });

        return ['rows' => $groups];
    }

    private function buildStatusByGroupChart(array $rows, string $labelKey): array
    {
        $statusOrder = $this->statusOrder();
        $colors = $this->statusColors();

        return [
            'labels' => array_map(fn ($row) => (string) ($row[$labelKey] ?? '-'), $rows),
            'datasets' => array_map(function ($status) use ($rows, $colors) {
                return [
                    'label' => $status,
                    'backgroundColor' => $colors[$status] ?? '#94a3b8',
                    'data' => array_map(fn ($row) => (int) ($row['counts'][$status] ?? 0), $rows),
                ];
            }, $statusOrder),
        ];
    }

    private function buildStatusPercentageChart(array $rows): array
    {
        $statusOrder = $this->statusOrder();
        $colors = $this->statusColors();

        return [
            'labels' => array_map(fn ($row) => (string) ($row['group'] ?? '-'), $rows),
            'datasets' => array_map(function ($status) use ($rows, $colors) {
                return [
                    'label' => $status,
                    'backgroundColor' => $colors[$status] ?? '#94a3b8',
                    'data' => array_map(fn ($row) => (float) ($row['percentages'][$status] ?? 0), $rows),
                ];
            }, $statusOrder),
        ];
    }

    private function buildTrendData(Collection $rows, ?int $selectedYear, bool $closedDate): array
    {
        $dateAccessor = $closedDate ? 'closed_reference_at_obj' : 'created_at_obj';
        $baseRows = $rows->filter(fn ($row) => !is_null($row[$dateAccessor] ?? null));

        $periods = [];
        if (!is_null($selectedYear)) {
            for ($month = 1; $month <= 12; $month++) {
                $start = Carbon::create($selectedYear, $month, 1)->startOfMonth();
                $periods[$start->format('Y-m')] = $start;
            }
        } else {
            $cursor = Carbon::today()->startOfMonth()->subMonths(11);
            for ($i = 0; $i < 12; $i++) {
                $start = (clone $cursor)->addMonths($i);
                $periods[$start->format('Y-m')] = $start;
            }
        }

        $statuses = $closedDate ? ['Closed'] : $this->statusOrder();
        $colors = $this->statusColors();
        $labels = array_map(fn (Carbon $period) => $period->isoFormat('MMM YYYY'), array_values($periods));

        $datasets = [];
        foreach ($statuses as $status) {
            $data = [];
            foreach (array_keys($periods) as $periodKey) {
                $count = $baseRows->filter(function ($row) use ($periodKey, $status, $closedDate, $dateAccessor) {
                    $date = $row[$dateAccessor] ?? null;
                    if (!$date instanceof Carbon) {
                        return false;
                    }

                    if ($closedDate && ($row['status_label'] ?? null) !== 'Closed') {
                        return false;
                    }

                    if (!$closedDate && ($row['status_label'] ?? null) !== $status) {
                        return false;
                    }

                    return $date->format('Y-m') === $periodKey;
                })->count();

                $data[] = (int) $count;
            }

            $datasets[] = [
                'label' => $status,
                'borderColor' => $colors[$status] ?? '#2563eb',
                'backgroundColor' => $colors[$status] ?? '#2563eb',
                'data' => $data,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    private function buildDetailRows(Collection $rows): array
    {
        $result = $rows->map(function ($row) {
            return [
                'bank' => (string) ($row['bank'] ?? '-'),
                'date' => (string) ($row['created_day'] ?? '-'),
                'month' => (string) ($row['created_month'] ?? '-'),
                'year' => (string) ($row['created_year'] ?? '-'),
                'product' => (string) ($row['product'] ?? '-'),
                'product_name' => (string) ($row['product_name'] ?? '-'),
                'status_detail' => (string) ($row['status_detail'] ?? '-'),
                'pic' => (string) ($row['pic'] ?? '-'),
                'log_css' => (string) ($row['risk_id'] ?? '-'),
                'status_summary' => (string) ($row['status_label'] ?? '-'),
                'task' => (string) ($row['task'] ?? '-'),
                'assigned_to' => (string) ($row['assigned_to'] ?? '-'),
                'is_overdue' => (bool) ($row['is_overdue'] ?? false),
            ];
        })->values()->all();

        usort($result, function ($a, $b) {
            return strcmp(
                strtoupper((string) ($a['bank'] . '|' . $a['year'] . '|' . $a['month'])),
                strtoupper((string) ($b['bank'] . '|' . $b['year'] . '|' . $b['month']))
            );
        });

        return $result;
    }

    private function buildDueMaturityData(Collection $rows): array
    {
        $overdueItems = $rows->flatMap(function ($row) {
            $items = [];
            foreach (($row['overdue_items'] ?? []) as $item) {
                $items[] = [
                    'bank' => (string) ($row['bank'] ?? '-'),
                    'division' => (string) ($row['division'] ?? '-'),
                    'product' => (string) ($row['product'] ?? '-'),
                    'bucket' => (string) ($item['bucket'] ?? ''),
                ];
            }

            return $items;
        })->values();

        $byBank = $this->buildAgingTable($overdueItems, 'bank');
        $byDivision = $this->buildAgingTable($overdueItems, 'division');
        $byProduct = $this->buildAgingTable($overdueItems, 'product');

        return [
            'by_bank' => $byBank,
            'by_division' => $byDivision,
            'by_product' => $byProduct,
            'pie_by_bank' => $this->buildAgingPieSlices($byBank),
            'pie_by_division' => $this->buildAgingPieSlices($byDivision),
            'pie_by_product' => $this->buildAgingPieSlices($byProduct),
            'grand_total' => (int) $overdueItems->count(),
        ];
    }

    private function buildAgingTable(Collection $overdueItems, string $groupKey): array
    {
        $grouped = $overdueItems->groupBy($groupKey)->map(function (Collection $items, $group) {
            $bucketLE6 = (int) $items->where('bucket', '<=6 Bulan')->count();
            $bucketLE9 = (int) $items->where('bucket', '<=9 Bulan')->count();
            $bucketGT9 = (int) $items->where('bucket', '>9 Bulan')->count();

            return [
                'group' => (string) $group,
                '<=6 Bulan' => $bucketLE6,
                '<=9 Bulan' => $bucketLE9,
                '>9 Bulan' => $bucketGT9,
                'grand_total' => $bucketLE6 + $bucketLE9 + $bucketGT9,
            ];
        })->filter(fn ($row) => (int) ($row['grand_total'] ?? 0) > 0)->values()->all();

        usort($grouped, function ($a, $b) {
            if ((int) $a['grand_total'] !== (int) $b['grand_total']) {
                return ((int) $b['grand_total']) <=> ((int) $a['grand_total']);
            }

            return strcmp(strtoupper((string) $a['group']), strtoupper((string) $b['group']));
        });

        $totalLE6 = array_sum(array_map(fn ($row) => (int) ($row['<=6 Bulan'] ?? 0), $grouped));
        $totalLE9 = array_sum(array_map(fn ($row) => (int) ($row['<=9 Bulan'] ?? 0), $grouped));
        $totalGT9 = array_sum(array_map(fn ($row) => (int) ($row['>9 Bulan'] ?? 0), $grouped));

        $grouped[] = [
            'group' => 'Grand Total',
            '<=6 Bulan' => $totalLE6,
            '<=9 Bulan' => $totalLE9,
            '>9 Bulan' => $totalGT9,
            'grand_total' => $totalLE6 + $totalLE9 + $totalGT9,
            'is_total' => true,
        ];

        return $grouped;
    }

    private function buildAgingPieSlices(array $tableRows): array
    {
        $slices = [];
        foreach ($tableRows as $row) {
            if (!empty($row['is_total'])) {
                continue;
            }

            foreach (['<=6 Bulan', '<=9 Bulan', '>9 Bulan'] as $bucket) {
                $count = (int) ($row[$bucket] ?? 0);
                if ($count <= 0) {
                    continue;
                }

                $slices[] = [
                    'label' => (string) ($row['group'] . ' ' . $bucket),
                    'count' => $count,
                ];
            }
        }

        usort($slices, fn ($a, $b) => ((int) $b['count']) <=> ((int) $a['count']));

        return $slices;
    }

    private function resolveAgingBucket(int $monthsOverdue): string
    {
        if ($monthsOverdue <= 6) {
            return '<=6 Bulan';
        }

        if ($monthsOverdue <= 9) {
            return '<=9 Bulan';
        }

        return '>9 Bulan';
    }

    private function normalizeStatus(string $status, string $description = ''): string
    {
        $raw = strtolower(trim($status));
        $descriptionRaw = strtolower($description);

        foreach ((array) config('audit_dashboard.status.domain_keywords', []) as $keyword) {
            $needle = strtolower(trim((string) $keyword));
            if ($needle !== '' && (Str::contains($raw, $needle) || Str::contains($descriptionRaw, $needle))) {
                return 'Domain Bank';
            }
        }

        foreach ((array) config('audit_dashboard.status.closed_keywords', []) as $keyword) {
            $needle = strtolower(trim((string) $keyword));
            if ($needle !== '' && (Str::contains($raw, $needle) || Str::contains($descriptionRaw, $needle))) {
                return 'Closed';
            }
        }

        $default = trim((string) config('audit_dashboard.status.default', 'On Progress'));

        return $default !== '' ? $default : 'On Progress';
    }

    private function resolveBankLabel(array $parts): string
    {
        $haystack = strtoupper(implode(' ', array_filter($parts, fn ($part) => trim((string) $part) !== '')));

        foreach ($this->bankMappings() as $mapping) {
            foreach ($mapping['keywords'] as $keyword) {
                if ($keyword !== '' && Str::contains($haystack, strtoupper($keyword))) {
                    return $mapping['name'];
                }
            }
        }

        return $this->defaultBankName();
    }

    private function resolveRegionFromBank(string $bank): string
    {
        foreach ($this->bankMappings() as $mapping) {
            if ($mapping['name'] === strtoupper(trim($bank))) {
                return $mapping['region'];
            }
        }

        return 'Others';
    }

    private function coordinatesByBank(string $bank): array
    {
        foreach ($this->bankMappings() as $mapping) {
            if ($mapping['name'] === strtoupper(trim($bank))) {
                return $mapping['coordinates'];
            }
        }

        $fallback = (array) config('audit_dashboard.bank.fallback_coordinates', ['lat' => -6.2596, 'lng' => 106.7972]);

        return [
            'lat' => (float) ($fallback['lat'] ?? -6.2596),
            'lng' => (float) ($fallback['lng'] ?? 106.7972),
        ];
    }

    private function resolveProductLabel(string $riskOwner, string $title, string $description): string
    {
        $owner = strtoupper(trim(RiskOwnerAccess::displayLabel($riskOwner, '')));

        foreach ($this->productMappings() as $mapping) {
            foreach ($mapping['keywords'] as $keyword) {
                if ($keyword !== '' && Str::contains($owner, strtoupper($keyword))) {
                    return $mapping['label'];
                }
            }
        }

        $source = $owner;
        if ($source === '') {
            $source = strtoupper(trim($title));
        }
        if ($source === '') {
            $source = strtoupper(trim($description));
        }
        if ($source === '') {
            return $this->defaultProductLabel();
        }

        $tokenLimit = max(1, (int) config('audit_dashboard.product.token_fallback_words', 2));
        $maxLength = max(4, (int) config('audit_dashboard.product.max_length', 24));
        $tokens = array_values(array_filter(preg_split('/\s+/', $source) ?: []));

        if (empty($tokens)) {
            return $this->defaultProductLabel();
        }

        return Str::limit(implode(' ', array_slice($tokens, 0, $tokenLimit)), $maxLength, '');
    }

    private function safeCarbon($value): ?Carbon
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        try {
            return $value instanceof Carbon ? $value : Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function summaryTotal(array $rows): int
    {
        return (int) array_sum(array_map(fn ($row) => (int) ($row['count'] ?? 0), $rows));
    }

    /** @return array<int, int> */
    private function availableYearsFromDatabase(): array
    {
        $years = Risk::query()
            ->select('year')
            ->whereNotNull('year')
            ->distinct()
            ->pluck('year')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        rsort($years, SORT_NUMERIC);

        return empty($years) ? [(int) now()->format('Y')] : $years;
    }

    /** @return array<int, int> */
    private function availableYearsFromRows(Collection $rows): array
    {
        $years = $rows->pluck('year')
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return empty($years) ? [(int) now()->format('Y')] : $years;
    }

    private function availableBanks(Collection $rows): array
    {
        $banksFromConfig = array_map(fn ($mapping) => (string) $mapping['name'], $this->bankMappings());
        $dynamic = $rows->pluck('bank')
            ->map(fn ($value) => strtoupper(trim((string) $value)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $all = array_values(array_unique(array_merge($banksFromConfig, $dynamic)));
        sort($all, SORT_NATURAL | SORT_FLAG_CASE);

        return $all;
    }

    /** @return array<int, string> */
    private function statusOrder(): array
    {
        $order = array_values(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, (array) config('audit_dashboard.status.order', ['Closed', 'Domain Bank', 'On Progress']))));

        return empty($order) ? ['Closed', 'Domain Bank', 'On Progress'] : $order;
    }

    /** @return array<string, string> */
    private function statusColors(): array
    {
        return array_map(fn ($value) => (string) $value, (array) config('audit_dashboard.status.colors', []));
    }

    /** @return array<int, string> */
    private function executiveActions(): array
    {
        $actions = array_values(array_filter(array_map(function ($value) {
            return trim((string) $value);
        }, (array) config('audit_dashboard.executive_actions', []))));

        return empty($actions)
            ? [
                'Reminder (email, WA) kepada PIC penyelesaian temuan.',
                'Koordinasi meeting internal dengan divisi terkait.',
                'Koordinasi berkala dengan klien untuk memastikan komitmen close temuan.',
            ]
            : $actions;
    }

    private function defaultBankName(): string
    {
        $name = trim((string) config('audit_dashboard.bank.default', 'COLLEGA'));

        return $name !== '' ? strtoupper($name) : 'COLLEGA';
    }

    private function defaultProductLabel(): string
    {
        $name = trim((string) config('audit_dashboard.product.default', 'UNKNOWN'));

        return $name !== '' ? $name : 'UNKNOWN';
    }

    /**
     * @return array<int, array{name: string, keywords: array<int, string>, coordinates: array{lat: float, lng: float}, region: string}>
     */
    private function bankMappings(): array
    {
        $rows = (array) config('audit_dashboard.bank.mappings', []);
        $mappings = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = strtoupper(trim((string) ($row['name'] ?? '')));
            if ($name === '') {
                continue;
            }

            $keywords = array_values(array_filter(array_map(function ($keyword) {
                return strtoupper(trim((string) $keyword));
            }, (array) ($row['keywords'] ?? []))));

            $coord = (array) ($row['coordinates'] ?? []);
            $mappings[] = [
                'name' => $name,
                'keywords' => $keywords,
                'coordinates' => [
                    'lat' => (float) ($coord['lat'] ?? 0),
                    'lng' => (float) ($coord['lng'] ?? 0),
                ],
                'region' => trim((string) ($row['region'] ?? 'Others')) ?: 'Others',
            ];
        }

        if (empty($mappings)) {
            $mappings[] = [
                'name' => 'COLLEGA',
                'keywords' => ['COLLEGA'],
                'coordinates' => ['lat' => -6.2596, 'lng' => 106.7972],
                'region' => 'Internal Collega',
            ];
        }

        return $mappings;
    }

    /** @return array<int, array{label: string, keywords: array<int, string>}> */
    private function productMappings(): array
    {
        $rows = (array) config('audit_dashboard.product.mappings', []);
        $mappings = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $keywords = array_values(array_filter(array_map(function ($keyword) {
                return strtoupper(trim((string) $keyword));
            }, (array) ($row['keywords'] ?? []))));

            if (empty($keywords)) {
                continue;
            }

            $mappings[] = [
                'label' => $label,
                'keywords' => $keywords,
            ];
        }

        return $mappings;
    }
}


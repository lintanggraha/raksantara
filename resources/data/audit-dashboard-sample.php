<?php

/*
| Fictional audit dashboard sample, shown until a spreadsheet is imported.
| To show your organisation's own snapshot instead, put a file with the same
| structure at private/audit_dashboard_snapshot.php (gitignored).
*/

$summaryRows = [
    ['label' => 'Closed', 'count' => 13],
    ['label' => 'Domain Bank', 'count' => 3],
    ['label' => 'On Progress', 'count' => 9],
];

$overdueRows = [
    ['label' => 'Closed', 'count' => 6],
    ['label' => 'Domain Bank', 'count' => 2],
    ['label' => 'On Progress', 'count' => 4],
];

$rekapRows = [
    ['region' => 'Region 1', 'bank' => 'BANK CONTOH A', 'year' => '2025', 'auditee' => 'Audit Keamanan Aplikasi', 'total' => 6, 'closed' => 5, 'on_progress' => 1, 'domain_bank' => 0],
    ['region' => 'Region 1', 'bank' => 'BANK CONTOH B', 'year' => '2025', 'auditee' => 'Audit Infrastruktur & DRC', 'total' => 8, 'closed' => 4, 'on_progress' => 2, 'domain_bank' => 2],
    ['region' => 'Region 2', 'bank' => 'BANK CONTOH C', 'year' => '2024', 'auditee' => 'Penetration Test Core Banking', 'total' => 5, 'closed' => 3, 'on_progress' => 1, 'domain_bank' => 1],
    ['region' => 'Internal', 'bank' => 'INTERNAL', 'year' => '2025', 'auditee' => 'Audit Internal TI', 'total' => 6, 'closed' => 1, 'on_progress' => 5, 'domain_bank' => 0],
];

$progressByBankRows = [
    ['group' => 'BANK CONTOH A', 'total' => 6, 'counts' => ['Closed' => 5, 'Domain Bank' => 0, 'On Progress' => 1], 'percentages' => ['Closed' => 83.33, 'Domain Bank' => 0.00, 'On Progress' => 16.67]],
    ['group' => 'BANK CONTOH B', 'total' => 8, 'counts' => ['Closed' => 4, 'Domain Bank' => 2, 'On Progress' => 2], 'percentages' => ['Closed' => 50.00, 'Domain Bank' => 25.00, 'On Progress' => 25.00]],
    ['group' => 'BANK CONTOH C', 'total' => 5, 'counts' => ['Closed' => 3, 'Domain Bank' => 1, 'On Progress' => 1], 'percentages' => ['Closed' => 60.00, 'Domain Bank' => 20.00, 'On Progress' => 20.00]],
    ['group' => 'INTERNAL', 'total' => 6, 'counts' => ['Closed' => 1, 'Domain Bank' => 0, 'On Progress' => 5], 'percentages' => ['Closed' => 16.67, 'Domain Bank' => 0.00, 'On Progress' => 83.33]],
];

$progressByRegionRows = [
    ['group' => 'Region', 'total' => 25, 'counts' => ['Closed' => 13, 'Domain Bank' => 3, 'On Progress' => 9], 'percentages' => ['Closed' => 52.00, 'Domain Bank' => 12.00, 'On Progress' => 36.00]],
];

$progressByProductRows = [
    ['group' => 'CBS', 'total' => 8, 'counts' => ['Closed' => 6, 'Domain Bank' => 1, 'On Progress' => 1], 'percentages' => ['Closed' => 75.00, 'Domain Bank' => 12.50, 'On Progress' => 12.50]],
    ['group' => 'CS', 'total' => 3, 'counts' => ['Closed' => 1, 'Domain Bank' => 0, 'On Progress' => 2], 'percentages' => ['Closed' => 33.33, 'Domain Bank' => 0.00, 'On Progress' => 66.67]],
    ['group' => 'INFRA', 'total' => 9, 'counts' => ['Closed' => 4, 'Domain Bank' => 1, 'On Progress' => 4], 'percentages' => ['Closed' => 44.44, 'Domain Bank' => 11.11, 'On Progress' => 44.44]],
    ['group' => 'ITOM', 'total' => 5, 'counts' => ['Closed' => 2, 'Domain Bank' => 1, 'On Progress' => 2], 'percentages' => ['Closed' => 40.00, 'Domain Bank' => 20.00, 'On Progress' => 40.00]],
];

$detailRows = [
    ['bank' => 'BANK CONTOH A', 'date' => '12', 'month' => 'March', 'year' => '2025', 'product' => 'CBS', 'product_name' => 'Core Banking API', 'status_detail' => 'Closed 100%', 'pic' => 'CBS', 'log_css' => 'LOG-2025-0312', 'status_summary' => 'Closed', 'task' => 'Penguatan validasi input dan rate limiting pada API core banking.', 'assigned_to' => 'Null', 'is_overdue' => true],
    ['bank' => 'BANK CONTOH A', 'date' => '20', 'month' => 'June', 'year' => '2025', 'product' => 'CS', 'product_name' => 'WAF', 'status_detail' => 'On Progress 60%', 'pic' => 'CS', 'log_css' => 'LOG-2025-0620', 'status_summary' => 'On Progress', 'task' => 'Tuning rule WAF untuk aplikasi internet banking.', 'assigned_to' => 'Null', 'is_overdue' => true],
    ['bank' => 'BANK CONTOH B', 'date' => '5', 'month' => 'February', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'Firewall', 'status_detail' => 'Closed 100%', 'pic' => 'INFRA', 'log_css' => 'LOG-2025-0205', 'status_summary' => 'Closed', 'task' => 'Review dan perapian firewall policy yang tidak terpakai.', 'assigned_to' => 'Null', 'is_overdue' => true],
    ['bank' => 'BANK CONTOH B', 'date' => '18', 'month' => 'April', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'Server', 'status_detail' => 'On Progress 40%', 'pic' => 'INFRA', 'log_css' => 'LOG-2025-0418', 'status_summary' => 'On Progress', 'task' => 'Hardening OS dan patching berkala server kritikal.', 'assigned_to' => 'Null', 'is_overdue' => true],
    ['bank' => 'BANK CONTOH B', 'date' => '30', 'month' => 'May', 'year' => '2025', 'product' => 'ITOM', 'product_name' => 'DRC', 'status_detail' => 'Closed 100%', 'pic' => 'ITOM', 'log_css' => 'LOG-2025-0530', 'status_summary' => 'Domain Bank', 'task' => 'Uji failover DRC yang memerlukan jadwal dari pihak bank.', 'assigned_to' => 'Null', 'is_overdue' => true],
    ['bank' => 'BANK CONTOH C', 'date' => '14', 'month' => 'August', 'year' => '2024', 'product' => 'CBS', 'product_name' => 'Core Banking', 'status_detail' => 'Closed 100%', 'pic' => 'CBS', 'log_css' => 'LOG-2024-0814', 'status_summary' => 'Closed', 'task' => 'Perbaikan kontrol sesi dan enkripsi data sensitif.', 'assigned_to' => 'Null', 'is_overdue' => true],
    ['bank' => 'BANK CONTOH C', 'date' => '2', 'month' => 'October', 'year' => '2024', 'product' => 'ITOM', 'product_name' => 'Backup', 'status_detail' => 'On Progress 70%', 'pic' => 'ITOM', 'log_css' => 'LOG-2024-1002', 'status_summary' => 'On Progress', 'task' => 'Otomasi verifikasi restore backup harian.', 'assigned_to' => 'Null', 'is_overdue' => true],
    ['bank' => 'INTERNAL', 'date' => '-', 'month' => 'Null', 'year' => '2025', 'product' => 'INFRA', 'product_name' => 'Monitoring', 'status_detail' => 'On Progress', 'pic' => 'INFRA', 'log_css' => '0', 'status_summary' => 'On Progress', 'task' => 'Sentralisasi log dan alert keamanan ke SIEM.', 'assigned_to' => 'Null', 'is_overdue' => false],
];

$mapPoints = [
    ['bank' => 'BANK CONTOH A', 'total' => 6, 'closed' => 5, 'domain_bank' => 0, 'on_progress' => 1],
    ['bank' => 'BANK CONTOH B', 'total' => 8, 'closed' => 4, 'domain_bank' => 2, 'on_progress' => 2],
    ['bank' => 'BANK CONTOH C', 'total' => 5, 'closed' => 3, 'domain_bank' => 1, 'on_progress' => 1],
    ['bank' => 'INTERNAL', 'total' => 6, 'closed' => 1, 'domain_bank' => 0, 'on_progress' => 5],
];

$dueByBank = [
    ['group' => 'BANK CONTOH A', '<=6 Bulan' => 0, '<=9 Bulan' => 1, '>9 Bulan' => 2, 'grand_total' => 3],
    ['group' => 'BANK CONTOH B', '<=6 Bulan' => 2, '<=9 Bulan' => 1, '>9 Bulan' => 2, 'grand_total' => 5],
    ['group' => 'BANK CONTOH C', '<=6 Bulan' => 1, '<=9 Bulan' => 0, '>9 Bulan' => 3, 'grand_total' => 4],
    ['group' => 'Grand Total', '<=6 Bulan' => 3, '<=9 Bulan' => 2, '>9 Bulan' => 7, 'grand_total' => 12, 'is_total' => true],
];

$dueByDivision = [
    ['group' => 'CBS', '<=6 Bulan' => 1, '<=9 Bulan' => 0, '>9 Bulan' => 3, 'grand_total' => 4],
    ['group' => 'INFRA', '<=6 Bulan' => 2, '<=9 Bulan' => 1, '>9 Bulan' => 2, 'grand_total' => 5],
    ['group' => 'ITOM', '<=6 Bulan' => 0, '<=9 Bulan' => 1, '>9 Bulan' => 2, 'grand_total' => 3],
    ['group' => 'Grand Total', '<=6 Bulan' => 3, '<=9 Bulan' => 2, '>9 Bulan' => 7, 'grand_total' => 12, 'is_total' => true],
];

$dueByProduct = $dueByDivision;

return [
    'period' => ['month_label' => 'Okt 2026', 'week_label' => 'W2', 'overdue_until' => '10/14/2026'],
    'summary_rows' => $summaryRows,
    'overdue_rows' => $overdueRows,
    'rekap_rows' => $rekapRows,
    'progress_by_bank' => $progressByBankRows,
    'progress_by_region' => $progressByRegionRows,
    'progress_by_product' => $progressByProductRows,
    'detail_rows' => $detailRows,
    'map_points' => $mapPoints,
    'due_by_bank' => $dueByBank,
    'due_by_division' => $dueByDivision,
    'due_by_product' => $dueByProduct,
    'pie_by_bank' => [
        ['label' => 'BANK CONTOH C >9 Bulan', 'count' => 3],
        ['label' => 'BANK CONTOH A >9 Bulan', 'count' => 2],
        ['label' => 'BANK CONTOH B >9 Bulan', 'count' => 2],
        ['label' => 'BANK CONTOH B <=6 Bulan', 'count' => 2],
        ['label' => 'BANK CONTOH C <=6 Bulan', 'count' => 1],
        ['label' => 'BANK CONTOH A <=9 Bulan', 'count' => 1],
        ['label' => 'BANK CONTOH B <=9 Bulan', 'count' => 1],
    ],
    'pie_by_division' => [
        ['label' => 'CBS >9 Bulan', 'count' => 3],
        ['label' => 'INFRA >9 Bulan', 'count' => 2],
        ['label' => 'ITOM >9 Bulan', 'count' => 2],
        ['label' => 'INFRA <=6 Bulan', 'count' => 2],
        ['label' => 'CBS <=6 Bulan', 'count' => 1],
        ['label' => 'INFRA <=9 Bulan', 'count' => 1],
        ['label' => 'ITOM <=9 Bulan', 'count' => 1],
    ],
    'pie_by_product' => [
        ['label' => 'CBS >9 Bulan', 'count' => 3],
        ['label' => 'INFRA >9 Bulan', 'count' => 2],
        ['label' => 'ITOM >9 Bulan', 'count' => 2],
        ['label' => 'INFRA <=6 Bulan', 'count' => 2],
        ['label' => 'CBS <=6 Bulan', 'count' => 1],
        ['label' => 'INFRA <=9 Bulan', 'count' => 1],
        ['label' => 'ITOM <=9 Bulan', 'count' => 1],
    ],
    'due_grand_total' => 12,
];

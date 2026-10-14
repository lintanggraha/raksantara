<?php

$config = [
    'status' => [
        'order' => ['Closed', 'Domain Bank', 'On Progress'],
        'colors' => [
            'Closed' => '#f59e0b',
            'Domain Bank' => '#ef4444',
            'On Progress' => '#f9a8d4',
        ],
        'domain_keywords' => ['domain bank', 'domain'],
        'closed_keywords' => ['closed', 'close', 'done', 'resolved', 'selesai'],
        'default' => 'On Progress',
    ],

    'bank' => [
        'default' => 'INTERNAL',
        'fallback_coordinates' => ['lat' => -6.2088, 'lng' => 106.8456],
        // Example mapping. Put your real client list in private/audit_dashboard.php.
        'mappings' => [
            [
                'name' => 'BANK CONTOH A',
                'keywords' => ['BANK CONTOH A', 'CONTOH A'],
                'coordinates' => ['lat' => 3.5952, 'lng' => 98.6722],
                'region' => 'Region 1',
            ],
            [
                'name' => 'BANK CONTOH B',
                'keywords' => ['BANK CONTOH B', 'CONTOH B'],
                'coordinates' => ['lat' => -6.9175, 'lng' => 107.6191],
                'region' => 'Region 1',
            ],
            [
                'name' => 'BANK CONTOH C',
                'keywords' => ['BANK CONTOH C', 'CONTOH C'],
                'coordinates' => ['lat' => -5.1477, 'lng' => 119.4327],
                'region' => 'Region 2',
            ],
            [
                'name' => 'INTERNAL',
                'keywords' => ['INTERNAL'],
                'coordinates' => ['lat' => -6.2088, 'lng' => 106.8456],
                'region' => 'Internal',
            ],
        ],
    ],

    'product' => [
        'mappings' => [
            ['label' => 'CBS SYARIAH', 'keywords' => ['CORE BANKING SYSTEM SYARIAH', 'CBS SYARIAH']],
            ['label' => 'CBS CONVEN', 'keywords' => ['CORE BANKING SYSTEM CONVENTIONAL', 'CBS CONVEN']],
            ['label' => 'CBS', 'keywords' => ['BANKING APPLICATIONS']],
            ['label' => 'DC', 'keywords' => ['DATA CENTER OPERATION']],
            ['label' => 'ITOM', 'keywords' => ['IT OPERATION MANAGEMENT']],
            ['label' => 'INFRA', 'keywords' => ['INFRASTRUCTURE']],
            ['label' => 'CR', 'keywords' => ['CUSTOMER RELATIONS']],
            ['label' => 'SURROUNDING', 'keywords' => ['SURROUNDING APPLICATION']],
            ['label' => 'CS', 'keywords' => ['CYBER SECURITY']],
            ['label' => 'QA', 'keywords' => ['QA & INNOVATION']],
            ['label' => 'RFR', 'keywords' => ['REGULATORY & FINANCIAL REPORTING']],
        ],
        'default' => 'UNKNOWN',
        'token_fallback_words' => 2,
        'max_length' => 24,
    ],

    // Snapshot shown before a spreadsheet is imported. Falls back to the
    // fictional sample in resources/data/audit-dashboard-sample.php.
    'snapshot_path' => dirname(__DIR__) . '/private/audit_dashboard_snapshot.php',

    'executive_actions' => [
        'Reminder (email, wa).',
        'Koordinasi meeting internal dengan divisi terkait.',
        'Koordinasi dengan klien.',
    ],
];

// Organisation-specific values (real client list) can live in the gitignored private/ folder.
$local = dirname(__DIR__) . '/private/audit_dashboard.php';

return is_file($local) ? array_replace($config, require $local) : $config;

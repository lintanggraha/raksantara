<?php

return [
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
        'default' => 'COLLEGA',
        'fallback_coordinates' => ['lat' => -6.2596, 'lng' => 106.7972],
        'mappings' => [
            [
                'name' => 'BANK ACEH',
                'keywords' => ['BANK ACEH', 'ACEH'],
                'coordinates' => ['lat' => 5.5483, 'lng' => 95.3238],
                'region' => 'Region 1',
            ],
            [
                'name' => 'BANK BENGKULU',
                'keywords' => ['BANK BENGKULU', 'BENGKULU'],
                'coordinates' => ['lat' => -3.8004, 'lng' => 102.2655],
                'region' => 'Region 1',
            ],
            [
                'name' => 'BANK SUMUT',
                'keywords' => ['BANK SUMUT', 'SUMUT'],
                'coordinates' => ['lat' => 3.5952, 'lng' => 98.6722],
                'region' => 'Region 1',
            ],
            [
                'name' => 'BANK SULTENG',
                'keywords' => ['BANK SULTENG', 'SULTENG'],
                'coordinates' => ['lat' => -0.8986, 'lng' => 119.8506],
                'region' => 'Region 2',
            ],
            [
                'name' => 'COLLEGA',
                'keywords' => ['COLLEGA', 'INTERNAL'],
                'coordinates' => ['lat' => -6.2596, 'lng' => 106.7972],
                'region' => 'Internal Collega',
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

    'executive_actions' => [
        'Reminder (email, wa).',
        'Koordinasi meeting internal dengan divisi terkait.',
        'Koordinasi dengan klien.',
    ],
];

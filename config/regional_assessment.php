<?php

/*
|--------------------------------------------------------------------------
| Regional Monitoring Assessment (Sulteng / Sumut)
|--------------------------------------------------------------------------
|
| Physical evidence folders and source spreadsheets for the regional
| investigation workspaces. When a configured folder does not exist on the
| server, evidence falls back to storage/app/<region>_evidence.
|
*/

return [
    'sulteng' => [
        'source_root' => env('SULTENG_SOURCE_ROOT', 'D:/Playground/SULTENG'),
        'evidence_path' => env('SULTENG_EVIDENCE_PATH', 'D:/Playground/SULTENG/Evidence-latest'),
        'sync_excel_path' => env('SULTENG_SYNC_EXCEL_PATH', 'D:/Playground/SULTENG/Checklist_Permintaan_Data_Log.xlsx'),
    ],

    'sumut' => [
        'evidence_path' => env('SUMUT_EVIDENCE_PATH', 'D:/Playground/SUMUT/Status_OJK_02102026'),
    ],

    'directory_mode' => 0750,
];

<?php

return [
    'enabled' => filter_var(env('KRI_INGESTION_ENABLED', false), FILTER_VALIDATE_BOOL),
    'shared_secret' => env('KRI_INGESTION_SHARED_SECRET'),
    'allowed_sources' => array_values(array_filter(array_map('trim', explode(',', (string) env('KRI_INGESTION_ALLOWED_SOURCES', ''))))),
    'max_timestamp_skew_seconds' => (int) env('KRI_INGESTION_MAX_TIMESTAMP_SKEW_SECONDS', 300),
];

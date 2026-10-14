<?php

$config = [
    /*
    | Suggestions for the "affected client / party" field on incidents. The
    | field stays free text; this list only feeds the autocomplete. Names from
    | the audit dashboard bank mappings are added automatically.
    */
    'affected_parties' => [
        'INTERNAL',
        'SEMUA CLIENT / MULTI CLIENT',
    ],
];

// Organisation-specific values can live in the gitignored private/ folder.
$local = dirname(__DIR__) . '/private/incidents.php';

return is_file($local) ? array_replace($config, require $local) : $config;

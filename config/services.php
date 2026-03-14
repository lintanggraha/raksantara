<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'nvd' => [
        'api_key' => env('NVD_API_KEY'),
        'base_url' => env('NVD_API_BASE_URL', 'https://services.nvd.nist.gov/rest/json/cves/2.0'),
    ],

    'cisa' => [
        'kev_url' => env('CISA_KEV_FEED_URL', 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json'),
        'kev_mirror_url' => env('CISA_KEV_MIRROR_URL', 'https://raw.githubusercontent.com/cisagov/kev-data/develop/known_exploited_vulnerabilities.json'),
    ],

    'owasp' => [
        'top10_url' => env('OWASP_TOP10_URL', 'https://owasp.org/Top10/'),
    ],

    'threat_intel' => [
        // Auto refresh on authenticated page access (useful for local Laragon without external scheduler)
        'auto_refresh_enabled' => env('THREAT_INTEL_AUTO_REFRESH_ENABLED', true),
        'auto_refresh_interval_minutes' => env('THREAT_INTEL_AUTO_REFRESH_INTERVAL_MINUTES', 30),
        'auto_refresh_force' => env('THREAT_INTEL_AUTO_REFRESH_FORCE', true),
    ],

];

<?php

return [
    'oidc' => [
        'enabled' => filter_var(env('SSO_OIDC_ENABLED', false), FILTER_VALIDATE_BOOL),
        'discovery_url' => env('SSO_OIDC_DISCOVERY_URL'),
        'client_id' => env('SSO_OIDC_CLIENT_ID'),
        'client_secret' => env('SSO_OIDC_CLIENT_SECRET'),
        'redirect_uri' => env('SSO_OIDC_REDIRECT_URI'),
        'scopes' => array_values(array_filter(array_map('trim', explode(' ', (string) env('SSO_OIDC_SCOPES', 'openid profile email'))))),
        'claim_map' => [
            'subject' => env('SSO_OIDC_SUBJECT_CLAIM', 'sub'),
            'email' => env('SSO_OIDC_EMAIL_CLAIM', 'email'),
            'name' => env('SSO_OIDC_NAME_CLAIM', 'name'),
            'username' => env('SSO_OIDC_USERNAME_CLAIM', 'preferred_username'),
            'division' => env('SSO_OIDC_DIVISION_CLAIM', 'department'),
        ],
        'allowed_email_domains' => array_values(array_filter(array_map('trim', explode(',', (string) env('SSO_OIDC_ALLOWED_EMAIL_DOMAINS', ''))))),
        'auto_provision' => filter_var(env('SSO_OIDC_AUTO_PROVISION', false), FILTER_VALIDATE_BOOL),
    ],
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email One-Time Code MFA
    |--------------------------------------------------------------------------
    |
    | enabled  : turns the email OTP method on. When false the method is hidden
    |            and never challenged, even for users who opted in earlier.
    | enforced : every user without an authenticator app (TOTP) must pass an
    |            email code at sign-in. When false, users opt in from the
    |            Two-Factor Authentication page.
    |
    | An authenticator app (TOTP), when configured, always takes precedence.
    |
    */
    'email_otp' => [
        'enabled' => filter_var(env('MFA_EMAIL_OTP_ENABLED', false), FILTER_VALIDATE_BOOL),
        'enforced' => filter_var(env('MFA_EMAIL_OTP_ENFORCED', false), FILTER_VALIDATE_BOOL),
        'code_length' => 6,
        'ttl_minutes' => (int) env('MFA_EMAIL_OTP_TTL_MINUTES', 10),
        'max_attempts' => (int) env('MFA_EMAIL_OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown_seconds' => (int) env('MFA_EMAIL_OTP_RESEND_COOLDOWN', 60),
    ],
];

<?php

namespace App\Providers;

use App\Support\AuditLogger;
use App\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(Login::class, function (Login $event): void {
            if (!$event->user instanceof User) {
                return;
            }

            AuditLogger::logModelEvent('auth.login.succeeded', $event->user, null, [
                'guard' => (string) $event->guard,
                'remember' => (bool) $event->remember,
            ]);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if (!$event->user instanceof User) {
                return;
            }

            AuditLogger::logModelEvent('auth.logout', $event->user, null, [
                'guard' => (string) $event->guard,
            ]);
        });

        Event::listen(Failed::class, function (Failed $event): void {
            $identifier = (string) ($event->credentials['username'] ?? $event->credentials['email'] ?? '-');
            $auditable = $event->user instanceof User ? $event->user : new User();

            AuditLogger::logModelEvent('auth.login.failed', $auditable, null, [
                'guard' => (string) $event->guard,
                'identifier' => $identifier,
                'reason' => 'invalid_credentials',
            ]);
        });

        Event::listen(Lockout::class, function (Lockout $event): void {
            $identifier = (string) ($event->request->input('username') ?: $event->request->input('email') ?: '-');
            $user = null;

            if ($identifier !== '-' && $identifier !== '') {
                $user = User::query()
                    ->where('username', $identifier)
                    ->orWhere('email', $identifier)
                    ->first();
            }

            $auditable = $user instanceof User ? $user : new User();

            AuditLogger::logModelEvent('auth.login.locked', $auditable, null, [
                'identifier' => $identifier,
                'retry_after_seconds' => (int) $event->request->headers->get('Retry-After', 0),
            ]);
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            if (!$event->user instanceof User) {
                return;
            }

            AuditLogger::logModelEvent('auth.password.reset', $event->user, null, [
                'source' => 'password_reset_flow',
            ]);
        });

        Event::listen(Registered::class, function (Registered $event): void {
            if (!$event->user instanceof User) {
                return;
            }

            AuditLogger::logModelEvent('auth.user.registered', $event->user, null, [
                'email' => (string) ($event->user->email ?? '-'),
            ]);
        });

        Event::listen(Verified::class, function (Verified $event): void {
            if (!$event->user instanceof User) {
                return;
            }

            AuditLogger::logModelEvent('auth.email.verified', $event->user, null, [
                'email_verified_at' => optional($event->user->email_verified_at)->format('Y-m-d H:i:s'),
            ]);
        });
    }
}

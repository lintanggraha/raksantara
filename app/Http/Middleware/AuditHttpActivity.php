<?php

namespace App\Http\Middleware;

use App\Support\AuditLogger;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditHttpActivity
{
    public function handle(Request $request, Closure $next)
    {
        $startedAt = microtime(true);
        $response = $next($request);

        $this->logHttpActivity($request, $response, $startedAt);

        return $response;
    }

    private function logHttpActivity(Request $request, $response, float $startedAt): void
    {
        if (!$this->shouldLog($request)) {
            return;
        }

        $method = strtoupper((string) $request->method());
        $event = 'http.request.' . strtolower($method);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $statusCode = method_exists($response, 'getStatusCode') ? (int) $response->getStatusCode() : 200;

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $routeAction = $route ? $route->getActionName() : null;

        $input = [];
        if ($this->isWriteMethod($method)) {
            $input = $request->except([
                '_token',
                '_method',
                'password',
                'password_confirmation',
                'current_password',
                'new_password',
                'token',
            ]);
        }

        $payload = [
            'request_id' => (string) ($request->header('X-Request-Id') ?: (string) Str::uuid()),
            'method' => $method,
            'path' => '/' . ltrim((string) $request->path(), '/'),
            'route_name' => $routeName ?: '-',
            'route_action' => $routeAction ?: '-',
            'status_code' => $statusCode,
            'status_group' => (string) floor($statusCode / 100) . 'xx',
            'duration_ms' => $durationMs,
            'is_ajax' => $request->ajax(),
            'is_json' => $request->expectsJson(),
            'query' => $request->query(),
            'input' => $input,
        ];

        $user = auth()->user();
        $auditable = $user instanceof User ? $user : new User();

        AuditLogger::logModelEvent($event, $auditable, null, $payload);
    }

    private function shouldLog(Request $request): bool
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return false;
        }

        if (strtoupper((string) $request->method()) === 'OPTIONS') {
            return false;
        }

        $path = ltrim((string) $request->path(), '/');
        $excludedPatterns = [
            '_debugbar*',
            '_ignition*',
            'telescope*',
            'horizon*',
        ];

        foreach ($excludedPatterns as $pattern) {
            if (Str::is($pattern, $path)) {
                return false;
            }
        }

        return true;
    }

    private function isWriteMethod(string $method): bool
    {
        return in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }
}

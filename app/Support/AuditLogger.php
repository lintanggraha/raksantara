<?php

namespace App\Support;

use App\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public static function logModelEvent(
        string $event,
        Model $model,
        ?array $before = null,
        ?array $after = null
    ): void {
        try {
            AuditLog::create([
                'actor_id' => auth()->id(),
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'event' => $event,
                'before_state' => self::sanitize($before),
                'after_state' => self::sanitize($after),
                'ip_address' => optional(request())->ip(),
                'user_agent' => substr((string) optional(request())->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Keep business flow resilient even when audit insert fails.
        }
    }

    private static function sanitize(?array $payload): ?array
    {
        if (is_null($payload)) {
            return null;
        }

        return self::sanitizeArray($payload);
    }

    private static function sanitizeArray(array $payload): array
    {
        $safe = [];

        foreach ($payload as $key => $value) {
            $keyString = is_string($key) ? strtolower(trim($key)) : (string) $key;
            if (self::isSensitiveKey($keyString)) {
                $safe[$key] = '[REDACTED]';
                continue;
            }

            $safe[$key] = self::sanitizeValue($value);
        }

        return $safe;
    }

    private static function sanitizeValue($value)
    {
        if (is_array($value)) {
            return self::sanitizeArray($value);
        }

        if (is_null($value) || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTime::ATOM);
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            return [
                'uploaded_file' => true,
                'original_name' => (string) $value->getClientOriginalName(),
                'mime_type' => (string) $value->getClientMimeType(),
                'size' => (int) $value->getSize(),
            ];
        }

        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return [
                'model' => get_class($value),
                'id' => $value->getKey(),
            ];
        }

        if ($value instanceof \Stringable) {
            return mb_substr((string) $value, 0, 1024);
        }

        return '[UNSERIALIZABLE:' . gettype($value) . ']';
    }

    private static function isSensitiveKey(string $key): bool
    {
        $blocked = [
            'password',
            'password_confirmation',
            'remember_token',
            'token',
            'access_token',
            'refresh_token',
            'authorization',
            'cookie',
            'set-cookie',
            'current_password',
            'new_password',
            'old_password',
            'secret',
            'api_key',
            'private_key',
            'otp',
            'pin',
        ];

        foreach ($blocked as $item) {
            if ($key === $item || strpos($key, $item) !== false) {
                return true;
            }
        }

        return false;
    }
}

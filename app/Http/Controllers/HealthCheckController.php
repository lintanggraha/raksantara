<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthCheckController extends Controller
{
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function ready(): JsonResponse
    {
        $checks = [
            'database' => $this->databaseCheck(),
            'cache' => $this->cacheCheck(),
            'storage' => $this->storageCheck(),
        ];

        $isReady = collect($checks)->every(function (array $result): bool {
            return (bool) ($result['ok'] ?? false);
        });

        return response()->json([
            'status' => $isReady ? 'ready' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $isReady ? 200 : 503);
    }

    private function databaseCheck(): array
    {
        try {
            DB::select('SELECT 1');

            return [
                'ok' => true,
                'message' => 'database reachable',
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => 'database unavailable',
            ];
        }
    }

    private function cacheCheck(): array
    {
        try {
            $key = 'health:cache:' . bin2hex(random_bytes(8));
            $value = (string) now()->timestamp;

            Cache::put($key, $value, 60);
            $cached = (string) Cache::get($key, '');
            Cache::forget($key);

            if ($cached !== $value) {
                return [
                    'ok' => false,
                    'message' => 'cache read mismatch',
                ];
            }

            return [
                'ok' => true,
                'message' => 'cache reachable',
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => 'cache unavailable',
            ];
        }
    }

    private function storageCheck(): array
    {
        $writable = is_writable(storage_path());

        if (!$writable) {
            $testFile = storage_path('framework/health_' . bin2hex(random_bytes(4)) . '.tmp');
            $writable = (@file_put_contents($testFile, '1') !== false);
            if ($writable) {
                @unlink($testFile);
            }
        }

        return [
            'ok' => $writable,
            'message' => $writable ? 'storage writable' : 'storage not writable',
        ];
    }
}

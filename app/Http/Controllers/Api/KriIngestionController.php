<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Kri;
use App\KriIngestionEvent;
use App\Services\KriDataQualityService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KriIngestionController extends Controller
{
    public function store(Request $request, KriDataQualityService $qualityService): JsonResponse
    {
        $config = config('kri_ingestion');
        if (!(bool) ($config['enabled'] ?? false) || empty($config['shared_secret'])) {
            return response()->json(['message' => 'KRI ingestion is not enabled.'], 404);
        }

        $source = trim((string) $request->header('X-KRI-Source'));
        $eventId = trim((string) $request->header('X-KRI-Event-ID'));
        $timestamp = (int) $request->header('X-KRI-Timestamp');
        $signature = trim((string) $request->header('X-KRI-Signature'));
        $rawPayload = $request->getContent();

        if (empty($source) || empty($eventId) || $timestamp <= 0 || empty($signature)) {
            return response()->json(['message' => 'Missing KRI ingestion authentication headers.'], 401);
        }

        $allowedSources = $config['allowed_sources'] ?? [];
        if (!empty($allowedSources) && !in_array($source, $allowedSources, true)) {
            return response()->json(['message' => 'KRI ingestion source is not allowed.'], 403);
        }

        $maxSkew = max(30, (int) ($config['max_timestamp_skew_seconds'] ?? 300));
        if (abs(time() - $timestamp) > $maxSkew) {
            return response()->json(['message' => 'KRI ingestion timestamp is outside the accepted window.'], 401);
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $rawPayload, (string) $config['shared_secret']);
        $providedSignature = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        if (!hash_equals($expectedSignature, $providedSignature)) {
            return response()->json(['message' => 'KRI ingestion signature is invalid.'], 401);
        }

        $payload = $request->validate([
            'kri_id' => ['required', 'integer', 'exists:kris,id'],
            'value' => ['required', 'numeric'],
            'recorded_at' => ['nullable', 'date'],
            'source_reference' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $event = KriIngestionEvent::create([
                'source' => $source,
                'event_id' => $eventId,
                'kri_id' => $payload['kri_id'],
                'source_recorded_at' => $payload['recorded_at'] ?? now(),
                'payload_hash' => hash('sha256', $rawPayload),
                'status' => 'accepted',
            ]);
        } catch (QueryException $exception) {
            if ($this->isDuplicateEvent($exception)) {
                return response()->json(['status' => 'duplicate', 'event_id' => $eventId], 202);
            }

            throw $exception;
        }

        try {
            $kri = Kri::query()->findOrFail($payload['kri_id']);
            $kri->forceFill([
                'data_source' => 'api',
                'source_identifier' => $source,
            ])->save();

            $log = DB::transaction(function () use ($kri, $payload, $qualityService, $source) {
                return $qualityService->recordReading($kri, (float) $payload['value'], [
                    'source_recorded_at' => $payload['recorded_at'] ?? now(),
                    'source_reference' => $payload['source_reference'] ?? $source,
                    'notes' => $payload['notes'] ?? 'External API ingestion from ' . $source,
                ]);
            });

            $event->update(['status' => 'processed', 'processed_at' => now()]);

            return response()->json([
                'status' => 'processed',
                'event_id' => $event->event_id,
                'kri_id' => $kri->id,
                'kri_log_id' => $log->id,
                'data_quality_status' => $kri->fresh()->data_quality_status,
            ], 201);
        } catch (\Throwable $exception) {
            report($exception);
            $event->update([
                'status' => 'failed',
                'error_message' => substr($exception->getMessage(), 0, 500),
                'processed_at' => now(),
            ]);

            return response()->json(['message' => 'KRI ingestion could not be processed.', 'event_id' => $event->event_id], 422);
        }
    }

    private function isDuplicateEvent(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'unique') || str_contains($message, 'duplicate');
    }
}

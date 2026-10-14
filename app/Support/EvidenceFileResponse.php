<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;

class EvidenceFileResponse
{
    /**
     * Extensions that are safe to render inline. Everything else (HTML, SVG,
     * archives, binaries) is forced to download so it never executes on the
     * application's origin.
     */
    public const SAFE_INLINE_EXTENSIONS = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt', 'log', 'json', 'csv'];

    private const INLINE_MIME_TYPES = [
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'txt' => 'text/plain; charset=UTF-8',
        'log' => 'text/plain; charset=UTF-8',
        'json' => 'text/plain; charset=UTF-8',
        'csv' => 'text/plain; charset=UTF-8',
    ];

    public static function preview(string $path, string $fileName): BinaryFileResponse
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::SAFE_INLINE_EXTENSIONS, true)) {
            return self::download($path, $fileName);
        }

        return response()->file($path, [
            'Content-Type' => self::INLINE_MIME_TYPES[$extension],
            'Content-Disposition' => self::disposition('inline', $fileName),
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }

    public static function download(string $path, string $fileName): BinaryFileResponse
    {
        return response()->file($path, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => self::disposition('attachment', $fileName),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public static function disposition(string $type, string $fileName): string
    {
        $fileName = str_replace(["\r", "\n", '/', '\\'], '_', $fileName);
        $fallback = Str::ascii($fileName);
        $fallback = preg_replace('/[^\x20-\x7E]|["%]/', '_', $fallback) ?: 'evidence';

        return HeaderUtils::makeDisposition($type, $fileName, $fallback);
    }
}

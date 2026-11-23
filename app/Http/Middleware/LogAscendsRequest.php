<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Diagnostics for Ascend report requests: raise the runtime memory limit,
 * reject payloads above post_max_size with a clear 413, and log request
 * size/status/memory so a client-side failure can be told apart from an
 * API-side one.
 */
class LogAscendsRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('api/internal/ascends/*')) {
            return $next($request);
        }

        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $this->raiseMemoryLimit((string) config('app.ascends_memory_limit', '1024M'));

        $contentLength = (int) $request->header('Content-Length', 0);
        $postMaxSize = $this->toBytes((string) ini_get('post_max_size'));

        if ($contentLength > 0 && $postMaxSize > 0 && $contentLength > $postMaxSize) {
            $this->writeLog($request, 413, 0, $contentLength);

            return response()->json([
                'message' => 'Ukuran request melebihi post_max_size server.',
                'content_length' => $contentLength,
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'memory_limit' => ini_get('memory_limit'),
            ], 413);
        }

        $startedAt = microtime(true);
        $response = $next($request);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $this->writeLog($request, $response->getStatusCode(), $durationMs, $contentLength);

        return $response;
    }

    private function raiseMemoryLimit(string $target): void
    {
        $targetBytes = $this->toBytes($target);
        $currentBytes = $this->toBytes((string) ini_get('memory_limit'));

        if ($targetBytes <= 0) {
            return;
        }

        // -1 or 0 means unlimited; never lower an existing higher limit.
        if ($currentBytes === 0 || $currentBytes < $targetBytes) {
            @ini_set('memory_limit', $target);
        }
    }

    private function writeLog(Request $request, int $status, int $durationMs, int $contentLength): void
    {
        $line = json_encode([
            'time' => now()->toIso8601String(),
            'method' => $request->method(),
            'path' => $request->path(),
            'content_length' => $contentLength,
            'content_type' => $request->header('Content-Type'),
            'status' => $status,
            'duration_ms' => $durationMs,
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ], JSON_UNESCAPED_SLASHES);

        if (is_string($line)) {
            @file_put_contents(storage_path('logs/ascends-requests.log'), $line.PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}

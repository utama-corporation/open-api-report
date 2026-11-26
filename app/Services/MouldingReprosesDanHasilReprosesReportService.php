<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class MouldingReprosesDanHasilReprosesReportService
{
    /**
     * @return array<string, mixed>
     */
    public function fetch(string $startDate, string $endDate): array
    {
        $rows = $this->runProcedureQuery($startDate, $endDate);

        return $this->buildReportData($startDate, $endDate, $rows);
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<string, mixed>
     */
    public function buildReportData(string $startDate, string $endDate, array $rows): array
    {
        $normalized = array_map(function (mixed $row): array {
            $item = (array) $row;

            return [
                'tanggal' => trim((string) ($item['Tanggal'] ?? '')),
                'input_reproses' => $this->toFloat($item['InputReproses'] ?? null),
                'output_reproses' => $this->toFloat($item['OutputReproses'] ?? null),
                'hasil_dari_reproses' => $this->toFloat($item['HasilDariReproses'] ?? null),
            ];
        }, array_values($rows));

        usort($normalized, static fn (array $a, array $b): int => strcmp($a['tanggal'], $b['tanggal']));

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'rows' => $normalized,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function healthCheck(string $startDate, string $endDate): array
    {
        $raw = $this->runProcedureQuery($startDate, $endDate);
        $first = (array) ($raw[0] ?? []);
        $detected = array_keys($first);

        $required = [
            'Tanggal',
            'InputReproses',
            'OutputReproses',
            'HasilDariReproses',
        ];
        $missing = array_values(array_diff($required, $detected));

        return [
            'is_healthy' => $missing === [],
            'required_columns' => $required,
            'detected_columns' => $detected,
            'missing_columns' => $missing,
            'row_count' => count($raw),
        ];
    }

    /**
     * @return array<int, object>
     */
    private function runProcedureQuery(string $startDate, string $endDate): array
    {
        $startDate = trim($startDate);
        $endDate = trim($endDate);

        if ($startDate === '' || $endDate === '') {
            throw new RuntimeException('Tanggal awal dan tanggal akhir wajib diisi.');
        }

        if (strtotime($endDate) === false || strtotime($startDate) === false) {
            throw new RuntimeException('Format tanggal awal atau tanggal akhir tidak valid.');
        }

        if (strtotime($endDate) < strtotime($startDate)) {
            throw new RuntimeException('Tanggal akhir harus sama atau setelah tanggal awal.');
        }

        $configPath = 'reports.moulding_reproses_dan_hasil_reproses';
        $connectionName = config("{$configPath}.database_connection");
        $procedure = (string) config("{$configPath}.stored_procedure", 'SPWpsLaporanMouldingReprosesDanHasilReproses');
        $syntax = (string) config("{$configPath}.call_syntax", 'exec');
        $customQuery = config("{$configPath}.query");
        $parameterCount = (int) config("{$configPath}.parameter_count", 2);

        if ($procedure === '' && ! is_string($customQuery)) {
            throw new RuntimeException('Stored procedure laporan Moulding Reproses dan Hasil Reproses belum dikonfigurasi.');
        }

        $connection = DB::connection($connectionName ?: null);
        $driver = $connection->getDriverName();

        $bindings = [$startDate, $endDate];
        $safeParameterCount = $parameterCount > 0 ? min($parameterCount, count($bindings)) : 0;
        $bindings = array_slice($bindings, 0, $safeParameterCount);

        if ($driver !== 'sqlsrv' && $syntax !== 'query') {
            throw new RuntimeException(
                'Laporan Moulding Reproses dan Hasil Reproses dikonfigurasi untuk SQL Server. '
                .'Set MOULDING_REPROSES_DAN_HASIL_REPROSES_REPORT_CALL_SYNTAX=query jika ingin memakai query manual pada driver lain.',
            );
        }

        if ($syntax === 'query') {
            $query = is_string($customQuery) && trim($customQuery) !== ''
                ? $customQuery
                : throw new RuntimeException(
                    'MOULDING_REPROSES_DAN_HASIL_REPROSES_REPORT_QUERY belum diisi. '
                    .'Isi query manual jika menggunakan MOULDING_REPROSES_DAN_HASIL_REPROSES_REPORT_CALL_SYNTAX=query.',
                );

            return $connection->select($query, str_contains($query, '?') ? $bindings : []);
        }

        if (! preg_match('/^[A-Za-z0-9_$.]+$/', $procedure)) {
            throw new RuntimeException('Nama stored procedure tidak valid.');
        }

        $sql = match ($syntax) {
            'exec' => $this->buildExecSql($procedure, $safeParameterCount),
            'call' => $this->buildCallSql($procedure, $safeParameterCount),
            default => $driver === 'sqlsrv'
                ? $this->buildExecSql($procedure, $safeParameterCount)
                : $this->buildCallSql($procedure, $safeParameterCount),
        };

        return $connection->select("SET NOCOUNT ON; {$sql}", $bindings);
    }

    private function buildExecSql(string $procedure, int $parameterCount): string
    {
        if ($parameterCount <= 0) {
            return "EXEC {$procedure}";
        }

        return "EXEC {$procedure} ".implode(', ', array_fill(0, $parameterCount, '?'));
    }

    private function buildCallSql(string $procedure, int $parameterCount): string
    {
        if ($parameterCount <= 0) {
            return "CALL {$procedure}()";
        }

        return "CALL {$procedure}(".implode(', ', array_fill(0, $parameterCount, '?')).')';
    }

    private function toFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return 0.0;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return 0.0;
        }

        $normalized = str_replace(' ', '', $normalized);

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }
}

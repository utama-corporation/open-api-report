<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PenerimaanStUpahReportService
{
    private const EXPECTED_COLUMNS = [
        'NoPenerimaanST',
        'TglMasuk',
        'NamaCustomer',
        'Jenis',
        'NoTruk',
        'NoPlat',
        'NoSJ',
        'Note',
        'Tebal',
        'Lebar',
        'IdTblLebar',
        'Panjang',
        'IdPanjang',
        'JmlhBatang',
        'Ton',
    ];

    /**
     * @return array<string, mixed>
     */
    public function buildReportData(string $noUpah): array
    {
        $rows = $this->fetchMainRows($noUpah);

        if ($rows === []) {
            throw new RuntimeException('Penerimaan ST Upah tidak ditemukan.');
        }

        $returnedMaterials = $this->buildReturnedMaterials($this->fetchSubRows($noUpah));
        $dimensions = [];
        $lengths = [];
        $woodTypes = [];

        foreach ($rows as $rawRow) {
            $row = (array) $rawRow;
            $woodType = trim((string) ($row['Jenis'] ?? ''));
            if ($woodType !== '') {
                $woodTypes[$woodType] = $woodType;
            }

            $tebal = $this->toPositiveFloat($row['Tebal'] ?? null);
            $lebar = $this->toPositiveFloat($row['Lebar'] ?? null);
            $panjang = $this->toPositiveFloat($row['Panjang'] ?? null);
            $pcs = is_numeric($row['JmlhBatang'] ?? null) ? (int) $row['JmlhBatang'] : 0;

            if ($tebal === null || $lebar === null || $panjang === null || $pcs <= 0) {
                continue;
            }

            $uom = trim((string) ($row['IdTblLebar'] ?? ''));
            $tebalKey = $this->numberKey($tebal);
            $lebarKey = $this->numberKey($lebar);
            $lengthKey = $this->numberKey($panjang);
            $groupKey = implode('|', [$tebalKey, $lebarKey, $uom]);

            $dimensions[$groupKey] ??= [
                'tebal' => $tebal,
                'lebar' => $lebar,
                'uom' => $uom !== '' ? $uom : '-',
                'cells' => [],
                'total_ton' => 0.0,
            ];
            $dimensions[$groupKey]['cells'][$lengthKey] = ($dimensions[$groupKey]['cells'][$lengthKey] ?? 0) + $pcs;
            $dimensions[$groupKey]['total_ton'] += is_numeric($row['Ton'] ?? null) ? (float) $row['Ton'] : 0.0;
            $lengths[$lengthKey] ??= [
                'key' => $lengthKey,
                'label' => $lengthKey,
                'raw_panjang' => $panjang,
            ];
        }

        $lengthColumns = array_values($lengths);
        usort($lengthColumns, static fn (array $left, array $right): int => $left['raw_panjang'] <=> $right['raw_panjang']);

        $totalsByLength = array_fill_keys(array_column($lengthColumns, 'key'), 0);
        $groupedRows = array_values($dimensions);
        foreach ($groupedRows as &$group) {
            foreach ($group['cells'] as $lengthKey => $pcs) {
                $totalsByLength[$lengthKey] += $pcs;
            }
            $group['total_pcs'] = array_sum($group['cells']);
            $group['total_ton'] = round($group['total_ton'], 4);
        }
        unset($group);

        usort($groupedRows, static fn (array $left, array $right): int => $left['tebal'] <=> $right['tebal']
            ?: $left['lebar'] <=> $right['lebar']
            ?: $left['uom'] <=> $right['uom']);

        $tebalGroups = [];
        foreach ($groupedRows as $group) {
            $tebalKey = $this->numberKey($group['tebal']);
            $tebalGroups[$tebalKey] ??= [
                'tebal' => $group['tebal'],
                'rows' => [],
            ];

            unset($group['tebal']);
            $tebalGroups[$tebalKey]['rows'][] = $group;
        }

        $summary = [
            'total_rows' => count($groupedRows),
            'totals_by_length' => $totalsByLength,
            'total_pcs' => array_sum($totalsByLength),
            'total_ton' => round(array_sum(array_column($groupedRows, 'total_ton')), 4),
        ];

        return [
            'no_upah' => $noUpah,
            'header' => $this->buildHeader((array) $rows[0], array_values($woodTypes)),
            'length_columns' => $lengthColumns,
            'tebal_groups' => array_values($tebalGroups),
            'summary' => $summary,
            'returned_materials' => $returnedMaterials,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function healthCheck(string $noUpah): array
    {
        $rows = $this->fetchMainRows($noUpah);
        if ($rows !== []) {
            $this->fetchSubRows($noUpah);
        }
        $detectedColumns = array_keys((array) ($rows[0] ?? []));
        $missingColumns = array_values(array_diff(self::EXPECTED_COLUMNS, $detectedColumns));

        return [
            'is_healthy' => $rows !== [] && $missingColumns === [],
            'expected_columns' => self::EXPECTED_COLUMNS,
            'detected_columns' => $detectedColumns,
            'missing_columns' => $missingColumns,
            'extra_columns' => array_values(array_diff($detectedColumns, self::EXPECTED_COLUMNS)),
            'row_count' => count($rows),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $woodTypes
     * @return array<string, mixed>
     */
    private function buildHeader(array $row, array $woodTypes): array
    {
        return [
            'no_upah' => $this->stringOrDash($row['NoPenerimaanST'] ?? null),
            'tgl_masuk' => $this->dateValue($row['TglMasuk'] ?? null),
            'customer' => $this->stringOrDash($row['NamaCustomer'] ?? null),
            'no_truk' => $this->stringOrDash($row['NoTruk'] ?? null),
            'no_plat' => $this->stringOrDash($row['NoPlat'] ?? null),
            'no_sj' => $this->stringOrDash($row['NoSJ'] ?? null),
            'jenis_kayu' => $woodTypes !== [] ? implode(', ', $woodTypes) : '-',
            'note' => $this->stringOrDash($row['Note'] ?? null),
        ];
    }

    /**
     * @return array<int, object>
     */
    private function fetchMainRows(string $noUpah): array
    {
        return $this->runProcedureQuery('stored_procedure', $noUpah);
    }

    /**
     * @return array<int, object>
     */
    private function fetchSubRows(string $noUpah): array
    {
        return $this->runProcedureQuery('sub_stored_procedure', $noUpah);
    }

    /**
     * @return array<int, object>
     */
    private function runProcedureQuery(string $procedureConfigKey, string $noUpah): array
    {
        $configKey = 'reports.penerimaan_st_upah';
        $connectionName = config("{$configKey}.database_connection");
        $procedure = (string) config("{$configKey}.{$procedureConfigKey}");
        $syntax = (string) config("{$configKey}.call_syntax", 'exec');
        $customQuery = config("{$configKey}.query");

        if ($procedure === '' && ! is_string($customQuery)) {
            throw new RuntimeException('Stored procedure laporan penerimaan ST upah belum dikonfigurasi.');
        }

        $connection = DB::connection($connectionName ?: null);
        $driver = $connection->getDriverName();

        if ($driver !== 'sqlsrv' && $syntax !== 'query') {
            throw new RuntimeException(
                'Laporan penerimaan ST upah dikonfigurasi untuk SQL Server. '
                .'Set PENERIMAAN_ST_UPAH_REPORT_CALL_SYNTAX=query jika ingin memakai query manual pada driver lain.',
            );
        }

        if ($syntax === 'query') {
            $query = is_string($customQuery) && trim($customQuery) !== ''
                ? $customQuery
                : throw new RuntimeException('Query manual laporan penerimaan ST upah belum diisi.');

            return $connection->select($query, str_contains($query, '?') ? [$noUpah] : []);
        }

        if (! preg_match('/^[A-Za-z0-9_$.]+$/', $procedure)) {
            throw new RuntimeException('Nama stored procedure tidak valid.');
        }

        $sql = match ($syntax) {
            'exec' => "SET NOCOUNT ON; EXEC {$procedure} ?",
            'call' => "CALL {$procedure}(?)",
            default => $driver === 'sqlsrv' ? "SET NOCOUNT ON; EXEC {$procedure} ?" : "CALL {$procedure}(?)",
        };

        try {
            return $connection->select($sql, [$noUpah]);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Gagal mengambil data laporan penerimaan ST upah: '.$exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param  array<int, object>  $rows
     * @return array<string, mixed>
     */
    private function buildReturnedMaterials(array $rows): array
    {
        $details = [];

        foreach ($rows as $rawRow) {
            $row = (array) $rawRow;
            $details[] = [
                'no' => (int) ($row['NoUrut'] ?? 0),
                'tebal' => $this->toPositiveFloat($row['Tebal'] ?? null),
                'lebar' => $this->toPositiveFloat($row['Lebar'] ?? null),
                'uom_tbl_lebar' => $this->stringOrDash($row['UOMTblLebar'] ?? null),
                'panjang' => $this->toPositiveFloat($row['Panjang'] ?? null),
                'pcs' => is_numeric($row['JmlhBatang'] ?? null) ? (int) $row['JmlhBatang'] : 0,
                'uom_panjang' => $this->stringOrDash($row['UOMPanjang'] ?? null),
                'ton' => is_numeric($row['Ton'] ?? null) ? (float) $row['Ton'] : 0.0,
            ];
        }

        return [
            'rows' => $details,
            'summary' => [
                'total_pcs' => array_sum(array_column($details, 'pcs')),
                'total_ton' => round(array_sum(array_column($details, 'ton')), 4),
            ],
        ];
    }

    private function toPositiveFloat(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return is_finite($number) && $number > 0 ? $number : null;
    }

    private function numberKey(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
    }

    private function stringOrDash(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : '-';
    }

    private function dateValue(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $date = trim((string) ($value ?? ''));

        return $date !== '' ? $date : null;
    }
}

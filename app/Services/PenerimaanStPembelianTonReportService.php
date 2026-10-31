<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PenerimaanStPembelianTonReportService
{
    private const EXPECTED_COLUMNS = [
        'NoPenerimaanST',
        'TglLaporan',
        'TglMasuk',
        'Supplier',
        'NoTruk',
        'NoPlat',
        'Suket',
        'TonSuratJalan',
        'Note',
        'SuratTanah',
        'IsKering',
        'JenisKayu',
        'IdUOMTblLebar',
        'Tebal',
        'Lebar',
        'Panjang',
        'JmlhBatang',
    ];

    /**
     * @return array<string, mixed>
     */
    public function buildReportData(string $noPenerimaanST): array
    {
        $rows = $this->fetchRows($noPenerimaanST);

        if ($rows === []) {
            throw new RuntimeException('Penerimaan ST tidak ditemukan.');
        }

        $returnedMaterials = $this->buildReturnedMaterials($this->fetchReturnedMaterialRows($noPenerimaanST));
        $dimensions = [];
        $lengths = [];
        $woodTypes = [];

        foreach ($rows as $rawRow) {
            $row = (array) $rawRow;
            $woodType = trim((string) ($row['JenisKayu'] ?? ''));
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

            $uomId = (int) ($row['IdUOMTblLebar'] ?? 0);
            $tebalKey = $this->numberKey($tebal);
            $lebarKey = $this->numberKey($lebar);
            $lengthKey = $this->numberKey($panjang);
            $groupKey = implode('|', [$tebalKey, $lebarKey, $uomId]);

            $dimensions[$groupKey] ??= [
                'tebal' => $tebal,
                'lebar' => $lebar,
                'uom_id' => $uomId,
                'uom' => $this->widthUom($uomId),
                'cells' => [],
                'total_ton' => 0.0,
            ];
            $dimensions[$groupKey]['cells'][$lengthKey] = ($dimensions[$groupKey]['cells'][$lengthKey] ?? 0) + $pcs;
            $dimensions[$groupKey]['total_ton'] += $this->calculateTon($tebal, $lebar, $panjang, $pcs, $uomId);
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
            ?: $left['uom_id'] <=> $right['uom_id']);

        $tebalGroups = [];
        foreach ($groupedRows as $group) {
            $tebalKey = $this->numberKey($group['tebal']);
            $tebalGroups[$tebalKey] ??= [
                'tebal' => $group['tebal'],
                'rows' => [],
            ];

            unset($group['tebal'], $group['uom_id']);
            $tebalGroups[$tebalKey]['rows'][] = $group;
        }

        $summary = [
            'total_rows' => count($groupedRows),
            'totals_by_length' => $totalsByLength,
            'total_pcs' => array_sum($totalsByLength),
            'total_ton' => round(array_sum(array_column($groupedRows, 'total_ton')), 4),
        ];

        return [
            'no_penerimaan_st' => $noPenerimaanST,
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
    public function healthCheck(string $noPenerimaanST): array
    {
        $rows = $this->fetchRows($noPenerimaanST);
        if ($rows !== []) {
            $this->fetchReturnedMaterialRows($noPenerimaanST);
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
        $isKering = $row['IsKering'] ?? null;
        $tonSuratJalan = $row['TonSuratJalan'] ?? null;

        return [
            'no_penerimaan_st' => $this->stringOrDash($row['NoPenerimaanST'] ?? null),
            'tgl_laporan' => $this->dateValue($row['TglLaporan'] ?? null),
            'tgl_masuk' => $this->dateValue($row['TglMasuk'] ?? null),
            'supplier' => $this->stringOrDash($row['Supplier'] ?? null),
            'no_truk' => $this->stringOrDash($row['NoTruk'] ?? null),
            'no_plat' => $this->stringOrDash($row['NoPlat'] ?? null),
            'no_suket' => $this->stringOrDash($row['Suket'] ?? null),
            'ton_surat_jalan' => is_numeric($tonSuratJalan) ? (float) $tonSuratJalan : null,
            'surat_tanah' => $this->stringOrDash($row['SuratTanah'] ?? null),
            'jenis_kayu' => $woodTypes !== [] ? implode(', ', $woodTypes) : '-',
            'kondisi' => $isKering === null ? '-' : ((int) $isKering === 1 ? 'Kering' : 'Basah'),
            'note' => $this->stringOrDash($row['Note'] ?? null),
        ];
    }

    /**
     * @return array<int, object>
     */
    private function fetchRows(string $noPenerimaanST): array
    {
        $sql = <<<'SQL'
SELECT
    p.NoPenerimaanST,
    p.TglLaporan,
    p.TglMasuk,
    s.NmSupplier AS Supplier,
    p.NoTruk,
    p.NoPlat,
    p.Suket,
    p.TonSJ AS TonSuratJalan,
    p.Note,
    t.NamaTanah AS SuratTanah,
    p.IsKering,
    j.Jenis AS JenisKayu,
    st.IdUOMTblLebar,
    d.Tebal,
    d.Lebar,
    d.Panjang,
    d.JmlhBatang
FROM PenerimaanSTPembelian_h AS p
LEFT JOIN MstSupplier AS s ON s.IdSupplier = p.IdSupplier
LEFT JOIN MstSuratTanah AS t ON t.IdTanah = p.IdTanah
LEFT JOIN PenerimaanSTPembelian_d AS receipt_detail ON receipt_detail.NoPenerimaanST = p.NoPenerimaanST
LEFT JOIN ST_h AS st ON st.NoST = receipt_detail.NoST
LEFT JOIN ST_d AS d ON d.NoST = st.NoST
LEFT JOIN MstJenisKayu AS j ON j.IdJenisKayu = st.IdJenisKayu
WHERE p.NoPenerimaanST = ?
ORDER BY d.Tebal, d.Lebar, st.IdUOMTblLebar, d.Panjang
SQL;

        return $this->selectRows($sql, $noPenerimaanST);
    }

    /**
     * @return array<int, object>
     */
    private function fetchReturnedMaterialRows(string $noPenerimaanST): array
    {
        $sql = <<<'SQL'
SELECT
    rejected.NoUrut,
    rejected.Tebal,
    rejected.Lebar,
    rejected.Panjang,
    rejected.JmlhBatang,
    width_uom.UOM AS UOMTblLebar,
    length_uom.UOM AS UOMPanjang,
    rejected.Ton
FROM PenerimaanSTPembelian_Reject AS rejected
LEFT JOIN MstUOM AS width_uom ON width_uom.IdUOM = rejected.IdUOMTblLebar
LEFT JOIN MstUOM AS length_uom ON length_uom.IdUOM = rejected.IdUOMPanjang
WHERE rejected.NoPenerimaanST = ?
ORDER BY rejected.NoUrut
SQL;

        return $this->selectRows($sql, $noPenerimaanST);
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

    /**
     * @return array<int, object>
     */
    private function selectRows(string $sql, string $noPenerimaanST): array
    {
        try {
            $connectionName = config('reports.penerimaan_st_pembelian_ton.database_connection');

            return DB::connection($connectionName ?: null)->select($sql, [$noPenerimaanST]);
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                'Gagal mengambil data laporan penerimaan ST pembelian: '.$exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    private function calculateTon(float $tebal, float $lebar, float $panjang, int $pcs, int $uomId): float
    {
        // shortcut: non-1 UOMs use the inch formula, upgrade if another width UOM is introduced.
        $raw = $uomId === 1
            ? ($tebal * $lebar * $panjang * $pcs * 304.8) / 1000000000 / 1.416
            : ($tebal * $lebar * $panjang * $pcs) / 7200.8;

        return floor($raw * 10000) / 10000;
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

    private function widthUom(int $uomId): string
    {
        return match ($uomId) {
            1 => 'mm',
            3 => 'inch',
            default => '-',
        };
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

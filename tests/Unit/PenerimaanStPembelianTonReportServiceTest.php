<?php

namespace Tests\Unit;

use App\Services\PenerimaanStPembelianTonReportService;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PenerimaanStPembelianTonReportServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_aggregates_pieces_and_matches_the_reference_row_ton(): void
    {
        $service = $this->serviceWithRows([
            $this->row(2.5, 12),
            $this->row(3, 6),
            $this->row(3.5, 4),
            $this->row(4, 116),
        ]);

        $report = $service->buildReportData('B.160214');
        $row = $report['tebal_groups'][0]['rows'][0];

        $this->assertSame(['2.5', '3', '3.5', '4'], array_column($report['length_columns'], 'key'));
        $this->assertSame(['2.5' => 12, '3' => 6, '3.5' => 4, '4' => 116], $row['cells']);
        $this->assertSame(138, $row['total_pcs']);
        $this->assertEqualsWithDelta(0.1332, $row['total_ton'], 0.0000001);
        $this->assertSame(138, $report['summary']['total_pcs']);
        $this->assertEqualsWithDelta(0.1332, $report['summary']['total_ton'], 0.0000001);
        $this->assertSame('Kering', $report['header']['kondisi']);
    }

    public function test_it_floors_ton_per_raw_detail_before_summing_same_dimensions(): void
    {
        $details = [
            [2, 1], [2.5, 3], [3, 26], [3, 1], [3, 2],
            [3.5, 3], [3.5, 2], [3.5, 18], [3.5, 6], [3.5, 1], [3.5, 4],
            [4, 109], [4, 118], [4, 231], [4, 111], [4, 144],
        ];
        $rows = array_map(
            fn (array $detail): object => $this->row($detail[0], $detail[1], 1, 44, 55),
            $details,
        );
        $service = $this->serviceWithRows($rows);

        $report = $service->buildReportData('B.160214');
        $tebalGroup = array_values(array_filter(
            $report['tebal_groups'],
            static fn (array $group): bool => (float) $group['tebal'] === 44.0,
        ))[0];
        $row = array_values(array_filter(
            $tebalGroup['rows'],
            static fn (array $dimension): bool => (float) $dimension['lebar'] === 55.0,
        ))[0];

        $this->assertSame(780, $row['total_pcs']);
        $this->assertEqualsWithDelta(1.5972, $row['total_ton'], 0.0000001);
    }

    public function test_it_uses_the_inch_width_formula_for_uom_three(): void
    {
        $service = $this->serviceWithRows([$this->row(2, 1, 3)]);

        $report = $service->buildReportData('B.160214');

        $this->assertSame('inch', $report['tebal_groups'][0]['rows'][0]['uom']);
        $this->assertEqualsWithDelta(0.3271, $report['summary']['total_ton'], 0.0000001);
    }

    public function test_it_keeps_nonstandard_lengths_in_numeric_order(): void
    {
        $service = $this->serviceWithRows([
            $this->row(10, 1),
            $this->row(4.5, 2),
            $this->row(2, 3),
        ]);

        $report = $service->buildReportData('B.160214');

        $this->assertSame(['2', '4.5', '10'], array_column($report['length_columns'], 'key'));
        $this->assertSame(['2' => 3, '4.5' => 2, '10' => 1], $report['summary']['totals_by_length']);
    }

    public function test_it_rejects_a_receipt_without_a_header(): void
    {
        $service = $this->serviceWithRows([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Penerimaan ST tidak ditemukan');

        $service->buildReportData('B.160214');
    }

    public function test_it_preserves_a_missing_ton_surat_jalan_value(): void
    {
        $row = $this->row(2.5, 12);
        $row->TonSuratJalan = null;
        $service = $this->serviceWithRows([$row]);

        $report = $service->buildReportData('B.160214');

        $this->assertNull($report['header']['ton_surat_jalan']);
    }

    public function test_it_includes_returned_material_rows_and_totals(): void
    {
        $receipt = $this->row(2, 1);
        $receipt->NoPenerimaanST = 'B.160187';
        $returnedRows = [
            (object) ['NoUrut' => 1, 'Tebal' => 44, 'Lebar' => 79, 'Panjang' => 4, 'JmlhBatang' => 72, 'UOMTblLebar' => 'mm', 'UOMPanjang' => 'feet', 'Ton' => 0.2154],
            (object) ['NoUrut' => 2, 'Tebal' => 44, 'Lebar' => 43, 'Panjang' => 4, 'JmlhBatang' => 62, 'UOMTblLebar' => 'mm', 'UOMPanjang' => 'feet', 'Ton' => 0.1010],
            (object) ['NoUrut' => 3, 'Tebal' => 44, 'Lebar' => 95, 'Panjang' => 4, 'JmlhBatang' => 38, 'UOMTblLebar' => 'mm', 'UOMPanjang' => 'feet', 'Ton' => 0.1367],
            (object) ['NoUrut' => 4, 'Tebal' => 16, 'Lebar' => 95, 'Panjang' => 4, 'JmlhBatang' => 15, 'UOMTblLebar' => 'mm', 'UOMPanjang' => 'feet', 'Ton' => 0.0196],
            (object) ['NoUrut' => 5, 'Tebal' => 16, 'Lebar' => 43, 'Panjang' => 4, 'JmlhBatang' => 44, 'UOMTblLebar' => 'mm', 'UOMPanjang' => 'feet', 'Ton' => 0.0260],
            (object) ['NoUrut' => 6, 'Tebal' => 32, 'Lebar' => 43, 'Panjang' => 4, 'JmlhBatang' => 41, 'UOMTblLebar' => 'mm', 'UOMPanjang' => 'feet', 'Ton' => 0.0485],
            (object) ['NoUrut' => 7, 'Tebal' => 32, 'Lebar' => 95, 'Panjang' => 4, 'JmlhBatang' => 8, 'UOMTblLebar' => 'mm', 'UOMPanjang' => 'feet', 'Ton' => 0.0209],
        ];
        $service = $this->serviceWithRows([$receipt], $returnedRows, 'B.160187');

        $report = $service->buildReportData('B.160187');

        $this->assertSame([
            'no' => 1,
            'tebal' => 44.0,
            'lebar' => 79.0,
            'uom_tbl_lebar' => 'mm',
            'panjang' => 4.0,
            'pcs' => 72,
            'uom_panjang' => 'feet',
            'ton' => 0.2154,
        ], $report['returned_materials']['rows'][0]);
        $this->assertSame(7, count($report['returned_materials']['rows']));
        $this->assertSame(280, $report['returned_materials']['summary']['total_pcs']);
        $this->assertEqualsWithDelta(0.5681, $report['returned_materials']['summary']['total_ton'], 0.0000001);
    }

    public function test_health_check_verifies_the_returned_material_source_query(): void
    {
        $service = $this->serviceWithRows([$this->row(2, 1)]);

        $health = $service->healthCheck('B.160214');

        $this->assertTrue($health['is_healthy']);
    }

    /**
     * @param  array<int, object>  $rows
     */
    private function serviceWithRows(
        array $rows,
        array $returnedRows = [],
        string $receiptNumber = 'B.160214',
    ): PenerimaanStPembelianTonReportService {
        config()->set('reports.penerimaan_st_pembelian_ton.database_connection', 'sqlsrv');

        $connection = Mockery::mock();
        $connection->shouldReceive('select')
            ->once()
            ->ordered()
            ->withArgs(function (string $query, array $bindings) use ($receiptNumber): bool {
                $this->assertStringContainsString('WHERE p.NoPenerimaanST = ?', $query);
                $this->assertSame([$receiptNumber], $bindings);
                $this->assertStringNotContainsString($receiptNumber, $query);

                return true;
            })
            ->andReturn($rows);

        if ($rows !== []) {
            $connection->shouldReceive('select')
                ->once()
                ->ordered()
                ->withArgs(function (string $query, array $bindings) use ($receiptNumber): bool {
                    $this->assertStringContainsString('FROM PenerimaanSTPembelian_Reject AS rejected', $query);
                    $this->assertStringContainsString('LEFT JOIN MstUOM AS width_uom', $query);
                    $this->assertStringContainsString('LEFT JOIN MstUOM AS length_uom', $query);
                    $this->assertStringContainsString('WHERE rejected.NoPenerimaanST = ?', $query);
                    $this->assertSame([$receiptNumber], $bindings);
                    $this->assertStringNotContainsString($receiptNumber, $query);

                    return true;
                })
                ->andReturn($returnedRows);
        }

        DB::shouldReceive('connection')
            ->times($rows === [] ? 1 : 2)
            ->with('sqlsrv')
            ->andReturn($connection);

        return new PenerimaanStPembelianTonReportService;
    }

    private function row(
        float $panjang,
        int $pcs,
        int $idUomTblLebar = 1,
        float $tebal = 31,
        float $lebar = 38,
    ): object {
        return (object) [
            'NoPenerimaanST' => 'B.160214',
            'TglLaporan' => '2026-10-31',
            'TglMasuk' => '2026-10-30',
            'Supplier' => 'IWAN SALIM',
            'NoTruk' => 1,
            'NoPlat' => 'BK 8641 GQ',
            'Suket' => 'SJ/STTW/2610/0047',
            'TonSuratJalan' => 13.7641,
            'Note' => 'FISIK SESUAI',
            'SuratTanah' => 'LUKMAN-1',
            'IsKering' => 1,
            'JenisKayu' => 'RAMBUNG - STD',
            'IdUOMTblLebar' => $idUomTblLebar,
            'Tebal' => $tebal,
            'Lebar' => $lebar,
            'Panjang' => $panjang,
            'JmlhBatang' => $pcs,
        ];
    }
}

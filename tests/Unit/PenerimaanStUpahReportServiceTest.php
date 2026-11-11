<?php

namespace Tests\Unit;

use App\Services\PenerimaanStUpahReportService;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PenerimaanStUpahReportServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_aggregates_pieces_and_sums_the_stored_ton(): void
    {
        $service = $this->serviceWithRows([
            $this->row(2.5, 12, 0.04),
            $this->row(3, 6, 0.03),
            $this->row(3.5, 4, 0.02),
            $this->row(4, 116, 0.0432),
        ]);

        $report = $service->buildReportData('O.000057');
        $row = $report['tebal_groups'][0]['rows'][0];

        $this->assertSame(['2.5', '3', '3.5', '4'], array_column($report['length_columns'], 'key'));
        $this->assertSame(['2.5' => 12, '3' => 6, '3.5' => 4, '4' => 116], $row['cells']);
        $this->assertSame(138, $row['total_pcs']);
        $this->assertEqualsWithDelta(0.1332, $row['total_ton'], 0.0000001);
        $this->assertSame(138, $report['summary']['total_pcs']);
        $this->assertEqualsWithDelta(0.1332, $report['summary']['total_ton'], 0.0000001);
        $this->assertSame('PAK BENYAMIN', $report['header']['customer']);
    }

    public function test_it_sums_pieces_and_ton_of_identical_dimensions(): void
    {
        $service = $this->serviceWithRows([
            $this->row(7, 199, 0.7738, 'inch', 1, 4),
            $this->row(8, 11, 0.0488, 'inch', 1, 4),
            $this->row(7, 180, 0.6999, 'inch', 1, 4),
        ]);

        $report = $service->buildReportData('O.000057');
        $row = $report['tebal_groups'][0]['rows'][0];

        $this->assertSame(['7' => 379, '8' => 11], $row['cells']);
        $this->assertSame(390, $row['total_pcs']);
        $this->assertEqualsWithDelta(1.5225, $row['total_ton'], 0.0000001);
    }

    public function test_it_passes_through_the_sp_uom_string(): void
    {
        $service = $this->serviceWithRows([$this->row(7, 199, 0.7738, 'inch')]);

        $report = $service->buildReportData('O.000057');

        $this->assertSame('inch', $report['tebal_groups'][0]['rows'][0]['uom']);
    }

    public function test_it_keeps_nonstandard_lengths_in_numeric_order(): void
    {
        $service = $this->serviceWithRows([
            $this->row(10, 1, 0.01),
            $this->row(4.5, 2, 0.02),
            $this->row(2, 3, 0.03),
        ]);

        $report = $service->buildReportData('O.000057');

        $this->assertSame(['2', '4.5', '10'], array_column($report['length_columns'], 'key'));
        $this->assertSame(['2' => 3, '4.5' => 2, '10' => 1], $report['summary']['totals_by_length']);
    }

    public function test_it_rejects_a_receipt_without_a_header(): void
    {
        $service = $this->serviceWithRows([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Penerimaan ST Upah tidak ditemukan');

        $service->buildReportData('O.000057');
    }

    public function test_it_defaults_a_missing_no_sj_to_dash(): void
    {
        $row = $this->row(2.5, 12, 0.04);
        $row->NoSJ = null;
        $service = $this->serviceWithRows([$row]);

        $report = $service->buildReportData('O.000057');

        $this->assertSame('-', $report['header']['no_sj']);
    }

    public function test_it_includes_returned_material_rows_and_totals(): void
    {
        $receipt = $this->row(2, 1, 0.01);
        $receipt->NoPenerimaanST = 'O.000001';
        $returnedRows = [
            (object) ['NoUrut' => 1, 'NoST' => 'E.491490', 'Tebal' => 1, 'Lebar' => 4, 'Panjang' => 7, 'JmlhBatang' => 199, 'UOMTblLebar' => 'inch', 'UOMPanjang' => 'feet', 'Ton' => 0.7738],
            (object) ['NoUrut' => 2, 'NoST' => 'E.491490', 'Tebal' => 1, 'Lebar' => 4, 'Panjang' => 8, 'JmlhBatang' => 11, 'UOMTblLebar' => 'inch', 'UOMPanjang' => 'feet', 'Ton' => 0.0488],
            (object) ['NoUrut' => 3, 'NoST' => 'E.491521', 'Tebal' => 1, 'Lebar' => 4, 'Panjang' => 4, 'JmlhBatang' => 11, 'UOMTblLebar' => 'inch', 'UOMPanjang' => 'feet', 'Ton' => 0.0244],
            (object) ['NoUrut' => 4, 'NoST' => 'E.491521', 'Tebal' => 1, 'Lebar' => 4, 'Panjang' => 5, 'JmlhBatang' => 10, 'UOMTblLebar' => 'inch', 'UOMPanjang' => 'feet', 'Ton' => 0.0277],
            (object) ['NoUrut' => 5, 'NoST' => 'E.491521', 'Tebal' => 1, 'Lebar' => 4, 'Panjang' => 6, 'JmlhBatang' => 9, 'UOMTblLebar' => 'inch', 'UOMPanjang' => 'feet', 'Ton' => 0.0299],
            (object) ['NoUrut' => 6, 'NoST' => 'E.491521', 'Tebal' => 1, 'Lebar' => 4, 'Panjang' => 7, 'JmlhBatang' => 180, 'UOMTblLebar' => 'inch', 'UOMPanjang' => 'feet', 'Ton' => 0.6999],
            (object) ['NoUrut' => 7, 'NoST' => 'E.491523', 'Tebal' => 1, 'Lebar' => 3, 'Panjang' => 7, 'JmlhBatang' => 146, 'UOMTblLebar' => 'inch', 'UOMPanjang' => 'feet', 'Ton' => 0.4257],
        ];
        $service = $this->serviceWithRows([$receipt], $returnedRows, 'O.000001');

        $report = $service->buildReportData('O.000001');

        $this->assertSame([
            'no' => 1,
            'tebal' => 1.0,
            'lebar' => 4.0,
            'uom_tbl_lebar' => 'inch',
            'panjang' => 7.0,
            'pcs' => 199,
            'uom_panjang' => 'feet',
            'ton' => 0.7738,
        ], $report['returned_materials']['rows'][0]);
        $this->assertSame(7, count($report['returned_materials']['rows']));
        $this->assertSame(566, $report['returned_materials']['summary']['total_pcs']);
        $this->assertEqualsWithDelta(2.0302, $report['returned_materials']['summary']['total_ton'], 0.0000001);
    }

    public function test_health_check_verifies_the_returned_material_source_query(): void
    {
        $service = $this->serviceWithRows([$this->row(2, 1, 0.01)]);

        $health = $service->healthCheck('O.000057');

        $this->assertTrue($health['is_healthy']);
    }

    /**
     * @param  array<int, object>  $rows
     */
    private function serviceWithRows(
        array $rows,
        array $returnedRows = [],
        string $receiptNumber = 'O.000057',
    ): PenerimaanStUpahReportService {
        config()->set('reports.penerimaan_st_upah.database_connection', 'sqlsrv');

        $connection = Mockery::mock();
        $connection->shouldReceive('getDriverName')
            ->andReturn('sqlsrv');

        $connection->shouldReceive('select')
            ->once()
            ->ordered()
            ->withArgs(function (string $query, array $bindings) use ($receiptNumber): bool {
                $this->assertStringContainsString('EXEC SPWps_LapPenSTSawmilUpah ?', $query);
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
                    $this->assertStringContainsString('EXEC SPWps_LapPenSTSawmilUpahSub ?', $query);
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

        return new PenerimaanStUpahReportService;
    }

    private function row(
        float $panjang,
        int $pcs,
        ?float $ton = null,
        string $uomTblLebar = 'mm',
        float $tebal = 31,
        float $lebar = 38,
    ): object {
        return (object) [
            'NoPenerimaanST' => 'O.000057',
            'TglMasuk' => '2025-04-28',
            'NamaCustomer' => 'PAK BENYAMIN',
            'Jenis' => 'MAHONI',
            'NoTruk' => 53,
            'NoPlat' => 'BK 9615 CL',
            'NoSJ' => '01/IV/MJ/TT/25',
            'Note' => 'UPAH STICK KD DAN BONGKAR STICK',
            'Tebal' => $tebal,
            'Lebar' => $lebar,
            'IdTblLebar' => $uomTblLebar,
            'Panjang' => $panjang,
            'IdPanjang' => 'feet',
            'JmlhBatang' => $pcs,
            'Ton' => $ton ?? 0.0,
        ];
    }
}

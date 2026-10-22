<?php

namespace Tests\Unit;

use App\Services\RekapPembelianKayuBulatKgReportService;
use Tests\TestCase;

class RekapPembelianKayuBulatKgReportServiceTest extends TestCase
{
    public function test_year_rows_include_harga_and_summary_statistics(): void
    {
        $year = (int) now()->format('Y');

        $service = new class extends RekapPembelianKayuBulatKgReportService
        {
            /**
             * @var array<int, array<string, mixed>>
             */
            public array $rows = [];

            /**
             * @return array<int, array<string, mixed>>
             */
            public function fetch(): array
            {
                return $this->rows;
            }
        };

        $service->rows = [
            ['Tahun' => $year, 'Bulan' => 1, 'Ton' => 100.4, 'Harga' => 500.0],
            ['Tahun' => $year, 'Bulan' => 2, 'Ton' => 200.6, 'Harga' => 600.0],
            ['Tahun' => $year, 'Bulan' => 3, 'Ton' => 0.0, 'Harga' => null],
        ];

        $reportData = $service->buildReportData();
        $yearRow = $reportData['year_rows'][count($reportData['year_rows']) - 1];

        $this->assertSame($year, $yearRow['tahun']);
        $this->assertSame(100.4, $yearRow['months'][1]);
        $this->assertSame(200.6, $yearRow['months'][2]);
        $this->assertSame(500.0, $yearRow['harga_months'][1]);
        $this->assertSame(600.0, $yearRow['harga_months'][2]);
        $this->assertNull($yearRow['harga_months'][3]);
        $this->assertEqualsWithDelta(301.0, $yearRow['total'], 0.0001);

        $expectedTotalHarga = ((500.0 * 100.4) + (600.0 * 200.6)) / 301.0;
        $this->assertEqualsWithDelta($expectedTotalHarga, $yearRow['total_harga'], 0.0001);
        $this->assertEqualsWithDelta(150.5, $yearRow['berat_average'], 0.0001);
        $this->assertEqualsWithDelta(100.4, $yearRow['berat_min'], 0.0001);
        $this->assertEqualsWithDelta(200.6, $yearRow['berat_max'], 0.0001);
        $this->assertEqualsWithDelta(550.0, $yearRow['harga_average'], 0.0001);
        $this->assertEqualsWithDelta(500.0, $yearRow['harga_min'], 0.0001);
        $this->assertEqualsWithDelta(600.0, $yearRow['harga_max'], 0.0001);
    }
}

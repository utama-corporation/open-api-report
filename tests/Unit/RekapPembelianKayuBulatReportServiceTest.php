<?php

namespace Tests\Unit;

use App\Services\RekapPembelianKayuBulatReportService;
use Tests\TestCase;

class RekapPembelianKayuBulatReportServiceTest extends TestCase
{
    public function test_year_rows_include_average_min_and_max_from_active_months(): void
    {
        $year = (int) now()->format('Y');

        $service = new class extends RekapPembelianKayuBulatReportService
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
            ['Tahun' => $year, 'Bulan' => 1, 'Ton' => 100.4],
            ['Tahun' => $year, 'Bulan' => 2, 'Ton' => 200.6],
            ['Tahun' => $year, 'Bulan' => 3, 'Ton' => 0.0],
        ];

        $reportData = $service->buildReportData();
        $yearRow = $reportData['year_rows'][count($reportData['year_rows']) - 1];

        $this->assertSame($year, $yearRow['tahun']);
        $this->assertSame(100.4, $yearRow['months'][1]);
        $this->assertSame(200.6, $yearRow['months'][2]);
        $this->assertEqualsWithDelta(301.0, $yearRow['total'], 0.0001);
        $this->assertEqualsWithDelta(150.5, $yearRow['average'], 0.0001);
        $this->assertEqualsWithDelta(100.4, $yearRow['min'], 0.0001);
        $this->assertEqualsWithDelta(200.6, $yearRow['max'], 0.0001);
    }
}

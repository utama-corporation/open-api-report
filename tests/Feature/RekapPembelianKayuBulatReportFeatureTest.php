<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PdfGenerator;
use App\Services\RekapPembelianKayuBulatReportService;
use Mockery;
use Tests\TestCase;

class RekapPembelianKayuBulatReportFeatureTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_form_page_is_accessible(): void
    {
        $this->get('/reports/kayu-bulat/rekap-pembelian?start_year=2025&end_year=2026')
            ->assertOk()
            ->assertSee('Rekap Pembelian Kayu Bulat')
            ->assertSee('Preview PDF');
    }

    public function test_pdf_download_endpoint_returns_attachment(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(RekapPembelianKayuBulatReportService::class);
        $service
            ->shouldReceive('buildReportData')
            ->once()
            ->andReturn([
                'rows' => [],
                'table_rows' => [],
                'grand_total' => 0.0,
                'chart_years' => [2025, 2026],
                'chart_month_labels' => ['Jan'],
                'chart_series_by_year' => [2025 => [0], 2026 => [0]],
                'yearly_totals' => [2025 => 0, 2026 => 0],
            ]);

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator
            ->shouldReceive('render')
            ->once()
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(RekapPembelianKayuBulatReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $response = $this->actingAs($user)
            ->post('/reports/kayu-bulat/rekap-pembelian/download', ['start_year' => 2025, 'end_year' => 2026]);
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertPdfDisposition($response, 'attachment', 'Laporan Rekap Pembelian Kayu Bulat');
    }

    public function test_pdf_preview_endpoint_returns_attachment_disposition(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(RekapPembelianKayuBulatReportService::class);
        $service
            ->shouldReceive('buildReportData')
            ->once()
            ->andReturn([
                'rows' => [],
                'table_rows' => [],
                'grand_total' => 0.0,
                'chart_years' => [2025, 2026],
                'chart_month_labels' => ['Jan'],
                'chart_series_by_year' => [2025 => [0], 2026 => [0]],
                'yearly_totals' => [2025 => 0, 2026 => 0],
            ]);

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator
            ->shouldReceive('render')
            ->once()
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(RekapPembelianKayuBulatReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $response = $this->actingAs($user, 'web')
            ->post('/reports/kayu-bulat/rekap-pembelian/download', ['start_year' => 2025, 'end_year' => 2026, 'preview_pdf' => 1])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertPdfDisposition($response, 'attachment', 'Laporan Rekap Pembelian Kayu Bulat');
    }
}

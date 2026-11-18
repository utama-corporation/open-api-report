<?php

namespace Tests\Feature\Ascends;

use App\Http\Controllers\Ascends\CompanyReportController;
use App\Services\Ascends\Shared\Associate\CustomerBaruPerTahunReportService;
use App\Services\Ascends\Shared\Associate\CustomerBaruReportService;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangDiatas60HariReportService;
use App\Services\Ascends\Shared\Finance\ReceivableSummary\UmurPiutangRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LabaKotorRuReportService;
use App\Services\Ascends\Shared\Hrm\UsiaGenerasiTahunKelahiranMasaKerjaReportService;
use App\Services\PdfGenerator;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class CompanyReportControllerTest extends TestCase
{
    private const REPORT_SLUG = 'finance.receivable_details.piutang-diatas-60-hari';

    protected function setUp(): void
    {
        parent::setUp();

        // Route registration is Task 4; these mirror the routes it will generate.
        Route::post(
            '/api/internal/ascends/{company}/finance/receivable_details/piutang-diatas-60-hari/pdf',
            [CompanyReportController::class, 'pdf']
        )->defaults('report', self::REPORT_SLUG);

        Route::post(
            '/api/internal/ascends/{company}/general_ledger/journal_details/laba-kotor/pdf',
            [CompanyReportController::class, 'pdf']
        )->defaults('report', 'general_ledger.journal_details.laba-kotor');

        Route::post(
            '/api/internal/ascends/{company}/associate/customer-baru/pdf',
            [CompanyReportController::class, 'pdf']
        )->defaults('report', 'associate.customer-baru');

        Route::post(
            '/api/internal/ascends/{company}/associate/customer-baru-per-tahun/pdf',
            [CompanyReportController::class, 'pdf']
        )->defaults('report', 'associate.customer-baru-per-tahun');
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_uses_company_from_route_and_ignores_client_db_company_name(): void
    {
        $xml = '<root/>';

        $service = Mockery::mock(PiutangDiatas60HariReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->with($xml, 'request field: xml', Mockery::on(
                static fn (array $filters): bool => ($filters['company'] ?? null) === 'RU'
                && ($filters['DB_CompanyName'] ?? null) === 'RU'
            ))
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator
            ->shouldReceive('render')
            ->once()
            ->with('ascends.shared.finance.receivable_details.piutang_diatas_60_hari.pdf', Mockery::on(
                static fn (array $data): bool => ($data['company'] ?? null) === 'RU'
                && ($data['pdf_orientation'] ?? null) === 'portrait'
                && ($data['pdf_column_count'] ?? null) === 2
            ))
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(PiutangDiatas60HariReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $response = $this->post('/api/internal/ascends/ru/finance/receivable_details/piutang-diatas-60-hari/pdf', [
            'xml' => $xml,
            'company' => 'GSU',
            'DB_CompanyName' => 'GSU',
        ])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertPdfDisposition($response, 'attachment', 'Laporan Umur Piutang Di Atas 60 Hari (RU).pdf');
    }

    public function test_works_without_db_company_name(): void
    {
        $xml = '<root/>';

        $service = Mockery::mock(PiutangDiatas60HariReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->with($xml, 'request field: xml', Mockery::on(
                static fn (array $filters): bool => ($filters['company'] ?? null) === 'RU'
                && ($filters['DB_CompanyName'] ?? null) === 'RU'
            ))
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator->shouldReceive('render')->once()->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(PiutangDiatas60HariReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $this->post('/api/internal/ascends/ru/finance/receivable_details/piutang-diatas-60-hari/pdf', [
            'xml' => $xml,
        ])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_keeps_pinned_column_count_for_ru_laba_kotor(): void
    {
        $xml = '<root/>';

        $service = Mockery::mock(LabaKotorRuReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator
            ->shouldReceive('render')
            ->once()
            ->with('ascends.shared.general_ledger.journal_details.laba_kotor_ru.pdf', Mockery::on(
                static fn (array $data): bool => ($data['pdf_column_count'] ?? null) === 6
                && ($data['pdf_orientation'] ?? null) === 'portrait'
            ))
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(LabaKotorRuReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $this->post('/api/internal/ascends/ru/general_ledger/journal_details/laba-kotor/pdf', [
            'xml' => $xml,
        ])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_passes_printed_by_as_generated_by_name(): void
    {
        $xml = '<root/>';

        $service = Mockery::mock(PiutangDiatas60HariReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator
            ->shouldReceive('render')
            ->once()
            ->with('ascends.shared.finance.receivable_details.piutang_diatas_60_hari.pdf', Mockery::on(
                static fn (array $data): bool => ($data['generatedByName'] ?? null) === 'operator1'
            ))
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(PiutangDiatas60HariReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $this->post('/api/internal/ascends/ru/finance/receivable_details/piutang-diatas-60-hari/pdf', [
            'xml' => $xml,
            'Sys_Username' => 'operator1',
        ])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_fills_canonical_dates_from_documented_aliases_without_overwriting_explicit_ones(): void
    {
        $received = [];

        $service = Mockery::mock(UmurPiutangRuReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->twice()
            ->andReturnUsing(function (string $xml, string $label, array $filters) use (&$received): array {
                $received[] = $filters;

                return $this->reportData();
            });

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator->shouldReceive('render')->twice()->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(UmurPiutangRuReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $url = '/api/internal/ascends/ru/finance/receivable-summary/umur-piutang/pdf';

        $this->post($url, [
            'xml' => '<root/>',
            'SalesDate.StartDate' => '2026-11-01',
            'SalesDate.EndDate' => '2026-11-30',
        ])->assertOk();

        $this->post($url, [
            'xml' => '<root/>',
            'start_date' => '2026-10-01',
            'SalesDate.StartDate' => '2026-11-01',
        ])->assertOk();

        $this->assertSame('2026-11-01', $received[0]['start_date'] ?? null);
        $this->assertSame('2026-11-30', $received[0]['end_date'] ?? null);
        $this->assertSame('2026-10-01', $received[1]['start_date'] ?? null);
    }

    public function test_usia_generasi_view_renders_with_per_date_and_printed_by(): void
    {
        $service = Mockery::mock(UsiaGenerasiTahunKelahiranMasaKerjaReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->andReturn($this->usiaReportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator
            ->shouldReceive('render')
            ->once()
            ->with('ascends.shared.hrm.employee_list.usia_generasi_tahun_kelahiran_masa_kerja.pdf', Mockery::on(
                static fn (array $data): bool => ($data['generatedByName'] ?? null) === 'operator1'
            ))
            ->andReturnUsing(
                static fn (string $view, array $data): string => view($view, $data)->render()
            );

        $this->app->instance(UsiaGenerasiTahunKelahiranMasaKerjaReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $response = $this->post('/api/internal/ascends/ru/hrm/usia-generasi-tahun-kelahiran-masa-kerja/pdf', [
            'xml' => '<root/>',
            'PerDate' => '2026-11-16',
            'Sys_Username' => 'operator1',
        ]);

        // The real Blade view is rendered, so a missing $reportData key fails here.
        $response->assertOk()
            ->assertSee('Per Tanggal : 16-Nov-26', false)
            ->assertSee('Dicetak oleh: operator1', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function usiaReportData(): array
    {
        return [
            'title' => 'Laporan Usia Generasi Berdasakan Tahun Kelahiran dan Masa Kerja',
            'company' => '',
            'grouped_rows' => [[
                'label' => 'Generasi X',
                'rows' => [['Nama' => 'Budi', 'Jabatan' => 'Staff', 'Departemen' => 'HR', 'Usia' => '30', 'Masa Kerja' => '5']],
                'subtotal' => 1,
                'percent' => '100.0%',
            ]],
            'generation_summary' => [['label' => 'Generasi X', 'count' => 1, 'percent' => '100.0%']],
            'total_rows' => 1,
            'printed_at' => '16 November 2026 10:00',
            'printed_by' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function test_removes_company_marker_from_report_title(): void
    {
        $xml = '<root/>';

        $service = Mockery::mock(PiutangDiatas60HariReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->andReturn([
                'title' => 'List Karyawan RU',
                'label' => 'List Karyawan RU',
                'company' => 'RU',
                'headers' => ['Nama'],
                'rows' => [],
                'printed_by' => '',
            ]);

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator
            ->shouldReceive('render')
            ->once()
            ->with('ascends.shared.finance.receivable_details.piutang_diatas_60_hari.pdf', Mockery::on(
                static fn (array $data): bool => ($data['reportData']['title'] ?? null) === 'List Karyawan'
                && ($data['reportData']['label'] ?? null) === 'List Karyawan'
                && ($data['company'] ?? null) === 'UC'
            ))
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(PiutangDiatas60HariReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $this->post('/api/internal/ascends/uc/finance/receivable_details/piutang-diatas-60-hari/pdf', [
            'xml' => $xml,
        ])->assertOk();
    }

    public function test_maps_tanggal_alias_for_customer_baru(): void
    {
        $xml = '<root/>';

        $service = Mockery::mock(CustomerBaruReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->with($xml, 'request field: xml', Mockery::on(
                static fn (array $filters): bool => ($filters['tanggal'] ?? null) === '2026-06-30'
            ))
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator->shouldReceive('render')->once()->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(CustomerBaruReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $this->post('/api/internal/ascends/gsu/associate/customer-baru/pdf', [
            'xml' => $xml,
            'Tanggal' => '2026-06-30',
        ])->assertOk();
    }

    public function test_maps_tahun_alias_for_customer_baru_per_tahun(): void
    {
        $xml = '<root/>';

        $service = Mockery::mock(CustomerBaruPerTahunReportService::class);
        $service
            ->shouldReceive('buildReportDataFromXml')
            ->once()
            ->with($xml, 'request field: xml', Mockery::on(
                static fn (array $filters): bool => ($filters['tahun'] ?? null) === '2025'
            ))
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator->shouldReceive('render')->once()->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(CustomerBaruPerTahunReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $this->post('/api/internal/ascends/gsu/associate/customer-baru-per-tahun/pdf', [
            'xml' => $xml,
            'Tahun' => '2025',
        ])->assertOk();
    }

    private function reportData(): array
    {
        return [
            'title' => 'Laporan Umur Piutang Di Atas 60 Hari',
            'company' => '',
            'headers' => ['No', 'Customer'],
            'rows' => [],
            'printed_at' => '16 November 2026 10:00',
            'printed_by' => '',
        ];
    }
}

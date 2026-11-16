<?php

namespace Tests\Feature\Ascends;

use App\Http\Controllers\Ascends\CompanyReportController;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangDiatas60HariReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LabaKotorRuReportService;
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

    /**
     * @return array<string, mixed>
     */
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

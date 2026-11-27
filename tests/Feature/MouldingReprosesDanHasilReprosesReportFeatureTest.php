<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MouldingReprosesDanHasilReprosesReportService;
use App\Services\PdfGenerator;
use Mockery;
use Tests\TestCase;

class MouldingReprosesDanHasilReprosesReportFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('reports.report_auth.issuers', []);
        config()->set('reports.report_auth.audiences', []);
        config()->set('reports.report_auth.required_scope', null);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_build_report_data_groups_and_sorts_detail_rows(): void
    {
        $service = new MouldingReprosesDanHasilReprosesReportService;

        $data = $service->buildReportData('2024-02-01', '2024-02-02', [
            (object) [
                'Section' => 'Hasil Dari Reproses',
                'Tanggal' => '2024-02-02',
                'NoLabel' => 'T.032734',
                'Tebal' => 27,
                'Lebar' => 38,
                'Panjang' => 4250,
                'JmlhBatang' => 480,
                'Kubik' => '2.0930',
                'NoSPKTujuan' => '2026-61',
            ],
            (object) [
                'Section' => 'Output Reproses',
                'Tanggal' => '2024-02-01',
                'NoLabel' => 'Y.002710',
                'Tebal' => '15',
                'Lebar' => 42,
                'Panjang' => 2500,
                'JmlhBatang' => 20,
                'Kubik' => 0.0315,
                'NoSPKTujuan' => '2019-NO',
            ],
            (object) [
                'Section' => 'Input Reproses',
                'Tanggal' => '2024-02-01',
                'NoLabel' => 'Y.002481',
                'Tebal' => 17,
                'Lebar' => 36,
                'Panjang' => 2500,
                'JmlhBatang' => 10,
                'Kubik' => 0.0153,
                'NoSPKTujuan' => '2019-NO',
            ],
        ]);

        $this->assertSame('2024-02-01', $data['start_date']);
        $this->assertSame('2024-02-02', $data['end_date']);
        $this->assertCount(3, $data['rows']);

        $this->assertSame('input_reproses', $data['rows'][0]['section_key']);
        $this->assertSame('Input Reproses', $data['rows'][0]['section']);
        $this->assertSame('Y.002481', $data['rows'][0]['no_label']);
        $this->assertSame(17.0, $data['rows'][0]['tebal']);
        $this->assertSame(10, $data['rows'][0]['jmlh_batang']);
        $this->assertSame(0.0153, $data['rows'][0]['kubik']);
        $this->assertSame('2019-NO', $data['rows'][0]['no_spk_tujuan']);

        $this->assertSame('output_reproses', $data['rows'][1]['section_key']);
        $this->assertSame('Y.002710', $data['rows'][1]['no_label']);

        $this->assertSame('hasil_dari_reproses', $data['rows'][2]['section_key']);
        $this->assertSame('T.032734', $data['rows'][2]['no_label']);
        $this->assertSame(480, $data['rows'][2]['jmlh_batang']);
        $this->assertSame(2.0930, $data['rows'][2]['kubik']);
    }

    public function test_pdf_view_renders_each_section_with_totals(): void
    {
        $html = view('reports.moulding.moulding-reproses-dan-hasil-reproses-pdf', [
            'reportData' => $this->sampleReportData(),
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $this->assertStringContainsString('Laporan Proses Produksi Moulding Reproses Dan Hasil Dari Reproses', $html);
        $this->assertStringContainsString('Dari 01-Feb-24 s/d 02-Feb-24', $html);

        $this->assertStringContainsString('Laporan Input Reproses', $html);
        $this->assertStringContainsString('Output Reproses', $html);
        $this->assertStringContainsString('Hasil Dari Reproses', $html);
        $this->assertStringContainsString('Tebal (mm)', $html);
        $this->assertStringContainsString('Kubik (m&sup3;)', $html);
        $this->assertStringContainsString('No. SPK Tujuan', $html);

        $this->assertStringContainsString('Y.002481', $html);
        $this->assertStringContainsString('Y.002710', $html);
        $this->assertStringContainsString('T.032734', $html);
        $this->assertStringContainsString('0.0153', $html);
        $this->assertStringContainsString('0.0315', $html);
        $this->assertStringContainsString('2.0930', $html);

        $this->assertStringContainsString('Total Input Reproses', $html);
        $this->assertStringContainsString('Total Output Reproses', $html);
        $this->assertStringContainsString('Total Hasil Dari Reproses', $html);
        $this->assertStringContainsString('0.0670', $html, 'Total input = 0.0153 + 0.0517.');
    }

    public function test_preview_endpoint_returns_detail_rows(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(MouldingReprosesDanHasilReprosesReportService::class);
        $service->shouldReceive('fetch')
            ->once()
            ->with('2024-02-01', '2024-02-02')
            ->andReturn($this->sampleReportData());

        $this->app->instance(MouldingReprosesDanHasilReprosesReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/moulding/moulding-reproses-dan-hasil-reproses', [
                'start_date' => '2024-02-01',
                'end_date' => '2024-02-02',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Preview laporan berhasil diambil.')
            ->assertJsonPath('meta.start_date', '2024-02-01')
            ->assertJsonPath('meta.end_date', '2024-02-02')
            ->assertJsonPath('meta.total_rows', 4)
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.section_key', 'input_reproses')
            ->assertJsonPath('data.0.no_label', 'Y.002481')
            ->assertJsonPath('data.3.section_key', 'hasil_dari_reproses')
            ->assertJsonPath('data.3.no_label', 'T.032734');
    }

    public function test_pdf_endpoint_returns_pdf_content(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(MouldingReprosesDanHasilReprosesReportService::class);
        $service->shouldReceive('fetch')
            ->once()
            ->with('2024-02-01', '2024-02-02')
            ->andReturn($this->sampleReportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator->shouldReceive('render')
            ->once()
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(MouldingReprosesDanHasilReprosesReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $response = $this->actingAs($user)
            ->withHeaders($this->authJsonHeaders($user))
            ->post('/api/reports/moulding/moulding-reproses-dan-hasil-reproses/pdf', [
                'start_date' => '2024-02-01',
                'end_date' => '2024-02-02',
            ]);

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertSame('%PDF-1.4 mocked content', $response->getContent());
    }

    public function test_health_endpoint_returns_column_status(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $requiredColumns = ['Section', 'Tanggal', 'NoLabel', 'Tebal', 'Lebar', 'Panjang', 'JmlhBatang', 'Kubik', 'NoSPKTujuan'];

        $service = Mockery::mock(MouldingReprosesDanHasilReprosesReportService::class);
        $service->shouldReceive('healthCheck')
            ->once()
            ->with('2024-02-01', '2024-02-02')
            ->andReturn([
                'is_healthy' => true,
                'required_columns' => $requiredColumns,
                'detected_columns' => $requiredColumns,
                'missing_columns' => [],
                'row_count' => 4,
            ]);

        $this->app->instance(MouldingReprosesDanHasilReprosesReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/moulding/moulding-reproses-dan-hasil-reproses/health', [
                'start_date' => '2024-02-01',
                'end_date' => '2024-02-02',
            ])
            ->assertOk()
            ->assertJsonPath('health.is_healthy', true)
            ->assertJsonPath('health.row_count', 4);
    }

    /**
     * @return array<string, string>
     */
    private function authJsonHeaders(User $user): array
    {
        return [
            'Authorization' => 'Bearer '.$this->issueJwtForUser($user),
            'Accept' => 'application/json',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleReportData(): array
    {
        return [
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-02',
            'rows' => [
                [
                    'section' => 'Input Reproses',
                    'section_key' => 'input_reproses',
                    'tanggal' => '2024-02-01',
                    'no_label' => 'Y.002481',
                    'tebal' => 17.0,
                    'lebar' => 36.0,
                    'panjang' => 2500.0,
                    'jmlh_batang' => 10,
                    'kubik' => 0.0153,
                    'no_spk_tujuan' => '2019-NO',
                ],
                [
                    'section' => 'Input Reproses',
                    'section_key' => 'input_reproses',
                    'tanggal' => '2024-02-01',
                    'no_label' => 'Y.002690',
                    'tebal' => 20.0,
                    'lebar' => 45.0,
                    'panjang' => 2500.0,
                    'jmlh_batang' => 23,
                    'kubik' => 0.0517,
                    'no_spk_tujuan' => '2019-NO',
                ],
                [
                    'section' => 'Output Reproses',
                    'section_key' => 'output_reproses',
                    'tanggal' => '2024-02-01',
                    'no_label' => 'Y.002710',
                    'tebal' => 15.0,
                    'lebar' => 42.0,
                    'panjang' => 2500.0,
                    'jmlh_batang' => 20,
                    'kubik' => 0.0315,
                    'no_spk_tujuan' => '2019-NO',
                ],
                [
                    'section' => 'Hasil Dari Reproses',
                    'section_key' => 'hasil_dari_reproses',
                    'tanggal' => '2024-02-02',
                    'no_label' => 'T.032734',
                    'tebal' => 27.0,
                    'lebar' => 38.0,
                    'panjang' => 4250.0,
                    'jmlh_batang' => 480,
                    'kubik' => 2.0930,
                    'no_spk_tujuan' => '2026-61',
                ],
            ],
        ];
    }
}

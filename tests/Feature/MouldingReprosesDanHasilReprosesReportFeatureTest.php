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

    public function test_build_report_data_sorts_and_normalizes_rows(): void
    {
        $service = new MouldingReprosesDanHasilReprosesReportService;

        $data = $service->buildReportData('2024-02-01', '2024-02-02', [
            (object) [
                'Tanggal' => '2024-02-02',
                'InputReproses' => '2.4999',
                'OutputReproses' => '0.7394',
                'HasilDariReproses' => null,
            ],
            (object) [
                'Tanggal' => '2024-02-01',
                'InputReproses' => 0.1899,
                'OutputReproses' => 2.5656,
                'HasilDariReproses' => 0,
            ],
        ]);

        $this->assertSame('2024-02-01', $data['start_date']);
        $this->assertSame('2024-02-02', $data['end_date']);
        $this->assertCount(2, $data['rows']);

        $this->assertSame('2024-02-01', $data['rows'][0]['tanggal']);
        $this->assertSame(0.1899, $data['rows'][0]['input_reproses']);
        $this->assertSame(2.5656, $data['rows'][0]['output_reproses']);
        $this->assertSame(0.0, $data['rows'][0]['hasil_dari_reproses']);

        $this->assertSame('2024-02-02', $data['rows'][1]['tanggal']);
        $this->assertSame(2.4999, $data['rows'][1]['input_reproses']);
        $this->assertSame(0.7394, $data['rows'][1]['output_reproses']);
        $this->assertSame(0.0, $data['rows'][1]['hasil_dari_reproses']);
    }

    public function test_pdf_view_renders_daily_reproses_totals(): void
    {
        $html = view('reports.moulding.moulding-reproses-dan-hasil-reproses-pdf', [
            'reportData' => [
                'start_date' => '2024-02-01',
                'end_date' => '2024-02-02',
                'rows' => [
                    [
                        'tanggal' => '2024-02-01',
                        'input_reproses' => 0.1899,
                        'output_reproses' => 2.5656,
                        'hasil_dari_reproses' => 0.0,
                    ],
                    [
                        'tanggal' => '2024-02-02',
                        'input_reproses' => 2.4999,
                        'output_reproses' => 0.7394,
                        'hasil_dari_reproses' => 0.0,
                    ],
                ],
            ],
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $this->assertStringContainsString('Laporan Proses Produksi Moulding Reproses Dan Hasil Dari Reproses', $html);
        $this->assertStringContainsString('Dari 01-Feb-24 s/d 02-Feb-24', $html);
        $this->assertStringContainsString('Tanggal', $html);
        $this->assertStringContainsString('Input Reproses', $html);
        $this->assertStringContainsString('Output Reproses', $html);
        $this->assertStringContainsString('Hasil Dari Reproses', $html);
        $this->assertStringContainsString('01-Feb-24', $html);
        $this->assertStringContainsString('02-Feb-24', $html);
        $this->assertStringContainsString('0.1899 m³', $html);
        $this->assertStringContainsString('2.5656 m³', $html);
        $this->assertStringContainsString('2.4999 m³', $html);
        $this->assertStringContainsString('0.7394 m³', $html);
        $this->assertStringNotContainsString('Satuan:', $html);
    }

    public function test_preview_endpoint_returns_daily_rows(): void
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
            ->assertJsonPath('meta.total_rows', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.tanggal', '2024-02-01')
            ->assertJsonPath('data.0.input_reproses', 0.1899)
            ->assertJsonPath('data.1.tanggal', '2024-02-02');
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

        $service = Mockery::mock(MouldingReprosesDanHasilReprosesReportService::class);
        $service->shouldReceive('healthCheck')
            ->once()
            ->with('2024-02-01', '2024-02-02')
            ->andReturn([
                'is_healthy' => true,
                'required_columns' => ['Tanggal', 'InputReproses', 'OutputReproses', 'HasilDariReproses'],
                'detected_columns' => ['Tanggal', 'InputReproses', 'OutputReproses', 'HasilDariReproses'],
                'missing_columns' => [],
                'row_count' => 2,
            ]);

        $this->app->instance(MouldingReprosesDanHasilReprosesReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/moulding/moulding-reproses-dan-hasil-reproses/health', [
                'start_date' => '2024-02-01',
                'end_date' => '2024-02-02',
            ])
            ->assertOk()
            ->assertJsonPath('health.is_healthy', true)
            ->assertJsonPath('health.row_count', 2);
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
                    'tanggal' => '2024-02-01',
                    'input_reproses' => 0.1899,
                    'output_reproses' => 2.5656,
                    'hasil_dari_reproses' => 0.0,
                ],
                [
                    'tanggal' => '2024-02-02',
                    'input_reproses' => 2.4999,
                    'output_reproses' => 0.7394,
                    'hasil_dari_reproses' => 0.0,
                ],
            ],
        ];
    }
}

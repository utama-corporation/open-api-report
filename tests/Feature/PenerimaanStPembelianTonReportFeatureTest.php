<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PdfGenerator;
use App\Services\PenerimaanStPembelianTonReportService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PenerimaanStPembelianTonReportFeatureTest extends TestCase
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

    public function test_preview_endpoint_returns_receipt_summary(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(PenerimaanStPembelianTonReportService::class);
        $service->shouldReceive('buildReportData')
            ->once()
            ->with('B.160214')
            ->andReturn($this->reportData());

        $this->app->instance(PenerimaanStPembelianTonReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/sawn-timber/penerimaan-st-pembelian-ton', [
                'NoPenerimaanST' => 'B.160214',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Preview laporan berhasil diambil.')
            ->assertJsonPath('meta.no_penerimaan_st', 'B.160214')
            ->assertJsonPath('meta.total_rows', 1)
            ->assertJsonPath('meta.total_pcs', 138)
            ->assertJsonPath('meta.total_ton', 0.1332)
            ->assertJsonPath('data.0.tebal', 31)
            ->assertJsonPath('report_data.header.supplier', 'IWAN SALIM')
            ->assertJsonPath('report_data.returned_materials.summary.total_pcs', 0);
    }

    public function test_request_requires_a_valid_receipt_number(): void
    {
        $user = User::factory()->make(['id' => 1]);
        $headers = $this->authJsonHeaders($user);

        $this->withHeaders($headers)
            ->postJson('/api/reports/sawn-timber/penerimaan-st-pembelian-ton', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('NoPenerimaanST');

        $this->withHeaders($headers)
            ->postJson('/api/reports/sawn-timber/penerimaan-st-pembelian-ton', [
                'NoPenerimaanST' => 'B.1234567890123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('NoPenerimaanST');
    }

    public function test_unknown_receipt_returns_unprocessable_response(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(PenerimaanStPembelianTonReportService::class);
        $service->shouldReceive('buildReportData')
            ->once()
            ->with('B.160214')
            ->andThrow(new RuntimeException('Penerimaan ST tidak ditemukan.'));

        $this->app->instance(PenerimaanStPembelianTonReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/sawn-timber/penerimaan-st-pembelian-ton', [
                'NoPenerimaanST' => 'B.160214',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Penerimaan ST tidak ditemukan.');
    }

    public function test_pdf_endpoint_returns_receipt_attachment(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(PenerimaanStPembelianTonReportService::class);
        $service->shouldReceive('buildReportData')
            ->once()
            ->with('B.160214')
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator->shouldReceive('render')
            ->once()
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(PenerimaanStPembelianTonReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $response = $this->withHeaders($this->authJsonHeaders($user))
            ->post('/api/reports/sawn-timber/penerimaan-st-pembelian-ton/pdf', [
                'NoPenerimaanST' => 'B.160214',
            ])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertPdfDisposition($response, 'attachment', 'Laporan Penerimaan ST Pembelian Ton B.160214');
    }

    public function test_health_endpoint_returns_source_health(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(PenerimaanStPembelianTonReportService::class);
        $service->shouldReceive('healthCheck')
            ->once()
            ->with('B.160214')
            ->andReturn([
                'is_healthy' => true,
                'expected_columns' => ['NoPenerimaanST'],
                'detected_columns' => ['NoPenerimaanST'],
                'missing_columns' => [],
                'extra_columns' => [],
                'row_count' => 9,
            ]);

        $this->app->instance(PenerimaanStPembelianTonReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/sawn-timber/penerimaan-st-pembelian-ton/health', [
                'NoPenerimaanST' => 'B.160214',
            ])
            ->assertOk()
            ->assertJsonPath('health.is_healthy', true)
            ->assertJsonPath('health.row_count', 9)
            ->assertJsonPath('meta.NoPenerimaanST', 'B.160214');
    }

    public function test_report_endpoints_require_jwt_authentication(): void
    {
        $this->postJson('/api/reports/sawn-timber/penerimaan-st-pembelian-ton', [
            'NoPenerimaanST' => 'B.160214',
        ])->assertUnauthorized();
    }

    public function test_openapi_documents_the_required_receipt_number(): void
    {
        $spec = $this->getJson('/api/openapi.json')->assertOk()->json();
        $requestSchema = $spec['components']['schemas']['PenerimaanStPembelianTonRequest'];

        $this->assertSame(['NoPenerimaanST'], $requestSchema['required']);
        $this->assertSame(13, $requestSchema['properties']['NoPenerimaanST']['maxLength']);
        $this->assertSame(
            '#/components/schemas/PenerimaanStPembelianTonRequest',
            $spec['paths']['/api/reports/sawn-timber/penerimaan-st-pembelian-ton']['post']['requestBody']['content']['application/json']['schema']['$ref'],
        );
        $previewResponseSchema = $spec['paths']['/api/reports/sawn-timber/penerimaan-st-pembelian-ton']['post']['responses']['200']['content']['application/json']['schema'];
        $this->assertSame('#/components/schemas/PenerimaanStPembelianTonPreviewResponse', $previewResponseSchema['$ref']);
        $this->assertSame(
            'array',
            $spec['components']['schemas']['PenerimaanStPembelianTonReportData']['properties']['returned_materials']['properties']['rows']['type'],
        );
    }

    public function test_pdf_view_renders_reference_fields_and_totals(): void
    {
        $html = view('reports.sawn-timber.penerimaan-st-pembelian-ton-pdf', [
            'reportData' => $this->referenceReportData(),
            'generatedBy' => User::factory()->make(['name' => 'Ridho']),
            'generatedAt' => now(),
        ])->render();
        $text = strip_tags($html);

        $this->assertStringContainsString('Laporan Penerimaan ST - Pembelian (Ton)', $text);
        $this->assertStringContainsString('B.160214', $text);
        $this->assertStringContainsString('IWAN SALIM', $text);
        $this->assertStringContainsString('31-Okt-26', $text);
        $this->assertStringContainsString('2.5', $text);
        $this->assertStringContainsString('0.1332', $text);
        $this->assertStringContainsString('4,700', $text);
        $this->assertStringContainsString('13.7534', $text);
        $this->assertStringContainsString('FISIK SESUAI', $text);
        $this->assertStringContainsString('Dibuat', $text);
        $this->assertStringContainsString('Diperiksa', $text);
        $this->assertStringContainsString('Diketahui', $text);
        $this->assertSame(1, preg_match_all('/data-row\s+row-(?:odd|even)\s+group-separator/', $html));
        $this->assertSame(3, substr_count($html, '__________________'));
        $this->assertStringNotContainsString('<table class="returned-materials-table">', $html);
    }

    public function test_pdf_view_renders_the_returned_materials_table(): void
    {
        $reportData = $this->referenceReportData();
        $reportData['no_penerimaan_st'] = 'B.160187';
        $reportData['header']['no_penerimaan_st'] = 'B.160187';
        $reportData['header']['note'] = "BAHAN YANG DIKEMBALIKAN\r\n( MC, PULUR, KULIT, TIPIS";
        $reportData['returned_materials'] = [
            'rows' => [
                ['no' => 1, 'tebal' => 44, 'lebar' => 79, 'uom_tbl_lebar' => 'mm', 'panjang' => 4, 'pcs' => 72, 'uom_panjang' => 'feet', 'ton' => 0.2154],
                ['no' => 2, 'tebal' => 44, 'lebar' => 43, 'uom_tbl_lebar' => 'mm', 'panjang' => 4, 'pcs' => 62, 'uom_panjang' => 'feet', 'ton' => 0.1010],
                ['no' => 3, 'tebal' => 44, 'lebar' => 95, 'uom_tbl_lebar' => 'mm', 'panjang' => 4, 'pcs' => 38, 'uom_panjang' => 'feet', 'ton' => 0.1367],
                ['no' => 4, 'tebal' => 16, 'lebar' => 95, 'uom_tbl_lebar' => 'mm', 'panjang' => 4, 'pcs' => 15, 'uom_panjang' => 'feet', 'ton' => 0.0196],
                ['no' => 5, 'tebal' => 16, 'lebar' => 43, 'uom_tbl_lebar' => 'mm', 'panjang' => 4, 'pcs' => 44, 'uom_panjang' => 'feet', 'ton' => 0.0260],
                ['no' => 6, 'tebal' => 32, 'lebar' => 43, 'uom_tbl_lebar' => 'mm', 'panjang' => 4, 'pcs' => 41, 'uom_panjang' => 'feet', 'ton' => 0.0485],
                ['no' => 7, 'tebal' => 32, 'lebar' => 95, 'uom_tbl_lebar' => 'mm', 'panjang' => 4, 'pcs' => 8, 'uom_panjang' => 'feet', 'ton' => 0.0209],
            ],
            'summary' => ['total_pcs' => 280, 'total_ton' => 0.5681],
        ];

        $html = view('reports.sawn-timber.penerimaan-st-pembelian-ton-pdf', [
            'reportData' => $reportData,
            'generatedBy' => User::factory()->make(['name' => 'Ridho']),
            'generatedAt' => now(),
        ])->render();
        $text = strip_tags($html);

        $this->assertStringContainsString('BAHAN YANG DIKEMBALIKAN', $text);
        $this->assertStringContainsString('( MC, PULUR, KULIT, TIPIS', $text);
        $this->assertSame(1, preg_match('/BAHAN YANG DIKEMBALIKAN<br\s*\/?>\s*\( MC, PULUR, KULIT, TIPIS/', $html));
        $this->assertStringContainsString('UOMTblLebar', $text);
        $this->assertStringContainsString('UOMPanjang', $text);
        $this->assertStringContainsString('280', $text);
        $this->assertStringContainsString('0.5681', $text);
        foreach (['0.2154', '0.1010', '0.1367', '0.0196', '0.0260', '0.0485', '0.0209'] as $ton) {
            $this->assertStringContainsString($ton, $text);
        }
        $this->assertSame(7, preg_match_all('/<tr class="data-row returned-material-row row-(?:odd|even)(?: before-total)?">/', $html));
        $this->assertStringContainsString('<table class="report-table returned-materials-table">', $html);
        $this->assertSame(4, preg_match_all('/<tr class="data-row returned-material-row row-odd(?: before-total)?">/', $html));
        $this->assertSame(3, preg_match_all('/<tr class="data-row returned-material-row row-even(?: before-total)?">/', $html));
        $this->assertSame(1, preg_match_all('/<tr class="data-row returned-material-row row-(?:odd|even) before-total">/', $html));
        $this->assertStringContainsString('<td class="data-cell number">1</td>', $html);
        $this->assertStringContainsString('<tr class="total-row returned-materials-total">', $html);
    }

    public function test_pdf_view_handles_empty_details_and_missing_metadata(): void
    {
        $reportData = $this->referenceReportData();
        $reportData['header']['supplier'] = null;
        $reportData['header']['ton_surat_jalan'] = null;
        $reportData['tebal_groups'] = [];
        $reportData['length_columns'] = [];
        $reportData['summary'] = [
            'total_rows' => 0,
            'totals_by_length' => [],
            'total_pcs' => 0,
            'total_ton' => 0,
        ];

        $html = view('reports.sawn-timber.penerimaan-st-pembelian-ton-pdf', [
            'reportData' => $reportData,
            'generatedBy' => User::factory()->make(['name' => 'Ridho']),
            'generatedAt' => now(),
        ])->render();
        $text = strip_tags($html);

        $this->assertStringContainsString('Tidak ada data.', $text);
        $this->assertStringContainsString('2.5', $text);
        $this->assertStringContainsString('4', $text);
        $this->assertMatchesRegularExpression('/Supplier\s*:\s*-/', $text);
        $this->assertMatchesRegularExpression('/Ton Surat Jalan\s*:\s*-/', $text);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(): array
    {
        return [
            'no_penerimaan_st' => 'B.160214',
            'header' => [
                'no_penerimaan_st' => 'B.160214',
                'supplier' => 'IWAN SALIM',
                'jenis_kayu' => 'RAMBUNG - STD',
                'kondisi' => 'Kering',
            ],
            'length_columns' => [
                ['key' => '2.5', 'label' => '2.5', 'raw_panjang' => 2.5],
            ],
            'tebal_groups' => [
                [
                    'tebal' => 31.0,
                    'rows' => [
                        [
                            'lebar' => 38.0,
                            'uom' => 'mm',
                            'cells' => ['2.5' => 138],
                            'total_pcs' => 138,
                            'total_ton' => 0.1332,
                        ],
                    ],
                ],
            ],
            'summary' => [
                'total_rows' => 1,
                'totals_by_length' => ['2.5' => 138],
                'total_pcs' => 138,
                'total_ton' => 0.1332,
            ],
            'returned_materials' => [
                'rows' => [],
                'summary' => ['total_pcs' => 0, 'total_ton' => 0.0],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function referenceReportData(): array
    {
        $dimensions = [
            [31, 38, [2 => 0, '2.5' => 12, 3 => 6, '3.5' => 4, 4 => 116], 138, 0.1332],
            [31, 92, [2 => 0, '2.5' => 0, 3 => 2, '3.5' => 4, 4 => 101], 107, 0.2601],
            [44, 32, [2 => 0, '2.5' => 0, 3 => 0, '3.5' => 0, 4 => 4], 4, 0.0048],
            [44, 38, [2 => 0, '2.5' => 0, 3 => 2, '3.5' => 6, 4 => 42], 50, 0.0700],
            [44, 44, [2 => 16, '2.5' => 5, 3 => 15, '3.5' => 16, 4 => 283], 335, 0.5321],
            [44, 55, [2 => 1, '2.5' => 3, 3 => 29, '3.5' => 34, 4 => 713], 780, 1.5972],
            [44, 78, [2 => 4, '2.5' => 5, 3 => 53, '3.5' => 43, 4 => 1240], 1345, 3.9067],
            [44, 92, [2 => 2, '2.5' => 2, 3 => 29, '3.5' => 23, 4 => 1407], 1463, 5.0563],
            [44, 122, [2 => 1, '2.5' => 1, 3 => 3, '3.5' => 14, 4 => 459], 478, 2.1930],
        ];

        $tebalGroups = [];
        foreach ($dimensions as [$tebal, $lebar, $cells, $totalPcs, $totalTon]) {
            $tebalKey = (string) $tebal;
            $tebalGroups[$tebalKey] ??= [
                'tebal' => $tebal,
                'rows' => [],
            ];
            $tebalGroups[$tebalKey]['rows'][] = [
                'lebar' => $lebar,
                'uom' => 'mm',
                'cells' => $cells,
                'total_pcs' => $totalPcs,
                'total_ton' => $totalTon,
            ];
        }

        return [
            'no_penerimaan_st' => 'B.160214',
            'header' => [
                'no_penerimaan_st' => 'B.160214',
                'tgl_laporan' => '2026-10-31',
                'tgl_masuk' => '2026-10-30',
                'supplier' => 'IWAN SALIM',
                'no_truk' => '1',
                'no_plat' => 'BK 8641 GQ',
                'no_suket' => 'SJ/STTW/2610/0047',
                'ton_surat_jalan' => 13.7641,
                'surat_tanah' => 'LUKMAN-1',
                'jenis_kayu' => 'RAMBUNG - STD',
                'kondisi' => 'Kering',
                'note' => 'FISIK SESUAI',
            ],
            'length_columns' => [
                ['key' => '2', 'label' => '2', 'raw_panjang' => 2.0],
                ['key' => '2.5', 'label' => '2.5', 'raw_panjang' => 2.5],
                ['key' => '3', 'label' => '3', 'raw_panjang' => 3.0],
                ['key' => '3.5', 'label' => '3.5', 'raw_panjang' => 3.5],
                ['key' => '4', 'label' => '4', 'raw_panjang' => 4.0],
            ],
            'tebal_groups' => array_values($tebalGroups),
            'summary' => [
                'total_rows' => 9,
                'totals_by_length' => [2 => 24, '2.5' => 28, 3 => 139, '3.5' => 144, 4 => 4365],
                'total_pcs' => 4700,
                'total_ton' => 13.7534,
            ],
            'returned_materials' => [
                'rows' => [],
                'summary' => ['total_pcs' => 0, 'total_ton' => 0.0],
            ],
        ];
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
}

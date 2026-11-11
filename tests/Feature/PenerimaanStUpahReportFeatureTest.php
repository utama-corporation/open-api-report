<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PdfGenerator;
use App\Services\PenerimaanStUpahReportService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PenerimaanStUpahReportFeatureTest extends TestCase
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

        $service = Mockery::mock(PenerimaanStUpahReportService::class);
        $service->shouldReceive('buildReportData')
            ->once()
            ->with('O.000057')
            ->andReturn($this->reportData());

        $this->app->instance(PenerimaanStUpahReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/sawn-timber/penerimaan-st-upah', [
                'NoUpah' => 'O.000057',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Preview laporan berhasil diambil.')
            ->assertJsonPath('meta.no_upah', 'O.000057')
            ->assertJsonPath('meta.total_rows', 1)
            ->assertJsonPath('meta.total_pcs', 199)
            ->assertJsonPath('meta.total_ton', 0.7738)
            ->assertJsonPath('data.0.tebal', 1)
            ->assertJsonPath('report_data.header.customer', 'PAK BENYAMIN')
            ->assertJsonPath('report_data.returned_materials.summary.total_pcs', 0);
    }

    public function test_request_requires_a_valid_receipt_number(): void
    {
        $user = User::factory()->make(['id' => 1]);
        $headers = $this->authJsonHeaders($user);

        $this->withHeaders($headers)
            ->postJson('/api/reports/sawn-timber/penerimaan-st-upah', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('NoUpah');

        $this->withHeaders($headers)
            ->postJson('/api/reports/sawn-timber/penerimaan-st-upah', [
                'NoUpah' => 'O.1234567890123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('NoUpah');
    }

    public function test_unknown_receipt_returns_unprocessable_response(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(PenerimaanStUpahReportService::class);
        $service->shouldReceive('buildReportData')
            ->once()
            ->with('O.000057')
            ->andThrow(new RuntimeException('Penerimaan ST Upah tidak ditemukan.'));

        $this->app->instance(PenerimaanStUpahReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/sawn-timber/penerimaan-st-upah', [
                'NoUpah' => 'O.000057',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Penerimaan ST Upah tidak ditemukan.');
    }

    public function test_pdf_endpoint_returns_receipt_attachment(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(PenerimaanStUpahReportService::class);
        $service->shouldReceive('buildReportData')
            ->once()
            ->with('O.000057')
            ->andReturn($this->reportData());

        $pdfGenerator = Mockery::mock(PdfGenerator::class);
        $pdfGenerator->shouldReceive('render')
            ->once()
            ->andReturn('%PDF-1.4 mocked content');

        $this->app->instance(PenerimaanStUpahReportService::class, $service);
        $this->app->instance(PdfGenerator::class, $pdfGenerator);

        $response = $this->withHeaders($this->authJsonHeaders($user))
            ->post('/api/reports/sawn-timber/penerimaan-st-upah/pdf', [
                'NoUpah' => 'O.000057',
            ])
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertPdfDisposition($response, 'attachment', 'Laporan Penerimaan ST Upah O.000057');
    }

    public function test_health_endpoint_returns_source_health(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $service = Mockery::mock(PenerimaanStUpahReportService::class);
        $service->shouldReceive('healthCheck')
            ->once()
            ->with('O.000057')
            ->andReturn([
                'is_healthy' => true,
                'expected_columns' => ['NoPenerimaanST'],
                'detected_columns' => ['NoPenerimaanST'],
                'missing_columns' => [],
                'extra_columns' => [],
                'row_count' => 9,
            ]);

        $this->app->instance(PenerimaanStUpahReportService::class, $service);

        $this->withHeaders($this->authJsonHeaders($user))
            ->postJson('/api/reports/sawn-timber/penerimaan-st-upah/health', [
                'NoUpah' => 'O.000057',
            ])
            ->assertOk()
            ->assertJsonPath('health.is_healthy', true)
            ->assertJsonPath('health.row_count', 9)
            ->assertJsonPath('meta.NoUpah', 'O.000057');
    }

    public function test_report_endpoints_require_jwt_authentication(): void
    {
        $this->postJson('/api/reports/sawn-timber/penerimaan-st-upah', [
            'NoUpah' => 'O.000057',
        ])->assertUnauthorized();
    }

    public function test_openapi_documents_the_required_receipt_number(): void
    {
        $spec = $this->getJson('/api/openapi.json')->assertOk()->json();
        $requestSchema = $spec['components']['schemas']['PenerimaanStUpahRequest'];

        $this->assertSame(['NoUpah'], $requestSchema['required']);
        $this->assertSame(13, $requestSchema['properties']['NoUpah']['maxLength']);
        $this->assertSame(
            '#/components/schemas/PenerimaanStUpahRequest',
            $spec['paths']['/api/reports/sawn-timber/penerimaan-st-upah']['post']['requestBody']['content']['application/json']['schema']['$ref'],
        );
        $previewResponseSchema = $spec['paths']['/api/reports/sawn-timber/penerimaan-st-upah']['post']['responses']['200']['content']['application/json']['schema'];
        $this->assertSame('#/components/schemas/PenerimaanStUpahPreviewResponse', $previewResponseSchema['$ref']);
        $this->assertSame(
            'array',
            $spec['components']['schemas']['PenerimaanStUpahReportData']['properties']['returned_materials']['properties']['rows']['type'],
        );
    }

    public function test_pdf_view_renders_reference_fields_and_totals(): void
    {
        $html = view('reports.sawn-timber.penerimaan-st-upah-pdf', [
            'reportData' => $this->referenceReportData(),
            'generatedBy' => User::factory()->make(['name' => 'Ridho']),
            'generatedAt' => now(),
        ])->render();
        $text = strip_tags($html);

        $this->assertStringContainsString('Laporan Penerimaan ST (Upah)', $text);
        $this->assertMatchesRegularExpression('/No\. Upah\s*:\s*O\.000057/', $text);
        $this->assertMatchesRegularExpression('/No\. SJ\s*:\s*01\/IV\/MJ\/TT\/25/', $text);
        $this->assertMatchesRegularExpression('/Customer\s*:\s*PAK BENYAMIN/', $text);
        $this->assertStringContainsString('28-Apr-25', $text);
        $this->assertStringContainsString('2.5', $text);
        $this->assertStringContainsString('0.0957', $text);
        $this->assertStringContainsString('4,708', $text);
        $this->assertStringContainsString('3.3867', $text);
        $this->assertStringContainsString('UPAH STICK KD DAN BONGKAR STICK', $text);
        $this->assertStringContainsString('Dibuat', $text);
        $this->assertStringContainsString('Diperiksa', $text);
        $this->assertStringContainsString('Diketahui', $text);
        $this->assertSame(1, preg_match_all('/data-row\s+row-(?:odd|even)\s+group-separator/', $html));
        $this->assertSame(3, substr_count($html, '__________________'));
        $this->assertStringNotContainsString('<table class="returned-materials-table">', $html);
        $this->assertStringNotContainsString('Tgl Laporan', $text);
        $this->assertStringNotContainsString('Surat Tanah', $text);
        $this->assertStringNotContainsString('Kondisi', $text);
        $this->assertStringNotContainsString('Ton Surat Jalan', $text);
        $this->assertStringNotContainsString('Suket', $text);
        $this->assertStringNotContainsString('Supplier', $text);
    }

    public function test_pdf_view_renders_the_returned_materials_table(): void
    {
        $reportData = $this->referenceReportData();
        $reportData['no_upah'] = 'O.000001';
        $reportData['header']['no_upah'] = 'O.000001';
        $reportData['header']['note'] = "BAHAN YANG DIKEMBALIKAN\r\n( MC, PULUR, KULIT, TIPIS";
        $reportData['returned_materials'] = [
            'rows' => [
                ['no' => 1, 'tebal' => 1, 'lebar' => 4, 'uom_tbl_lebar' => 'inch', 'panjang' => 7, 'pcs' => 199, 'uom_panjang' => 'feet', 'ton' => 0.7738],
                ['no' => 2, 'tebal' => 1, 'lebar' => 4, 'uom_tbl_lebar' => 'inch', 'panjang' => 8, 'pcs' => 11, 'uom_panjang' => 'feet', 'ton' => 0.0488],
                ['no' => 3, 'tebal' => 1, 'lebar' => 4, 'uom_tbl_lebar' => 'inch', 'panjang' => 4, 'pcs' => 11, 'uom_panjang' => 'feet', 'ton' => 0.0244],
                ['no' => 4, 'tebal' => 1, 'lebar' => 4, 'uom_tbl_lebar' => 'inch', 'panjang' => 5, 'pcs' => 10, 'uom_panjang' => 'feet', 'ton' => 0.0277],
                ['no' => 5, 'tebal' => 1, 'lebar' => 4, 'uom_tbl_lebar' => 'inch', 'panjang' => 6, 'pcs' => 9, 'uom_panjang' => 'feet', 'ton' => 0.0299],
                ['no' => 6, 'tebal' => 1, 'lebar' => 4, 'uom_tbl_lebar' => 'inch', 'panjang' => 7, 'pcs' => 180, 'uom_panjang' => 'feet', 'ton' => 0.6999],
                ['no' => 7, 'tebal' => 1, 'lebar' => 3, 'uom_tbl_lebar' => 'inch', 'panjang' => 7, 'pcs' => 146, 'uom_panjang' => 'feet', 'ton' => 0.4257],
            ],
            'summary' => ['total_pcs' => 566, 'total_ton' => 2.0302],
        ];

        $html = view('reports.sawn-timber.penerimaan-st-upah-pdf', [
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
        $this->assertStringContainsString('566', $text);
        $this->assertStringContainsString('2.0302', $text);
        foreach (['0.7738', '0.0488', '0.0244', '0.0277', '0.0299', '0.6999', '0.4257'] as $ton) {
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
        $reportData['header']['customer'] = null;
        $reportData['header']['no_sj'] = null;
        $reportData['tebal_groups'] = [];
        $reportData['length_columns'] = [];
        $reportData['summary'] = [
            'total_rows' => 0,
            'totals_by_length' => [],
            'total_pcs' => 0,
            'total_ton' => 0,
        ];

        $html = view('reports.sawn-timber.penerimaan-st-upah-pdf', [
            'reportData' => $reportData,
            'generatedBy' => User::factory()->make(['name' => 'Ridho']),
            'generatedAt' => now(),
        ])->render();
        $text = strip_tags($html);

        $this->assertStringContainsString('Tidak ada data.', $text);
        $this->assertStringContainsString('2.5', $text);
        $this->assertStringContainsString('4', $text);
        $this->assertMatchesRegularExpression('/Customer\s*:\s*-/', $text);
        $this->assertMatchesRegularExpression('/No\. SJ\s*:\s*-/', $text);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(): array
    {
        return [
            'no_upah' => 'O.000057',
            'header' => [
                'no_upah' => 'O.000057',
                'customer' => 'PAK BENYAMIN',
                'jenis_kayu' => 'MAHONI',
            ],
            'length_columns' => [
                ['key' => '7', 'label' => '7', 'raw_panjang' => 7.0],
            ],
            'tebal_groups' => [
                [
                    'tebal' => 1.0,
                    'rows' => [
                        [
                            'lebar' => 4.0,
                            'uom' => 'inch',
                            'cells' => ['7' => 199],
                            'total_pcs' => 199,
                            'total_ton' => 0.7738,
                        ],
                    ],
                ],
            ],
            'summary' => [
                'total_rows' => 1,
                'totals_by_length' => ['7' => 199],
                'total_pcs' => 199,
                'total_ton' => 0.7738,
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
            [1, 3, [2 => 0, '2.5' => 0, 3 => 12, '3.5' => 4, 4 => 116], 132, 0.0957],
            [1, 4, [2 => 24, '2.5' => 28, 3 => 127, '3.5' => 140, 4 => 4249], 4568, 3.2788],
            [2, 13, [2 => 0, '2.5' => 0, 3 => 0, '3.5' => 0, 4 => 8], 8, 0.0122],
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
                'uom' => 'inch',
                'cells' => $cells,
                'total_pcs' => $totalPcs,
                'total_ton' => $totalTon,
            ];
        }

        return [
            'no_upah' => 'O.000057',
            'header' => [
                'no_upah' => 'O.000057',
                'tgl_masuk' => '2025-04-28',
                'customer' => 'PAK BENYAMIN',
                'no_truk' => '53',
                'no_plat' => 'BK 9615 CL',
                'no_sj' => '01/IV/MJ/TT/25',
                'jenis_kayu' => 'MAHONI',
                'note' => 'UPAH STICK KD DAN BONGKAR STICK',
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
                'total_rows' => 3,
                'totals_by_length' => [2 => 24, '2.5' => 28, 3 => 139, '3.5' => 144, 4 => 4373],
                'total_pcs' => 4708,
                'total_ton' => 3.3867,
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

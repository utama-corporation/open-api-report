<?php

namespace Tests\Feature;

use App\Services\ProduksiMouldingPerNomorProduksiReportService;

class ProduksiMouldingPerNomorProduksiReportFeatureTest extends ProduksiPerNomorProduksiReportTestCase
{
    protected function service(): object
    {
        return new ProduksiMouldingPerNomorProduksiReportService;
    }

    protected function pdfView(): string
    {
        return 'reports.proses-produksi.produksi-moulding-per-nomor-produksi-pdf';
    }

    protected function expectedDetailTableCount(): int
    {
        return 7;
    }

    /**
     * Moulding memisahkan output Hasil Dari Reproses ke tabelnya sendiri.
     */
    public function test_hasil_dari_reproses_flag_marks_output_reproses(): void
    {
        $report = $this->build([
            $this->spRow('Output', 'T.032724', ['HasilDariReproses' => '1']),
        ]);

        $this->assertSame([], $report['output_rows']);
        $this->assertSame([], $report['output_reproses_rows']);
        $this->assertSame(['T.032724'], array_column($report['output_hasil_reproses_rows'], 'no_label'));
        $this->assertSame(1, $report['totals']['output_hasil_reproses']['count']);
    }

    public function test_pdf_view_shows_hasil_dari_reproses_section(): void
    {
        $report = $this->build([
            $this->spRow('Output', 'T.032724', ['HasilDariReproses' => '1']),
        ]);

        $html = view($this->pdfView(), [
            'report' => $report,
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $this->assertStringContainsString('Hasil Dari Reproses', $html);
        $this->assertStringContainsString('Total Hasil Reproses', $html);
        $this->assertStringContainsString('T.032724', $html);
    }
}

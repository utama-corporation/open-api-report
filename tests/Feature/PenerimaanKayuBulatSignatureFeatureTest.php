<?php

namespace Tests\Feature;

use Tests\TestCase;

class PenerimaanKayuBulatSignatureFeatureTest extends TestCase
{
    public function test_four_receipt_pdf_views_render_only_the_requested_signatures(): void
    {
        $views = [
            'reports.kayu-bulat.penerimaan-kayu-bulat-int-ton-pdf',
            'reports.kayu-bulat.penerimaan-kayu-bulat-ext-ton-pdf',
            'reports.kayu-bulat-rambung.penerimaan-kayu-bulat-kg-pdf',
            'reports.kayu-bulat-rambung.penerimaan-kayu-bulat-extkg-pdf',
        ];
        $expectedLabels = ['Ukur ;', 'Diperiksa Oleh ;', 'Diinput Oleh ;'];

        foreach ($views as $view) {
            $html = view($view, [
                'reportData' => ['header' => [], 'rows' => [], 'groups' => [], 'summary' => []],
                'generatedBy' => (object) ['name' => 'Tester'],
                'generatedByName' => 'Tester',
                'generatedAt' => now(),
            ])->render();

            preg_match_all('/<div class="signature-label">([^<]+)<\/div>/', $html, $matches);

            $this->assertSame($expectedLabels, array_map('trim', $matches[1]), $view);
            $this->assertSame(3, preg_match_all('/<table class="signature-placeholder-table">/', $html), $view);
        }
    }
}

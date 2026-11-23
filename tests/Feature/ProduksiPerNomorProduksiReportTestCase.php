<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Kontrak bersama untuk seluruh laporan "produksi per nomor produksi".
 *
 * Semua laporan dalam keluarga ini memanggil SP berbentuk sama (18 kolom, sama
 * dengan flag IsReject/IsRepair), jadi perilaku partisi Input / Output /
 * Repair / Afkir harus identik. Subkelas hanya perlu menyatakan service dan
 * view PDF-nya masing-masing.
 */
abstract class ProduksiPerNomorProduksiReportTestCase extends TestCase
{
    /**
     * Service laporan yang diuji.
     */
    abstract protected function service(): object;

    /**
     * View PDF laporan yang diuji.
     */
    abstract protected function pdfView(): string;

    /**
     * Baris SPVqwe sintetis mengikuti bentuk output SPWps_LapProduksi*.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function spRow(string $type, string $label, array $overrides = []): array
    {
        return array_merge([
            'NoProduksi' => 'SA.000001',
            'NamaOperator' => 'BUDI',
            'NamaMesin' => 'MESHIN-1',
            'Tanggal' => '2026-09-23',
            'Shift' => 1,
            'JamKerja' => 8,
            'JmlhAnggota' => 12,
            'Group' => $type === 'Input' ? 'S4S' : 'FJ',
            'Type' => $type,
            'NoLabel' => $label,
            'Tebal' => 45,
            'Lebar' => 50,
            'Panjang' => 4550,
            'JmlhBatang' => 100,
            'Kubik' => 1.5,
            'IsReject' => '0',
            'IsRepair' => null,
            'IsReproses' => '0',
            'HasilDariReproses' => '0',
            'NoSPKTujuan' => '',
        ], $overrides);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    protected function build(array $rows): array
    {
        return $this->service()->buildReportData('SA.000001', $rows);
    }

    public function test_reject_rows_are_partitioned_into_afkir_and_removed_from_output(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001'),
            $this->spRow('Output', 'S.000001'),
            $this->spRow('Output', 'S.000002', ['IsReject' => '1', 'Kubik' => 0.75]),
        ]);

        $this->assertSame(['R.000001'], array_column($report['input_rows'], 'no_label'));
        $this->assertSame(['S.000001'], array_column($report['output_rows'], 'no_label'));
        $this->assertSame(['S.000002'], array_column($report['afkir_rows'], 'no_label'));
        $this->assertSame([], $report['repair_rows']);
    }

    public function test_reject_rows_are_partitioned_into_afkir_and_removed_from_input(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001'),
            $this->spRow('Input', 'R.000002', ['IsReject' => '1']),
            $this->spRow('Output', 'S.000001'),
        ]);

        $this->assertSame(['R.000001'], array_column($report['input_rows'], 'no_label'));
        $this->assertSame(['R.000002'], array_column($report['afkir_rows'], 'no_label'));
    }

    public function test_output_repair_rows_are_partitioned_into_repair_bucket(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001'),
            $this->spRow('Output', 'S.000001', ['IsRepair' => '1']),
        ]);

        $this->assertSame(['S.000001'], array_column($report['repair_rows'], 'no_label'));
        $this->assertSame([], $report['output_rows'], 'Baris repair dikeluarkan dari tabel Output.');
        $this->assertSame(['R.000001'], array_column($report['input_rows'], 'no_label'));
        $this->assertSame([], $report['afkir_rows']);
    }

    public function test_repair_flag_takes_precedence_over_reject_flag(): void
    {
        $report = $this->build([
            $this->spRow('Output', 'S.000001', ['IsRepair' => '1', 'IsReject' => '1']),
        ]);

        $this->assertSame(['S.000001'], array_column($report['repair_rows'], 'no_label'));
        $this->assertSame([], $report['afkir_rows']);
    }

    public function test_boolean_and_string_flag_values_are_both_recognised(): void
    {
        $report = $this->build([
            $this->spRow('Output', 'S.000001', ['IsReject' => true]),
            $this->spRow('Output', 'S.000002', ['IsRepair' => '1']),
            $this->spRow('Output', 'S.000003', ['IsReject' => false, 'IsRepair' => false]),
        ]);

        $this->assertSame(['S.000001'], array_column($report['afkir_rows'], 'no_label'));
        $this->assertSame(['S.000002'], array_column($report['repair_rows'], 'no_label'));
        $this->assertSame(['S.000003'], array_column($report['output_rows'], 'no_label'));
    }

    public function test_blank_flag_value_is_treated_as_not_set(): void
    {
        // Branch WIP mengirim string kosong karena WIP_h tidak punya kolom IsRepair.
        $report = $this->build([
            $this->spRow('Input', 'W.000001', ['Group' => 'WIP', 'IsRepair' => '']),
            $this->spRow('Input', 'W.000002', ['Group' => 'WIP', 'IsReject' => '']),
        ]);

        $this->assertSame(
            ['W.000001', 'W.000002'],
            array_column($report['input_rows'], 'no_label')
        );
        $this->assertSame([], $report['repair_rows']);
        $this->assertSame([], $report['afkir_rows']);
    }

    public function test_no_spk_tujuan_follows_its_row_into_whichever_bucket(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001', ['NoSPKTujuan' => '2026-12']),
            $this->spRow('Output', 'S.000001', ['NoSPKTujuan' => '2026-13']),
            $this->spRow('Output', 'S.000002', ['IsReject' => '1', 'NoSPKTujuan' => 'LOKAL']),
            $this->spRow('Output', 'S.000003', ['IsRepair' => '1', 'NoSPKTujuan' => '2026-14']),
        ]);

        $this->assertSame(['2026-12'], array_column($report['input_rows'], 'no_spk_tujuan'));
        $this->assertSame(['2026-13'], array_column($report['output_rows'], 'no_spk_tujuan'));
        $this->assertSame(['LOKAL'], array_column($report['afkir_rows'], 'no_spk_tujuan'));
        $this->assertSame(['2026-14'], array_column($report['repair_rows'], 'no_spk_tujuan'));
    }

    public function test_no_spk_tujuan_blank_stays_null_not_empty_string(): void
    {
        // Cabang WIP di SP memang mengirim '' untuk NoSPKTujuan.
        $report = $this->build([
            $this->spRow('Input', 'W.000001', ['Group' => 'WIP', 'NoSPKTujuan' => '']),
        ]);

        $this->assertNull($report['input_rows'][0]['no_spk_tujuan']);
    }

    public function test_reproses_rows_only_appear_in_their_own_buckets(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001', ['Kubik' => 10.0]),
            $this->spRow('Input', 'Y.000001', ['Group' => 'REPROSES', 'IsReproses' => '1', 'Kubik' => 2.0]),
            $this->spRow('Output', 'S.000001', ['Kubik' => 8.0]),
            $this->spRow('Output', 'Y.000002', ['Group' => 'REPROSES', 'HasilDariReproses' => '1', 'Kubik' => 1.0]),
        ]);

        // Baris reproses tidak lagi muncul di tabel Input/Output utama.
        $this->assertSame(['R.000001'], array_column($report['input_rows'], 'no_label'));
        $this->assertSame(['S.000001'], array_column($report['output_rows'], 'no_label'));

        $this->assertSame(['Y.000001'], array_column($report['input_reproses_rows'], 'no_label'));
        $this->assertSame(['Y.000002'], array_column($report['output_reproses_rows'], 'no_label'));
        $this->assertSame(2.0, $report['totals']['input_reproses']['kubik']);
        $this->assertSame(1.0, $report['totals']['output_reproses']['kubik']);

        $this->assertSame(10.0, $report['totals']['input']['kubik']);
        $this->assertSame(8.0, $report['totals']['output']['kubik']);
        $this->assertEqualsWithDelta(80.0, $report['totals']['rendemen'], 0.0001);
        $this->assertEqualsWithDelta(50.0, $report['totals']['rendemen_reproses'], 0.0001);
    }

    public function test_reject_flag_takes_precedence_over_reproses_flag(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'Y.000001', ['Group' => 'REPROSES', 'IsReproses' => '1', 'IsReject' => '1']),
        ]);

        $this->assertSame(['Y.000001'], array_column($report['afkir_rows'], 'no_label'));
        $this->assertSame([], $report['input_reproses_rows']);
    }

    public function test_isreproses_flag_marks_reproses_without_relying_on_group(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'Y.000001', ['Group' => 'Laminating', 'IsReproses' => '1']),
        ]);

        $this->assertSame([], $report['input_rows']);
        $this->assertSame(['Y.000001'], array_column($report['input_reproses_rows'], 'no_label'));
    }

    public function test_hasil_dari_reproses_flag_marks_output_reproses(): void
    {
        $report = $this->build([
            $this->spRow('Output', 'Y.000001', ['HasilDariReproses' => '1']),
        ]);

        $this->assertSame([], $report['output_rows']);
        $this->assertSame(['Y.000001'], array_column($report['output_reproses_rows'], 'no_label'));
    }

    public function test_reproses_flags_are_direction_specific(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'Y.000001', ['HasilDariReproses' => '1']),
            $this->spRow('Output', 'Y.000002', ['IsReproses' => '1']),
        ]);

        // Input hanya membaca IsReproses; Output hanya membaca HasilDariReproses.
        $this->assertSame(['Y.000001'], array_column($report['input_rows'], 'no_label'));
        $this->assertSame(['Y.000002'], array_column($report['output_rows'], 'no_label'));
        $this->assertSame([], $report['input_reproses_rows']);
        $this->assertSame([], $report['output_reproses_rows']);
    }

    public function test_group_reproses_still_works_for_old_sp_without_the_flags(): void
    {
        $input = $this->spRow('Input', 'Y.000001', ['Group' => 'REPROSES']);
        $output = $this->spRow('Output', 'Y.000002', ['Group' => 'REPROSES']);
        unset($input['IsReproses'], $input['HasilDariReproses'], $output['IsReproses'], $output['HasilDariReproses']);

        $report = $this->build([$input, $output]);

        $this->assertSame([], $report['input_rows']);
        $this->assertSame([], $report['output_rows']);
        $this->assertSame(['Y.000001'], array_column($report['input_reproses_rows'], 'no_label'));
        $this->assertSame(['Y.000002'], array_column($report['output_reproses_rows'], 'no_label'));
    }

    public function test_every_row_lands_in_exactly_one_bucket(): void
    {
        $rows = [
            $this->spRow('Input', 'R.000001'),
            $this->spRow('Input', 'R.000002', ['IsReject' => '1']),
            $this->spRow('Input', 'R.000003', ['IsRepair' => '1']),
            $this->spRow('Input', 'Y.000001', ['Group' => 'REPROSES', 'IsReproses' => '1']),
            $this->spRow('Output', 'S.000001'),
            $this->spRow('Output', 'S.000002', ['IsRepair' => '1']),
            $this->spRow('Output', 'S.000003', ['IsReject' => '1']),
            $this->spRow('Output', 'Y.000002', ['Group' => 'REPROSES', 'HasilDariReproses' => '1']),
        ];

        $report = $this->build($rows);

        $bucketCount = count($report['input_rows'])
            + count($report['output_rows'])
            + count($report['repair_rows'])
            + count($report['afkir_rows'])
            + count($report['input_reproses_rows'])
            + count($report['output_reproses_rows']);

        $this->assertSame(count($rows), $bucketCount, 'Setiap baris masuk tepat satu bucket.');

        $labels = array_merge(
            array_column($report['input_rows'], 'no_label'),
            array_column($report['output_rows'], 'no_label'),
            array_column($report['repair_rows'], 'no_label'),
            array_column($report['afkir_rows'], 'no_label'),
            array_column($report['input_reproses_rows'], 'no_label'),
            array_column($report['output_reproses_rows'], 'no_label'),
        );

        $this->assertCount(8, array_unique($labels), 'Label harus muncul tepat satu kali di seluruh bucket.');
        $this->assertSame(['R.000001', 'R.000003'], array_column($report['input_rows'], 'no_label'));
        $this->assertSame(['S.000001'], array_column($report['output_rows'], 'no_label'));
        $this->assertSame(['Y.000001'], array_column($report['input_reproses_rows'], 'no_label'));
        $this->assertSame(['Y.000002'], array_column($report['output_reproses_rows'], 'no_label'));
    }

    public function test_rendemen_uses_output_plus_repair_over_input(): void
    {
        $rows = [
            $this->spRow('Input', 'R.000001', ['Kubik' => 10.0]),
            $this->spRow('Output', 'S.000001', ['Kubik' => 8.0]),
            $this->spRow('Output', 'S.000002', ['IsReject' => '1', 'Kubik' => 2.0]),
            $this->spRow('Output', 'S.000003', ['IsRepair' => '1', 'Kubik' => 1.0]),
        ];

        $report = $this->build($rows);

        $this->assertSame(10.0, $report['totals']['input']['kubik']);
        $this->assertSame(8.0, $report['totals']['output']['kubik']);
        $this->assertSame(2.0, $report['totals']['afkir']['kubik']);
        $this->assertSame(1.0, $report['totals']['repair']['kubik']);

        // Rumus dari manajemen: (Output + Repair) / Input = (8 + 1) / 10.
        // Afkir tetap di luar, baik dari numerator maupun denominator.
        $this->assertEqualsWithDelta(90.0, $report['totals']['rendemen'], 0.0001);
    }

    public function test_repair_flag_on_an_input_row_is_ignored(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001', ['Kubik' => 10.0]),
            $this->spRow('Input', 'R.000002', ['IsRepair' => '1', 'Kubik' => 1.0]),
            $this->spRow('Output', 'S.000001', ['Kubik' => 8.0]),
        ]);

        // Hanya repair dari baris Output yang diakui. Baris input yang ber-flag repair
        // sudah diperbaiki di proses sebelumnya, jadi tetap tampil dan dihitung sebagai input.
        $this->assertSame(['R.000001', 'R.000002'], array_column($report['input_rows'], 'no_label'));
        $this->assertSame([], $report['repair_rows']);
        $this->assertSame(11.0, $report['totals']['input']['kubik']);
        $this->assertSame(0.0, $report['totals']['repair']['kubik']);

        // Rendemen = (8 + 0) / 11 * 100.
        $this->assertEqualsWithDelta(72.727272, $report['totals']['rendemen'], 0.0001);
    }

    public function test_pdf_view_prints_output_plus_repair_in_the_rendemen_line(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001', ['Kubik' => 10.0]),
            $this->spRow('Output', 'S.000001', ['Kubik' => 8.0]),
            $this->spRow('Output', 'S.000002', ['IsRepair' => '1', 'Kubik' => 1.0]),
        ]);

        $html = view($this->pdfView(), [
            'report' => $report,
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $this->assertStringContainsString('(8.0000', $html, 'Numerator menampilkan kubik Output.');
        $this->assertStringContainsString('1.0000)', $html, 'Numerator menampilkan kubik Repair.');
        $this->assertStringContainsString('90.00%', $html);
    }

    public function test_repair_and_afkir_totals_default_to_zero_when_absent(): void
    {
        $report = $this->build([$this->spRow('Output', 'S.000001')]);

        $this->assertSame(
            ['count' => 0, 'jmlh_batang' => 0, 'kubik' => 0.0],
            $report['totals']['repair']
        );
        $this->assertSame(
            ['count' => 0, 'jmlh_batang' => 0, 'kubik' => 0.0],
            $report['totals']['afkir']
        );
        $this->assertSame(
            ['count' => 0, 'jmlh_batang' => 0, 'kubik' => 0.0],
            $report['totals']['input_reproses']
        );
        $this->assertSame(
            ['count' => 0, 'jmlh_batang' => 0, 'kubik' => 0.0],
            $report['totals']['output_reproses']
        );
        $this->assertNull($report['totals']['rendemen_reproses']);
    }

    public function test_pdf_view_renders_six_detail_tables_with_reproses_sections(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001', ['NoSPKTujuan' => '2026-12']),
            $this->spRow('Input', 'Y.000001', ['Group' => 'REPROSES', 'IsReproses' => '1']),
            $this->spRow('Output', 'S.000001'),
            $this->spRow('Output', 'Y.000002', ['Group' => 'REPROSES', 'HasilDariReproses' => '1']),
            $this->spRow('Output', 'S.000002', ['IsReject' => '1', 'NoSPKTujuan' => '2026-13']),
        ]);

        $html = view($this->pdfView(), [
            'report' => $report,
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $this->assertSame(6, substr_count($html, '<table class="detail-table">'));
        $this->assertSame(6, substr_count($html, 'Total :'));
        $this->assertSame(6, substr_count($html, 'No SPK Tujuan'), 'Tiap tabel punya kolom No SPK Tujuan.');
        $this->assertStringContainsString('Data repair tidak tersedia.', $html);
        $this->assertStringNotContainsString('Data afkir tidak tersedia.', $html);
        $this->assertStringContainsString('Input Reproses', $html);
        $this->assertStringContainsString('Output Reproses', $html);
        $this->assertStringContainsString('Y.000001', $html);
        $this->assertStringContainsString('Y.000002', $html);
        $this->assertStringContainsString('S.000002', $html);
        $this->assertStringContainsString('2026-12', $html);
        $this->assertStringContainsString('2026-13', $html);
    }

    public function test_meta_reads_jam_lembur_and_hour_meter_from_header_row(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001', ['JamLembur' => 3, 'HourMeter' => 1234.567]),
        ]);

        $this->assertSame(3, $report['meta']['jam_lembur']);
        $this->assertEqualsWithDelta(1234.567, $report['meta']['hour_meter'], 0.0001);
    }

    public function test_pdf_view_shows_rangkuman_with_lembur_hour_meter_and_kubik_totals(): void
    {
        $report = $this->build([
            $this->spRow('Input', 'R.000001', ['Kubik' => 10.0, 'JamLembur' => 3, 'HourMeter' => 1234.567]),
            $this->spRow('Input', 'Y.000001', ['Group' => 'REPROSES', 'IsReproses' => '1', 'Kubik' => 0.5]),
            $this->spRow('Output', 'S.000001', ['Kubik' => 8.0]),
            $this->spRow('Output', 'Y.000002', ['Group' => 'REPROSES', 'HasilDariReproses' => '1', 'Kubik' => 0.25]),
            $this->spRow('Output', 'S.000002', ['IsRepair' => '1', 'Kubik' => 1.0]),
            $this->spRow('Output', 'S.000003', ['IsReject' => '1', 'Kubik' => 2.0]),
        ]);

        $html = view($this->pdfView(), [
            'report' => $report,
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $this->assertStringContainsString('Rangkuman', $html);
        $this->assertStringContainsString('Jam Lembur', $html);
        $this->assertStringContainsString('1234.57', $html);
        $this->assertStringContainsString('Total Input Reproses', $html);
        $this->assertStringContainsString('Total Output Reproses', $html);
        $this->assertStringContainsString('Rendemen Reproses', $html);
        $this->assertStringContainsString('50.00%', $html, 'Rendemen reproses memakai tabel reproses.');
        $this->assertStringContainsString('10.0000', $html, 'Total Input tidak termasuk reproses.');
        $this->assertStringContainsString('8.0000', $html, 'Total Output tidak termasuk reproses.');
        $this->assertStringContainsString('1.0000', $html, 'Total Repair memakai kubik repair.');
        $this->assertStringContainsString('2.0000', $html, 'Total Afkir memakai kubik afkir.');
        $this->assertStringContainsString('0.5000', $html, 'Total Input Reproses memakai kubiknya sendiri.');
        $this->assertStringContainsString('0.2500', $html, 'Total Output Reproses memakai kubiknya sendiri.');
    }

    public function test_pdf_view_prints_main_table_before_repair_afkir_and_reproses(): void
    {
        $rows = [];
        for ($i = 1; $i <= 30; $i++) {
            $rows[] = $this->spRow('Input', sprintf('R.%04d', $i));
            $rows[] = $this->spRow('Output', sprintf('S.%04d', $i));
        }
        $rows[] = $this->spRow('Output', 'A.0001', ['IsReject' => '1']);
        $rows[] = $this->spRow('Input', 'Y.0001', ['IsReproses' => '1']);

        $report = $this->build($rows);

        $html = view($this->pdfView(), [
            'report' => $report,
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $lastMainInput = strpos($html, 'R.0030');
        $repair = strpos($html, '>Repair<');
        $reproses = strpos($html, 'Input Reproses');
        $summary = strpos($html, 'Rangkuman');

        $this->assertGreaterThan(1, substr_count($html, 'class="page-block'), 'Data utama harus dipaginasi.');
        $this->assertNotFalse($lastMainInput);
        $this->assertNotFalse($repair);
        $this->assertNotFalse($reproses);
        $this->assertNotFalse($summary);

        $this->assertLessThan($repair, $lastMainInput, 'Semua baris Input/Output harus tampil sebelum Repair/Afkir.');
        $this->assertLessThan($reproses, $repair, 'Repair/Afkir harus tampil sebelum Reproses.');
        $this->assertLessThan($summary, $reproses, 'Reproses harus tampil sebelum Rangkuman.');
    }

    public function test_pdf_view_paginates_when_data_is_large(): void
    {
        $rows = [];
        for ($i = 1; $i <= 30; $i++) {
            $rows[] = $this->spRow('Input', sprintf('R.%04d', $i));
            $rows[] = $this->spRow('Output', sprintf('S.%04d', $i));
            $rows[] = $this->spRow('Output', sprintf('A.%04d', $i), ['IsReject' => '1']);
        }

        $report = $this->build($rows);

        $html = view($this->pdfView(), [
            'report' => $report,
            'generatedBy' => null,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ])->render();

        $this->assertGreaterThan(1, substr_count($html, 'class="page-block'), 'Data manyak harus dipaginasi.');
        $this->assertSame(6, substr_count($html, 'Total :'), 'Baris total hanya di halaman terakhir tiap tabel.');
    }
}

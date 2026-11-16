<?php

namespace Tests\Unit\Ascends;

use App\Services\Ascends\Shared\Finance\ReceivableSummary\UmurPiutangRuReportService;
use App\Support\Ascends\CompanyReportDefinition;
use App\Support\Ascends\CompanyReportRegistry;
use Tests\TestCase;

class CompanyReportRegistryTest extends TestCase
{
    public function test_registry_resolves_service_view_and_filename_for_company(): void
    {
        $def = new CompanyReportDefinition(
            slug: 'finance.umur-piutang',
            service: UmurPiutangRuReportService::class,
            view: 'ascends.shared.finance.receivable-summary.umur_piutang_ru.pdf',
            pdf: ['format' => 'A4', 'orientation' => 'portrait', 'column_count' => 5],
            filename: fn (array $data, string $company): string => 'Laporan Umur Piutang ('.$company.').pdf',
            overrides: ['ru' => UmurPiutangRuReportService::class],
        );

        $this->assertSame(UmurPiutangRuReportService::class, $def->serviceFor('gsu'));
        $this->assertSame(UmurPiutangRuReportService::class, $def->serviceFor('ru'));
        $this->assertSame('Laporan Umur Piutang (RU).pdf', $def->filenameFor([], 'RU'));
        $this->assertSame(['gsu', 'ru', 'uc'], CompanyReportRegistry::companies());
    }

    public function test_definition_uses_view_override_for_company(): void
    {
        $def = new CompanyReportDefinition(
            slug: 'finance.umur-piutang',
            service: UmurPiutangRuReportService::class,
            view: 'ascends.shared.finance.umur_piutang.pdf',
            pdf: ['format' => 'A4'],
            filename: 'Laporan Umur Piutang.pdf',
            overrides: ['uc' => ['view' => 'ascends.shared.finance.umur_piutang_uc.pdf']],
        );

        $this->assertSame('ascends.shared.finance.umur_piutang.pdf', $def->viewFor('gsu'));
        $this->assertSame('ascends.shared.finance.umur_piutang_uc.pdf', $def->viewFor('uc'));
        $this->assertSame('Laporan Umur Piutang.pdf', $def->filenameFor([], 'gsu'));
    }

    public function test_registry_get_returns_null_for_unknown_slug(): void
    {
        $this->assertNull(CompanyReportRegistry::get('unknown.report'));
    }
}

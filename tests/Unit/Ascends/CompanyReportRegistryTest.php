<?php

namespace Tests\Unit\Ascends;

use App\Services\Ascends\Shared\Finance\ReceivableSummary\UmurPiutangRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\ArusKasGsuReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\ArusKasRuReportService;
use App\Support\Ascends\CompanyReportDefinition;
use App\Support\Ascends\CompanyReportRegistry;
use Illuminate\Support\Facades\Route;
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
        $this->assertSame([], CompanyReportRegistry::allowedCompanies('unknown.report'));
    }

    public function test_registry_covers_every_shared_route_after_suffix_normalization(): void
    {
        $paths = $this->sharedRoutePaths();
        sort($paths);

        // 180 shared route paths collapse to 163 slugs once company suffixes are stripped.
        $this->assertSame(163, count($paths));
        $this->assertSame($paths, array_keys(CompanyReportRegistry::all()));
        $this->assertNotNull(CompanyReportRegistry::get('general_ledger.trial_balance_monthly.arus-kas'));
        $this->assertSame(['ru'], CompanyReportRegistry::allowedCompanies('finance.receivable-summary.umur-piutang'));
        $this->assertNull(CompanyReportRegistry::get('finance.receivable-summary.umur-piutang-ru'));
    }

    public function test_family_overrides_keep_per_company_pdf_and_filename(): void
    {
        $labaKotor = CompanyReportRegistry::get('general_ledger.journal_details.laba-kotor');

        $this->assertNotNull($labaKotor);
        $this->assertSame('landscape', $labaKotor->pdfFor('gsu')['orientation']);
        $this->assertSame('portrait', $labaKotor->pdfFor('ru')['orientation']);
        $this->assertSame('A4', $labaKotor->pdfFor('ru')['format']);

        $bunga = CompanyReportRegistry::get('general_ledger.journal_details.perhitungan-bunga');

        $this->assertNotNull($bunga);
        $this->assertStringNotContainsString('GSU', $bunga->filenameFor([], 'ru'));
        $this->assertStringContainsString('Bunga RU', $bunga->filenameFor([], 'ru'));
    }

    public function test_registry_key_equals_definition_slug(): void
    {
        foreach (CompanyReportRegistry::all() as $key => $definition) {
            $this->assertSame($key, $definition->slug);
        }
    }

    public function test_override_uses_a_different_service_than_the_base(): void
    {
        $definition = CompanyReportRegistry::get('general_ledger.trial_balance_monthly.arus-kas');

        $this->assertNotNull($definition);
        $this->assertSame(ArusKasGsuReportService::class, $definition->serviceFor('gsu'));
        $this->assertSame(ArusKasRuReportService::class, $definition->serviceFor('ru'));
        $this->assertNotSame($definition->service, $definition->serviceFor('ru'));
    }

    public function test_static_filenames_do_not_embed_a_company_label(): void
    {
        foreach (CompanyReportRegistry::all() as $slug => $definition) {
            if (! is_string($definition->filename)) {
                continue;
            }

            foreach (CompanyReportRegistry::companies() as $company) {
                $this->assertStringNotContainsString('('.strtoupper($company).')', $definition->filename, $slug);
            }
        }
    }

    public function test_overrides_are_lowercase_published_company_codes(): void
    {
        foreach (CompanyReportRegistry::all() as $slug => $definition) {
            $allowed = CompanyReportRegistry::allowedCompanies($slug);

            // allowedCompanies keeps the canonical gsu, ru, uc order.
            $this->assertSame(
                array_values(array_intersect(CompanyReportRegistry::companies(), $allowed)),
                $allowed,
                $slug
            );

            foreach (array_keys($definition->overrides) as $company) {
                $this->assertSame(strtolower($company), $company, $slug);
                $this->assertContains($company, $allowed, $slug);
            }
        }
    }

    public function test_every_registered_service_and_view_exists(): void
    {
        foreach (CompanyReportRegistry::all() as $slug => $definition) {
            $this->assertTrue(class_exists($definition->service), $slug);
            $this->assertFileExists(
                resource_path('views/'.str_replace('.', '/', $definition->view).'.blade.php'),
                $slug
            );

            foreach ($definition->overrides as $override) {
                $this->assertTrue(class_exists($override['service'] ?? $definition->service), $slug);
                $this->assertFileExists(
                    resource_path('views/'.str_replace('.', '/', $override['view'] ?? $definition->view).'.blade.php'),
                    $slug
                );
            }
        }
    }

    public function test_hrm_reports_constant_matches_shared_dynamic_route_where_in(): void
    {
        $shared = Route::getRoutes()->getByName('api.internal.ascends.shared.hrm.report.pdf');

        $this->assertNotNull($shared);
        $this->assertSame(CompanyReportRegistry::HRM_REPORTS, explode('|', $shared->wheres['report']));
    }

    /**
     * Normalized shared report slugs parsed from routes/api.php: the wildcard HRM
     * route expands to its whereIn list and company suffixes are stripped.
     *
     * @return list<string>
     */
    private function sharedRoutePaths(): array
    {
        $routes = file_get_contents(base_path('routes/api.php'));

        preg_match_all(
            "#Route::post\(\s*'(/internal/ascends/shared/[^']+)'\s*,\s*\[AscendXmlTestController::class,\s*'\w+'\]\s*\)#",
            $routes,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $paths = [];
        foreach ($matches[1] as [$path, $offset]) {
            if (! str_contains($path, '{report}')) {
                $paths[] = $path;

                continue;
            }

            preg_match("/->whereIn\('report',\s*\[(.*?)\]\)/s", substr($routes, $offset), $whereIn);
            preg_match_all("/'([^']+)'/", $whereIn[1] ?? '', $reports);

            // The list now lives in the registry constant.
            $reportNames = $reports[1] === [] ? CompanyReportRegistry::HRM_REPORTS : $reports[1];

            foreach ($reportNames as $report) {
                $paths[] = str_replace('{report}', $report, $path);
            }
        }

        $slugs = array_map(function (string $path): string {
            $slug = str_replace('/internal/ascends/shared/', '', $path);
            $slug = str_replace('/pdf', '', $slug);
            $slug = str_replace('/', '.', $slug);

            return preg_replace('/-(gsu|ru|uc)$/', '', $slug);
        }, $paths);

        return array_values(array_unique($slugs));
    }
}

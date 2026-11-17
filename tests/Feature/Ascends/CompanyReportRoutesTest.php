<?php

namespace Tests\Feature\Ascends;

use App\Http\Controllers\Ascends\CompanyReportController;
use App\Http\Controllers\AscendXmlTestController;
use App\Support\Ascends\CompanyReportRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CompanyReportRoutesTest extends TestCase
{
    public function test_generates_routes_for_all_companies(): void
    {
        foreach (['gsu', 'ru', 'uc'] as $company) {
            $this->assertTrue(Route::has("api.internal.ascends.{$company}.associate.customer-baru.pdf"));
        }

        // Company-suffixed shared report is published to its own company only.
        $this->assertTrue(Route::has('api.internal.ascends.ru.finance.receivable-summary.umur-piutang.pdf'));
        $this->assertFalse(Route::has('api.internal.ascends.gsu.finance.receivable-summary.umur-piutang.pdf'));
        $this->assertFalse(Route::has('api.internal.ascends.uc.finance.receivable-summary.umur-piutang.pdf'));

        foreach (array_keys(CompanyReportRegistry::all()) as $slug) {
            foreach (['gsu', 'ru', 'uc'] as $company) {
                $name = "api.internal.ascends.{$company}.{$slug}.pdf";
                $uri = "api/internal/ascends/{$company}/".str_replace('.', '/', $slug).'/pdf';

                if (in_array($company, CompanyReportRegistry::allowedCompanies($slug), true)) {
                    $this->assertTrue(Route::has($name), "Missing route {$name}");
                    $this->assertSame($uri, Route::getRoutes()->getByName($name)?->uri());
                } else {
                    $this->assertFalse(Route::has($name), "Unexpected route {$name}");
                }
            }
        }
    }

    public function test_existing_company_route_still_wins(): void
    {
        $legacyCompany = Route::getRoutes()->match(
            Request::create('/api/internal/ascends/ru/hrm/list-karyawan/pdf', 'POST')
        );
        $this->assertSame(AscendXmlTestController::class.'@apiPdf', $legacyCompany->getActionName());

        $legacyShared = Route::getRoutes()->match(
            Request::create('/api/internal/ascends/shared/finance/receivable_details/piutang-diatas-60-hari/pdf', 'POST')
        );
        $this->assertSame(
            AscendXmlTestController::class.'@apiSharedFinanceReceivableDetailsPiutangDiatas60HariPdf',
            $legacyShared->getActionName()
        );

        $generated = Route::getRoutes()->match(
            Request::create('/api/internal/ascends/uc/associate/customer-baru/pdf', 'POST')
        );
        $this->assertSame(CompanyReportController::class.'@pdf', $generated->getActionName());
    }

    public function test_dynamic_hrm_route_keeps_where_in(): void
    {
        $this->assertTrue(Route::has('api.internal.ascends.company.hrm.report.pdf'));

        $route = Route::getRoutes()->getByName('api.internal.ascends.company.hrm.report.pdf');

        $this->assertNotNull($route);
        $this->assertSame('api/internal/ascends/{company}/hrm/{report}/pdf', $route->uri());
        $this->assertSame('gsu|ru|uc', $route->wheres['company']);

        $publishedToAll = array_values(array_filter(
            CompanyReportRegistry::HRM_REPORTS,
            static fn (string $report): bool => CompanyReportRegistry::allowedCompanies('hrm.'.$report) === CompanyReportRegistry::companies()
        ));

        $this->assertSame($publishedToAll, explode('|', $route->wheres['report']));
    }

    public function test_dynamic_hrm_route_resolves_bare_report_keys(): void
    {
        // Without its static twin the request can only reach the dynamic route, which
        // hands the controller the bare report name the shared endpoint also uses.
        $this->forgetRoute('api.internal.ascends.uc.hrm.kehadiran-kk-kt-st.pdf');
        $this->assertFalse(Route::has('api.internal.ascends.uc.hrm.kehadiran-kk-kt-st.pdf'));

        $matched = Route::getRoutes()->match(
            Request::create('/api/internal/ascends/uc/hrm/kehadiran-kk-kt-st/pdf', 'POST')
        );
        $this->assertSame('api/internal/ascends/{company}/hrm/{report}/pdf', $matched->uri());

        $this->post('/api/internal/ascends/uc/hrm/kehadiran-kk-kt-st/pdf')
            ->assertStatus(422)
            ->assertJson(['message' => 'File XML (xml_file) wajib dikirim.']);

        $this->post('/api/internal/ascends/gsu/hrm/bukan-laporan/pdf')->assertNotFound();
    }

    public function test_generated_static_route_reaches_controller(): void
    {
        $this->post('/api/internal/ascends/uc/associate/customer-baru/pdf')->assertStatus(422);
    }

    public function test_rejects_unknown_company_prefix(): void
    {
        $this->post('/api/internal/ascends/xx/finance/receivable_details/piutang-diatas-60-hari/pdf')
            ->assertNotFound();

        $this->post('/api/internal/ascends/xx/hrm/kehadiran-kk-kt-st/pdf')
            ->assertNotFound();
    }

    /**
     * Replace the router's route collection with the same routes minus one name.
     */
    private function forgetRoute(string $name): void
    {
        $collection = new RouteCollection;

        foreach (Route::getRoutes() as $route) {
            if ($route->getName() !== $name) {
                $collection->add($route);
            }
        }

        $this->app['router']->setRoutes($collection);
    }
}

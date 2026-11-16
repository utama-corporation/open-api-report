<?php

use App\Http\Controllers\Ascends\CompanyReportController;
use App\Support\Ascends\CompanyReportRegistry;
use Illuminate\Support\Facades\Route;

/*
 * Company (gsu/ru/uc) copies of the shared Ascends endpoints.
 *
 * Required at the end of api.php on purpose: Laravel matches routes in
 * registration order, so the existing shared and company endpoints keep
 * owning their URLs. Paths already registered there are skipped.
 */

// Reports served by the shared dynamic /internal/ascends/shared/hrm/{report}/pdf route.
// A single {company} route may only whitelist reports published to every company;
// restricted ones are covered by their own static routes.
$hrmReports = array_values(array_filter(
    CompanyReportRegistry::HRM_REPORTS,
    static fn (string $report): bool => CompanyReportRegistry::allowedCompanies('hrm.'.$report) === CompanyReportRegistry::companies()
));

$registeredRoutes = [];
foreach (Route::getRoutes() as $route) {
    foreach ($route->methods() as $method) {
        $registeredRoutes[$method.' '.$route->uri()] = true;
    }
}

foreach (array_keys(CompanyReportRegistry::all()) as $slug) {
    $path = str_replace('.', '/', $slug);

    foreach (CompanyReportRegistry::allowedCompanies($slug) as $company) {
        $uri = "internal/ascends/{$company}/{$path}/pdf";

        // Already owned by routes/api.php, e.g. /internal/ascends/ru/hrm/list-karyawan/pdf.
        // These routes are registered inside api.php, so their URI carries the api prefix.
        if (isset($registeredRoutes['POST api/'.$uri])) {
            continue;
        }

        Route::post("/{$uri}", [CompanyReportController::class, 'pdf'])
            // The literal company segment is not a route parameter, so both
            // controller arguments are supplied as defaults, in signature order.
            ->defaults('company', $company)
            ->defaults('report', $slug)
            ->name("api.internal.ascends.{$company}.{$slug}.pdf");
    }
}

// Mirrors the shared hrm endpoint (same report list, plus the company prefix).
// Route name: api.internal.ascends.company.hrm.report.pdf — one route serves all
// three companies, so it cannot carry a per-company name.
Route::post('/internal/ascends/{company}/hrm/{report}/pdf', [CompanyReportController::class, 'pdf'])
    ->whereIn('company', ['gsu', 'ru', 'uc'])
    ->whereIn('report', $hrmReports)
    ->name('api.internal.ascends.company.hrm.report.pdf');

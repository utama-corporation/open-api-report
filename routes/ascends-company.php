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
$hrmReports = [
    'list-karyawan',
    'daftar-karyawan',
    'daftar-karyawan-berdasarkan-abjad',
    'data-karyawan-status-kerja',
    'karyawan-aktif-per-departemen',
    'karyawan-masuk-per-departemen-per-tanggal-masuk',
    'karyawan-per-agama',
    'karyawan-per-departemen-per-jabatan',
    'karyawan-per-etnis',
    'karyawan-per-level',
    'karyawan-per-masa-kerja',
    'karyawan-per-umur',
    'kehadiran-kk-kt-st',
    'list-karyawan-habis-kontrak',
    'perbandingan-jumlah-karyawan-tahunan-per-bulan',
    'usia-generasi-tahun-kelahiran-masa-kerja',
];

$registeredUris = [];
foreach (Route::getRoutes() as $route) {
    $registeredUris[$route->uri()] = true;
}

foreach (array_keys(CompanyReportRegistry::all()) as $slug) {
    $path = str_replace('.', '/', $slug);

    foreach (CompanyReportRegistry::allowedCompanies($slug) as $company) {
        $uri = "internal/ascends/{$company}/{$path}/pdf";

        // Already owned by routes/api.php, e.g. /internal/ascends/ru/hrm/list-karyawan/pdf.
        // These routes are registered inside api.php, so their URI carries the api prefix.
        if (isset($registeredUris['api/'.$uri])) {
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

// Mirrors the shared hrm endpoint, keeping the same report list and adding the company prefix.
Route::post('/internal/ascends/{company}/hrm/{report}/pdf', [CompanyReportController::class, 'pdf'])
    ->whereIn('company', ['gsu', 'ru', 'uc'])
    ->whereIn('report', $hrmReports)
    ->name('api.internal.ascends.company.hrm.report.pdf');

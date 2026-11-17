<?php

/**
 * Generates app/Support/Ascends/CompanyReportRegistry.php from the shared Ascend routes.
 *
 * Usage: php scripts/ascends/generate-company-report-registry.php
 *
 * The output is generated then reviewed manually: it is committed together with
 * this script so a regeneration diff stays readable. Run Pint afterwards.
 */
$root = dirname(__DIR__, 2);
$routesPath = $root.'/routes/api.php';
$controllerPath = $root.'/app/Http/Controllers/AscendXmlTestController.php';
$outputPath = $root.'/app/Support/Ascends/CompanyReportRegistry.php';

$companies = ['gsu', 'ru', 'uc'];

/*
 * The wildcard HRM route dispatches on {report}; its service, view and title live
 * in match expressions, so they are enumerated here instead of parsed. These keys
 * are also emitted as CompanyReportRegistry::HRM_REPORTS.
 */
$wildcardReports = [
    'list-karyawan' => ['EmployeeListReportService', 'ascends.shared.hrm.employee_list.list_karyawan.pdf', 'Employee List - List Karyawan'],
    'daftar-karyawan' => ['DaftarKaryawanReportService', 'ascends.shared.hrm.employee_list.daftar_karyawan.pdf', 'Employee List - Laporan Daftar Karyawan'],
    'daftar-karyawan-berdasarkan-abjad' => ['DaftarKaryawanBerdasarkanAbjadReportService', 'ascends.shared.hrm.employee_list.daftar_karyawan_berdasarkan_abjad.pdf', 'Employee List - Laporan Daftar Karyawan Berdasarkan Abjad'],
    'data-karyawan-status-kerja' => ['DataKaryawanStatusKerjaReportService', 'ascends.shared.hrm.employee_list.data_karyawan_status_kerja.pdf', 'Employee List - Laporan Data Karyawan Staff, Karyawan Tetap & Karyawan Kontrak Berdasarkan Status Kerja'],
    'karyawan-aktif-per-departemen' => ['KaryawanAktifPerDepartemenReportService', 'ascends.shared.hrm.employee_list.karyawan_aktif_per_departemen.pdf', 'Employee List - Laporan Karyawan Aktif Per Departemen'],
    'karyawan-masuk-per-departemen-per-tanggal-masuk' => ['KaryawanMasukPerDepartemenPerTanggalMasukReportService', 'ascends.shared.hrm.employee_list.karyawan_masuk_per_departemen_per_tanggal_masuk.pdf', 'Employee List - Laporan Karyawan Masuk Per Departemen Per Tanggal Masuk'],
    'karyawan-per-agama' => ['KaryawanPerAgamaReportService', 'ascends.shared.hrm.employee_list.karyawan_per_agama.pdf', 'Employee List - Laporan Karyawan Per Agama'],
    'karyawan-per-departemen-per-jabatan' => ['KaryawanPerDepartemenPerJabatanReportService', 'ascends.shared.hrm.employee_list.karyawan_per_departemen_per_jabatan.pdf', 'Employee List - Laporan Karyawan Per Departemen Per Jabatan'],
    'karyawan-per-etnis' => ['KaryawanPerEtnisReportService', 'ascends.shared.hrm.employee_list.karyawan_per_etnis.pdf', 'Employee List - Laporan Karyawan Per Etnis'],
    'karyawan-per-level' => ['KaryawanPerLevelReportService', 'ascends.shared.hrm.employee_list.karyawan_per_level.pdf', 'Employee List - Laporan Karyawan Per Level'],
    'karyawan-per-masa-kerja' => ['KaryawanPerMasaKerjaReportService', 'ascends.shared.hrm.employee_list.karyawan_per_masa_kerja.pdf', 'Employee List - Laporan Karyawan Per Masa Kerja'],
    'karyawan-per-umur' => ['KaryawanPerUmurReportService', 'ascends.shared.hrm.employee_list.karyawan_per_umur.pdf', 'Employee List - Laporan Karyawan Per Umur'],
    'kehadiran-kk-kt-st' => ['KehadiranKkKtStReportService', 'ascends.shared.hrm.employee_list.kehadiran_kk_kt_st.pdf', 'Employee List - Laporan Kehadiran KK KT ST'],
    'list-karyawan-habis-kontrak' => ['ListKaryawanHabisKontrakReportService', 'ascends.shared.hrm.employee_list.list_karyawan_habis_kontrak.pdf', 'Employee List - Laporan List Karyawan Habis Kontrak'],
    'perbandingan-jumlah-karyawan-tahunan-per-bulan' => ['PerbandinganJumlahKaryawanTahunanPerBulanReportService', 'ascends.shared.hrm.employee_list.perbandingan_jumlah_karyawan_tahunan_per_bulan.pdf', 'Employee List - Laporan Perbandingan Jumlah Karyawan Tahunan Per Bulan'],
    'usia-generasi-tahun-kelahiran-masa-kerja' => ['UsiaGenerasiTahunKelahiranMasaKerjaReportService', 'ascends.shared.hrm.employee_list.usia_generasi_tahun_kelahiran_masa_kerja.pdf', 'Employee List - Laporan Usia Generasi Berdasakan Tahun Kelahiran dan Masa Kerja'],
];

/*
 * Filename bodies that need a data label on top of the company label. Written as
 * PHP source for the generated closure; $data is the report data array.
 */
$filenameOverrides = [
    'apiSharedHrmAbsensiBriefingHarianPdf' => "'Attendance Full - Laporan Absensi Briefing Harian ('.\$company.')'.((\$data['group'] ?? '') !== '' ? ' - '.\$data['group'] : '').'.pdf'",
    'apiSharedHrmAbsensiBriefingHarianGsuPdf' => "'Attendance Full - Laporan Absensi Briefing Harian ('.\$company.')'.((\$data['group'] ?? '') !== '' ? ' - '.\$data['group'] : '').'.pdf'",
    'apiSharedHrmAbsensiBriefingHarianUcPdf' => "'Attendance Full - Laporan Absensi Briefing Harian ('.\$company.')'.((\$data['group'] ?? '') !== '' ? ' - '.\$data['group'] : '').'.pdf'",
    'apiSharedHrmKaryawanKeluarTahunanCustomPdf' => "'Laporan Karyawan Keluar Per Departemen Tahunan ('.(\$data['status'] ?? '').') ('.\$company.').pdf'",
    'apiSharedHrmCustomLemburBulananPdf' => "'Custom Reports - Laporan Lembur Bulanan Per Departemen ('.(\$data['type_label'] ?? 'KK/KT').').pdf'",
    'apiSharedHrmThrPdf' => "'Employee List - Laporan THR ('.(\$data['thr_type'] ?? 'Idul Fitri').') ('.\$company.').pdf'",
    'apiSharedHrmLossTimePdf' => "'Loss Time - Laporan Loss Time '.(\$data['type'] ?? '').' ('.\$company.').pdf'",
    'apiSharedHrmMppTahunanPerDivisiGsuPdf' => "'Employee List - Laporan MPP Tahunan Per Divisi '.(\$data['divisi'] ?? '').' ('.\$company.').pdf'",
    'apiSharedHrmKetidakhadiranBulananPdf' => "'Absence - Laporan Ketidakhadiran Bulanan ('.\$company.') - '.((\$data['tipe'] ?? '') === 'ST' ? 'Staff' : 'KK/KT').'.pdf'",
    'apiSharedHrmPersentaseKehadiranBulananPdf' => "'Attendance Full - Laporan Persentase Kehadiran Bulanan ('.(\$data['type'] ?? 'KK/KT').').pdf'",
    'apiSharedHrmRekapitulasiKehadiranKurang93TahunanPdf' => "'Attendance Full - Laporan Rekapitulasi Kehadiran Kurang dari 93 % Tahunan ('.(\$data['status'] ?? 'KK/KT').').pdf'",
    'apiSharedHrmRekapitulasiPengabaianKeterlambatanTahunanPdf' => "'Attendance Full - Laporan Rekapitulasi Pengabaian Keterlambatan Tahunan ('.(\$data['status'] ?? 'KK/KT').').pdf'",
    'apiSharedHrmPengabaianKeterlambatanKehadiranManualPdf' => "'Attendance Full - Laporan Pengabaian Keterlambatan & Kehadiran Manual Per Departemen ('.(\$data['status'] ?? 'KK/KT').').pdf'",
    'apiSharedHrmDurasiDendaKeterlambatanPdf' => "'Late Sign In - Laporan Durasi & Denda Keterlambatan Per Departemen '.str_replace('/', ' ', (string) (\$data['type'] ?? 'KK KT')).' ('.\$company.').pdf'",
    'apiSharedHrmLemburBulananPdf' => "'Overtime - Laporan Lembur Bulanan '.str_replace('/', ' ', (string) (\$data['type'] ?? 'KK KT')).' Per Departemen ('.\$company.').pdf'",
    'apiSharedFinanceReceiptVoucherDetailsGiroCashTransferPdf' => "'Laporan Pembayaran '.((\$data['payment_method'] ?? '') !== '' ? ucfirst(strtolower((string) \$data['payment_method'])).' - ' : '').\$company.'.pdf'",
];

$wildcardNames = array_keys($wildcardReports);

$routes = file_get_contents($routesPath);
$controller = file_get_contents($controllerPath);

preg_match_all(
    "#Route::post\(\s*'(/internal/ascends/shared/[^']+)'\s*,\s*\[AscendXmlTestController::class,\s*'(\w+)'\]\s*\)#",
    $routes,
    $routeMatches,
    PREG_OFFSET_CAPTURE
);

$routeDefinitions = [];
foreach ($routeMatches[1] as $index => [$path, $offset]) {
    $method = $routeMatches[2][$index][0];

    if (! str_contains($path, '{report}')) {
        $routeDefinitions[] = ['path' => $path, 'method' => $method, 'report' => null];

        continue;
    }

    // The report list is either an inline array or CompanyReportRegistry::HRM_REPORTS;
    // in both cases it must match the enumeration above.
    preg_match("/->whereIn\('report',\s*(?:\[(.*?)\]|CompanyReportRegistry::HRM_REPORTS)\)/s", substr($routes, $offset), $whereIn);
    preg_match_all("/'([^']+)'/", $whereIn[1] ?? '', $reports);

    if ($reports[1] !== [] && $reports[1] !== $wildcardNames) {
        throw new RuntimeException('hrm whereIn list does not match $wildcardReports');
    }

    foreach ($wildcardNames as $report) {
        $routeDefinitions[] = [
            'path' => str_replace('{report}', $report, $path),
            'method' => $method,
            'report' => $report,
        ];
    }
}

// Controller method bodies: everything up to the next class method.
$parts = preg_split('/(?=\n    (?:public|private|protected) function )/', $controller);
$methodBodies = [];
foreach ($parts as $part) {
    if (preg_match('/\n    public function (\w+)\(/', $part, $name)) {
        $methodBodies[$name[1]] = $part;
    }
}

preg_match_all('/^use (App\\\\Services\\\\[^;]+);$/m', $controller, $importMatches);
$services = [];
foreach ($importMatches[1] as $import) {
    [$fqcn, $alias] = array_pad(preg_split('/ as /', $import), 2, null);
    $short = $alias ?? substr($fqcn, strrpos($fqcn, '\\') + 1);
    $services[$short] = $fqcn;
}

/**
 * Filename titles only need the literal text of the header expression; dynamic
 * data labels are handled by $filenameOverrides.
 *
 * @return array{title?: string, closure?: string, company: bool}
 */
function resolveFilename(string $method, string $body, array $overrides): array
{
    if (isset($overrides[$method])) {
        return ['closure' => $overrides[$method], 'company' => true];
    }

    if (! preg_match('/Content-Disposition[^\n]*filename="(.*)"\',\r?$/m', $body, $header)) {
        throw new RuntimeException("No filename header in {$method}");
    }

    $expression = $header[1];

    if (preg_match('/sharedHrmEmployeeListFilename\(\'([^\']+)\', \$company\)/', $expression, $helper)) {
        return ['title' => 'Employee List - '.$helper[1], 'company' => true];
    }

    if (preg_match('/sharedHrmEmployeeTerminationFilename\(\'([^\']+)\', \$company\)/', $expression, $helper)) {
        return ['title' => 'Employee Termination - '.$helper[1], 'company' => true];
    }

    if (str_contains($expression, '$filename')) {
        // Header is '...filename="'.$filename.'"', so the assignment carries the title.
        if (preg_match('/\$filename = (.+);/', $body, $assignment)) {
            $expression = $assignment[1];
        } else {
            throw new RuntimeException("No filename assignment in {$method}");
        }
    } else {
        // The capture starts inside the header string, so restore its opening quote
        // to keep the literal pairing (odd explode segments) aligned.
        $expression = "'".$expression;
    }

    $company = str_contains($expression, '$company')
        || str_contains($expression, '$companySuffix')
        || str_contains($expression, '$titleCompany');

    $segments = array_filter(
        explode("'", $expression),
        fn (string $segment, int $index): bool => $index % 2 === 1,
        ARRAY_FILTER_USE_BOTH
    );
    $title = implode('', $segments);
    $title = str_replace(['()', '( )'], '', $title);
    $title = preg_replace(['/\s+/', '/\s*-\s*\.pdf$/', '/\s+\.pdf$/'], [' ', '', ''], $title);
    $title = trim(preg_replace('/\.pdf$/', '', trim($title)));

    if ($title === '') {
        throw new RuntimeException("Empty filename title in {$method}");
    }

    return ['title' => $title, 'company' => $company];
}

/**
 * @return array{service: string, view: string, pdf: array<string, string>, filename: array<string, mixed>}
 */
function resolveRecord(array $route, array $methodBodies, array $services, array $wildcardReports, array $overrides): array
{
    $body = $methodBodies[$route['method']] ?? null;
    if ($body === null) {
        throw new RuntimeException("Missing controller method {$route['method']}");
    }

    if ($route['report'] !== null) {
        [$service, $view, $title] = $wildcardReports[$route['report']];

        return [
            'service' => $services[$service] ?? throw new RuntimeException("Missing import for {$service}"),
            'view' => $view,
            'pdf' => ['format' => 'A4', 'orientation' => 'portrait'],
            'filename' => ['title' => $title, 'company' => true],
        ];
    }

    preg_match('/function \w+\((.*?)\)/s', $body, $signature);
    preg_match('/(\w+ReportService) \$/', $signature[1] ?? '', $serviceMatch);
    $service = $services[$serviceMatch[1] ?? ''] ?? throw new RuntimeException("Missing service import in {$route['method']}");

    preg_match("/\\\$pdfGenerator->render\(\s*'([^']+)'/", $body, $viewMatch);
    $view = $viewMatch[1] ?? throw new RuntimeException("Missing view in {$route['method']}");

    $pdf = [];
    if (preg_match("/'pdf_format'\s*=>\s*'([^']+)'/", $body, $format)) {
        $pdf['format'] = $format[1];
    }
    if (preg_match("/'pdf_orientation'\s*=>\s*'([^']+)'/", $body, $orientation)) {
        $pdf['orientation'] = $orientation[1];
    }

    return [
        'service' => $service,
        'view' => $view,
        'pdf' => $pdf,
        'filename' => resolveFilename($route['method'], $body, $overrides),
    ];
}

$records = [];
foreach ($routeDefinitions as $route) {
    $slug = str_replace(['/internal/ascends/shared/', '/pdf'], '', $route['path']);
    $slug = str_replace('/', '.', $slug);

    $suffix = null;
    if (preg_match('/-(gsu|ru|uc)$/', $slug, $suffixMatch)) {
        $suffix = $suffixMatch[1];
        $slug = substr($slug, 0, -strlen($suffix) - 1);
    }

    $record = resolveRecord($route, $methodBodies, $services, $wildcardReports, $filenameOverrides);
    $record['filenameSource'] = filenameSource($record['filename']);

    if ($suffix === null) {
        foreach ($companies as $company) {
            if (isset($records[$slug][$company]) && $records[$slug][$company] !== $record) {
                throw new RuntimeException("Conflicting shared records for {$slug} ({$company})");
            }
            $records[$slug][$company] ??= $record;
        }
    } else {
        if (isset($records[$slug][$suffix]) && $records[$slug][$suffix] !== $record) {
            throw new RuntimeException("Conflicting shared records for {$slug} ({$suffix})");
        }
        $records[$slug][$suffix] = $record;
    }
}

ksort($records);

$usedServices = [];
$entries = [];
$published = [];
foreach ($records as $slug => $byCompany) {
    $allowed = array_values(array_filter($companies, fn (string $company): bool => isset($byCompany[$company])));
    $base = $byCompany[$allowed[0]];
    $usedServices[$base['service']] = true;

    $overrides = [];
    foreach (array_slice($allowed, 1) as $company) {
        $override = [];
        if ($byCompany[$company]['service'] !== $base['service']) {
            $override['service'] = $byCompany[$company]['service'];
            $usedServices[$override['service']] = true;
        }
        if ($byCompany[$company]['view'] !== $base['view']) {
            $override['view'] = $byCompany[$company]['view'];
        }
        if ($byCompany[$company]['pdf'] !== $base['pdf']) {
            $override['pdf'] = $byCompany[$company]['pdf'];
        }
        if ($byCompany[$company]['filenameSource'] !== $base['filenameSource']) {
            $override['filename'] = $byCompany[$company]['filenameSource'];
        }
        if ($override !== []) {
            $overrides[$company] = $override;
        }
    }

    if ($allowed !== $companies) {
        $published[$slug] = $allowed;
    }

    $entries[$slug] = ['base' => $base, 'overrides' => $overrides];
}

/**
 * @param  array<string, mixed>  $filename
 */
function filenameSource(array $filename): string
{
    if (isset($filename['closure'])) {
        return 'fn (array $data, string $company): string => '.$filename['closure'];
    }

    $title = str_replace("'", "\\'", $filename['title']);

    return $filename['company']
        ? "fn (array \$data, string \$company): string => '{$title} ('.\$company.').pdf'"
        : "'{$title}.pdf'";
}

/**
 * @param  array<string, string>  $pdf
 */
function pdfSource(array $pdf): string
{
    $parts = [];
    foreach ($pdf as $key => $value) {
        $parts[] = "'{$key}' => '{$value}'";
    }

    return '['.implode(', ', $parts).']';
}

$imports = [];
$shortNames = [];
foreach (array_keys($usedServices) as $fqcn) {
    $shortNames[$fqcn] = substr($fqcn, strrpos($fqcn, '\\') + 1);
}

// Two services share a class name, so keep generated identifiers unique.
$nameCounts = array_count_values($shortNames);
foreach ($shortNames as $fqcn => $short) {
    if ($nameCounts[$short] > 1) {
        $parts = explode('\\', $fqcn);
        $shortNames[$fqcn] = $parts[count($parts) - 2].$short;
    }
}

foreach ($shortNames as $fqcn => $short) {
    $imports[] = $short === substr($fqcn, strrpos($fqcn, '\\') + 1)
        ? 'use '.$fqcn.';'
        : 'use '.$fqcn.' as '.$short.';';
}
sort($imports);

$lines = [];
$lines[] = '<?php';
$lines[] = '';
$lines[] = 'namespace App\Support\Ascends;';
$lines[] = '';
$lines[] = implode("\n", $imports);
$lines[] = '';
$lines[] = '/**';
$lines[] = ' * Registry of shared reports published under the gsu, ru and uc route prefixes.';
$lines[] = ' *';
$lines[] = ' * Generated by scripts/ascends/generate-company-report-registry.php from the';
$lines[] = ' * shared routes then reviewed manually. Company suffixes (-gsu, -ru, -uc) are';
$lines[] = ' * stripped from slugs: the route prefix is the only company marker. Filenames';
$lines[] = ' * are static strings only when they carry no company label, and override keys';
$lines[] = ' * are lowercase company codes.';
$lines[] = ' */';
$lines[] = 'final class CompanyReportRegistry';
$lines[] = '{';
$lines[] = '    private const COMPANIES = [\'gsu\', \'ru\', \'uc\'];';
$lines[] = '';
$lines[] = '    /**';
$lines[] = '     * Short report names of the shared dynamic hrm endpoint; the single source';
$lines[] = '     * for both its whereIn list and the per-company dynamic route whitelist.';
$lines[] = '     *';
$lines[] = '     * @var list<string>';
$lines[] = '     */';
$lines[] = '    public const HRM_REPORTS = [';
foreach ($wildcardNames as $report) {
    $lines[] = "        '{$report}',";
}
$lines[] = '    ];';
$lines[] = '';
$lines[] = '    /**';
$lines[] = '     * Slugs published to only some companies; every other slug goes to all three.';
$lines[] = '     *';
$lines[] = '     * @var array<string, list<string>>';
$lines[] = '     */';
$lines[] = '    private const PUBLISHED = [';
foreach ($published as $slug => $allowed) {
    $lines[] = "        '{$slug}' => ['".implode("', '", $allowed)."'],";
}
$lines[] = '    ];';
$lines[] = '';
$lines[] = '    /** @var array<string, CompanyReportDefinition>|null */';
$lines[] = '    private static ?array $reports = null;';
$lines[] = '';
$lines[] = '    /**';
$lines[] = '     * @return array<string, CompanyReportDefinition>';
$lines[] = '     */';
$lines[] = '    public static function all(): array';
$lines[] = '    {';
$lines[] = '        return self::$reports ??= [';
foreach ($entries as $slug => $entry) {
    $base = $entry['base'];
    $lines[] = "            '{$slug}' => new CompanyReportDefinition(";
    $lines[] = "                slug: '{$slug}',";

    $serviceShort = $shortNames[$base['service']];
    $lines[] = "                service: {$serviceShort}::class,";
    $lines[] = "                view: '{$base['view']}',";

    $pdfParts = [];
    $lines[] = '                pdf: '.pdfSource($base['pdf']).',';
    $lines[] = '                filename: '.$base['filenameSource'].',';

    if ($entry['overrides'] !== []) {
        $lines[] = '                overrides: [';
        foreach ($entry['overrides'] as $company => $override) {
            $parts = [];
            if (isset($override['service'])) {
                $short = $shortNames[$override['service']];
                $parts[] = "'service' => {$short}::class";
            }
            if (isset($override['view'])) {
                $parts[] = "'view' => '{$override['view']}'";
            }
            if (isset($override['pdf'])) {
                $parts[] = "'pdf' => ".pdfSource($override['pdf']);
            }
            if (isset($override['filename'])) {
                $parts[] = "'filename' => ".$override['filename'];
            }
            $lines[] = "                    '{$company}' => [".implode(', ', $parts).'],';
        }
        $lines[] = '                ],';
    }

    $lines[] = '            ),';
}
$lines[] = '        ];';
$lines[] = '    }';
$lines[] = '';
$lines[] = '    public static function get(string $slug): ?CompanyReportDefinition';
$lines[] = '    {';
$lines[] = '        return self::all()[$slug] ?? null;';
$lines[] = '    }';
$lines[] = '';
$lines[] = '    /**';
$lines[] = '     * Company codes the slug is published to; an unknown slug is published nowhere.';
$lines[] = '     *';
$lines[] = '     * @return list<string>';
$lines[] = '     */';
$lines[] = '    public static function allowedCompanies(string $slug): array';
$lines[] = '    {';
$lines[] = '        if (! isset(self::all()[$slug])) {';
$lines[] = '            return [];';
$lines[] = '        }';
$lines[] = '';
$lines[] = '        return self::PUBLISHED[$slug] ?? self::COMPANIES;';
$lines[] = '    }';
$lines[] = '';
$lines[] = '    /**';
$lines[] = '     * @return list<string>';
$lines[] = '     */';
$lines[] = '    public static function companies(): array';
$lines[] = '    {';
$lines[] = '        return self::COMPANIES;';
$lines[] = '    }';
$lines[] = '}';
$lines[] = '';

file_put_contents($outputPath, implode("\n", $lines));

printf(
    "Generated %d report slugs from %d shared route paths (%d published to a subset, %d overrides).\n",
    count($entries),
    count($routeDefinitions),
    count($published),
    array_sum(array_map(fn (array $entry): int => count($entry['overrides']), $entries))
);

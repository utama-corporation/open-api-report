<?php

namespace App\Http\Controllers\Ascends;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateAscendsEmployeeListReportRequest;
use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerDepartemenReportService;
use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerLevelReportService;
use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerMasaKerjaReportService;
use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerUsiaGenerasiReportService;
use App\Services\Concerns\ResolvesFilterAliases;
use App\Services\PdfGenerator;
use App\Support\Ascends\CompanyReportDefinition;
use App\Support\Ascends\CompanyReportRegistry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;
use Throwable;

/**
 * Generic PDF endpoint for the gsu, ru and uc company report prefixes.
 *
 * The company is read from the route segment only. A client-supplied
 * DB_CompanyName is discarded and replaced by the route company, so the
 * printed header and filename always match the URL. The legacy
 * /internal/ascends/shared/... endpoints stay untouched.
 */
class CompanyReportController extends Controller
{
    use ResolvesFilterAliases;

    /**
     * Fixed column counts of reports whose service exposes no headers, so
     * PdfGenerator cannot infer them. Keyed by slug then lowercase company.
     *
     * @var array<string, array<string, int>>
     */
    private const PINNED_COLUMN_COUNTS = [
        'custom-report.cek-produksi' => ['gsu' => 7],
        'general_ledger.journal_details.laba-kotor' => ['ru' => 6],
    ];

    /**
     * Employee-list reports whose first page is a diagram.
     *
     * @var array<string, class-string>
     */
    private const DIAGRAM_SERVICES = [
        'hrm.karyawan-per-departemen-per-jabatan' => DiagramKaryawanPerDepartemenReportService::class,
        'hrm.karyawan-per-level' => DiagramKaryawanPerLevelReportService::class,
        'hrm.karyawan-per-masa-kerja' => DiagramKaryawanPerMasaKerjaReportService::class,
        'hrm.usia-generasi-tahun-kelahiran-masa-kerja' => DiagramKaryawanPerUsiaGenerasiReportService::class,
    ];

    /**
     * Combined diagram + list titles.
     *
     * @var array<string, string>
     */
    private const DIAGRAM_TITLES = [
        'hrm.karyawan-per-departemen-per-jabatan' => 'Laporan Karyawan Per Departemen Per Jabatan (Diagram)',
        'hrm.karyawan-per-level' => 'Laporan Karyawan Per Level (Diagram)',
        'hrm.karyawan-per-masa-kerja' => 'Laporan Karyawan Per Masa Kerja (Diagram)',
        'hrm.usia-generasi-tahun-kelahiran-masa-kerja' => 'Laporan Karyawan Berdasarkan Generasi, Usia, Tahun Kelahiran dan Masa Kerja (Diagram)',
    ];

    /**
     * Canonical filter keys mapped to the alias families the shared endpoints
     * accept. Without this the raw request never reaches services that read
     * only the canonical key, printing a wrong period without any error.
     *
     * @var array<string, list<string>>
     */
    private const CANONICAL_FILTER_ALIASES = [
        'start_date' => [
            'start_date', 'StartDate', 'TglAwal', 'tgl_awal', 'date_start', 'DateStart', 'dari_tanggal',
            'Date.StartDate', 'DateRange.StartDate', 'AdjustmentDate.StartDate',
            'AttendanceDate.StartDate', 'ItemDate.StartDate', 'ProductionDate.StartDate',
            'PurchaseDate.StartDate', 'PurchaseOrderDate.StartDate',
            'ReceiptVoucherDate.StartDate', 'ReceivableSummaryDate.StartDate',
            'PayableSummaryDate.StartDate', 'SalesDate.StartDate',
        ],
        'end_date' => [
            'end_date', 'EndDate', 'TglAkhir', 'tgl_akhir', 'date_end', 'DateEnd', 'sampai_tanggal',
            'Date.EndDate', 'DateRange.EndDate', 'AdjustmentDate.EndDate',
            'AttendanceDate.EndDate', 'ItemDate.EndDate', 'ProductionDate.EndDate',
            'PurchaseDate.EndDate', 'PurchaseOrderDate.EndDate',
            'ReceiptVoucherDate.EndDate', 'ReceivableSummaryDate.EndDate',
            'PayableSummaryDate.EndDate', 'SalesDate.EndDate',
        ],
        'per_date' => [
            'per_date', 'PerDate', 'Per Date', 'tanggal', 'Tanggal', 'date', 'Date',
            'report_date', 'ReportDate', 'Report Date',
        ],
        'PerDate' => [
            'per_date', 'PerDate', 'Per Date', 'tanggal', 'Tanggal', 'date', 'Date',
            'report_date', 'ReportDate', 'Report Date',
        ],
        'tanggal' => [
            'tanggal', 'Tanggal', 'date', 'Date', 'per_date', 'PerDate',
        ],
        'tahun' => [
            'tahun', 'Tahun', 'year', 'Year',
        ],
        'PaymentMethod' => [
            'PaymentMethod', 'PymentMethod', 'payment_method', 'payment method',
            'Pilih Payment Method', 'Pilih_x0020_Payment_x0020_Method', 'pilih_payment_method',
        ],
    ];

    public function __construct(private readonly PdfGenerator $pdfGenerator) {}

    /**
     * Resolve the registry definition, call its service with URL-derived filters,
     * then render the shared view with the same company label.
     */
    public function pdf(
        GenerateAscendsEmployeeListReportRequest $request,
        string $company,
        string $report,
    ): Response|JsonResponse {
        // The shared dynamic hrm endpoint passes the bare report name; registry keys carry the hrm. prefix.
        $definition = CompanyReportRegistry::get($report) ?? CompanyReportRegistry::get('hrm.'.$report);
        if ($definition === null) {
            abort(404);
        }

        $companyCode = strtoupper($company);

        try {
            // Client DB_CompanyName is ignored; the route prefix decides the company.
            $filters = $this->canonicalizeFilters($request->all());
            unset($filters['DB_CompanyName'], $filters['company']);
            $filters['company'] = $companyCode;
            $filters['DB_CompanyName'] = $companyCode;

            $xmlPayload = '';

            if ($definition->slug === 'hrm.custom-reports.diagram-lembur-tahunan') {
                $fileSt = $request->file('xml_file_st');
                $fileKkKt = $request->file('xml_file_kk_kt');

                if (($fileSt !== null && $fileSt->isValid()) || ($fileKkKt !== null && $fileKkKt->isValid())) {
                    // This report can carry two XML payloads instead of one.
                    $xmlSt = ($fileSt !== null && $fileSt->isValid())
                        ? file_get_contents((string) $fileSt->getRealPath())
                        : null;
                    $xmlKkKt = ($fileKkKt !== null && $fileKkKt->isValid())
                        ? file_get_contents((string) $fileKkKt->getRealPath())
                        : null;

                    $xmlSt = is_string($xmlSt) ? $xmlSt : null;
                    $xmlKkKt = is_string($xmlKkKt) ? $xmlKkKt : null;

                    $sourceLabel = 'request upload';
                    if ($fileSt !== null) {
                        $sourceLabel .= ' ST:'.$fileSt->getClientOriginalName();
                    }
                    if ($fileKkKt !== null) {
                        $sourceLabel .= ' KK/KT:'.$fileKkKt->getClientOriginalName();
                    }

                    $reportData = app($definition->serviceFor($company))->buildReportDataFromXml(
                        $xmlSt,
                        $xmlKkKt,
                        $sourceLabel,
                        $filters,
                    );
                } else {
                    // Ascend normally sends one DataSet; split it by DailyWorkerTypeCode.
                    $xmlPayload = $request->xmlPayload();
                    if ($xmlPayload === null || trim($xmlPayload) === '') {
                        throw new RuntimeException('File XML (xml_file) wajib dikirim.');
                    }

                    $reportData = app($definition->serviceFor($company))->buildReportDataFromSingleXml(
                        $xmlPayload,
                        $request->xmlSourceLabel() ?? 'request xml payload',
                        $filters,
                    );
                }
            } else {
                $xmlPayload = $request->xmlPayload();
                if ($xmlPayload === null || trim($xmlPayload) === '') {
                    throw new RuntimeException('File XML (xml_file) wajib dikirim.');
                }

                $reportData = app($definition->serviceFor($company))->buildReportDataFromXml(
                    $xmlPayload,
                    $request->xmlSourceLabel() ?? 'request xml payload',
                    $filters,
                );
            }

            $reportData['company'] = $companyCode;

            // The company is printed in the header; keep it out of the report title.
            if (isset($reportData['title'])) {
                $reportData['title'] = $this->stripCompanyFromTitle((string) $reportData['title']);
            }
            if (isset($reportData['label'])) {
                $reportData['label'] = $this->stripCompanyFromTitle((string) $reportData['label']);
            }

            $diagramService = self::DIAGRAM_SERVICES[$definition->slug] ?? null;
            if ($diagramService !== null) {
                $reportData['diagram'] = app($diagramService)->buildReportDataFromXml(
                    $xmlPayload,
                    $request->xmlSourceLabel() ?? 'request xml payload',
                    $filters,
                );
                $reportData['title'] = self::DIAGRAM_TITLES[$definition->slug] ?? $reportData['title'];
                $reportData['label'] = $reportData['title'];
            }

            if ($definition->slug === 'hrm.usia-generasi-tahun-kelahiran-masa-kerja') {
                $reportData['per_date'] = $this->perDateLabel(
                    (string) ($filters['per_date'] ?? $filters['PerDate'] ?? '')
                );
            }

            $username = trim((string) ($request->input('Sys_Username') ?? $request->input('Sys_UserName', '')));
            if ($username !== '') {
                $reportData['printed_by'] = $username;
            }
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $pdfOptions = $definition->pdfFor($company);

        $pdf = $this->pdfGenerator->render($definition->viewFor($company), [
            'company' => $companyCode,
            'reportData' => $reportData,
            'generatedAt' => now(),
            'generatedByName' => $reportData['printed_by'] ?? 'sistem',
            'pdf_format' => $pdfOptions['format'] ?? 'A4',
            'pdf_orientation' => $pdfOptions['orientation'] ?? null,
            'pdf_simple_tables' => false,
            'pdf_column_count' => $this->columnCount($definition, $reportData, $company),
        ]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$definition->filenameFor($reportData, $company).'"',
        ]);
    }

    /**
     * Fill canonical date keys from their alias families before the service
     * runs. A value the client already sent under the canonical key wins.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function canonicalizeFilters(array $filters): array
    {
        foreach (self::CANONICAL_FILTER_ALIASES as $canonical => $aliases) {
            $explicit = $filters[$canonical] ?? null;
            if (is_array($explicit) || trim((string) $explicit) !== '') {
                continue;
            }

            $value = self::resolveFilterValue($filters, $aliases);
            if ($value !== '') {
                $filters[$canonical] = $value;
            }
        }

        return $filters;
    }

    /**
     * Period label for the usia generasi report, matching the shared endpoint.
     * An empty or unparsable PerDate falls back to today instead of 500ing.
     */
    private function perDateLabel(string $value): string
    {
        try {
            $date = trim($value) !== '' ? Carbon::parse($value) : now();
        } catch (Throwable) {
            $date = now();
        }

        return $date->locale('id')->translatedFormat('d-M-y');
    }

    /**
     * Remove a company marker from a report title; the header already prints it.
     */
    private function stripCompanyFromTitle(string $title): string
    {
        $cleaned = preg_replace('/\s*\((?:GSU|RU|UC)\)\s*/i', ' ', $title) ?? $title;
        $cleaned = preg_replace('/\s+(?:GSU|RU|UC)\s*$/i', '', $cleaned) ?? $cleaned;
        $cleaned = trim(preg_replace('/\s{2,}/', ' ', $cleaned) ?? $cleaned);

        return $cleaned !== '' ? $cleaned : $title;
    }

    /**
     * @param  array<string, mixed>  $reportData
     */
    private function columnCount(CompanyReportDefinition $definition, array $reportData, string $company): int
    {
        return self::PINNED_COLUMN_COUNTS[$definition->slug][strtolower($company)]
            ?? count($reportData['headers'] ?? []);
    }
}

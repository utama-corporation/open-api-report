<?php

namespace App\Http\Controllers\Ascends;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateAscendsEmployeeListReportRequest;
use App\Services\PdfGenerator;
use App\Support\Ascends\CompanyReportDefinition;
use App\Support\Ascends\CompanyReportRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use RuntimeException;

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
            $xmlPayload = $request->xmlPayload();
            if ($xmlPayload === null || trim($xmlPayload) === '') {
                throw new RuntimeException('File XML (xml_file) wajib dikirim.');
            }

            // Client DB_CompanyName is ignored; the route prefix decides the company.
            $filters = $request->all();
            unset($filters['DB_CompanyName'], $filters['company']);
            $filters['company'] = $companyCode;
            $filters['DB_CompanyName'] = $companyCode;

            $reportData = app($definition->serviceFor($company))->buildReportDataFromXml(
                $xmlPayload,
                $request->xmlSourceLabel() ?? 'request xml payload',
                $filters,
            );

            $reportData['company'] = $companyCode;

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
     * @param  array<string, mixed>  $reportData
     */
    private function columnCount(CompanyReportDefinition $definition, array $reportData, string $company): int
    {
        return self::PINNED_COLUMN_COUNTS[$definition->slug][strtolower($company)]
            ?? count($reportData['headers'] ?? []);
    }
}

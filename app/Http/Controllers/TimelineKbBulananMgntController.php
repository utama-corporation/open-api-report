<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateTimelineKbBulananMgntReportRequest;
use App\Services\PdfGenerator;
use App\Services\TimelineKbBulananMgntReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class TimelineKbBulananMgntController extends Controller
{
    public function index(): View
    {
        return view('reports.management.timeline-kb-bulanan-mgnt-form');
    }

    public function download(
        GenerateTimelineKbBulananMgntReportRequest $request,
        TimelineKbBulananMgntReportService $reportService,
        PdfGenerator $pdfGenerator,
    ) {
        return $this->buildPdfResponse($request, $reportService, $pdfGenerator, false);
    }

    public function previewPdf(
        GenerateTimelineKbBulananMgntReportRequest $request,
        TimelineKbBulananMgntReportService $reportService,
        PdfGenerator $pdfGenerator,
    ) {
        return $this->buildPdfResponse($request, $reportService, $pdfGenerator, true);
    }

    private function buildPdfResponse(
        GenerateTimelineKbBulananMgntReportRequest $request,
        TimelineKbBulananMgntReportService $reportService,
        PdfGenerator $pdfGenerator,
        bool $attachment,
    ) {
        $generatedBy = $request->user() ?? auth('api')->user();

        if ($generatedBy === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return back()
                ->withInput()
                ->withErrors(['auth' => 'Silakan login terlebih dahulu untuk mencetak laporan.']);
        }

        [$startDate, $endDate] = $this->extractDates($request);

        try {
            $rows = $reportService->fetch($startDate, $endDate);
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['report' => $exception->getMessage()]);
        }

        $pdf = $pdfGenerator->render('reports.management.timeline-kb-bulanan-mgnt-pdf', [
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,

            // No + Supplier + (months x Qty/Rp) + Total + Min/Max/Avg.
            'pdf_column_count' => $this->resolvePdfColumnCount($startDate, $endDate),
        ]);

        $filename = sprintf('Laporan-Timeline-KB-Bulanan-Rambung-MGNT-%s-sd-%s.pdf', $startDate, $endDate);
        $dispositionType = $attachment ? 'attachment' : 'inline';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('%s; filename="%s"', $dispositionType, $filename),
        ]);
    }

    public function preview(
        GenerateTimelineKbBulananMgntReportRequest $request,
        TimelineKbBulananMgntReportService $reportService,
    ): JsonResponse {
        [$startDate, $endDate] = $this->extractDates($request);

        try {
            $rows = $reportService->fetch($startDate, $endDate);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Preview laporan berhasil diambil.',
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'TglAwal' => $startDate,
                'TglAkhir' => $endDate,
                'total_rows' => count($rows),
                'column_order' => array_keys($rows[0] ?? []),
            ],
            'data' => $rows,
        ]);
    }

    public function health(
        GenerateTimelineKbBulananMgntReportRequest $request,
        TimelineKbBulananMgntReportService $reportService,
    ): JsonResponse {
        [$startDate, $endDate] = $this->extractDates($request);

        try {
            $result = $reportService->healthCheck($startDate, $endDate);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => $result['is_healthy']
                ? 'Struktur output SP_LapTimelineKBBulananKG valid.'
                : 'Struktur output SP_LapTimelineKBBulananKG berubah.',
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'TglAwal' => $startDate,
                'TglAkhir' => $endDate,
            ],
            'health' => $result,
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function extractDates(GenerateTimelineKbBulananMgntReportRequest $request): array
    {
        return [$request->startDate(), $request->endDate()];
    }

    private function resolvePdfColumnCount(string $startDate, string $endDate): int
    {
        try {
            $startMonth = Carbon::parse($startDate)->startOfMonth();
            $endMonth = Carbon::parse($endDate)->startOfMonth();

            if ($startMonth->greaterThan($endMonth)) {
                return 30;
            }

            $monthSpan = $startMonth->diffInMonths($endMonth) + 1;

            return max(8, $monthSpan * 2 + 6);
        } catch (\Throwable $exception) {
            return 30;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateMouldingReprosesDanHasilReprosesReportRequest;
use App\Services\MouldingReprosesDanHasilReprosesReportService;
use App\Services\PdfGenerator;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class MouldingReprosesDanHasilReprosesController extends Controller
{
    public function download(
        GenerateMouldingReprosesDanHasilReprosesReportRequest $request,
        MouldingReprosesDanHasilReprosesReportService $reportService,
        PdfGenerator $pdfGenerator,
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

        $startDate = $request->startDate();
        $endDate = $request->endDate();

        try {
            $reportData = $reportService->fetch($startDate, $endDate);
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['report' => $exception->getMessage()]);
        }

        $pdf = $pdfGenerator->render('reports.moulding.moulding-reproses-dan-hasil-reproses-pdf', [
            'reportData' => $reportData,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
            'pdf_simple_tables' => false,
        ]);

        $filename = sprintf(
            'Laporan-Proses-Produksi-Moulding-Reproses-Dan-Hasil-Dari-Reproses-%s-sd-%s.pdf',
            $startDate,
            $endDate,
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
        ]);
    }

    public function preview(
        GenerateMouldingReprosesDanHasilReprosesReportRequest $request,
        MouldingReprosesDanHasilReprosesReportService $reportService,
    ): JsonResponse {
        $startDate = $request->startDate();
        $endDate = $request->endDate();

        try {
            $reportData = $reportService->fetch($startDate, $endDate);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $rows = $reportData['rows'] ?? [];

        return response()->json([
            'message' => 'Preview laporan berhasil diambil.',
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_rows' => count($rows),
                'column_order' => array_keys($rows[0] ?? []),
            ],
            'data' => $rows,
        ]);
    }

    public function health(
        GenerateMouldingReprosesDanHasilReprosesReportRequest $request,
        MouldingReprosesDanHasilReprosesReportService $reportService,
    ): JsonResponse {
        $startDate = $request->startDate();
        $endDate = $request->endDate();

        try {
            $result = $reportService->healthCheck($startDate, $endDate);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => $result['is_healthy']
                ? 'Struktur output SPWpsLaporanMouldingReprosesDanHasilReproses valid.'
                : 'Struktur output SPWpsLaporanMouldingReprosesDanHasilReproses berubah.',
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'health' => $result,
        ]);
    }
}

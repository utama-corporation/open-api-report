<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePenerimaanStUpahReportRequest;
use App\Services\PdfGenerator;
use App\Services\PenerimaanStUpahReportService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class PenerimaanStUpahController extends Controller
{
    public function preview(
        GeneratePenerimaanStUpahReportRequest $request,
        PenerimaanStUpahReportService $reportService,
    ): JsonResponse {
        $noUpah = $request->noUpah();

        try {
            $reportData = $reportService->buildReportData($noUpah);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Preview laporan berhasil diambil.',
            'meta' => [
                'no_upah' => $noUpah,
                'total_rows' => $reportData['summary']['total_rows'] ?? 0,
                'total_pcs' => $reportData['summary']['total_pcs'] ?? 0,
                'total_ton' => $reportData['summary']['total_ton'] ?? 0,
            ],
            'data' => $reportData['tebal_groups'] ?? [],
            'report_data' => $reportData,
        ]);
    }

    public function download(
        GeneratePenerimaanStUpahReportRequest $request,
        PenerimaanStUpahReportService $reportService,
        PdfGenerator $pdfGenerator,
    ) {
        $noUpah = $request->noUpah();
        $generatedBy = $request->user() ?? auth('api')->user();

        if ($generatedBy === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $reportData = $reportService->buildReportData($noUpah);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $pdf = $pdfGenerator->render('reports.sawn-timber.penerimaan-st-upah-pdf', [
            'reportData' => $reportData,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
            'pdf_orientation' => 'portrait',
            'pdf_simple_tables' => false,
            'pdf_title' => 'Laporan Penerimaan ST (Upah)',
        ]);

        $safeReceipt = preg_replace('/[^A-Za-z0-9._-]/', '-', $noUpah) ?: 'upah';
        $filename = sprintf('Laporan-Penerimaan-ST-Upah-%s.pdf', $safeReceipt);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    public function health(
        GeneratePenerimaanStUpahReportRequest $request,
        PenerimaanStUpahReportService $reportService,
    ): JsonResponse {
        $noUpah = $request->noUpah();

        try {
            $health = $reportService->healthCheck($noUpah);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => $health['is_healthy']
                ? 'Struktur sumber data laporan penerimaan ST upah valid.'
                : 'Struktur sumber data laporan penerimaan ST upah berubah atau tidak ditemukan.',
            'meta' => ['NoUpah' => $noUpah],
            'health' => $health,
        ]);
    }
}

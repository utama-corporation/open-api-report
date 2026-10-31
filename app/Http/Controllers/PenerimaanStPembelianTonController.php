<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePenerimaanStPembelianTonReportRequest;
use App\Services\PdfGenerator;
use App\Services\PenerimaanStPembelianTonReportService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class PenerimaanStPembelianTonController extends Controller
{
    public function preview(
        GeneratePenerimaanStPembelianTonReportRequest $request,
        PenerimaanStPembelianTonReportService $reportService,
    ): JsonResponse {
        $noPenerimaanST = $request->noPenerimaanST();

        try {
            $reportData = $reportService->buildReportData($noPenerimaanST);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Preview laporan berhasil diambil.',
            'meta' => [
                'no_penerimaan_st' => $noPenerimaanST,
                'total_rows' => $reportData['summary']['total_rows'] ?? 0,
                'total_pcs' => $reportData['summary']['total_pcs'] ?? 0,
                'total_ton' => $reportData['summary']['total_ton'] ?? 0,
            ],
            'data' => $reportData['tebal_groups'] ?? [],
            'report_data' => $reportData,
        ]);
    }

    public function download(
        GeneratePenerimaanStPembelianTonReportRequest $request,
        PenerimaanStPembelianTonReportService $reportService,
        PdfGenerator $pdfGenerator,
    ) {
        $noPenerimaanST = $request->noPenerimaanST();
        $generatedBy = $request->user() ?? auth('api')->user();

        if ($generatedBy === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $reportData = $reportService->buildReportData($noPenerimaanST);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $pdf = $pdfGenerator->render('reports.sawn-timber.penerimaan-st-pembelian-ton-pdf', [
            'reportData' => $reportData,
            'generatedBy' => $generatedBy,
            'generatedAt' => now(),
            'pdf_orientation' => 'portrait',
            'pdf_simple_tables' => false,
            'pdf_title' => 'Laporan Penerimaan ST - Pembelian (Ton)',
        ]);

        $safeReceipt = preg_replace('/[^A-Za-z0-9._-]/', '-', $noPenerimaanST) ?: 'receipt';
        $filename = sprintf('Laporan-Penerimaan-ST-Pembelian-Ton-%s.pdf', $safeReceipt);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    public function health(
        GeneratePenerimaanStPembelianTonReportRequest $request,
        PenerimaanStPembelianTonReportService $reportService,
    ): JsonResponse {
        $noPenerimaanST = $request->noPenerimaanST();

        try {
            $health = $reportService->healthCheck($noPenerimaanST);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => $health['is_healthy']
                ? 'Struktur sumber data laporan penerimaan ST pembelian valid.'
                : 'Struktur sumber data laporan penerimaan ST pembelian berubah atau tidak ditemukan.',
            'meta' => ['NoPenerimaanST' => $noPenerimaanST],
            'health' => $health,
        ]);
    }
}

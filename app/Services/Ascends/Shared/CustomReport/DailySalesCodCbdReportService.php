<?php

namespace App\Services\Ascends\Shared\CustomReport;

use Carbon\Carbon;
use RuntimeException;
use Throwable;
use XMLReader;

/**
 * Laporan Penjualan Harian COD & CBD.
 *
 * Satu section per Remark (COD/CBD), dengan Grand Total gabungan.
 */
class DailySalesCodCbdReportService
{
    private const TITLE = 'Laporan Penjualan Harian COD & CBD';

    private const SECTION_ORDER = ['COD', 'CBD'];

    public function buildReportDataFromXml(string $xmlContents, string $sourceLabel = 'request xml payload', array $filters = []): array
    {
        $parsed = $this->parseXml($xmlContents, $sourceLabel);

        if ($parsed['rows'] === [] && $parsed['saw_data']) {
            throw new RuntimeException('Data tidak ditemukan pada XML.');
        }

        $sections = $this->groupSections($parsed['rows']);

        return [
            'title' => self::TITLE,
            'company' => '',
            'period_label' => $this->resolvePeriodLabel($filters, $parsed['rows']),
            'sections' => $sections,
            'grand_total_total' => round((float) array_sum(array_column($parsed['rows'], 'total')), 2),
            'grand_total_disc' => round((float) array_sum(array_column($parsed['rows'], 'disc')), 2),
            'grand_total_net' => round((float) array_sum(array_column($parsed['rows'], 'net_total')), 2),
            'printed_by' => '',
        ];
    }

    /**
     * @return array{rows: list<array<string, mixed>>, saw_data: bool}
     */
    private function parseXml(string $xmlContents, string $sourceLabel): array
    {
        if (trim($xmlContents) === '') {
            throw new RuntimeException('Data XML kosong.');
        }

        $reader = new XMLReader;
        if (! @$reader->XML($xmlContents, null, LIBXML_NOCDATA | LIBXML_NONET)) {
            throw new RuntimeException("File XML tidak valid: {$sourceLabel}");
        }

        $rows = [];
        $sawData = false;

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            if ($reader->depth >= 1) {
                $sawData = true;
            }

            if ($reader->name !== 'Table') {
                continue;
            }

            $node = @simplexml_load_string($reader->readOuterXml(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($node === false) {
                continue;
            }

            $invoiceNumber = trim((string) $node->InvoiceNumber);
            if ($invoiceNumber === '') {
                continue;
            }

            $status = trim((string) $node->Status);

            $rows[] = [
                'invoice_date' => trim((string) $node->InvoiceDate),
                'invoice_number' => $invoiceNumber,
                'customer_name' => trim((string) $node->CustomerName),
                'city' => trim((string) $node->BillingCity),
                'remark' => strtoupper(trim((string) $node->Ket)),
                'payment_term' => (int) trim((string) $node->PaymentTerm),
                'age' => (int) trim((string) $node->Age),
                'total' => round((float) $node->Total, 2),
                'disc' => round((float) $node->Disc, 2),
                'net_total' => round((float) $node->NetTotal, 2),
                'status' => $status === '' || $status === '-' ? 'Belum Lunas' : $status,
                '_date' => $this->parseDate((string) $node->InvoiceDate),
            ];
        }

        $reader->close();

        return ['rows' => $rows, 'saw_data' => $sawData];
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function resolvePeriodLabel(array $filters, array $rows): string
    {
        $perDate = trim((string) ($filters['per_date'] ?? $filters['PerDate'] ?? ''));
        $date = $this->parseDate($perDate);

        if ($date !== null) {
            return 'Tanggal '.$date->locale('id')->isoFormat('DD-MMM-YY');
        }

        $dates = array_values(array_filter(array_column($rows, '_date')));
        if ($dates === []) {
            return '';
        }

        $min = min($dates);
        $max = max($dates);

        if ($min->isSameDay($max)) {
            return 'Tanggal '.$min->locale('id')->isoFormat('DD-MMM-YY');
        }

        return 'Dari '.$min->locale('id')->isoFormat('DD-MMM-YY')
            .' s/d '.$max->locale('id')->isoFormat('DD-MMM-YY');
    }

    private function groupSections(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = $row['remark'] !== '' ? $row['remark'] : 'LAINNYA';

            $groups[$key] ??= ['label' => $key, 'rows' => [], 'total' => 0.0, 'disc' => 0.0, 'net_total' => 0.0];
            $groups[$key]['rows'][] = $row;
            $groups[$key]['total'] += $row['total'];
            $groups[$key]['disc'] += $row['disc'];
            $groups[$key]['net_total'] += $row['net_total'];
        }

        uksort($groups, function (string $a, string $b): int {
            $orderA = array_search($a, self::SECTION_ORDER, true);
            $orderB = array_search($b, self::SECTION_ORDER, true);

            $orderA = $orderA === false ? count(self::SECTION_ORDER) : $orderA;
            $orderB = $orderB === false ? count(self::SECTION_ORDER) : $orderB;

            return $orderA === $orderB ? strcmp($a, $b) : $orderA <=> $orderB;
        });

        $sections = [];
        foreach ($groups as $group) {
            usort($group['rows'], function (array $a, array $b): int {
                $dateA = $a['_date']?->getTimestamp() ?? PHP_INT_MAX;
                $dateB = $b['_date']?->getTimestamp() ?? PHP_INT_MAX;

                return $dateA === $dateB
                    ? strcmp($a['invoice_number'], $b['invoice_number'])
                    : $dateA <=> $dateB;
            });

            foreach ($group['rows'] as &$row) {
                $row['invoice_date_label'] = $row['_date']?->locale('id')->isoFormat('DD-MMM-YY') ?? '';
                unset($row['_date']);
            }
            unset($row);

            $group['total'] = round($group['total'], 2);
            $group['disc'] = round($group['disc'], 2);
            $group['net_total'] = round($group['net_total'], 2);

            $sections[] = $group;
        }

        return $sections;
    }
}

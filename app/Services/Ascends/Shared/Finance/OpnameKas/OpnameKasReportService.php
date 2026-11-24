<?php

namespace App\Services\Ascends\Shared\Finance\OpnameKas;

use Carbon\Carbon;
use RuntimeException;
use Throwable;
use XMLReader;

/**
 * Laporan Opname Kas per tanggal.
 *
 * Mendukung dua dataset Ascend:
 * - <opname> (Outstanding Advance): satu baris per advance yang masih outstanding.
 * - <Account>/<Ledger> (GL.LedgerReport): mutasi ledger dengan OpeningBalance.
 *
 * Keduanya dinormalisasi ke bentuk account + baris Debit/Kredit/Saldo berjalan,
 * tanpa batas tahun, sampai tanggal laporan.
 */
class OpnameKasReportService
{
    private const TITLE = 'Laporan Opname Kas';

    public function buildReportDataFromXml(string $xmlContents, string $sourceLabel = 'request xml payload', array $filters = []): array
    {
        $parsed = $this->parseXml($xmlContents, $sourceLabel);

        if ($parsed['accounts'] === [] && $parsed['ledger'] === [] && $parsed['advances'] === [] && $parsed['saw_data']) {
            throw new RuntimeException('Data tidak ditemukan pada XML.');
        }

        $perDate = $this->resolvePerDate($filters);

        $accounts = $parsed['advances'] !== []
            ? $this->groupAdvances($this->upToDate($parsed['advances'], $perDate))
            : $this->groupLedger($parsed['accounts'], $this->upToDate($parsed['ledger'], $perDate));

        return [
            'title' => self::TITLE,
            'company' => '',
            'period_label' => 'Per Tanggal : '.$perDate->locale('id')->isoFormat('DD-MMM-YY'),
            'accounts' => $accounts,
            'grand_total_debit' => round((float) array_sum(array_column($accounts, 'total_debit')), 2),
            'grand_total_credit' => round((float) array_sum(array_column($accounts, 'total_credit')), 2),
            'grand_total_balance' => round((float) array_sum(array_column($accounts, 'ending_balance')), 2),
            'printed_by' => '',
        ];
    }

    /**
     * @return array{
     *     accounts: array<string, array{code: string, name: string, opening_balance: float}>,
     *     ledger: list<array<string, mixed>>,
     *     advances: list<array<string, mixed>>,
     *     saw_data: bool
     * }
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

        $accounts = [];
        $ledger = [];
        $advances = [];
        $sawData = false;

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            if ($reader->depth >= 1) {
                $sawData = true;
            }

            $kind = match ($reader->name) {
                'Account' => 'account',
                'Ledger' => 'ledger',
                'opname' => 'advance',
                default => null,
            };

            if ($kind === null) {
                continue;
            }

            $recordXml = $reader->readOuterXml();
            if (! is_string($recordXml) || trim($recordXml) === '') {
                continue;
            }

            $node = @simplexml_load_string($recordXml, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($node === false) {
                continue;
            }

            $fields = [];
            foreach ($node->children() as $key => $value) {
                if ($value->children()->count() > 0) {
                    continue;
                }

                $fields[$this->cleanXmlKey((string) $key)] = trim((string) $value);
            }

            if ($kind === 'account') {
                $code = $fields['AccountCode'] ?? '';
                if ($code === '') {
                    continue;
                }

                $accounts[$code] = [
                    'code' => $code,
                    'name' => $fields['AccountName'] ?? '',
                    'opening_balance' => (float) ($fields['OpeningBalance'] ?? 0),
                ];

                continue;
            }

            if ($kind === 'ledger') {
                $code = $fields['AccountCode'] ?? '';
                if ($code === '') {
                    continue;
                }

                $ledger[] = [
                    'account_code' => $code,
                    'account_name' => $fields['AccountName'] ?? '',
                    'date' => $this->parseDate($fields['VoucherDate'] ?? ''),
                    'voucher_no' => $fields['VoucherNo'] ?? '',
                    'description' => $fields['Description'] ?? '',
                    'debit' => round((float) ($fields['DebitAmount'] ?? 0), 2),
                    'credit' => round((float) ($fields['CreditAmount'] ?? 0), 2),
                ];

                continue;
            }

            $code = $fields['Advance Account Code'] ?? $fields['Customer Code'] ?? '';
            $outstanding = round((float) ($fields['Amount Outstanding (Local)'] ?? $fields['Amount Outstanding'] ?? 0), 2);

            // Advance yang sudah selesai (outstanding nol) bukan data opname.
            if ($code === '' || abs($outstanding) < 0.005) {
                continue;
            }

            // Payable memakai Supplier, receivable memakai Customer.
            $party = $fields['Supplier Name'] ?? $fields['Customer Name'] ?? '';
            $remarks = $fields['Remarks'] ?? '';

            $advances[] = [
                'account_code' => $code,
                'account_name' => $fields['Advance Account Name'] ?? $fields['Customer Name'] ?? '',
                'date' => $this->parseDate($fields['Voucher Date'] ?? ''),
                'voucher_no' => $fields['Voucher Number'] ?? '',
                'description' => $remarks !== '' ? $party.' -- '.$remarks : $party,
                'debit' => round((float) ($fields['Net Total (Local)'] ?? $fields['Net Total'] ?? 0), 2),
                'credit' => round((float) ($fields['Amount Allocated (Local)'] ?? $fields['Amount Allocated'] ?? 0), 2),
                'outstanding' => $outstanding,
            ];
        }

        $reader->close();

        return ['accounts' => $accounts, 'ledger' => $ledger, 'advances' => $advances, 'saw_data' => $sawData];
    }

    /**
     * Crystal field names encode spaces/punctuation as _xHHHH_.
     */
    private function cleanXmlKey(string $key): string
    {
        $map = [
            '_x0020_' => ' ',
            '_x0028_' => '(',
            '_x0029_' => ')',
            '_x002F_' => '/',
            '_x002E_' => '.',
            '_x002D_' => '-',
            '_x0023_' => '#',
            '_x0027_' => "'",
        ];

        return str_replace(array_keys($map), array_values($map), $key);
    }

    private function parseDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            foreach (['d/m/Y', 'd/m/y'] as $format) {
                try {
                    $date = Carbon::createFromFormat('!'.$format, $value);
                } catch (Throwable) {
                    continue;
                }

                if ($date !== false) {
                    return $date;
                }
            }

            return null;
        }
    }

    private function resolvePerDate(array $filters): Carbon
    {
        $value = trim((string) ($filters['per_date'] ?? $filters['PerDate'] ?? ''));
        $date = $this->parseDate($value);

        return $date ?? now()->startOfDay();
    }

    private function upToDate(array $rows, Carbon $perDate): array
    {
        return array_values(array_filter($rows, function (array $row) use ($perDate): bool {
            return $row['date'] === null || $row['date']->lte($perDate);
        }));
    }

    private function sortRows(array &$rows): void
    {
        usort($rows, function (array $a, array $b): int {
            $dateA = $a['date']?->getTimestamp() ?? PHP_INT_MAX;
            $dateB = $b['date']?->getTimestamp() ?? PHP_INT_MAX;

            return $dateA === $dateB
                ? strcmp($a['voucher_no'], $b['voucher_no'])
                : $dateA <=> $dateB;
        });
    }

    private function label(string $code, string $name): string
    {
        return $name !== '' ? $code.': '.$name : $code;
    }

    /**
     * Outstanding Advance: saldo berjalan = akumulasi Amount Outstanding.
     */
    private function groupAdvances(array $advances): array
    {
        $groups = [];

        foreach ($advances as $row) {
            $code = $row['account_code'];

            $groups[$code] ??= [
                'code' => $code,
                'name' => $row['account_name'],
                'opening_balance' => 0.0,
                'rows' => [],
                'total_debit' => 0.0,
                'total_credit' => 0.0,
                'ending_balance' => 0.0,
            ];

            $groups[$code]['rows'][] = $row;
        }

        uasort($groups, fn (array $a, array $b): int => strcmp($a['code'], $b['code']));

        $result = [];
        foreach ($groups as $group) {
            $this->sortRows($group['rows']);

            $balance = 0.0;
            foreach ($group['rows'] as &$row) {
                $balance += $row['outstanding'];
                $row['balance'] = round($balance, 2);
                $row['date_label'] = $row['date']?->locale('id')->isoFormat('DD-MMM-YY') ?? '';
                unset($row['date'], $row['outstanding']);

                $group['total_debit'] += $row['debit'];
                $group['total_credit'] += $row['credit'];
            }
            unset($row);

            $group['total_debit'] = round($group['total_debit'], 2);
            $group['total_credit'] = round($group['total_credit'], 2);
            $group['ending_balance'] = round($balance, 2);
            $group['label'] = $this->label($group['code'], $group['name']);

            $result[] = $group;
        }

        return $result;
    }

    /**
     * GL ledger: saldo berjalan mulai dari OpeningBalance tiap account.
     */
    private function groupLedger(array $accounts, array $ledger): array
    {
        $groups = [];

        foreach ($accounts as $code => $account) {
            $groups[$code] = [
                'code' => $code,
                'name' => $account['name'],
                'opening_balance' => round($account['opening_balance'], 2),
                'rows' => [],
                'total_debit' => 0.0,
                'total_credit' => 0.0,
                'ending_balance' => 0.0,
            ];
        }

        foreach ($ledger as $row) {
            $code = $row['account_code'];

            $groups[$code] ??= [
                'code' => $code,
                'name' => $row['account_name'],
                'opening_balance' => 0.0,
                'rows' => [],
                'total_debit' => 0.0,
                'total_credit' => 0.0,
                'ending_balance' => 0.0,
            ];

            $groups[$code]['rows'][] = $row;
        }

        uasort($groups, fn (array $a, array $b): int => strcmp($a['code'], $b['code']));

        $result = [];
        foreach ($groups as $group) {
            if ($group['rows'] === [] && $group['opening_balance'] === 0.0) {
                continue;
            }

            $this->sortRows($group['rows']);

            $balance = $group['opening_balance'];
            foreach ($group['rows'] as &$row) {
                $balance += $row['debit'] - $row['credit'];
                $row['balance'] = round($balance, 2);
                $row['date_label'] = $row['date']?->locale('id')->isoFormat('DD-MMM-YY') ?? '';
                unset($row['date']);

                $group['total_debit'] += $row['debit'];
                $group['total_credit'] += $row['credit'];
            }
            unset($row);

            $group['total_debit'] = round($group['total_debit'], 2);
            $group['total_credit'] = round($group['total_credit'], 2);
            $group['ending_balance'] = round($balance, 2);
            $group['label'] = $this->label($group['code'], $group['name']);

            $result[] = $group;
        }

        return $result;
    }
}

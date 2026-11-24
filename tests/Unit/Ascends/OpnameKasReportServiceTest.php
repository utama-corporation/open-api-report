<?php

namespace Tests\Unit\Ascends;

use App\Services\Ascends\Shared\Finance\OpnameKas\OpnameKasReportService;
use RuntimeException;
use Tests\TestCase;

class OpnameKasReportServiceTest extends TestCase
{
    public function test_builds_all_years_up_to_per_date_from_gl_ledger_dataset(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <Account>
        <AccountID>1720</AccountID>
        <AccountCode>112.200.003</AccountCode>
        <AccountName>Biaya Lain-lain dibayar dimuka</AccountName>
        <IsDebit>true</IsDebit>
        <AccountType>B</AccountType>
        <OpeningBalance>1652845.6600</OpeningBalance>
    </Account>
    <Ledger>
        <AccountCode>112.200.003</AccountCode>
        <AccountName>Biaya Lain-lain dibayar dimuka</AccountName>
        <Department />
        <Description>CENTRAL PACKING -- PEMBELIAN PAKING STIEM</Description>
        <VoucherDate>2026-11-02T00:00:00+07:00</VoucherDate>
        <VoucherNo>BK/AP/2611/0010</VoucherNo>
        <DebitAmount>750000.0000</DebitAmount>
    </Ledger>
    <Ledger>
        <AccountCode>112.200.003</AccountCode>
        <AccountName>Biaya Lain-lain dibayar dimuka</AccountName>
        <Description>CASH -- WRITE-OFF</Description>
        <VoucherDate>2026-11-03T00:00:00+07:00</VoucherDate>
        <VoucherNo>JP/AP/2611/0001</VoucherNo>
        <CreditAmount>50000.0000</CreditAmount>
    </Ledger>
    <Ledger>
        <AccountCode>112.200.003</AccountCode>
        <AccountName>Biaya Lain-lain dibayar dimuka</AccountName>
        <Description>SETELAH TANGGAL LAPORAN</Description>
        <VoucherDate>2026-11-25T00:00:00+07:00</VoucherDate>
        <VoucherNo>KK/AP/2611/9999</VoucherNo>
        <DebitAmount>999999.0000</DebitAmount>
    </Ledger>
    <Ledger>
        <AccountCode>112.200.004</AccountCode>
        <AccountName>Biaya Dimuka Lainnya</AccountName>
        <Description>AKUN TANPA OPENING BALANCE</Description>
        <VoucherDate>2025-01-10T00:00:00+07:00</VoucherDate>
        <VoucherNo>KK/AP/2501/0001</VoucherNo>
        <DebitAmount>500000.0000</DebitAmount>
    </Ledger>
</NewDataSet>
XML;

        $data = (new OpnameKasReportService)->buildReportDataFromXml($xml, 'test', ['per_date' => '2026-11-24']);

        $this->assertSame('Laporan Opname Kas', $data['title']);
        $this->assertSame('Per Tanggal : 24-Nov-26', $data['period_label']);
        $this->assertCount(2, $data['accounts']);

        $first = $data['accounts'][0];
        $this->assertSame('112.200.003', $first['code']);
        $this->assertSame('112.200.003: Biaya Lain-lain dibayar dimuka', $first['label']);
        $this->assertSame(1652845.66, $first['opening_balance']);
        $this->assertCount(2, $first['rows']);
        $this->assertSame('02-Nov-26', $first['rows'][0]['date_label']);
        $this->assertSame(750000.0, $first['rows'][0]['debit']);
        $this->assertSame(2402845.66, $first['rows'][0]['balance']);
        $this->assertSame(2352845.66, $first['rows'][1]['balance']);
        $this->assertSame(750000.0, $first['total_debit']);
        $this->assertSame(50000.0, $first['total_credit']);
        $this->assertSame(2352845.66, $first['ending_balance']);

        $second = $data['accounts'][1];
        $this->assertSame(0.0, $second['opening_balance']);
        $this->assertSame(500000.0, $second['ending_balance']);

        $this->assertSame(1250000.0, $data['grand_total_debit']);
        $this->assertSame(50000.0, $data['grand_total_credit']);
        $this->assertSame(2852845.66, $data['grand_total_balance']);
    }

    public function test_builds_outstanding_advances_from_opname_dataset(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <opname>
        <Voucher_x0020_Number>KK/AP/2503/0001</Voucher_x0020_Number>
        <Voucher_x0020_Date>2025-03-05T00:00:00+07:00</Voucher_x0020_Date>
        <Supplier_x0020_Name>SUPPLIER A</Supplier_x0020_Name>
        <Remarks>REMARK A</Remarks>
        <Net_x0020_Total_x0020__x0028_Local_x0029_>1000000.0000</Net_x0020_Total_x0020__x0028_Local_x0029_>
        <Amount_x0020_Allocated_x0020__x0028_Local_x0029_>250000.0000</Amount_x0020_Allocated_x0020__x0028_Local_x0029_>
        <Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>750000.0000</Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>
        <Advance_x0020_Account_x0020_Code>112.200.003</Advance_x0020_Account_x0020_Code>
        <Advance_x0020_Account_x0020_Name>Biaya Lain-lain dibayar dimuka</Advance_x0020_Account_x0020_Name>
    </opname>
    <opname>
        <Voucher_x0020_Number>JP/AP/2611/0030</Voucher_x0020_Number>
        <Voucher_x0020_Date>2026-11-23T00:00:00+07:00</Voucher_x0020_Date>
        <Supplier_x0020_Name>XINDING KNIVES MANUFACTURE (SHENZHEN) CO.,LTD.</Supplier_x0020_Name>
        <Remarks>PEMBELIAN CIRCULAR SAW BLADE</Remarks>
        <Net_x0020_Total_x0020__x0028_Local_x0029_>552846.0300</Net_x0020_Total_x0020__x0028_Local_x0029_>
        <Amount_x0020_Allocated_x0020__x0028_Local_x0029_>0.0000</Amount_x0020_Allocated_x0020__x0028_Local_x0029_>
        <Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>552846.0300</Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>
        <Advance_x0020_Account_x0020_Code>112.200.003</Advance_x0020_Account_x0020_Code>
        <Advance_x0020_Account_x0020_Name>Biaya Lain-lain dibayar dimuka</Advance_x0020_Account_x0020_Name>
    </opname>
    <opname>
        <Voucher_x0020_Number>JP/AP/2611/0099</Voucher_x0020_Number>
        <Voucher_x0020_Date>2026-11-20T00:00:00+07:00</Voucher_x0020_Date>
        <Supplier_x0020_Name>SUDAH SELESAI</Supplier_x0020_Name>
        <Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>0.0000</Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>
        <Advance_x0020_Account_x0020_Code>112.200.003</Advance_x0020_Account_x0020_Code>
        <Advance_x0020_Account_x0020_Name>Biaya Lain-lain dibayar dimuka</Advance_x0020_Account_x0020_Name>
    </opname>
    <opname>
        <Voucher_x0020_Number>JP/AP/2612/0001</Voucher_x0020_Number>
        <Voucher_x0020_Date>2026-12-01T00:00:00+07:00</Voucher_x0020_Date>
        <Supplier_x0020_Name>SETELAH TANGGAL LAPORAN</Supplier_x0020_Name>
        <Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>999999.0000</Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>
        <Advance_x0020_Account_x0020_Code>112.200.003</Advance_x0020_Account_x0020_Code>
        <Advance_x0020_Account_x0020_Name>Biaya Lain-lain dibayar dimuka</Advance_x0020_Account_x0020_Name>
    </opname>
    <opname>
        <Voucher_x0020_Number>BK/AP/2611/0104</Voucher_x0020_Number>
        <Voucher_x0020_Date>2026-11-24T00:00:00+07:00</Voucher_x0020_Date>
        <Supplier_x0020_Name>SAMUEL.</Supplier_x0020_Name>
        <Net_x0020_Total_x0020__x0028_Local_x0029_>3000000.0000</Net_x0020_Total_x0020__x0028_Local_x0029_>
        <Amount_x0020_Allocated_x0020__x0028_Local_x0029_>0.0000</Amount_x0020_Allocated_x0020__x0028_Local_x0029_>
        <Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>3000000.0000</Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>
        <Advance_x0020_Account_x0020_Code>111.300.206</Advance_x0020_Account_x0020_Code>
        <Advance_x0020_Account_x0020_Name>PUM-PIHAK KETIGA</Advance_x0020_Account_x0020_Name>
    </opname>
</NewDataSet>
XML;

        $data = (new OpnameKasReportService)->buildReportDataFromXml($xml, 'test', ['per_date' => '2026-11-24']);

        $this->assertSame('Per Tanggal : 24-Nov-26', $data['period_label']);
        $this->assertCount(2, $data['accounts']);

        $first = $data['accounts'][0];
        $this->assertSame('111.300.206', $first['code']);
        $this->assertSame('111.300.206: PUM-PIHAK KETIGA', $first['label']);
        $this->assertSame(3000000.0, $first['ending_balance']);

        $second = $data['accounts'][1];
        $this->assertSame('112.200.003', $second['code']);
        $this->assertSame('112.200.003: Biaya Lain-lain dibayar dimuka', $second['label']);
        $this->assertCount(2, $second['rows']);
        $this->assertSame('05-Mar-25', $second['rows'][0]['date_label']);
        $this->assertSame('SUPPLIER A -- REMARK A', $second['rows'][0]['description']);
        $this->assertSame(750000.0, $second['rows'][0]['balance']);
        $this->assertSame(1302846.03, $second['rows'][1]['balance']);
        $this->assertSame(1552846.03, $second['total_debit']);
        $this->assertSame(250000.0, $second['total_credit']);
        $this->assertSame(1302846.03, $second['ending_balance']);

        $this->assertSame(4552846.03, $data['grand_total_debit']);
        $this->assertSame(250000.0, $data['grand_total_credit']);
        $this->assertSame(4302846.03, $data['grand_total_balance']);
    }

    public function test_builds_outstanding_receivable_advances_from_customer_dataset(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <opname>
        <Voucher_x0020_Number>BM/ARV/2610/0015</Voucher_x0020_Number>
        <Voucher_x0020_Date>2026-10-08T00:00:00+07:00</Voucher_x0020_Date>
        <Customer_x0020_Code>3.1.2.0.1.D004</Customer_x0020_Code>
        <Customer_x0020_Name>DURAMEN BOIS ET SCIAGE</Customer_x0020_Name>
        <Remarks>PENERIMAAN DP KONTRAK 04</Remarks>
        <Net_x0020_Total_x0020__x0028_Local_x0029_>344554665.5000</Net_x0020_Total_x0020__x0028_Local_x0029_>
        <Amount_x0020_Allocated_x0020__x0028_Local_x0029_>0.0000</Amount_x0020_Allocated_x0020__x0028_Local_x0029_>
        <Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>344554665.5000</Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>
    </opname>
    <opname>
        <Voucher_x0020_Number>BM/ARV/2611/0054</Voucher_x0020_Number>
        <Voucher_x0020_Date>2026-11-21T00:00:00+07:00</Voucher_x0020_Date>
        <Customer_x0020_Code>3.1.2.0.1.M004</Customer_x0020_Code>
        <Customer_x0020_Name>MINDO JAYA</Customer_x0020_Name>
        <Remarks>PENERIMAAN UANG MUKA PENJUALAN</Remarks>
        <Net_x0020_Total_x0020__x0028_Local_x0029_>3833000.0000</Net_x0020_Total_x0020__x0028_Local_x0029_>
        <Amount_x0020_Allocated_x0020__x0028_Local_x0029_>800000.0000</Amount_x0020_Allocated_x0020__x0028_Local_x0029_>
        <Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>3033000.0000</Amount_x0020_Outstanding_x0020__x0028_Local_x0029_>
    </opname>
</NewDataSet>
XML;

        $data = (new OpnameKasReportService)->buildReportDataFromXml($xml, 'test', ['per_date' => '2026-11-24']);

        $this->assertCount(2, $data['accounts']);

        $first = $data['accounts'][0];
        $this->assertSame('3.1.2.0.1.D004', $first['code']);
        $this->assertSame('3.1.2.0.1.D004: DURAMEN BOIS ET SCIAGE', $first['label']);
        $this->assertSame('DURAMEN BOIS ET SCIAGE -- PENERIMAAN DP KONTRAK 04', $first['rows'][0]['description']);
        $this->assertSame(344554665.5, $first['ending_balance']);

        $second = $data['accounts'][1];
        $this->assertSame('3.1.2.0.1.M004', $second['code']);
        $this->assertSame(3833000.0, $second['total_debit']);
        $this->assertSame(800000.0, $second['total_credit']);
        $this->assertSame(3033000.0, $second['ending_balance']);

        $this->assertSame(348387665.5, $data['grand_total_debit']);
        $this->assertSame(800000.0, $data['grand_total_credit']);
        $this->assertSame(347587665.5, $data['grand_total_balance']);
    }

    public function test_renders_empty_report_for_empty_dataset(): void
    {
        $data = (new OpnameKasReportService)->buildReportDataFromXml(
            '<NewDataSet />',
            'test',
            ['per_date' => '2026-11-24']
        );

        $this->assertSame([], $data['accounts']);
        $this->assertSame(0.0, $data['grand_total_balance']);
        $this->assertSame('Per Tanggal : 24-Nov-26', $data['period_label']);
    }

    public function test_rejects_xml_without_known_records(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data tidak ditemukan pada XML.');

        (new OpnameKasReportService)->buildReportDataFromXml(
            '<NewDataSet><lain><Nilai>1</Nilai></lain></NewDataSet>',
            'test'
        );
    }

    public function test_rejects_empty_xml(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data XML kosong.');

        (new OpnameKasReportService)->buildReportDataFromXml('   ');
    }
}

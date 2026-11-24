<?php

namespace Tests\Feature\Ascends;

use Tests\TestCase;

class OpnameKasEndpointTest extends TestCase
{
    private const URL = '/api/internal/ascends/ru/finance/outstanding_advance_payable/opname-kas/pdf';

    private const RECEIVABLE_URL = '/api/internal/ascends/ru/finance/outstanding_advance_receivable/opname-kas/pdf';

    public function test_renders_pdf_from_gl_ledger_xml(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <Account>
        <AccountCode>112.200.003</AccountCode>
        <AccountName>Biaya Lain-lain dibayar dimuka</AccountName>
        <OpeningBalance>1652845.6600</OpeningBalance>
    </Account>
    <Ledger>
        <AccountCode>112.200.003</AccountCode>
        <AccountName>Biaya Lain-lain dibayar dimuka</AccountName>
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
</NewDataSet>
XML;

        $response = $this->postJson(self::URL, [
            'xml' => $xml,
            'PerDate' => '2026-11-24',
            'Sys_Username' => 'Ridho',
        ]);

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertPdfDisposition($response, 'attachment', 'Laporan Opname Kas (RU).pdf');
    }

    public function test_rejects_request_without_xml(): void
    {
        $this->postJson(self::URL, ['PerDate' => '2026-11-24'])
            ->assertStatus(422)
            ->assertJson(['message' => 'File XML (xml_file) wajib dikirim.']);
    }

    public function test_renders_empty_pdf_for_empty_dataset(): void
    {
        $response = $this->postJson(self::RECEIVABLE_URL, [
            'xml' => '<?xml version="1.0" encoding="utf-8"?><NewDataSet />',
            'PerDate' => '2026-11-24',
        ]);

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_renders_receivable_pdf_from_customer_dataset(): void
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
</NewDataSet>
XML;

        $response = $this->postJson(self::RECEIVABLE_URL, [
            'xml' => $xml,
            'PerDate' => '2026-11-24',
            'Sys_Username' => 'Ridho',
        ]);

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertPdfDisposition($response, 'attachment', 'Laporan Opname Kas (RU).pdf');
    }
}

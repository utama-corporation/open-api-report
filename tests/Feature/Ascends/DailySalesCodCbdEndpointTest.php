<?php

namespace Tests\Feature\Ascends;

use Tests\TestCase;

class DailySalesCodCbdEndpointTest extends TestCase
{
    private const URL = '/api/internal/ascends/ru/custom-report/laporan-penjualan-harian-cod-cbd/pdf';

    public function test_renders_pdf_from_daily_sales_xml(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <Table>
        <InvoiceDate>2026-11-26T00:00:00+07:00</InvoiceDate>
        <InvoiceNumber>SI/11/26/3251</InvoiceNumber>
        <CustomerName>PARNA PERABOT</CustomerName>
        <BillingCity>MEDAN</BillingCity>
        <PaymentTerm>14</PaymentTerm>
        <Total>450300.0000</Total>
        <Disc>0.0000</Disc>
        <NetTotal>450300.0000</NetTotal>
        <Ket>COD</Ket>
        <Status>-</Status>
        <Age>0</Age>
    </Table>
</NewDataSet>
XML;

        $response = $this->postJson(self::URL, [
            'xml' => $xml,
            'PerDate' => '2026-11-26',
            'Sys_Username' => 'Ridho',
        ]);

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertPdfDisposition($response, 'attachment', 'Laporan Penjualan Harian COD & CBD (RU).pdf');
    }

    public function test_renders_empty_pdf_for_empty_dataset(): void
    {
        $response = $this->postJson(self::URL, [
            'xml' => '<?xml version="1.0" encoding="utf-8"?><NewDataSet />',
            'PerDate' => '2026-11-26',
        ]);

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_rejects_request_without_xml(): void
    {
        $this->postJson(self::URL, ['PerDate' => '2026-11-26'])
            ->assertStatus(422)
            ->assertJson(['message' => 'File XML (xml_file) wajib dikirim.']);
    }
}

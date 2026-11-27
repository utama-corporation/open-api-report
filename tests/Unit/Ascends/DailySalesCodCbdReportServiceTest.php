<?php

namespace Tests\Unit\Ascends;

use App\Services\Ascends\Shared\CustomReport\DailySalesCodCbdReportService;
use RuntimeException;
use Tests\TestCase;

class DailySalesCodCbdReportServiceTest extends TestCase
{
    public function test_groups_cod_and_cbd_with_grand_total(): void
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
    <Table>
        <InvoiceDate>2026-11-26T00:00:00+07:00</InvoiceDate>
        <InvoiceNumber>SI/11/26/3312</InvoiceNumber>
        <CustomerName>CIPTA JAYA</CustomerName>
        <BillingCity>MEDAN</BillingCity>
        <PaymentTerm>0</PaymentTerm>
        <Total>3041900.0000</Total>
        <Disc>41900.0000</Disc>
        <NetTotal>3000000.0000</NetTotal>
        <Ket>CBD</Ket>
        <Status>-</Status>
        <Age>0</Age>
    </Table>
    <Table>
        <InvoiceDate>2026-11-26T00:00:00+07:00</InvoiceDate>
        <InvoiceNumber>SI/11/26/3330</InvoiceNumber>
        <CustomerName>WULAN PECAH BELAH</CustomerName>
        <BillingCity>DELI SERDANG</BillingCity>
        <PaymentTerm>14</PaymentTerm>
        <Total>2781600.0000</Total>
        <Disc>0.0000</Disc>
        <NetTotal>2781600.0000</NetTotal>
        <Ket>COD</Ket>
        <Status>Lunas</Status>
        <Age>0</Age>
    </Table>
</NewDataSet>
XML;

        $data = (new DailySalesCodCbdReportService)->buildReportDataFromXml($xml, 'test', ['per_date' => '2026-11-26']);

        $this->assertSame('Laporan Penjualan Harian COD & CBD', $data['title']);
        $this->assertSame('Tanggal 26-Nov-26', $data['period_label']);
        $this->assertCount(2, $data['sections']);

        $cod = $data['sections'][0];
        $this->assertSame('COD', $cod['label']);
        $this->assertCount(2, $cod['rows']);
        $this->assertSame('26-Nov-26', $cod['rows'][0]['invoice_date_label']);
        $this->assertSame('Belum Lunas', $cod['rows'][0]['status']);
        $this->assertSame('Lunas', $cod['rows'][1]['status']);
        $this->assertSame(3231900.0, $cod['total']);
        $this->assertSame(3231900.0, $cod['net_total']);

        $cbd = $data['sections'][1];
        $this->assertSame('CBD', $cbd['label']);
        $this->assertSame(41900.0, $cbd['disc']);

        $this->assertSame(6273800.0, $data['grand_total_total']);
        $this->assertSame(41900.0, $data['grand_total_disc']);
        $this->assertSame(6231900.0, $data['grand_total_net']);
    }

    public function test_derives_period_from_invoice_dates_when_per_date_is_absent(): void
    {
        $xml = '<NewDataSet><Table><InvoiceDate>2026-11-26T00:00:00+07:00</InvoiceDate><InvoiceNumber>SI/1</InvoiceNumber><Total>1000</Total><NetTotal>1000</NetTotal><Ket>COD</Ket></Table></NewDataSet>';

        $data = (new DailySalesCodCbdReportService)->buildReportDataFromXml($xml, 'test');

        $this->assertSame('Tanggal 26-Nov-26', $data['period_label']);
    }

    public function test_renders_empty_report_for_empty_dataset(): void
    {
        $data = (new DailySalesCodCbdReportService)->buildReportDataFromXml('<NewDataSet />', 'test', ['per_date' => '2026-11-26']);

        $this->assertSame([], $data['sections']);
        $this->assertSame(0.0, $data['grand_total_net']);
        $this->assertSame('Tanggal 26-Nov-26', $data['period_label']);
    }

    public function test_rejects_xml_without_known_records(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data tidak ditemukan pada XML.');

        (new DailySalesCodCbdReportService)->buildReportDataFromXml(
            '<NewDataSet><lain><Nilai>1</Nilai></lain></NewDataSet>',
            'test'
        );
    }

    public function test_rejects_empty_xml(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data XML kosong.');

        (new DailySalesCodCbdReportService)->buildReportDataFromXml('   ');
    }
}

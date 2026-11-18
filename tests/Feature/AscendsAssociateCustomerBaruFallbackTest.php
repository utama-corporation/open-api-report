<?php

namespace Tests\Feature;

use App\Services\Ascends\Shared\Associate\CustomerBaruPerTahunReportService;
use App\Services\Ascends\Shared\Associate\CustomerBaruReportService;
use Carbon\Carbon;
use Tests\TestCase;

class AscendsAssociateCustomerBaruFallbackTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_customer_baru_defaults_to_current_month_when_tanggal_is_missing(): void
    {
        Carbon::setTestNow('2026-11-18 10:00:00');

        $reportData = app(CustomerBaruReportService::class)
            ->buildReportDataFromXml($this->customerXml(), 'test xml');

        $this->assertSame(1, $reportData['total_rows']);
        $this->assertSame('Dari 01-Nov-26 s/d 30-Nov-26', $reportData['per_date']);
    }

    public function test_customer_baru_per_tahun_defaults_to_current_year_when_tahun_is_missing(): void
    {
        Carbon::setTestNow('2026-11-18 10:00:00');

        $reportData = app(CustomerBaruPerTahunReportService::class)
            ->buildReportDataFromXml($this->customerXml(), 'test xml');

        $this->assertSame(1, $reportData['total_rows']);
    }

    private function customerXml(): string
    {
        return <<<'XML'
<NewDataSet>
    <tabel>
        <Customer_x0020_Code>C001</Customer_x0020_Code>
        <Customer_x0020_Name>Customer Baru</Customer_x0020_Name>
        <Billing_x0020_Address_x0020_1>Jl. Contoh</Billing_x0020_Address_x0020_1>
        <Billing_x0020_City>Medan</Billing_x0020_City>
        <Sales_x0020_Person_x0020_Code>SP-0001</Sales_x0020_Person_x0020_Code>
        <Status>Active</Status>
        <Created_x0020_Date_x002F_Time>2026-11-11T10:00:00+07:00</Created_x0020_Date_x002F_Time>
    </tabel>
</NewDataSet>
XML;
    }
}

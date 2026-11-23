<?php

namespace Tests\Feature;

use App\Services\Ascends\Shared\FixedAsset\AssetSummary\PenyusutanAktivaReportService;
use Tests\TestCase;

class AscendsPenyusutanAktivaReportTest extends TestCase
{
    public function test_period_subtitle_uses_indonesian_short_date_format(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <dd>
        <Asset_x0020_Code>A-001</Asset_x0020_Code>
        <Asset_x0020_Name>Test Asset</Asset_x0020_Name>
        <Asset_x0020_Category>KATEGORI TEST</Asset_x0020_Category>
        <Acquisition_x0020_Date>2020-01-15T00:00:00+07:00</Acquisition_x0020_Date>
        <Acquisition_x0020_Cost>1000000</Acquisition_x0020_Cost>
        <Depreciation>10000</Depreciation>
        <Ending_x0020_Value>900000</Ending_x0020_Value>
    </dd>
</NewDataSet>
XML;

        $reportData = app(PenyusutanAktivaReportService::class)->buildReportDataFromXml($xml, 'test xml', [
            'Date.StartDate' => '2026-05-01',
            'Date.EndDate' => '2026-05-31',
        ]);

        $this->assertSame('Dari 01-Mei-26 s/d 31-Mei-26', $reportData['period_label']);
    }
}

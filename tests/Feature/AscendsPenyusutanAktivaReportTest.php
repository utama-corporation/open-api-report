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

    public function test_period_subtitle_is_derived_from_the_xml_when_no_date_filter_is_sent(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <dd>
        <Asset_x0020_Code>A-001</Asset_x0020_Code>
        <Asset_x0020_Name>Test Asset 1</Asset_x0020_Name>
        <Asset_x0020_Category>KATEGORI TEST</Asset_x0020_Category>
        <Usage_x0020_Date>2024-01-29T00:00:00+07:00</Usage_x0020_Date>
        <Acquisition_x0020_Cost>1000000</Acquisition_x0020_Cost>
        <Age_x0020__x0028_Months_x0029_>48</Age_x0020__x0028_Months_x0029_>
        <Months_x0020_Left>34</Months_x0020_Left>
    </dd>
    <dd>
        <Asset_x0020_Code>A-002</Asset_x0020_Code>
        <Asset_x0020_Name>Test Asset 2</Asset_x0020_Name>
        <Asset_x0020_Category>KATEGORI TEST</Asset_x0020_Category>
        <Usage_x0020_Date>2024-09-01T00:00:00+07:00</Usage_x0020_Date>
        <Acquisition_x0020_Cost>2000000</Acquisition_x0020_Cost>
        <Age_x0020__x0028_Months_x0029_>48</Age_x0020__x0028_Months_x0029_>
        <Months_x0020_Left>28</Months_x0020_Left>
    </dd>
</NewDataSet>
XML;

        $reportData = app(PenyusutanAktivaReportService::class)->buildReportDataFromXml($xml, 'test xml');

        $this->assertSame('Dari 01-Mei-26 s/d 31-Mei-26', $reportData['period_label']);
    }
}

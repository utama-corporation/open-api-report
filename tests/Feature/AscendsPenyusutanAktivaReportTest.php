<?php

namespace Tests\Feature;

use App\Services\Ascends\Shared\FixedAsset\AssetSummary\PenyusutanAktivaReportService;
use Carbon\Carbon;
use Tests\TestCase;

class AscendsPenyusutanAktivaReportTest extends TestCase
{
    private const XML = <<<'XML'
<NewDataSet>
    <dd>
        <Asset_x0020_Code>A-001</Asset_x0020_Code>
        <Asset_x0020_Name>Test Asset</Asset_x0020_Name>
        <Asset_x0020_Category>KATEGORI TEST</Asset_x0020_Category>
        <Usage_x0020_Date>2024-01-29T00:00:00+07:00</Usage_x0020_Date>
        <Acquisition_x0020_Cost>1000000</Acquisition_x0020_Cost>
        <Age_x0020__x0028_Months_x0029_>48</Age_x0020__x0028_Months_x0029_>
        <Months_x0020_Left>14</Months_x0020_Left>
    </dd>
</NewDataSet>
XML;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-23 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_period_subtitle_uses_indonesian_short_date_format(): void
    {
        $reportData = $this->buildReportData([
            'Date.StartDate' => '2026-05-01',
            'Date.EndDate' => '2026-05-31',
        ]);

        $this->assertSame('Dari 01-Mei-26 s/d 31-Mei-26', $reportData['period_label']);
    }

    public function test_period_subtitle_uses_exact_custom_dates_from_the_request(): void
    {
        $reportData = $this->buildReportData([
            'Date.StartDate' => '2026-11-05',
            'Date.EndDate' => '2026-11-18',
        ]);

        $this->assertSame('Dari 05-Nov-26 s/d 18-Nov-26', $reportData['period_label']);
    }

    public function test_period_subtitle_falls_back_to_per_tanggal(): void
    {
        $reportData = $this->buildReportData();

        $this->assertSame('Per Tanggal : 23-Nov-26', $reportData['period_label']);
    }

    /**
     * @param  array<string, string>  $filters
     * @return array<string, mixed>
     */
    private function buildReportData(array $filters = []): array
    {
        return app(PenyusutanAktivaReportService::class)
            ->buildReportDataFromXml(self::XML, 'test xml', $filters);
    }
}

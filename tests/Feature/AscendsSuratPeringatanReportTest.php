<?php

namespace Tests\Feature;

use App\Services\Ascends\Shared\Hrm\SuratPeringatanReportService;
use Tests\TestCase;

class AscendsSuratPeringatanReportTest extends TestCase
{
    public function test_parses_rows_with_any_ascend_record_tag(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <WarningNotice>
        <Full_x0020_Name>Budi</Full_x0020_Name>
        <Sex>Male</Sex>
        <Job_x0020_Title>Staff</Job_x0020_Title>
        <Daily_x0020_Worker_x0020_Type_x0020_Code>ST</Daily_x0020_Worker_x0020_Type_x0020_Code>
        <Warning_x0020_Level>SP 1</Warning_x0020_Level>
        <Date>2026-01-10T00:00:00+07:00</Date>
        <Department_x0020_Name>HRGA</Department_x0020_Name>
        <Department_x0020_Code>1101</Department_x0020_Code>
        <Level>2</Level>
    </WarningNotice>
</NewDataSet>
XML;

        $reportData = app(SuratPeringatanReportService::class)
            ->buildReportDataFromXml($xml, 'test xml');

        $this->assertSame(1, $reportData['total_rows']);
        $this->assertSame('Budi', $reportData['rows'][0]['Nama']);
        $this->assertSame('SP 1', $reportData['rows'][0]['SP']);
    }
}

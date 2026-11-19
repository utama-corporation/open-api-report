<?php

namespace Tests\Feature;

use App\Services\Ascends\Shared\Hrm\CustomReports\DiagramLemburTahunanReportService;
use Tests\TestCase;

class AscendsDiagramLemburTahunanReportTest extends TestCase
{
    public function test_single_xml_with_encoded_columns_is_split_by_employee_type(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <Table1>
        <Employee_x0020_ID>1</Employee_x0020_ID>
        <Employee_x0020_Code>E001</Employee_x0020_Code>
        <Date>2026-04-15T00:00:00+07:00</Date>
        <Department_x0020_Name>HRGA</Department_x0020_Name>
        <Daily_x0020_Worker_x0020_Type_x0020_Code>ST</Daily_x0020_Worker_x0020_Type_x0020_Code>
        <Actual_x0020_Hours>2</Actual_x0020_Hours>
        <Total_x0020_Cost>100000</Total_x0020_Cost>
    </Table1>
    <Table1>
        <Employee_x0020_ID>2</Employee_x0020_ID>
        <Employee_x0020_Code>E002</Employee_x0020_Code>
        <Date>2026-05-15T00:00:00+07:00</Date>
        <Department_x0020_Name>HRGA</Department_x0020_Name>
        <Daily_x0020_Worker_x0020_Type_x0020_Code>KK</Daily_x0020_Worker_x0020_Type_x0020_Code>
        <Actual_x0020_Hours>4</Actual_x0020_Hours>
        <Total_x0020_Cost>200000</Total_x0020_Cost>
    </Table1>
</NewDataSet>
XML;

        $reportData = app(DiagramLemburTahunanReportService::class)
            ->buildReportDataFromSingleXml($xml, 'test xml');

        $this->assertTrue($reportData['has_st']);
        $this->assertTrue($reportData['has_kk_kt']);
        $this->assertSame('Dari 15-Apr-26 s/d 15-Mei-26', $reportData['subtitle']);
        $this->assertSame('HRGA', $reportData['cost_table'][0]['department']);
    }
}

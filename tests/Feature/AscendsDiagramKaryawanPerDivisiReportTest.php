<?php

namespace Tests\Feature;

use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerDivisiReportService;
use Tests\TestCase;

class AscendsDiagramKaryawanPerDivisiReportTest extends TestCase
{
    public function test_groups_employees_by_department_as_divisi(): void
    {
        $reportData = app(DiagramKaryawanPerDivisiReportService::class)
            ->buildReportDataFromXml($this->employeeXml(), 'test xml');

        $this->assertSame(2, $reportData['total']);
        $this->assertSame([
            ['Kode' => 'D1', 'Divisi' => 'Produksi', 'Jumlah' => 1, '%' => '50.0%'],
            ['Kode' => 'D2', 'Divisi' => 'Sales', 'Jumlah' => 1, '%' => '50.0%'],
        ], $reportData['rows']);
    }

    public function test_falls_back_to_sub_department_when_department_is_absent(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <Employees>
        <Employee_x0020_Code>1</Employee_x0020_Code>
        <Sub-Department_x0020_Code>S1</Sub-Department_x0020_Code>
        <Sub-Department_x0020_Name>Rotary</Sub-Department_x0020_Name>
        <Active>Active</Active>
    </Employees>
</NewDataSet>
XML;

        $reportData = app(DiagramKaryawanPerDivisiReportService::class)
            ->buildReportDataFromXml($xml, 'test xml');

        $this->assertSame('S1', $reportData['rows'][0]['Kode']);
        $this->assertSame('Rotary', $reportData['rows'][0]['Divisi']);
    }

    private function employeeXml(): string
    {
        return <<<'XML'
<NewDataSet>
    <Employees>
        <Employee_x0020_Code>1</Employee_x0020_Code>
        <Department_x0020_Code>D1</Department_x0020_Code>
        <Department_x0020_Name>Produksi</Department_x0020_Name>
        <Active>Active</Active>
    </Employees>
    <Employees>
        <Employee_x0020_Code>2</Employee_x0020_Code>
        <Department_x0020_Code>D2</Department_x0020_Code>
        <Department_x0020_Name>Sales</Department_x0020_Name>
        <Active>Active</Active>
    </Employees>
</NewDataSet>
XML;
    }
}

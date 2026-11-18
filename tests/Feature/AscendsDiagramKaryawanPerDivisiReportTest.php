<?php

namespace Tests\Feature;

use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerDivisiReportService;
use Tests\TestCase;

class AscendsDiagramKaryawanPerDivisiReportTest extends TestCase
{
    public function test_groups_employees_by_division_columns(): void
    {
        $reportData = app(DiagramKaryawanPerDivisiReportService::class)
            ->buildReportDataFromXml($this->divisionXml(), 'test xml');

        $this->assertSame(2, $reportData['total']);
        $this->assertSame([
            ['Kode' => '13101', 'Divisi' => 'PHU', 'Jumlah' => 1, '%' => '50.0%'],
            ['Kode' => '13081', 'Divisi' => 'Band Saw', 'Jumlah' => 1, '%' => '50.0%'],
        ], $reportData['rows']);
    }

    public function test_falls_back_to_department_then_sub_department(): void
    {
        $reportData = app(DiagramKaryawanPerDivisiReportService::class)
            ->buildReportDataFromXml($this->departmentXml(), 'test xml');

        $this->assertSame('D1', $reportData['rows'][0]['Kode']);
        $this->assertSame('Produksi', $reportData['rows'][0]['Divisi']);

        $subDepartment = app(DiagramKaryawanPerDivisiReportService::class)
            ->buildReportDataFromXml($this->subDepartmentXml(), 'test xml');

        $this->assertSame('S1', $subDepartment['rows'][0]['Kode']);
        $this->assertSame('Rotary', $subDepartment['rows'][0]['Divisi']);
    }

    public function test_empty_division_is_labelled_tanpa_divisi(): void
    {
        $xml = <<<'XML'
<NewDataSet>
    <Employees>
        <Employee_x0020_Code>1</Employee_x0020_Code>
        <Active>Active</Active>
    </Employees>
</NewDataSet>
XML;

        $reportData = app(DiagramKaryawanPerDivisiReportService::class)
            ->buildReportDataFromXml($xml, 'test xml');

        $this->assertSame('Tanpa Divisi', $reportData['rows'][0]['Divisi']);
    }

    private function divisionXml(): string
    {
        return <<<'XML'
<NewDataSet>
    <Employees>
        <Employee_x0020_Code>1</Employee_x0020_Code>
        <Division_x0020_Code>13101</Division_x0020_Code>
        <Division_x0020_Name>PHU</Division_x0020_Name>
        <Active>Active</Active>
    </Employees>
    <Employees>
        <Employee_x0020_Code>2</Employee_x0020_Code>
        <Division_x0020_Code>13081</Division_x0020_Code>
        <Division_x0020_Name>Band Saw</Division_x0020_Name>
        <Active>Active</Active>
    </Employees>
</NewDataSet>
XML;
    }

    private function departmentXml(): string
    {
        return <<<'XML'
<NewDataSet>
    <Employees>
        <Employee_x0020_Code>1</Employee_x0020_Code>
        <Department_x0020_Code>D1</Department_x0020_Code>
        <Department_x0020_Name>Produksi</Department_x0020_Name>
        <Active>Active</Active>
    </Employees>
</NewDataSet>
XML;
    }

    private function subDepartmentXml(): string
    {
        return <<<'XML'
<NewDataSet>
    <Employees>
        <Employee_x0020_Code>1</Employee_x0020_Code>
        <Sub-Department_x0020_Code>S1</Sub-Department_x0020_Code>
        <Sub-Department_x0020_Name>Rotary</Sub-Department_x0020_Name>
        <Active>Active</Active>
    </Employees>
</NewDataSet>
XML;
    }
}

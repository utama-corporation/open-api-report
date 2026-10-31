# Laporan Penerimaan ST - Pembelian (Ton) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an authenticated Laravel report API that queries one ST purchase receipt directly from SQL Server and renders the reference report using this project's PDF styling.

**Architecture:** Add a report service for parameterized table queries, dimension grouping, and ton calculation; a FormRequest and controller expose preview/PDF/health endpoints; a Blade view formats the report through `PdfGenerator`. Register it in the Sawn Timber route definitions and describe its request in the dynamically augmented OpenAPI contract.

**Tech Stack:** Laravel 12, PHP 8.2+, SQL Server, Blade, mPDF via `App\Services\PdfGenerator`, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-10-30-penerimaan-st-pembelian-ton-design.md`

## Global Constraints

- Use direct SQL table queries; no stored procedure and bind `NoPenerimaanST` as a query parameter.
- Require `NoPenerimaanST` as a string with maximum length 13.
- Group displayed pcs by thickness, width, unit, and length.
- Apply the supplied ton formula and floor each raw `ST_d` detail row to four decimal places before summing size-row and grand totals.
- Include `PenerimaanSTPembelian_Reject` rows as a separate returned-material table, with UOM labels from `MstUOM`; omit the PDF table when there are no return rows.
- Render PDFs only through `PdfGenerator`; follow current Sawn Timber PDF typography, alternating fills, borders, and shared footer.
- Format PDF dates with `->locale('id')->isoFormat('DD-MMM-YY')`.
- Extend `BaseReportRequest`; do not override `failedValidation()`.
- Register preview, PDF, and health routes in the existing Sawn Timber API route definitions and keep JWT middleware.
- Do not add a web form, CRUD routes, migrations, stored procedures, or changes to another project.

## Review Focus

- Unknown receipt number: return the nearby report's validation/error response, not a PDF with fabricated zero totals. Test in Task 2.
- Oversized or SQL-special receipt input: enforce the 13-character request limit and keep SQL values bound. Test in Tasks 1 and 2.
- Repeated dimensions across ST labels: sum pcs for display but floor ton per raw detail row before summing; test the reference's 31×38 total of 138 pcs/0.1332 ton and 44×55 total of 780 pcs/1.5972 ton in Task 1.
- Width UOM `1` versus `3`: exercise both calculation branches in Task 1.
- Additional/non-standard lengths: retain them in numeric order without dropping their pcs; test in Task 1.
- Missing optional header values or no accepted detail rows: render safe placeholders/empty state without warnings; test in Task 3.

---

### Task 1: Query and aggregate receipt data

**Files:**
- Create: `app/Services/PenerimaanStPembelianTonReportService.php`
- Test: `tests/Unit/PenerimaanStPembelianTonReportServiceTest.php`
- Modify: `config/reports.php`

**Interfaces:**
- Produces `buildReportData(string $noPenerimaanST): array` with `header`, `length_columns`, `tebal_groups`, `summary`, and `returned_materials` keys.
- Each `tebal_groups` entry contains `tebal` and `rows`; each row contains `lebar`, `uom`, `cells` (length key to pcs), `total_pcs`, and `total_ton`.
- `header` contains the reference fields; `length_columns` contains normalized numeric keys/labels; `summary` contains grouped `total_rows`, `totals_by_length`, `total_pcs`, and `total_ton`.
- `returned_materials` contains ordered source rows and `total_pcs`/`total_ton` summaries; the preview API and PDF share this data.
- Produces `healthCheck(string $noPenerimaanST): array` with `is_healthy`, expected/detected columns, and row counts.

- [x] **Step 1: Write the failing unit tests**

  Add a test with four joined rows for 31×38 at lengths 2.5, 3, 3.5, and 4 with pcs 12, 6, 4, and 116; assert one row, 138 pcs, and `0.1332` ton. Add the live-reference 44×55 detail rows with pcs `[1,3,26,1,2,3,2,18,6,1,4,109,118,231,111,144]`; assert 780 pcs and `1.5972` ton after flooring each source row before summing. Also assert UOM `3` for 31×38×2/1 pc gives `0.3271`, retain length 4.5 in numeric order, verify the receipt is a SQL binding, and map the seven returned rows to `280` pcs / `0.5681` ton.

- [x] **Step 2: Run the unit test and verify the expected failure**

  Run: `php artisan test tests/Unit/PenerimaanStPembelianTonReportServiceTest.php`

  Expected: the 44×55 case fails because current aggregation floors summed pcs per length instead of each raw detail row (`1.5977` instead of `1.5972`).

- [x] **Step 3: Implement the direct query and aggregation**

  Add the connection setting `reports.penerimaan_st_pembelian_ton.database_connection`, defaulting to `DB_CONNECTION`. In `PenerimaanStPembelianTonReportService`, use parameterized queries joining `PenerimaanSTPembelian_h` → supplier/tanah and receipt labels → `ST_h` → `ST_d`, with wood type from `MstJenisKayu`, plus a separate query for `PenerimaanSTPembelian_Reject` and its `MstUOM` labels. Map `IsKering` 1 to `Kering`, 0 to `Basah`, and null to `-`. Throw `RuntimeException` when the receipt header is absent; allow an existing header with no detail rows to produce an empty report state. Wrap query failures using the nearby report-service error pattern.

  Aggregate pcs by thickness, width, UOM, and length for display. Calculate and floor ton from each raw detail row using the supplied UOM formula and `floor($raw * 10000) / 10000`, then add those rounded row values to the dimension-row and grand totals. Keep all actual lengths in numeric order.

- [x] **Step 4: Run the unit tests and verify they pass**

  Run: `php artisan test tests/Unit/PenerimaanStPembelianTonReportServiceTest.php`

  Expected: PASS, including reference totals 0.1332 and 1.5972 and both UOM branches.

---

### Task 2: Expose the authenticated API contract

**Files:**
- Create: `app/Http/Requests/GeneratePenerimaanStPembelianTonReportRequest.php`
- Create: `app/Http/Controllers/PenerimaanStPembelianTonController.php`
- Test: `tests/Feature/PenerimaanStPembelianTonReportFeatureTest.php`
- Modify: `routes/api.php`
- Modify: `app/Http/Controllers/Api/OpenApiController.php`

**Interfaces:**
- `POST /api/reports/sawn-timber/penerimaan-st-pembelian-ton` accepts JSON `{"NoPenerimaanST":"B.160214"}` and returns `message`, `meta` (`no_penerimaan_st`, `total_rows`, `total_pcs`, `total_ton`), `data` (the grouped thickness rows), and `report_data` (including returned-material rows and totals).
- `POST /api/reports/sawn-timber/penerimaan-st-pembelian-ton/pdf` accepts the same request and returns an attachment PDF.
- `POST /api/reports/sawn-timber/penerimaan-st-pembelian-ton/health` accepts the same request and returns health JSON.
- All three endpoints use report JWT middleware; the FormRequest validates `NoPenerimaanST`.

- [x] **Step 1: Write failing feature tests**

  Test authenticated preview response and summary, unauthenticated request returning 401, missing/over-13-character `NoPenerimaanST` and unknown receipt returning 422, PDF attachment headers/filename through a mocked `PdfGenerator`, health response, and OpenAPI request/preview response schemas including returned-material rows.

- [x] **Step 2: Run the feature tests and verify the expected failure**

  Run: `php artisan test tests/Feature/PenerimaanStPembelianTonReportFeatureTest.php`

  Expected: FAIL because the route and controller do not exist yet.

- [x] **Step 3: Implement request, controller, routes, and OpenAPI details**

  Add the FormRequest extending `BaseReportRequest`; add controller methods `preview()`, `download()`, and `health()` following `PenerimaanStHasilSawmillController`. Render `reports.sawn-timber.penerimaan-st-pembelian-ton-pdf` through `PdfGenerator` in portrait orientation and return a sanitized receipt-specific PDF filename.

  Import the controller and add its route definition to `$sawnTimberReportRouteDefinitions`. Add a specific OpenAPI request schema and attach it to the generated preview/PDF/health operations; document returned-material rows in the preview response while retaining generic response schemas for unrelated reports.

- [x] **Step 4: Run feature tests and verify they pass**

  Run: `php artisan test tests/Feature/PenerimaanStPembelianTonReportFeatureTest.php`

  Expected: PASS for auth, validation, preview, PDF headers, health, and OpenAPI contract.

---

### Task 3: Build and visually verify the PDF view

**Files:**
- Create: `resources/views/reports/sawn-timber/penerimaan-st-pembelian-ton-pdf.blade.php`
- Modify: `tests/Feature/PenerimaanStPembelianTonReportFeatureTest.php`

**Interfaces:**
- Consumes the `reportData` shape from Task 1 plus `generatedBy` and `generatedAt` from Task 2.
- Displays the reference's report fields, grouped size table, and returned-material table when populated, styled with the existing Sawn Timber PDF conventions and shared footer.

- [x] **Step 1: Write the failing view/render test**

  Render the Blade view with fixture data based on the PDF sample. Assert the report title, receipt/supplier metadata, length headers, 138 pcs, 0.1332 ton, note, all three approval labels, and one thickness-group separator are present. Add the returned-material sample and assert all seven rows plus `280` pcs / `0.5681` ton render; assert the table is omitted for an empty return list. Include an empty-detail fixture and assert the empty state and optional metadata placeholders render without errors.

- [x] **Step 2: Run the feature test and verify the expected failure**

  Run: `php artisan test tests/Feature/PenerimaanStPembelianTonReportFeatureTest.php --filter=view`

  Expected: FAIL because the thickness-group separator is not rendered yet.

- [x] **Step 3: Implement the Blade report view**

  Follow `resources/views/reports/sawn-timber/penerimaan-st-hasil-sawmill-pdf.blade.php` for Noto Serif typography, three metadata blocks, border rules, alternating fills, Indonesian date formatting, numeric formatting, and the shared footer. Include the reference fields; render thickness row spans, the grouped length header, totals, note, returned-material table when populated, and three signature lines. Add a horizontal rule before each thickness group after the first. Keep page breaks within rows and leave absent values safe.

- [x] **Step 4: Run the feature tests and verify they pass**

  Run: `php artisan test tests/Feature/PenerimaanStPembelianTonReportFeatureTest.php`

  Expected: PASS for both populated and empty-detail view fixtures.

- [x] **Step 5: Render and inspect a sample PDF**

  Generate a PDF using the real `PdfGenerator` and the reference fixture, save it outside the repository under `C:\Users\ridho\AppData\Local\Temp\opencode`, and inspect its rendered page preview for portrait fit, table borders/rowspans, headers, note, signatures, and footer. If the PDF conversion script's dependencies are already installed, use it; do not add a new dependency just for this check.

---

### Task 4: Run repository verification

**Files:** No additional files.

- [x] **Step 1: Run focused tests**

  Run: `php artisan test tests/Unit/PenerimaanStPembelianTonReportServiceTest.php` and `php artisan test tests/Feature/PenerimaanStPembelianTonReportFeatureTest.php`.

  Expected: PASS.

- [x] **Step 2: Run the full test suite and report audits**

  Run: `composer test`, `vendor/bin/pint --test`, `php artisan reports:audit-conventions`, and `php artisan reports:audit-api --fail-on-missing-openapi`.

  Expected: Pint and API route/OpenAPI audit pass. Compare full-suite and convention-audit failures to the observed baseline; report unrelated failures by name and do not expand scope to fix them.

- [x] **Step 3: Refresh the code graph**

  Run: `graphify update .`.

  Expected: graphify completes, or its inability to update without an existing graph is recorded.

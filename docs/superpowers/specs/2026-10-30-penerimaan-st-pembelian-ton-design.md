# Design: Laporan Penerimaan ST - Pembelian (Ton)

**Status:** Approved  
**Size:** Small change — one report using existing report/API conventions.

## Intent

Add the missing single-receipt **Laporan Penerimaan ST - Pembelian (Ton)** with this repository as the report backend. The Node source is a reference for table relationships and calculations only; this implementation will use the existing Laravel report/API architecture. The reference PDF defines the report's content and information layout; the visual styling and footer follow this project's current PDFs.

## Goals

- Accept one required `NoPenerimaanST` and expose the standard authenticated preview, PDF, and health endpoints under `sawn-timber`.
- Read data directly from SQL Server tables; no stored procedure and no edits to another project.
- Read receipt metadata from `PenerimaanSTPembelian_h`, `MstSupplier`, and `MstSuratTanah`; read accepted ST dimensions through `PenerimaanSTPembelian_d` → `ST_h` → `ST_d`, with wood type from `MstJenisKayu`.
- Read returned-material rows from `PenerimaanSTPembelian_Reject`, resolving width and length units through `MstUOM`.
- Group displayed pcs by thickness, width, and length. Use the supplied `hitungTon` formula and floor each raw `ST_d` detail row to four decimals before summing row and grand totals.
- Include receipt number, supplier, dates, truck/plate, Suket, wood type, Surat Tanah, Ton Surat Jalan, condition, note, and the reference's approval lines.
- Follow the reference's portrait report structure, three-column metadata, grouped size table, note, optional returned-material table, and three approval lines. Show the returned-material table only when rows exist; use its stored Ton values and totals. Keep the sample length columns (2, 2.5, 3, 3.5, 4) in order and retain any additional lengths present in data.
- Draw a clear horizontal separator when the thickness group changes (for example, between 31 and 44) to make the sections easy to scan.
- Use `PdfGenerator` and match the current Sawn Timber PDF styling (Noto Serif, black borders, alternating row fills, and shared PDF footer/print metadata); do not copy the reference's literal print footer.
- Document the required request field in the generated OpenAPI contract and cover grouping/calculation plus API responses in tests.

## Non-goals

- No CRUD endpoints, migrations, new stored procedures, or edits to another project.
- No manual web form; the report is exposed through this project's API.
- No changes to the existing `Pembelian ST Per Supplier (Ton)` report.

## Approaches

1. **Query tables in this report API (chosen).** The caller sends `NoPenerimaanST`; this service reads and aggregates the source tables, then renders the PDF. This keeps the report calculation and PDF in this repository and uses the source code/schema provided.
2. **Have `wps-backend` send pre-aggregated report data.** This keeps database access in the other project, but moves report logic there and requires changes outside this checkout. Not chosen.

## Contract and implementation shape

- `POST /api/reports/sawn-timber/penerimaan-st-pembelian-ton` returns preview JSON from this backend.
- `report_data.returned_materials` contains the returned-material rows and their pcs/ton totals, including an empty `rows` array when none exist.
- `POST /api/reports/sawn-timber/penerimaan-st-pembelian-ton/pdf` returns an attachment PDF.
- `POST /api/reports/sawn-timber/penerimaan-st-pembelian-ton/health` checks the requested receipt/source query.
- All use the report JWT middleware and require `NoPenerimaanST` (varchar-sized value, max 13); SQL values are bound parameters.
- Add a report connection setting that defaults to the configured application DB connection.
- PDF content/layout follows the supplied reference; styling, rendering, auth, and download handling follow the existing `Penerimaan ST Hasil Sawmill` implementation.
- Add the report to the Sawn Timber API route definitions and tests. OpenAPI describes the required request field and returned-material preview data; no Postman collection is present in this checkout.

## Assumptions and calls left to the builder

- The live `WPS_TEST3` schema confirms `IdTanah` and `IsKering` on `PenerimaanSTPembelian_h`; the local JSON schema export was stale. `IsKering` supplies the displayed condition, and `Jenis` is derived from the linked ST labels.
- A receipt uses one wood type/UOM family, as the reference shows one value in those header cells. The builder should preserve all length values present in the receipt rather than hard-code only the sample lengths.
- The returned-material table is separate from the accepted-dimensions grid. Its heading/description comes from the receipt `Note`, and its details come from `PenerimaanSTPembelian_Reject`.
- If the source rows or receipt are missing, follow the nearby report's validation/error-response conventions rather than emitting a misleading total.

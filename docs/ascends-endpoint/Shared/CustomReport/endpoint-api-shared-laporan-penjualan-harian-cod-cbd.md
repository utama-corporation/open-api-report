# Dokumentasi Hit Endpoint API Ascend Shared Custom Report Laporan Penjualan Harian COD & CBD

Dokumen ini berisi endpoint internal untuk render **Laporan Penjualan Harian COD & CBD** dari XML Ascend.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Endpoint

`POST http://192.168.10.100:5006/api/internal/ascends/shared/custom-report/laporan-penjualan-harian-cod-cbd/pdf`

Company pada header dan filename dibaca dari field `DB_CompanyName` (`GSU`, `RU`, atau `UC`).

## Konsep

Data `<Table>` dikelompokkan per `Ket` (Remark): section `COD` lebih dulu, lalu `CBD`, lalu remark lain secara alfabetis. Setiap section diurutkan per tanggal invoice, dengan zebra row, Grand Total gabungan, dan blok tanda tangan di bawah tabel.

## Input

- `xml_file`: file XML dari Ascend. Bisa juga dikirim sebagai field `xml` atau raw XML body.
- `DB_CompanyName`: wajib, contoh `RU`.
- `PerDate`: tanggal laporan (opsional), format `YYYY-MM-DD` atau `dd/mm/yyyy`. Dipakai untuk subtitle; bila kosong diambil dari tanggal invoice.
- `Sys_Username` / `Sys_UserName`: nama user print untuk footer `Dicetak oleh`.

## Field XML

| Kolom PDF | Field |
|---|---|
| Tanggal Invoice | `InvoiceDate` |
| No. Invoice | `InvoiceNumber` |
| Nama Pelanggan | `CustomerName` |
| Kota | `BillingCity` |
| Keterangan | `Ket` (sekaligus section) |
| Termin | `PaymentTerm` |
| Umur | `Age` |
| Nilai Invoice | `Total` |
| Diskon | `Disc / Total` (persentase) |
| Total Netto | `NetTotal` |
| Status | `Status` (`-` atau kosong → `Belum Lunas`) |

Grand Total = jumlah `Total` dan `NetTotal` seluruh section.

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment; filename="Laporan Penjualan Harian COD & CBD (COMPANY).pdf"`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak berisi element `Table`.

## Template Blade

`resources/views/ascends/shared/custom_report/laporan_penjualan_harian_cod_cbd/pdf.blade.php` (11 kolom, landscape, zebra row).

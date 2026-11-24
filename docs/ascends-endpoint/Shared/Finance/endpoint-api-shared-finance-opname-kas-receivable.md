# Dokumentasi Hit Endpoint API Ascend Shared Finance Opname Kas (Advance Receivable)

Dokumen ini berisi endpoint internal untuk render **Laporan Penerimaan Uang Muka Penjualan (Opname Kas — Advance Receivable)** per tanggal dari XML Ascend.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Endpoint

`POST http://192.168.10.100:5006/api/internal/ascends/shared/finance/outstanding_advance_receivable/opname-kas/pdf`

Company pada header dan filename dibaca dari field `DB_CompanyName` (`GSU`, `RU`, atau `UC`).

## Konsep

Laporan memakai **satu tanggal laporan (Per Tanggal)**. Seluruh uang muka customer yang masih outstanding sampai tanggal laporan ditampilkan sebagai baris detail (tanpa batas tahun), dikelompokkan per `Customer Code`.

- `Saldo` = akumulasi `Amount Outstanding (Local)` berurutan dalam satu customer.
- Advance dengan outstanding nol dan baris setelah `PerDate` tidak ditampilkan.
- Ada Sub Total per customer, TOTAL, dan blok tanda tangan di dasar halaman terakhir.

## Input

- `xml_file`: file XML dari Ascend. Bisa juga dikirim sebagai field `xml` atau raw XML body.
- `DB_CompanyName`: wajib, contoh `RU`.
- `PerDate`: tanggal laporan, format `YYYY-MM-DD` atau `dd/mm/yyyy`. Bila kosong dipakai tanggal hari ini.
- `Sys_Username` / `Sys_UserName`: nama user print untuk footer `Dicetak oleh`.

Dataset: `<NewDataSet><opname>` dengan field `Voucher Number`, `Voucher Date`, `Customer Code`, `Customer Name`, `Remarks`, `Net Total (Local)`, `Amount Allocated (Local)`, `Amount Outstanding (Local)`.

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment; filename="Laporan Opname Kas (COMPANY).pdf"`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak berisi element `opname` maupun `Account`/`Ledger`.

## Template Blade

`resources/views/ascends/shared/finance/outstanding_advance_receivable/opname_kas/pdf.blade.php` (6 kolom, portrait, zebra row).

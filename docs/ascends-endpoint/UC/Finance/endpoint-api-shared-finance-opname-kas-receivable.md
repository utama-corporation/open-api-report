# Dokumentasi Hit Endpoint API Ascend UC Finance Opname Kas (Advance Receivable)

Dokumen ini berisi endpoint internal untuk render **Laporan Opname Kas — Outstanding Advance Receivable** per tanggal dari XML Ascend.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`
- Company: `UC` (ditentukan dari segmen URL, bukan dari request)

## Endpoint

`POST http://192.168.10.100:5006/api/internal/ascends/uc/finance/outstanding_advance_receivable/opname-kas/pdf`

## Konsep

Laporan memakai **satu tanggal laporan (Per Tanggal)**, bukan rentang tanggal.
Seluruh uang muka customer yang masih outstanding sampai tanggal laporan ditampilkan sebagai baris detail (tanpa batas tahun).

- Satu section per **Customer** (`Customer : 3.1.2.0.1.D004: DURAMEN BOIS ET SCIAGE`).
- Baris diurutkan per tanggal voucher.
- `Saldo` = akumulasi `Amount Outstanding (Local)` berurutan dalam satu customer.
- Advance dengan outstanding nol (sudah selesai) tidak ditampilkan.
- Baris bertanggal setelah `PerDate` tidak ditampilkan.
- Sub total per customer dan TOTAL dicetak di bagian bawah.

## Input

- `xml_file`: file XML dari Ascend. Bisa juga dikirim sebagai field `xml` atau raw XML body.
- `PerDate`: tanggal laporan, format `YYYY-MM-DD` atau `dd/mm/yyyy`, contoh `2026-11-24`. Bila kosong dipakai tanggal hari ini. Tampil di subtitle `Per Tanggal : 24-Nov-26`.
- `Sys_Username` / `Sys_UserName`: nama user print untuk footer `Dicetak oleh`.

### Dataset: `<NewDataSet><opname>` (customer)

| Kolom PDF | Field XML |
|---|---|
| Tanggal | `Voucher Date` |
| No. Voucher | `Voucher Number` |
| Keterangan | `Customer Name` + ` -- ` + `Remarks` |
| Debit | `Net Total (Local)` |
| Kredit | `Amount Allocated (Local)` |
| Saldo | `Amount Outstanding (Local)` |
| Section | `Customer Code`, `Customer Name` |

Field lain (`Customer City`, `Customer State`, `Currency`, `Payment Method`, `Bank ...`, `Exchange Rate`, `SO Number`, `Validated ...`, `Voucher Type`, `Check No.`, `Internal Remarks`) tidak dicetak.

Catatan: service yang sama juga dipakai endpoint **payable** (`.../outstanding_advance_payable/...`) yang memakai field `Supplier Name` dan `Advance Account Code`.

## Contoh Request

```text
Sys_Username=Ridho
PerDate=2026-11-24
xml_file=AnlReports.Finance.AdvanceReceivable.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment; filename="Laporan Opname Kas (UC).pdf"`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak berisi element `opname` maupun `Account`/`Ledger`.

## Template Blade

`resources/views/ascends/shared/finance/outstanding_advance_receivable/opname_kas/pdf.blade.php` (6 kolom, portrait, zebra row).

## Route

- Shared: `api.internal.ascends.shared.finance.outstanding_advance_receivable.opname-kas.pdf`
- Company: `api.internal.ascends.ru.finance.outstanding_advance_receivable.opname-kas.pdf`

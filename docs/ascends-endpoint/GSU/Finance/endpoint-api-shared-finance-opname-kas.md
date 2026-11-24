# Dokumentasi Hit Endpoint API Ascend GSU Finance Opname Kas

Dokumen ini berisi endpoint internal untuk render **Laporan Opname Kas** per tanggal dari XML Ascend.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`
- Company: `GSU` (ditentukan dari segmen URL, bukan dari request)

## Endpoint

`POST http://192.168.10.100:5006/api/internal/ascends/gsu/finance/outstanding_advance_payable/opname-kas/pdf`

## Konsep

Laporan memakai **satu tanggal laporan (Per Tanggal)**, bukan rentang tanggal.
Seluruh advance yang masih outstanding sampai tanggal laporan ditampilkan sebagai baris detail (tanpa batas tahun), bukan hanya saldo awal.

- Satu section per `Advance Account Code` (`Cost Centre : 112.200.003: ...`).
- Baris diurutkan per tanggal voucher.
- `Saldo` = akumulasi `Amount Outstanding (Local)` berurutan dalam satu account.
- Advance dengan outstanding nol (sudah selesai) tidak ditampilkan.
- Baris bertanggal setelah `PerDate` tidak ditampilkan.
- Sub total per account dan TOTAL dicetak di bagian bawah.

## Input

- `xml_file`: file XML dari Ascend. Bisa juga dikirim sebagai field `xml` atau raw XML body.
- `PerDate`: tanggal laporan, format `YYYY-MM-DD` atau `dd/mm/yyyy`, contoh `2026-11-24`. Bila kosong dipakai tanggal hari ini. Tampil di subtitle `Per Tanggal : 24-Nov-26`.
- `Sys_Username` / `Sys_UserName`: nama user print untuk footer `Dicetak oleh`.

### Dataset utama: `<NewDataSet><opname>`

| Kolom PDF | Field XML |
|---|---|
| Tanggal | `Voucher Date` |
| No. Voucher | `Voucher Number` |
| Keterangan | `Supplier Name` + ` -- ` + `Remarks` |
| Debit | `Net Total (Local)` |
| Kredit | `Amount Allocated (Local)` |
| Saldo | `Amount Outstanding (Local)` |
| Section | `Advance Account Code`, `Advance Account Name` |

Field lain (`Supplier Code`, `Currency`, `Payment Method`, `Bank ...`, `Exchange Rate`, `Validated ...`, `Voucher Type`, `PO Number`, `Internal Remarks`) tidak dicetak.

### Dataset alternatif: `<NewDataSet><Account>` + `<Ledger>`

Dipakai bila XML berisi element `Account`/`Ledger` (mis. `GL.LedgerReport.xml`).
Saldo berjalan dimulai dari `OpeningBalance` tiap `AccountCode`, lalu `DebitAmount`/`CreditAmount` per `Ledger`.

## Contoh Request

```text
Sys_Username=Ridho
PerDate=2026-11-24
xml_file=GL.LedgerReport.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment; filename="Laporan Opname Kas (GSU).pdf"`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak berisi element `opname` maupun `Account`/`Ledger`.

## Template Blade

`resources/views/ascends/shared/finance/outstanding_advance_payable/opname_kas/pdf.blade.php` (6 kolom, portrait).

## Route

- Shared: `api.internal.ascends.shared.finance.outstanding_advance_payable.opname-kas.pdf`
- Company: `api.internal.ascends.ru.finance.outstanding_advance_payable.opname-kas.pdf`

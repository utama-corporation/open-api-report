# Dokumentasi Hit Endpoint API Ascend GSU Finance Penerimaan Piutang

Dokumen ini berisi endpoint internal untuk test/render laporan Penerimaan Piutang Ascend yang memakai Blade shared.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Company

Company ditentukan dari segmen URL (`gsu`), bukan dari request. Client cukup mengirim XML.
Sebagian URL sudah dimiliki route statis lama dan tetap memakai route tersebut.

## Konsep

Template shared Finance Penerimaan Piutang dipakai supaya struktur Blade laporan bisa digunakan lintas perusahaan:

- `RU`
- `GSU`
- `UC`

Company pada title dan filename berasal dari segmen URL `/gsu/`. Client cukup mengirim XML.
Nama user print pada footer dibaca dari parameter field `Sys_Username`.

## Endpoint

1. **Laporan Penerimaan Piutang**

   `POST http://192.168.10.100:5006/api/internal/ascends/gsu/finance/receipt_voucher_details/penerimaan-piutang/pdf`

## Input

Parameter field utama:

- `xml_file`: file XML dari Ascend, file `AnlReports.Finance.ReceiptVoucherDetails.xml` berisi data voucher penerimaan (element `<Sukamu>`).
- `Sys_Username`: nama user print dari parameter Crystal Report Ascend, dipakai untuk footer `Dicetak oleh`, contoh `Ridho`.
- `ReceiptVoucherDate.StartDate` + `ReceiptVoucherDate.EndDate`: periode laporan, contoh `01/06/2026` sampai `30/06/2026`.
  - Alias yang diterima: `StartDate` + `EndDate`.

Input XML alternatif yang tetap diterima untuk kebutuhan testing:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

Fallback kompatibilitas lama:

- `Sys_UserName`: alias lama untuk nama user print jika `Sys_Username` belum dikirim.

### Struktur Data

Setiap record XML `<Sukamu>` adalah alokasi pembayaran voucher untuk satu invoice. Struktur laporan:

- **Flat list** diurutkan berdasarkan Sales Person → Customer → Tanggal Invoice
- Kolom: Nama Sales, Nama Customer, No. Invoice, Tgl Invoice, Tgl Bayar, Lama, Nilai Invoice, Nilai Bayar, Cara Bayar, Nama Akun Bank Penerima
- Baris invoice yang sama digabung (continuation row, hanya menampilkan Tgl Bayar, Lama, Nilai Bayar)
- Grand Total di akhir

### Formula Fields

**AkunBank** — mapping `Bank Account Code` ke nama bank:
| Code | AkunBank |
|---|---|
| 111.102.101 - 111.102.102 | BCA |
| 111.102.103 - 111.102.105, 111.102.108 | MANDIRI |
| 111.102.106 - 111.102.107 | MAYBANK |
| 111.102.109 - 111.102.110 | BRI |
| 111.101.100 | Kas Kecil |
| 111.101.200 | Kas Bu Florida |
| 111.101.300 | Kas Dalam Perjalanan |
| 111.101.400 | Kas Gantung |
| 111.101.500 | Kas Besar |

**KETBANK** — mapping `Bank Account Code` ke nama pemilik akun.

**GabKet** — Jika code mulai `111.102` → `AkunBank - KETBANK`, jika tidak → `AkunBank`.

### Contoh Request

```text
Sys_Username=Ridho
ReceiptVoucherDate.StartDate=13/07/2026
ReceiptVoucherDate.EndDate=13/07/2026
xml_file=AnlReports.Finance.ReceiptVoucherDetails.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment`

Filename PDF:

- `Laporan Penerimaan Piutang - (GSU).pdf`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak bisa diproses.
- `422 application/json` jika data tidak ditemukan untuk periode yang diminta.

## Template Blade Tersedia

Template Blade shared Finance Penerimaan Piutang berada di `resources/views/ascends/shared/finance/receipt_voucher_details/penerimaan_piutang`.

- `penerimaan_piutang` — Laporan Penerimaan Piutang (flat list, 10 kolom)

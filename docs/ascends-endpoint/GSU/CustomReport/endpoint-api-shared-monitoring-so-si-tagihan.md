# Dokumentasi Endpoint API Ascend GSU Laporan Monitoring SO - SI - Tagihan

Dokumen ini berisi endpoint internal untuk test/render laporan **Laporan Monitoring SO - SI - Tagihan** Ascend yang memakai Blade shared.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Company

Company ditentukan dari segmen URL (`gsu`), bukan dari request. Client cukup mengirim XML.
Sebagian URL sudah dimiliki route statis lama dan tetap memakai route tersebut.

## Konsep

Template shared ini dipakai supaya struktur Blade laporan bisa digunakan lintas perusahaan:

- `RU`
- `GSU`
- `UC`

Company pada title dan filename berasal dari segmen URL `/gsu/`. Client cukup mengirim XML.
Nama user print pada footer dibaca dari parameter field `Sys_Username`.

## Endpoint

### Laporan Monitoring SO - SI - Tagihan

`POST /api/internal/ascends/gsu/custom-report/monitoring-so-si-tagihan/pdf`

**Route name:** `api.internal.ascends.gsu.custom-report.monitoring-so-si-tagihan.pdf`

**Controller:** `CompanyReportController::pdf`

**Deskripsi:** Laporan monitoring Sales Order (SO), Sales Invoice (SI), dan Tagihan per baris SO/SI, menampilkan tanggal SO/Inv serta lama hari menuju SI dan menuju tagihan, plus status pelunasan.

## Input

Parameter field utama:

- `xml_file`: file XML dari Ascend (contoh `Monitoring SO SI dan Tagihan.xml`).
- `Sys_Username`: nama user print, contoh `Ridho`.
- `StartDate`: tanggal awal periode (contoh `2026-07-01`).
- `EndDate`: tanggal akhir periode (contoh `2026-07-31`).

Input XML alternatif yang tetap diterima:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

Fallback kompatibilitas:

- `start_date` + `end_date`: alias untuk `StartDate` + `EndDate`.

Contoh `multipart/form-data`:

```text
Sys_Username=Ridho
StartDate=2026-07-01
EndDate=2026-07-31
xml_file=Monitoring SO SI dan Tagihan.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment`

Title yang tampil di halaman PDF:

```text
Laporan Monitoring SO - SI - Tagihan
```

Subtitle (periode):

```text
Dari 01-Jul-26 s/d 31-Jul-26
```

Filename PDF:

```text
Laporan Monitoring SO - SI - Tagihan (GSU).pdf
```

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak bisa diproses.
- `422 application/json` jika tidak ada data dalam rentang tanggal yang dipilih.

## Struktur Laporan

Tabel flat tanpa pengelompokan, diurutkan sesuai urutan baris pada XML (descending sesuai source). Kolom pada tabel:

| # | Kolom | Sumber / Formula |
|---|-------|------------------|
| 1 | Nama Customer | `{Table.CustomerName}` |
| 2 | No. SO | `{Table.SONumber}` |
| 3 | SO Date (Hr) | `{Table.SODate}` |
| 4 | No. Invoice | `{Table.InvoiceNumber}` |
| 5 | Inv Date | `{Table.InvoiceDate}` |
| 6 | SO Ke SI (Hr) | `sosi = {Table.SO-SI}` |
| 7 | Date Pelunas | `{Table.DateV}` |
| 8 | SI Ke Tgh (Hr) | `sosi 2` → jika `SI-TGH` null maka `printdate - InvoiceDate`, selain itu `SI-TGH` |
| 9 | Lunas | `Ket` → jika `Table.Ket` null atau mengandung `Belum` maka `No`, selain itu `Yes` |

- Kolom 3 (`{Table.SODate}` dan kolom 7 (`Date Pelunas`) ditampilkan sebagai tanggal `dd-MMM-yy` menggunakan locale Indonesia.
- Kolom 9 (`Lunas`) dihitung dari formula `Ket`: `if isnull({Table.Ket}) or 'Belum' in {Table.Ket} then 'No' else 'Yes'`.
- Kolom 8 (`SI Ke Tgh`) dihitung dari formula `sosi 2`.

Filter baris berdasarkan `{Table.SODate}` antara `StartDate` dan `EndDate` (inklusif).

Format A4 landscape dengan font Noto Serif.

## Template Blade

Template Blade berada di:

```
resources/views/ascends/shared/custom_report/monitoring_so_si_tagihan/pdf.blade.php
```
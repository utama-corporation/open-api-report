# Dokumentasi Hit Endpoint API Ascend GSU Finance Payable Summary

> Laporan pada dokumen ini tidak dipublikasikan untuk GSU.


Dokumen ini berisi endpoint internal untuk test/render laporan Payable Summary Ascend yang memakai Blade shared.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Company

Company ditentukan dari segmen URL (`gsu`), bukan dari request. Client cukup mengirim XML.
Sebagian URL sudah dimiliki route statis lama dan tetap memakai route tersebut.

## Konsep

Template shared Payable Summary dipakai supaya struktur Blade laporan bisa digunakan lintas perusahaan.

## Endpoint GSU Payable Summary

- Payable Summary - Laporan Saldo Hutang (RU): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/finance/payable-summary/saldo-hutang/pdf` (tidak dipublikasikan untuk GSU)

## Input

Parameter field utama untuk semua endpoint Ascends GSU Finance Payable Summary:

- `xml_file`: file XML dari Ascend.
- `Sys_Username`: nama user print dari parameter Crystal Report Ascend, dipakai untuk footer `Dicetak oleh`, contoh `Ridho`.

Input XML alternatif yang tetap diterima untuk kebutuhan testing:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

Fallback kompatibilitas lama:

- `Sys_UserName`: alias lama untuk nama user print jika `Sys_Username` belum dikirim.

Input tambahan khusus `saldo-hutang-ru`:

- `PayableSummaryDate.StartDate` + `PayableSummaryDate.EndDate`: periode filter data, contoh `2026-06-01` sampai `2026-06-30`.
- Alias tanggal yang diterima: `start_date` + `end_date`, `StartDate` + `EndDate`, `PayableSummaryDate.StartDate` + `PayableSummaryDate.EndDate`.

Contoh `multipart/form-data`:

```text
Sys_Username=Ridho
PayableSummaryDate.StartDate=2026-06-01
PayableSummaryDate.EndDate=2026-06-30
xml_file=AnlReports.Finance.PayableSummary.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment`

Title yang tampil di halaman PDF: `Laporan Saldo Hutang (GSU)`

Filename PDF: `Payable Summary - Laporan Saldo Hutang (GSU).pdf`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak bisa diproses.

## Template shared Payable Summary Tersedia

Template Blade shared Payable Summary berada di `resources/views/ascends/shared/finance/payable_summary`.

- `saldo_hutang_ru`

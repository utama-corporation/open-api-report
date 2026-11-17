# Dokumentasi Hit Endpoint API Ascend RU Finance Receivable Summary

Dokumen ini berisi endpoint internal untuk test/render laporan Receivable Summary Ascend yang memakai Blade shared.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Company

Company ditentukan dari segmen URL (`ru`), bukan dari request. Client cukup mengirim XML.
Sebagian URL sudah dimiliki route statis lama dan tetap memakai route tersebut.

## Konsep

Template shared Receivable Summary dipakai supaya struktur Blade laporan bisa digunakan lintas perusahaan.

## Endpoint RU Receivable Summary

- Receivable Summary - Laporan Umur Piutang Dagang (RU): `POST http://192.168.10.100:5006/api/internal/ascends/ru/finance/receivable-summary/umur-piutang/pdf`

## Input

Parameter field utama untuk semua endpoint Ascends RU Finance Receivable Summary:

- `xml_file`: file XML dari Ascend.
- `Sys_Username`: nama user print dari parameter Crystal Report Ascend, dipakai untuk footer `Dicetak oleh`, contoh `Ridho`.

Input XML alternatif yang tetap diterima untuk kebutuhan testing:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

Fallback kompatibilitas lama:

- `Sys_UserName`: alias lama untuk nama user print jika `Sys_Username` belum dikirim.

Input tambahan khusus `umur-piutang-ru`:

- `ReceivableSummaryDate.StartDate` + `ReceivableSummaryDate.EndDate`: periode filter data, contoh `2026-06-01` sampai `2026-06-30`.
- Alias tanggal yang diterima: `start_date` + `end_date`, `StartDate` + `EndDate`, `ReceivableSummaryDate.StartDate` + `ReceivableSummaryDate.EndDate`.

Contoh `multipart/form-data`:

```text
Sys_Username=Ridho
ReceivableSummaryDate.StartDate=2026-06-01
ReceivableSummaryDate.EndDate=2026-06-30
xml_file=AnlReports.Finance.ReceivableSummary.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment`

Title yang tampil di halaman PDF: `Laporan Umur Piutang Dagang (RU)`

Filename PDF: `Receivable Summary - Laporan Umur Piutang Dagang (RU).pdf`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak bisa diproses.

## Template shared Receivable Summary Tersedia

Template Blade shared Receivable Summary berada di `resources/views/ascends/shared/finance/receivable_summary`.

- `umur_piutang_ru`

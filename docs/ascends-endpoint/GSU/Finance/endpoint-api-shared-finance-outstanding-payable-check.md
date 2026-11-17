# Dokumentasi Hit Endpoint API Ascend GSU Finance Outstanding Payable Check

> Laporan pada dokumen ini tidak dipublikasikan untuk GSU.


Dokumen ini berisi endpoint internal untuk test/render laporan Outstanding Payable Check Ascend yang memakai Blade shared.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Company

Company ditentukan dari segmen URL (`gsu`), bukan dari request. Client cukup mengirim XML.
Sebagian URL sudah dimiliki route statis lama dan tetap memakai route tersebut.

## Konsep

Template shared Outstanding Payable Check dipakai supaya struktur Blade laporan bisa digunakan lintas perusahaan.

## Endpoint GSU Outstanding Payable Check

- Outstanding Payable Check - Laporan Hutang Giro (RU): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/finance/outstanding-payable-check/hutang-giro/pdf` (tidak dipublikasikan untuk GSU)

## Input

Parameter field utama untuk semua endpoint Ascends GSU Finance Outstanding Payable Check:

- `xml_file`: file XML dari Ascend.
- `Sys_Username`: nama user print dari parameter Crystal Report Ascend, dipakai untuk footer `Dicetak oleh`, contoh `Ridho`.

Input XML alternatif yang tetap diterima untuk kebutuhan testing:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

Fallback kompatibilitas lama:

- `Sys_UserName`: alias lama untuk nama user print jika `Sys_Username` belum dikirim.

Input tambahan khusus `hutang-giro-ru`:

- `PerDate`: tanggal filter untuk menentukan data per tanggal tertentu, contoh `2026-06-30`.
- Alias yang diterima: `per_date`, `PerDate`, `Per Date`, `tanggal`, `Tanggal`, `date`, `Date`.

Contoh `multipart/form-data`:

```text
Sys_Username=Ridho
PerDate=2026-06-30
xml_file=AnlReports.Finance.OutstandingPayableCheck.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment`

Title yang tampil di halaman PDF: `Laporan Hutang Giro (GSU)`

Filename PDF: `Outstanding Payable Check - Laporan Hutang Giro (GSU).pdf`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak bisa diproses.

## Template shared Outstanding Payable Check Tersedia

Template Blade shared Outstanding Payable Check berada di `resources/views/ascends/shared/finance/outstanding_payable_check`.

- `hutang_giro_ru`

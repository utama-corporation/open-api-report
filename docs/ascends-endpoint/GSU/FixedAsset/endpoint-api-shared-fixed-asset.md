# Dokumentasi Hit Endpoint API Ascend GSU Fixed Asset

Dokumen ini berisi endpoint internal untuk test/render laporan Fixed Asset Ascend yang memakai Blade shared.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Company

Company ditentukan dari segmen URL (`gsu`), bukan dari request. Client cukup mengirim XML.
Sebagian URL sudah dimiliki route statis lama dan tetap memakai route tersebut.

## Konsep

Template shared Fixed Asset dipakai supaya struktur Blade laporan bisa digunakan lintas perusahaan:

- `RU`
- `GSU`
- `UC`

Company pada title dan filename berasal dari segmen URL `/gsu/`. Client cukup mengirim XML.
Nama user print pada footer dibaca dari parameter field `Sys_Username`.

## Endpoint GSU Fixed Asset

- Asset Summary - Laporan Daftar Penyusutan Aktiva Tetap: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/fixed_asset/asset_summary/penyusutan_aktiva/pdf`

## Input

Parameter field utama untuk semua endpoint Ascends GSU Fixed Asset:

- `xml_file`: file XML dari Ascend.
- `Sys_Username`: nama user print dari parameter Crystal Report Ascend, dipakai untuk footer `Dicetak oleh`, contoh `Ridho`.

Input XML alternatif yang tetap diterima untuk kebutuhan testing:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

Fallback kompatibilitas lama:

- `Sys_UserName`: alias lama untuk nama user print jika `Sys_Username` belum dikirim.


### Filter Periode

- `Date.StartDate` + `Date.EndDate`: periode laporan penyusutan, contoh `2026-01-01` sampai `2026-01-31`.
- Alias yang diterima: `Date_StartDate` + `Date_EndDate`, `StartDate` + `EndDate`, `start_date` + `end_date`.
- Jika tidak dikirim, sistem memakai tanggal paling awal sampai paling akhir yang tersedia di data XML.

Catatan: Kategori `MESIN & PERLATAN PABRIK` tidak ditampilkan. Kode aset dengan prefix `KP-004`, `MS-1360`, `MSP-141`, dan `TN-1010` juga tidak ditampilkan.

Contoh `multipart/form-data`:

```text
Sys_Username=Ridho
Date.StartDate=2026-01-01
Date.EndDate=2026-01-31
xml_file=AnlReports.FixedAsset.AssetSummary.xml
```

Contoh tanpa parameter tanggal (periode otomatis dari data):

```text
Sys_Username=Ridho
xml_file=AnlReports.FixedAsset.AssetSummary.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: attachment`

Title yang tampil di halaman PDF memakai nama laporan. Nilai `GSU` berasal dari segmen URL:

```text
{Nama Laporan} (GSU)
```

Filename PDF memakai prefix section folder:

```text
Asset Summary - {Nama Laporan} (GSU).pdf
```

Contoh:

- `Asset Summary - Laporan Daftar Penyusutan Aktiva Tetap (GSU).pdf`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak bisa diproses.
- `422 application/json` jika data aktiva tetap tidak ditemukan pada XML.

## Template shared Fixed Asset Tersedia

Template Blade shared Fixed Asset berada di `resources/views/ascends/shared/fixed_asset/asset_summary`.

- `fixed_asset/asset_summary/penyusutan_aktiva`

Catatan: semua endpoint di atas memakai pola shared yang sama. XML menjadi sumber data laporan; company pada title dan filename berasal dari segmen URL.

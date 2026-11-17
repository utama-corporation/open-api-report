# Dokumentasi Endpoint API Ascend GSU Daftar Harga Furniture (ALL)

Dokumen ini berisi endpoint internal untuk test/render laporan **Daftar Harga Furniture** Ascend yang memakai Blade shared.

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

### Daftar Harga Furniture (ALL)

`POST /api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-furniture-all/pdf`

**Route name:** `api.internal.ascends.gsu.custom-report.check-price-group-a.daftar-harga-furniture-all.pdf`

**Controller:** `CompanyReportController::pdf`

**Deskripsi:** Laporan daftar harga furniture per kategori group (Merona, Mo.re, Modelux, Grande) beserta level harga konsumen, semi grosir, grosir, dan akun spesial.

## Input

Parameter field utama:

- `xml_file`: file XML dari Ascend (contoh `Custom2084.xml`).
- `Sys_Username`: nama user print, contoh `Ridho`.

Input XML alternatif yang tetap diterima:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

## Formula & Logika Bisnis

1. **Group**:
   - `MERONA`: Berdasarkan kode/nama produk (2401, 2402, 2501, 2301, 2302, 2303, 2304, 2502).
   - `MO.RE`: Berdasarkan kode/nama produk (2801, 2802, 2814, 2816, 2832, 2870).
   - `MODELUX`: Berdasarkan kode/nama produk (2601).
   - `GRANDE`: Berdasarkan kode/nama produk (53003, 53004, 53014, 53024).
   - `NOT`: Di luar daftar di atas (dilewati).
2. **Record Selection**:
   - FamilyName harus termasuk dalam `["PLASTIK KABINET 2", "PLASTIK KABINET 1", "FURNITURE LIPAT", "PLASTIK FURNITURE 1", "PLASTIK FURNITURE 2"]` dan Group `!= 'NOT'`.

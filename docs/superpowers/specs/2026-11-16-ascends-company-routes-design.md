# Design: Ascend laporan per-company (GSU, RU, UC)

Tanggal: 2026-11-16

## Intent

Aplikasi Ascends kini hanya mengirim payload XML saat mencetak PDF. Informasi company tidak lagi dikirim sebagai `DB_CompanyName`. Untuk itu, semua laporan yang saat ini berada di bawah `/internal/ascends/shared/...` harus tersedia juga di tiga prefix company: `/internal/ascends/gsu/...`, `/internal/ascends/ru/...`, `/internal/ascends/uc/...`. Company ditentukan dari segmen URL, bukan dari body request.

Endpoint shared lama **tetap dipertahankan** sampai tiga prefix baru stabil dan sudah rilis produksi. Refactor/penghapusan shared bukan bagian dari desain ini.

Endpoint company yang sudah ada (~30 route, mis. `/internal/ascends/gsu/hrm/list-karyawan/pdf`) juga **tetap dipertahankan** dan menjadi pemilik URL tersebut. Generator route baru melewati (skip) URL yang sudah terdaftar agar tidak menimpa laporan yang sudah dikerjakan user. Karena file route baru di-require di akhir `routes/api.php`, route lama tetap menang lebih dulu.

## Goals

- Menyediakan 3 prefix company untuk seluruh laporan shared yang ada saat ini (165 route shared, termasuk route dinamis `/shared/hrm/{report}/pdf`).
- Endpoint baru tidak membaca `DB_CompanyName` sama sekali. Jika client tetap mengirimkannya, nilai diabaikan.
- Mempertahankan perilaku laporan (service, filter, view, format PDF) persis seperti endpoint shared, kecuali company yang diambil dari URL.
- Menjaga endpoint shared lama tetap bekerja tanpa perubahan.
- Struktur folder, nama file, dan kode baru rapi, konsisten, dan mudah dibaca, dengan komentar singkat pada kelas/fitur yang dipanggil.

## Non-goals

- Menghapus atau mengubah endpoint `/internal/ascends/shared/...`.
- Menyalin seluruh service/view shared ke tiga folder company secara fisik.
- Mengubah logika bisnis laporan (rumus, kolom, format angka).
- Menangani migrasi klien Ascend; ini hanya menyediakan endpoint baru.

## Anti-goals

- Endpoint baru yang diam-diam tetap bergantung pada `DB_CompanyName`.
- Duplikasi ~165 method controller × 3 company di `AscendXmlTestController.php`.
- Perubahan pada `ArusKasUcReportService.php` atau file laporan lain yang sedang dikerjakan terpisah.

## Constraints

- Laravel 12, PHP ^8.2.
- Route Ascends berada di `routes/api.php` dan tidak memakai middleware `report.jwt.claims`.
- Semua service laporan memiliki kontrak `buildReportDataFromXml(string $xml, string $sourceLabel = ..., array $filters = []): array`.
- View shared sudah menerima variabel `$company`, sehingga tidak perlu disalin.
- Beberapa laporan sudah punya service khusus company (mis. `BebanUmumGsuReportService`, `ArusKasRuReportService`, `FinancialRasioUcReportService`).
- Beberapa endpoint shared memakai nama path berakhir `-gsu`, `-ru`, `-uc`, mis. `arus-kas-gsu`, `beban-umum-ru`, `umur-piutang-ru`.

## Approaches yang dipertimbangkan

### 1. Alias route + injeksi `DB_CompanyName` (ditolak)

Route baru mengarah ke controller shared lama, dengan middleware mengisi `DB_CompanyName` dari URL. Paling sedikit kode, tetapi endpoint baru tetap bergantung pada `DB_CompanyName` secara internal dan bertentangan dengan constraint user.

### 2. Controller company generik + registry laporan (dipilih)

Satu controller baru menerima `company` dan `report` dari route, mengambil definisi laporan dari registry, memanggil service yang ditentukan, lalu me-render view yang ditentukan dengan `$company` dari URL. Registry dibuat sekali dari 165 route/controller shared yang ada, lalu ditinjau manual. Endpoint shared tidak diubah.

Trade-off: perlu membangun registry berisi ~165 entri dan menangani beberapa kasus khusus (multi-XML, filter tambahan, service khusus company). Karena ini dilakukan sekali dengan generator + review, risikonya terkendali dan hasilnya mudah dibaca.

### 3. Duplikasi fisik service/view ke tiga company (ditolak)

Menyalin `app/Services/Ascends/Shared` dan `resources/views/ascends/shared` ke tiga folder company. Sesuai arti harfiah “pecah folder”, tetapi menghasilkan ~1.000 file baru dan diff besar. User menyatakan refactor fisik akan dilakukan setelah rilis, jadi ini terlalu dini.

## Keputusan desain

### Bentuk URL

Pola baru:

```
POST /api/internal/ascends/{company}/<path shared tanpa segmen "shared">/pdf
```

Contoh:

```
/api/internal/ascends/gsu/finance/receivable_details/piutang-diatas-60-hari/pdf
/api/internal/ascends/ru/finance/receivable_details/piutang-diatas-60-hari/pdf
/api/internal/ascends/uc/finance/receivable_details/piutang-diatas-60-hari/pdf
```

`{company}` dibatasi `gsu|ru|uc`. Label company di PDF dan filename memakai huruf besar (`GSU`, `RU`, `UC`).

### Normalisasi laporan yang sudah bersuffix company

Aturan yang dipakai:

- Laporan shared generik (tanpa akhiran company) → tersedia di ketiga company.
- Laporan shared yang sudah bersuffix `-gsu`, `-ru`, atau `-uc` → akhiran company **dibuang** dari nama path. Company tidak lagi ditulis di nama laporan; yang menjadi penanda adalah **route prefix**, misalnya `/ru/...`. Endpoint hanya dipublish di company yang bersesuaian dengan service yang sudah ada. Contoh: `arus-kas-gsu` → `/gsu/general_ledger/trial_balance_monthly/arus-kas/pdf`; `beban-umum-ru` → `/ru/general_ledger/journal_details/beban-umum/pdf`; `umur-piutang-ru` → `/ru/finance/receivable-summary/umur-piutang/pdf`; `cek-produksi-gsu` → `/gsu/custom-report/cek-produksi/pdf`.
- Keluarga laporan yang punya tiga versi (mis. `arus-kas-*`, `beban-umum-*`, `financial-rasio-*`) masing-masing menjadi satu path generik di tiga company, dengan service khusus company yang bersesuaian.
- Route dinamis `/shared/hrm/{report}/pdf` → `/{company}/hrm/{report}/pdf` dengan daftar `whereIn` yang sama.

Keputusan ini dikonfirmasi user: suffix company dibuang, prefix URL yang menjadi penanda.

### Struktur folder dan file baru

```
app/
  Http/
    Controllers/
      Ascends/
        CompanyReportController.php        # satu endpoint generik untuk 3 company
  Support/
    Ascends/
      CompanyReportDefinition.php          # value object definisi laporan
      CompanyReportRegistry.php            # registry generik + override per company
routes/
  ascends-company.php                      # semua route company baru, di-require dari api.php
docs/
  ascends-endpoint/
    GSU/                                   # salinan Shared dengan URL GSU
    RU/                                    # salinan Shared dengan URL RU
    UC/                                    # salinan Shared dengan URL UC
tests/
  Feature/
    Ascends/
      CompanyReportRoutesTest.php          # smoke test route + representasi tiap company
```

Penamaan file mengikuti konvensi existing: `PascalCase.php` untuk kelas, `kebab-case` untuk slug URL, `snake_case` untuk view/method route name.

### Registry

`CompanyReportRegistry` menyimpan `CompanyReportDefinition` per slug laporan:

- `service`: FQCN service shared yang dipakai.
- `view`: path view Blade yang dirender (mis. `ascends.shared.finance.receivable_details.piutang_diatas_60_hari.pdf`).
- `pdf`: format, orientasi, `pdf_simple_tables`, `pdf_column_count`.
- `filename`: template/callable untuk `Content-Disposition`, disalin dari pola endpoint shared.
- `filters`: daftar kunci request yang diteruskan ke service (default: seluruh input XML selain `DB_CompanyName`, lalu ditambah `company` dan `DB_CompanyName` dari URL).
- `overrides`: service/view khusus per company jika ada.

Registry dibuat sekali oleh script generator sekali pakai yang membaca 165 route shared dan method controller associated, lalu ditinjau manual. File registry akhir adalah PHP biasa yang dapat dibaca manusia, bukan hasil runtime reflection.

Contoh entri (ilustratif):

```php
// Dibuat dari endpoint shared dan ditinjau manual.
// company = GSU/RU/UC diisi dari URL, bukan dari request.
'finance.receivable_details.piutang-diatas-60-hari' => new CompanyReportDefinition(
    service: PiutangDiatas60HariReportService::class,
    view: 'ascends.shared.finance.receivable_details.piutang_diatas_60_hari.pdf',
    pdf: ['format' => 'A4', 'orientation' => 'portrait', 'column_count' => 5],
),
```

### Controller

`CompanyReportController::pdf(...)`:

1. Validasi `company` dari route (sudah dibatasi oleh `whereIn`).
2. Ambil `report` dari route/defaults.
3. Cari definisi di registry; 404/422 bila tidak ada.
4. Ambil XML dari `GenerateAscendsEmployeeListReportRequest::xmlPayload()`.
5. Bangun `filters` dari request, membuang `DB_CompanyName` bila dikirim client, lalu menyuntik `company` dan `DB_CompanyName` yang berasal dari URL. Ini diperlukan karena service shared membaca kedua key tersebut untuk mengisi `company`, `headerCompany`, dan data lain. Client tidak perlu mengirim `DB_CompanyName`; nilai internal ini murni hasil dari segmen URL.
6. Panggil service dari container.
7. Isi `reportData['company']`, `printed_by` dari `Sys_Username`/`Sys_UserName` bila ada.
8. Render view dengan `company`, `reportData`, `generatedAt`, dan opsi PDF dari definisi.
9. Kembalikan PDF dengan `Content-Disposition` yang memakai pola filename laporan shared yang sama persis. Karena ~165 endpoint shared punya pola nama beragam (prefix laporan, `(GSU)` vs ` GSU`, serta nilai dinamis seperti `$group`, `$divisi`, `$status`, `$reportData['type']`), registry menyimpan template/callable filename per laporan yang dihasilkan dari controller shared saat generator dijalankan.

Komentar yang wajib ada:
- PHPDoc kelas controller: menjelaskan bahwa company berasal dari URL dan endpoint shared lama sengaja dibiarkan.
- PHPDoc method `pdf()`: menjelaskan alur registry → service → view.
- Komentar di registry: menjelaskan bahwa file ini generated-then-reviewed dan `DB_CompanyName` sengaja tidak dipakai.
- Komentar singkat di route file: menjelaskan kenapa route company tetap terpisah dari shared.

### Dokumentasi

- Salin 46 file doc dari `docs/ascends-endpoint/Shared/` ke tiga folder `GSU`, `RU`, `UC`.
- Ganti semua `/internal/ascends/shared/` menjadi `/internal/ascends/{company}/` (huruf kecil).
- Hapus `DB_CompanyName` dari tabel parameter dan contoh `curl`; tambahkan catatan bahwa company berasal dari URL dan cukup kirim XML.
- Nama file doc tetap mengikuti nama file shared agar mudah dibandingkan.

### Test

- `CompanyReportRoutesTest`:
  - asserted every generated route exists for all three companies,
  - asserted `DB_CompanyName` tidak dibutuhkan (request hanya `xml`),
  - asserted representative endpoint per company me-render view dengan `$company` sesuai URL,
  - asserted company-suffixed laporan tidak dipublish ke company lain.
- Mock service/PdfGenerator seperti pola test Ascend existing; tidak memangki SP/SQL Server.

## Rollout

1. Generate registry + controller + route file + doc copies.
2. Jalankan audit: `php artisan reports:audit-api`, `php artisan reports:audit-conventions`, `./vendor/bin/pint`, `php artisan test`.
3. Verifikasi sample endpoint per company dengan XML uji.
4. Deploy dengan shared endpoints tetap aktif.
5. Setelah stabil, baru refactor/penghapusan shared (di luar desain ini).

## Risiko

- Registry bisa meleset jika 165 method controller memiliki variasi filter/opsi PDF yang tidak terdeteksi generator. Mitigasi: review manual terhadap semua entri dan smoke test per kategori.
- Laporan dengan suffix company mungkin sebaiknya tetap dipublish ke ketiga company dengan service berbeda; butuh konfirmasi bila dirasa perlu.
- Dokumentasi shared yang menyebut `DB_CompanyName` perlu dibersihkan di salinan company agar tidak menyesatkan.

## Yang diserahkan ke builder

- Implementasi generator registry sekali pakai (boleh script lokal, tidak harus masuk repo).
- Detail filter per laporan bila ternyata tidak bisa diturunkan dari `$request->all()`.
- Penyesuaian nama route name bila ada benturan.

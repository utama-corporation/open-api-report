# Dokumentasi Hit Endpoint API Ascend GSU General Ledger

Dokumen ini berisi endpoint internal untuk test/render laporan General Ledger Ascend yang memakai Blade shared.

- Base host: `http://192.168.10.100:5006`
- Prefix API: `/api`

## Company

Company ditentukan dari segmen URL (`gsu`), bukan dari request. Client cukup mengirim XML.
Sebagian URL sudah dimiliki route statis lama dan tetap memakai route tersebut.

## Konsep

Template shared General Ledger dipakai supaya struktur Blade laporan bisa digunakan lintas perusahaan:

- `RU`
- `GSU`
- `UC`

Company pada title dan filename berasal dari segmen URL `/gsu/`. Client cukup mengirim XML.
Nama user print pada footer dibaca dari parameter field `Sys_Username`.

## Endpoint GSU Journal Details

- Laporan Laba Rugi (RU): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/laporan-laba-rugi/pdf`
- Laporan Pendapatan Dan Biaya Lain-Lain: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/pendapatan-dan-biaya-lain/pdf`
- Laporan Pendapatan Dan Biaya Lain-Lain Baru: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/pendapatan-dan-biaya-lain-baru/pdf`
- Laporan Beban Umum (GSU): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/beban-umum/pdf`
- Laporan Beban: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/beban/pdf`
- Laporan Beban Penjualan: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/beban-penjualan/pdf`
- Laporan Beban Penjualan Summary: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/beban-penjualan-summary/pdf`
- Laporan Biaya Upah Langsung: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/biaya-upah-langsung-detail/pdf`
- Laporan Biaya Beban Umum: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/beban-umum-baru/pdf`
- Laporan Biaya Produksi Tidak Langsung: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/biaya-produksi-tidak-langsung/pdf`
- Laporan Biaya Produksi: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/biaya-produksi/pdf`
- Laporan Ringkasan Aktiva Dalam Proses: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/ringkasan-aktiva/pdf`
- Laporan Piutang & Perhitungan Bunga (GSU): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/perhitungan-bunga/pdf`
- Laporan Laba Kotor Tahunan (GSU): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/laba-kotor/pdf`
- Laporan Laba Kotor Per Kategori: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/laba-kotor-per-kategori/pdf`
- Laporan Laba Kotor (Periode 12 Bulan/Tahunan) (RU): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/journal_details/laba-kotor-ru-12-bulan/pdf`

## Endpoint GSU Trial Balance Monthly

- Laporan Laba Rugi Multi Periode: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/laba-rugi-multi-periode/pdf`
- Laporan Laba Rugi Multi Periode (Tahunan): `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/laba-rugi-multi-periode-tahunan/pdf`
- Laporan Pendukung Arus Kas: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/pendukung-arus-kas/pdf`
- Laporan Arus Kas: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/arus-kas/pdf`

## Endpoint GSU Trial Balance

- Laporan Saldo Bank: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance/saldo-bank/pdf`
- Neraca Per Bulan: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance/neraca-per-bulan/pdf`
- Laporan Hutang UC: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance/hutang/pdf` (tidak dipublikasikan untuk GSU)
- Laporan Hutang Lain-Lain: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance/hutang-lain-lain/pdf`
- Laporan Biaya Dibayar Dimuka: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance/biaya-bayar-dimuka/pdf`
- Laporan Ringkasan Hutang Bank: `POST http://192.168.10.100:5006/api/internal/ascends/gsu/general_ledger/trial_balance/ringkasan-hutang-bank/pdf`

## Input

Parameter field utama untuk semua endpoint Ascends GSU General Ledger:

- `xml_file`: file XML dari Ascend.
- `Sys_Username`: nama user print dari parameter Crystal Report Ascend, dipakai untuk footer `Dicetak oleh`, contoh `Ridho`.

Input XML alternatif yang tetap diterima untuk kebutuhan testing:

- field `xml` berisi string XML
- raw XML body dengan `Content-Type: application/xml`

Fallback kompatibilitas lama:

- `Sys_UserName`: alias lama untuk nama user print jika `Sys_Username` belum dikirim.


### Filter Periode (Semua Endpoint)

Semua endpoint General Ledger menerima parameter periode dengan format yang sama:

- `Date.StartDate` + `Date.EndDate`: periode laporan, contoh `2026-01-01` sampai `2026-01-31`.
- Alias yang diterima: `Date_StartDate` + `Date_EndDate`, `StartDate` + `EndDate`, `start_date` + `end_date`.
- Jika tidak dikirim, sistem memakai tanggal paling awal sampai paling akhir yang tersedia di data XML.

### Filter Khusus Perhitungan Bunga (RU dan GSU)

Input tambahan khusus `perhitungan-bunga-ru` dan `perhitungan-bunga-gsu`:

- `SaldoAwal` / `saldo_awal`: saldo awal piutang untuk perhitungan bunga, dengan suku bunga tetap 1% per bulan.
- Jika tidak dikirim, nilai default `0`.

### Filter Khusus Laba Rugi RU dan UC

Input tambahan khusus `laporan-laba-rugi-ru` dan `laporan-laba-rugi-uc`:

- Periode `EndDate` menentukan bulan laporan (Bulan B). Bulan A dihitung otomatis sebagai Bulan B - 1 bulan.
- Data difilter menurut mapping kode akun (prefix `411.000`/`412.000` untuk Penjualan, `516.000` untuk HPP, `621.000` untuk Pembelian, dll).

### Filter Khusus Laba Rugi Multi Periode dan Multi Periode Tahunan

- Tidak ada parameter filter tambahan.
- Periode diambil otomatis dari rentang data XML yang tersedia.

### Filter Khusus Pendukung Arus Kas

- `PeriodStart` + `PeriodEnd`: parameter untuk menentukan dua periode yang dibandingkan, format `YYYY-MM`.
- Alias yang diterima: `period_start` + `period_end`, `start_date` + `end_date`, `StartDate` + `EndDate`.
- **Jika tidak dikirim**, sistem otomatis mengambil bulan paling awal dan paling akhir yang tersedia di data XML.
- Catatan: parameter `System_Username` / `Sys_Username` dibaca dari data XML (field `Last_x0020_Modified_x0020_By` record pertama).

Contoh `multipart/form-data` (Laporan Laba Rugi RU):

```text
Sys_Username=Ridho
Date.StartDate=2026-01-01
Date.EndDate=2026-01-31
xml_file=AnlReports.GeneralLedger.JournalDetails.xml
```

Contoh `multipart/form-data` (Laporan Beban Umum):

```text
Date.StartDate=2026-01-01
Date.EndDate=2026-01-31
xml_file=AnlReports.GeneralLedger.JournalDetails.xml
```

Contoh `multipart/form-data` (Perhitungan Bunga RU):

```text
SaldoAwal=100000000
Date.StartDate=2026-01-01
Date.EndDate=2026-01-31
xml_file=AnlReports.GeneralLedger.JournalDetails.xml
```

Contoh `multipart/form-data` (Pendukung Arus Kas):

```text
PeriodStart=2026-01
PeriodEnd=2026-02
Sys_Username=Ridho
xml_file=AnlReports.GeneralLedger.TrialBalanceMonthly.xml
```

Contoh `multipart/form-data` (Pendukung Arus Kas — auto-detect periode dari XML):

```text
Sys_Username=Ridho
xml_file=AnlReports.GeneralLedger.TrialBalanceMonthly.xml
```

Contoh `multipart/form-data` (Neraca Per Bulan):

```text
Date.StartDate=2026-01-01
Date.EndDate=2026-01-31
xml_file=AnlReports.GeneralLedger.TrialBalance.xml
```

Contoh tanpa parameter tanggal (periode otomatis dari data):

```text
Sys_Username=Ridho
xml_file=AnlReports.GeneralLedger.JournalDetails.xml
```

## Response Sukses

- `200 application/pdf`
- `Content-Disposition: inline`

Title yang tampil di halaman PDF memakai nama laporan. Nilai `GSU` berasal dari segmen URL:

```text
{Nama Laporan} (GSU)
```

Filename PDF memakai prefix section folder:

```text
{Section} - {Nama Laporan} (GSU).pdf
```

Contoh filename:

- `Journal Details - Laporan Laba Rugi (GSU).pdf`
- `Journal Details - Laporan Pendapatan Dan Biaya Lain-Lain (GSU).pdf`
- `Journal Details - Laporan Beban (GSU).pdf`
- `Journal Details - Laporan Beban Penjualan (GSU).pdf`
- `Journal Details - Laporan Beban Penjualan Summary (GSU).pdf`
- `Journal Details - Laporan Biaya Upah Langsung (GSU).pdf`
- `Journal Details - Laporan Biaya Beban Umum (GSU).pdf`
- `Journal Details - Laporan Biaya Produksi Tidak Langsung (GSU).pdf`
- `Journal Details - Laporan Biaya Produksi (GSU).pdf`
- `Journal Details - Laporan Ringkasan Aktiva Dalam Proses (GSU).pdf`
- `Journal Details - Laporan Piutang & Perhitungan Bunga RU (GSU).pdf`
- `Journal Details - Laporan Piutang & Perhitungan Bunga GSU (GSU).pdf`
- `Journal Details - Laporan Laba Kotor (GSU).pdf`
- `Journal Details - Laporan Laba Kotor Per Kategori (GSU).pdf`
- `Journal Details - Laporan Laba Kotor Tahunan (GSU).pdf`
- `Laporan Laba Rugi Multi Periode (GSU).pdf`
- `Laporan Laba Rugi Multi Periode Tahunan (GSU).pdf`
- `Laporan Pendukung Arus Kas (GSU).pdf`
- `Laporan Saldo Bank (GSU).pdf`
- `Neraca Per Bulan (GSU).pdf`
- `Laporan Hutang UC (GSU).pdf`
- `Laporan Hutang Lain-Lain (GSU).pdf`
- `Laporan Biaya Bayar Dimuka (GSU).pdf`
- `Laporan Ringkasan Hutang Bank (GSU).pdf`

## Response Gagal

- `422 application/json` jika XML kosong, tidak valid, atau tidak bisa diproses.
- `422 application/json` jika data tidak ditemukan untuk periode yang diminta.

## Template shared General Ledger Tersedia

Template Blade shared Journal Details berada di `resources/views/ascends/shared/general_ledger/journal_details`.

- `journal_details/laporan_laba_rugi_ru`
- `journal_details/laporan_laba_rugi_uc`
- `journal_details/pendapatan_dan_biaya_lain`
- `journal_details/pendapatan_dan_biaya_lain_baru`
- `journal_details/beban_umum_ru`
- `journal_details/beban_umum_uc`
- `journal_details/beban_umum_gsu`
- `journal_details/beban_umum_baru`
- `journal_details/beban`
- `journal_details/beban_penjualan`
- `journal_details/beban_penjualan_summary`
- `journal_details/biaya_upah_langsung_detail`
- `journal_details/biaya_produksi_tidak_langsung`
- `journal_details/biaya_produksi`
- `journal_details/ringkasan_aktiva`
- `journal_details/perhitungan_bunga_ru`
- `journal_details/perhitungan_bunga_gsu`
- `journal_details/laba_kotor_ru`
- `journal_details/laba_kotor_per_kategori`
- `journal_details/laba_kotor_gsu`

Template Blade shared Trial Balance Monthly berada di `resources/views/ascends/shared/general_ledger/trial_balance_monthly`.

- `trial_balance_monthly/laba_rugi_multi_periode`
- `trial_balance_monthly/laba_rugi_multi_periode_tahunan`
- `trial_balance_monthly/pendukung_arus_kas`

Template Blade shared Trial Balance berada di `resources/views/ascends/shared/general_ledger/trial_balance`.

- `trial_balance/saldo_bank`
- `trial_balance/neraca_per_bulan`
- `trial_balance/hutang_uc`
- `trial_balance/hutang_lainnya`
- `trial_balance/biaya_bayar_dimuka`
- `trial_balance/ringkasan_hutang_bank`

Catatan: semua endpoint di atas memakai pola shared yang sama. XML menjadi sumber data laporan; company pada title dan filename berasal dari segmen URL.

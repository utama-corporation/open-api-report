# Desain — Laporan Timeline KB - Bulanan (Rambung) MGNT

## Intent & why
Tambah satu laporan di menu Management memakai SP `SP_LapTimelineKBBulananKG` yang sama seperti Laporan Timeline KB - Bulanan (Rambung), tetapi tampilan PDF berbeda: per bulan ada `Qty | Rp`, dan setelah Total ada `Min | Max | Avg`.

## Goals / non-goals
- Goals: header bertingkat Tahun → Bulan → Qty/Rp, Total satu kolom Qty (seperti laporan Rambung, tanpa Rp), Min/Max/Avg per supplier, tabel fallback bila pivot tidak valid, route web + API + menu Management.
- Non-goals: SP baru, kolom SP baru, perubahan laporan Rambung existing (kecuali `Harga` ditambahkan ke expected_columns), material baru.

## Constraints
- `Qty` dari `TonBerat`, `Rp` dari kolom `Harga` (alias Rp/Nilai didukung, fallback kolom numerik kedua).
- `Min/Max/Avg` dihitung dari nilai `Qty` bulanan per supplier yang tampil; bulan kosong tidak ikut dihitung (diganti koreksi terakhir).
- Pola laporan standar: Controller + Service + FormRequest + Blade, `BaseReportRequest` tanpa override `failedValidation`, PDF hanya via `PdfGenerator`.

## Approaches
- Dipilih (pendekatan 1): Controller + 2 Blade baru, service/request subclass tipis mewarisi executor SP lama — SP/config tidak diduplikasi, view sepenuhnya baru.
- Ditolak (pendekatan 2): duplikasi service/request penuh — terisolasi tapi ~300 baris duplikasi untuk SP yang sama.

## Parts
- `TimelineKbBulananMgntReportService` extends `TimelineKayuBulatBulananKgReportService`.
- `GenerateTimelineKbBulananMgntReportRequest` extends `GenerateTimelineKayuBulatBulananKgReportRequest`.
- `TimelineKbBulananMgntController`: `index`, `download`, `previewPdf`, `preview`, `health`; `pdf_column_count = bulan*2 + 6`.
- Blade form `timeline-kb-bulanan-mgnt-form`, PDF `timeline-kb-bulanan-mgnt-pdf` dengan pivot `qty/rp` per bulan, `total_qty` satu kolom, statistik hanya dari bulan tampil, baris Grand Total.
- Route: `reports.management.timeline-kb-bulanan-mgnt.*` (web) dan `api.reports.management.timeline-kb-bulanan-mgnt` (API), tombol di Management `welcome.blade.php`.

## Left to builder
Nama kolom SP selain `TonBerat`/`Harga` dideteksi fleksibel; format angka 0 desimal mengikuti laporan Rambung; Avg dibulatkan seperti nilai lain.

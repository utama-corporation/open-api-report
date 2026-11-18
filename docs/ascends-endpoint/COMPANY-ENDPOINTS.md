# Daftar Endpoint Laporan Ascends per Company

Total: **477 endpoint** (GSU 161, RU 165, UC 151).

Semua endpoint adalah `POST`.

## Catatan perubahan

- Seluruh endpoint HRM `employee-list` untuk GSU/RU/UC memakai satu view `resources/views/ascends/shared/hrm/...`.
- Empat laporan berikut sekarang **combined**: halaman pertama Diagram, halaman berikutnya List, dengan judul berakhiran `(Diagram)`:
  - Karyawan Per Departemen Per Jabatan
  - Karyawan Per Level
  - Karyawan Per Masa Kerja
  - Karyawan Usia Generasi Berdasarkan Tahun Kelahiran dan Masa Kerja
- Route diagram lama (`/hrm/employee-list/diagram-karyawan-*`) tetap tersedia untuk backward compatibility.
- Endpoint modul `sales` masih dilayani controller legacy `AscendXmlTestController`; endpoint lain memakai `CompanyReportController`.

## GSU (161 endpoint)

### associate (4)

- `/api/internal/ascends/gsu/associate/customer-baru-per-tahun/pdf`
- `/api/internal/ascends/gsu/associate/customer-baru/pdf`
- `/api/internal/ascends/gsu/associate/customer-modifikasi-6-bulan/pdf`
- `/api/internal/ascends/gsu/associate/list-customer-per-kota/pdf`

### custom-report (29)

- `/api/internal/ascends/gsu/custom-report/bank-account-daily-cash/pdf`
- `/api/internal/ascends/gsu/custom-report/biaya-mobil-truk/pdf`
- `/api/internal/ascends/gsu/custom-report/budgeting-realisasi-biaya-promosi-penjualan/pdf`
- `/api/internal/ascends/gsu/custom-report/cek-produksi/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-enamel-akun-spesial/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-enamel-all/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-enamel-grosir/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-enamel-retail/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-enamel-semi-grosir/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-furniture-akun-spesial/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-furniture-all/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-furniture-grosir/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-furniture-retail/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-furniture-sales-project/pdf`
- `/api/internal/ascends/gsu/custom-report/check-price-group-a/daftar-harga-furniture-semi-grosir/pdf`
- `/api/internal/ascends/gsu/custom-report/customer-over-limit/pdf`
- `/api/internal/ascends/gsu/custom-report/item-discontinue/pdf`
- `/api/internal/ascends/gsu/custom-report/monitoring-so-si-tagihan/pdf`
- `/api/internal/ascends/gsu/custom-report/pembelian-kayu-per-periode/pdf`
- `/api/internal/ascends/gsu/custom-report/pengiriman-kursi-dan-meja-harian/pdf`
- `/api/internal/ascends/gsu/custom-report/pengiriman-lemari-harian/pdf`
- `/api/internal/ascends/gsu/custom-report/pengiriman-lemari-tahunan/pdf`
- `/api/internal/ascends/gsu/custom-report/pengiriman-per-kategori-harian/pdf`
- `/api/internal/ascends/gsu/custom-report/pengiriman-per-kategori-tahunan/pdf`
- `/api/internal/ascends/gsu/custom-report/penjualan-per-item-analisa-sku-detail/pdf`
- `/api/internal/ascends/gsu/custom-report/penjualan-per-item-analisa-sku/pdf`
- `/api/internal/ascends/gsu/custom-report/penjualan-per-kategori-barang-bulanan/pdf`
- `/api/internal/ascends/gsu/custom-report/penjualan-vs-biaya-perjalanan-dinas/pdf`
- `/api/internal/ascends/gsu/custom-report/sales-summary-per-customer-per-period/pdf`

### finance (10)

- `/api/internal/ascends/gsu/finance/receipt_voucher_details/giro-cash-transfer/pdf`
- `/api/internal/ascends/gsu/finance/receipt_voucher_details/pelunasan/pdf`
- `/api/internal/ascends/gsu/finance/receipt_voucher_details/penerimaan-piutang/pdf`
- `/api/internal/ascends/gsu/finance/receipt_voucher_details/penerimaan-voucher/pdf`
- `/api/internal/ascends/gsu/finance/receivable_details/piutang-cash-14-hari/pdf`
- `/api/internal/ascends/gsu/finance/receivable_details/piutang-diatas-120-hari/pdf`
- `/api/internal/ascends/gsu/finance/receivable_details/piutang-diatas-45-hari/pdf`
- `/api/internal/ascends/gsu/finance/receivable_details/piutang-diatas-60-hari/pdf`
- `/api/internal/ascends/gsu/finance/receivable_details/piutang-semua/pdf`
- `/api/internal/ascends/gsu/finance/receivable_details/piutang-tak-tertagih-90-hari/pdf`

### fixed_asset (1)

- `/api/internal/ascends/gsu/fixed_asset/asset_summary/penyusutan_aktiva/pdf`

### general_ledger (28)

- `/api/internal/ascends/gsu/general_ledger/journal_details/beban-penjualan-summary/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/beban-penjualan/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/beban-umum-baru/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/beban-umum/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/beban/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/biaya-produksi-tidak-langsung/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/biaya-produksi/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/biaya-upah-langsung-detail/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/laba-kotor-per-kategori/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/laba-kotor-ru-12-bulan/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/laba-kotor/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/laporan-laba-rugi/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/pendapatan-dan-biaya-lain-baru/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/pendapatan-dan-biaya-lain/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/perhitungan-bunga/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/pinjaman-karyawan/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/rekap-biaya-laba-rugi/pdf`
- `/api/internal/ascends/gsu/general_ledger/journal_details/ringkasan-aktiva/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance/biaya-bayar-dimuka/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance/hutang-lain-lain/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance/neraca-per-bulan/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance/ringkasan-hutang-bank/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance/saldo-bank/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/arus-kas/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/financial-rasio/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/laba-rugi-multi-periode-tahunan/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/laba-rugi-multi-periode/pdf`
- `/api/internal/ascends/gsu/general_ledger/trial_balance_monthly/pendukung-arus-kas/pdf`

### hrm (55)

- `/api/internal/ascends/gsu/hrm/absence/ketidakhadiran-bulanan/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/absensi-briefing-harian/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/absensi-individu/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/data-peserta-makan-siang-ibadah-aula-per-departemen/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/data-peserta-makan-siang-shalat-jumat-per-departemen/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/kehadiran-kru-bahan-baku/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/kehadiran-kru-racip/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/kehadiran-kru-stick/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/pengabaian-keterlambatan-kehadiran-manual/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/persentase-kehadiran-bulanan/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/persentase-kehadiran-mingguan-per-departemen/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/rekapitulasi-absensi-briefing-harian/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/rekapitulasi-kehadiran-kurang-93-tahunan/pdf`
- `/api/internal/ascends/gsu/hrm/attendance-full/rekapitulasi-pengabaian-keterlambatan-tahunan/pdf`
- `/api/internal/ascends/gsu/hrm/attendance/keterlambatan-kehadiran-briefing-harian/pdf`
- `/api/internal/ascends/gsu/hrm/attendance/perbandingan-kehadiran-per-bulan/pdf`
- `/api/internal/ascends/gsu/hrm/custom-reports/diagram-lembur-tahunan/pdf`
- `/api/internal/ascends/gsu/hrm/custom-reports/karyawan-keluar-tahunan/pdf`
- `/api/internal/ascends/gsu/hrm/custom-reports/karyawan-keluar/pdf`
- `/api/internal/ascends/gsu/hrm/custom-reports/karyawan-masuk/pdf`
- `/api/internal/ascends/gsu/hrm/custom-reports/lembur-bulanan/pdf`
- `/api/internal/ascends/gsu/hrm/custom-reports/surat-peringatan/pdf`
- `/api/internal/ascends/gsu/hrm/custom-reports/verifikasi-lembur/pdf`
- `/api/internal/ascends/gsu/hrm/daftar-karyawan-berdasarkan-abjad/pdf`
- `/api/internal/ascends/gsu/hrm/daftar-karyawan/pdf`
- `/api/internal/ascends/gsu/hrm/data-karyawan-status-kerja/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/diagram-karyawan-per-departemen/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/diagram-karyawan-per-divisi/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/diagram-karyawan-per-jenis-kelamin/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/diagram-karyawan-per-level/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/diagram-karyawan-per-masa-kerja/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/diagram-karyawan-per-strata-pendidikan/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/diagram-karyawan-per-usia-generasi/pdf`
- `/api/internal/ascends/gsu/hrm/employee-list/mpp-tahunan-per-divisi/pdf`
- `/api/internal/ascends/gsu/hrm/employee-termination/pdf`
- `/api/internal/ascends/gsu/hrm/holiday/daftar-libur-cuti-bersama/pdf`
- `/api/internal/ascends/gsu/hrm/karyawan-aktif-per-departemen/pdf`
- `/api/internal/ascends/gsu/hrm/karyawan-masuk-per-departemen-per-tanggal-masuk/pdf`
- `/api/internal/ascends/gsu/hrm/karyawan-per-agama/pdf`
- `/api/internal/ascends/gsu/hrm/karyawan-per-departemen-per-jabatan/pdf` — **Diagram + List**
- `/api/internal/ascends/gsu/hrm/karyawan-per-etnis/pdf`
- `/api/internal/ascends/gsu/hrm/karyawan-per-level/pdf` — **Diagram + List**
- `/api/internal/ascends/gsu/hrm/karyawan-per-masa-kerja/pdf` — **Diagram + List**
- `/api/internal/ascends/gsu/hrm/karyawan-per-umur/pdf`
- `/api/internal/ascends/gsu/hrm/kehadiran-kk-kt-st/pdf`
- `/api/internal/ascends/gsu/hrm/late-sign-in/durasi-denda-keterlambatan/pdf`
- `/api/internal/ascends/gsu/hrm/list-karyawan-habis-kontrak/pdf`
- `/api/internal/ascends/gsu/hrm/list-karyawan/pdf`
- `/api/internal/ascends/gsu/hrm/loss-time/pdf`
- `/api/internal/ascends/gsu/hrm/other-income-deduction/pendapatan-lain-lain/pdf`
- `/api/internal/ascends/gsu/hrm/overtime/lembur-bulanan/pdf`
- `/api/internal/ascends/gsu/hrm/perbandingan-jumlah-karyawan-tahunan-per-bulan/pdf`
- `/api/internal/ascends/gsu/hrm/thr/pdf`
- `/api/internal/ascends/gsu/hrm/usia-generasi-tahun-kelahiran-masa-kerja/pdf` — **Diagram + List**
- `/api/internal/ascends/gsu/hrm/warning-notice/surat-peringatan/pdf`

### inventory_analysis (28)

- `/api/internal/ascends/gsu/inventory_analysis/adjustment-by-item/adjustment/adjustment-lemari/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/adjustment-by-item/adjustment/khusus-kursi/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/adjustment-by-item/adjustment/khusus-lemari/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/adjustment-by-item/goods-delivery-note/pengiriman-lemari/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/adjustment-by-item/goods-delivery-note/rekapan-value-surat-jalan/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/adjustment-by-item/penyesuaian-persediaan/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/outstanding-undelivery-goods/do-customer-belum-terkirim/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/outstanding-undelivery-goods/do-lemari-belum-terkirim/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/outstanding-undelivery-goods/do-per-kategori-belum-terkirim/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/outstanding-undelivery-goods/list-do-belum-terkirim/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production/hasil-broker-per-hari/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production/hasil-broker-per-kategori/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production/hasil-broker-per-mesin/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production/hasil-cuci-per-hari/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production/hasil-cuci-per-mesin/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production/hasil-cuci-per-supplier/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production/hasil-produksi-per-mesin/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production_by_item/produksi-per-minggu/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/production_by_item/produksi/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/purchase-order-by-item/history-harga-po/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/purchase-order-by-item/jangka-waktu-po-pi/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/purchase-request-by-item/jangka-waktu-approve-pr-po-detail/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/sales-by-item/target-sales-per-minggu/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/stock-activities-summary/aktifitas-stock-gsu-per-gudang/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/stock-activities-summary/aktifitas-stock/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/stock-activities-summary/khusus-plastik-kabinet/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/stock-activities-summary/laporan-hpp-dan-stock/pdf`
- `/api/internal/ascends/gsu/inventory_analysis/stock-blanace/saldo-stok-barang-per-gudang/pdf`

### sales (6)

- `/api/internal/ascends/gsu/sales/sales-invoice/normal/pdf` — legacy controller
- `/api/internal/ascends/gsu/sales/sales-invoice/panjang/pdf` — legacy controller
- `/api/internal/ascends/gsu/sales/sales-invoice/pdf` — legacy controller
- `/api/internal/ascends/gsu/sales/surat-jalan/normal/pdf` — legacy controller
- `/api/internal/ascends/gsu/sales/surat-jalan/panjang/pdf` — legacy controller
- `/api/internal/ascends/gsu/sales/surat-jalan/pdf` — legacy controller

## RU (165 endpoint)

### associate (4)

- `/api/internal/ascends/ru/associate/customer-baru-per-tahun/pdf`
- `/api/internal/ascends/ru/associate/customer-baru/pdf`
- `/api/internal/ascends/ru/associate/customer-modifikasi-6-bulan/pdf`
- `/api/internal/ascends/ru/associate/list-customer-per-kota/pdf`

### custom-report (28)

- `/api/internal/ascends/ru/custom-report/bank-account-daily-cash/pdf`
- `/api/internal/ascends/ru/custom-report/biaya-mobil-truk/pdf`
- `/api/internal/ascends/ru/custom-report/budgeting-realisasi-biaya-promosi-penjualan/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-enamel-akun-spesial/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-enamel-all/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-enamel-grosir/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-enamel-retail/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-enamel-semi-grosir/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-furniture-akun-spesial/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-furniture-all/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-furniture-grosir/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-furniture-retail/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-furniture-sales-project/pdf`
- `/api/internal/ascends/ru/custom-report/check-price-group-a/daftar-harga-furniture-semi-grosir/pdf`
- `/api/internal/ascends/ru/custom-report/customer-over-limit/pdf`
- `/api/internal/ascends/ru/custom-report/item-discontinue/pdf`
- `/api/internal/ascends/ru/custom-report/monitoring-so-si-tagihan/pdf`
- `/api/internal/ascends/ru/custom-report/pembelian-kayu-per-periode/pdf`
- `/api/internal/ascends/ru/custom-report/pengiriman-kursi-dan-meja-harian/pdf`
- `/api/internal/ascends/ru/custom-report/pengiriman-lemari-harian/pdf`
- `/api/internal/ascends/ru/custom-report/pengiriman-lemari-tahunan/pdf`
- `/api/internal/ascends/ru/custom-report/pengiriman-per-kategori-harian/pdf`
- `/api/internal/ascends/ru/custom-report/pengiriman-per-kategori-tahunan/pdf`
- `/api/internal/ascends/ru/custom-report/penjualan-per-item-analisa-sku-detail/pdf`
- `/api/internal/ascends/ru/custom-report/penjualan-per-item-analisa-sku/pdf`
- `/api/internal/ascends/ru/custom-report/penjualan-per-kategori-barang-bulanan/pdf`
- `/api/internal/ascends/ru/custom-report/penjualan-vs-biaya-perjalanan-dinas/pdf`
- `/api/internal/ascends/ru/custom-report/sales-summary-per-customer-per-period/pdf`

### finance (13)

- `/api/internal/ascends/ru/finance/outstanding-payable-check/hutang-giro/pdf`
- `/api/internal/ascends/ru/finance/payable-summary/saldo-hutang/pdf`
- `/api/internal/ascends/ru/finance/receipt_voucher_details/giro-cash-transfer/pdf`
- `/api/internal/ascends/ru/finance/receipt_voucher_details/pelunasan/pdf`
- `/api/internal/ascends/ru/finance/receipt_voucher_details/penerimaan-piutang/pdf`
- `/api/internal/ascends/ru/finance/receipt_voucher_details/penerimaan-voucher/pdf`
- `/api/internal/ascends/ru/finance/receivable-summary/umur-piutang/pdf`
- `/api/internal/ascends/ru/finance/receivable_details/piutang-cash-14-hari/pdf`
- `/api/internal/ascends/ru/finance/receivable_details/piutang-diatas-120-hari/pdf`
- `/api/internal/ascends/ru/finance/receivable_details/piutang-diatas-45-hari/pdf`
- `/api/internal/ascends/ru/finance/receivable_details/piutang-diatas-60-hari/pdf`
- `/api/internal/ascends/ru/finance/receivable_details/piutang-semua/pdf`
- `/api/internal/ascends/ru/finance/receivable_details/piutang-tak-tertagih-90-hari/pdf`

### fixed_asset (1)

- `/api/internal/ascends/ru/fixed_asset/asset_summary/penyusutan_aktiva/pdf`

### general_ledger (28)

- `/api/internal/ascends/ru/general_ledger/journal_details/beban-penjualan-summary/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/beban-penjualan/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/beban-umum-baru/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/beban-umum/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/beban/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/biaya-produksi-tidak-langsung/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/biaya-produksi/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/biaya-upah-langsung-detail/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/laba-kotor-per-kategori/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/laba-kotor-ru-12-bulan/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/laba-kotor/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/laporan-laba-rugi/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/pendapatan-dan-biaya-lain-baru/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/pendapatan-dan-biaya-lain/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/perhitungan-bunga/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/pinjaman-karyawan/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/rekap-biaya-laba-rugi/pdf`
- `/api/internal/ascends/ru/general_ledger/journal_details/ringkasan-aktiva/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance/biaya-bayar-dimuka/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance/hutang-lain-lain/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance/neraca-per-bulan/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance/ringkasan-hutang-bank/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance/saldo-bank/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance_monthly/arus-kas/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance_monthly/financial-rasio/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance_monthly/laba-rugi-multi-periode-tahunan/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance_monthly/laba-rugi-multi-periode/pdf`
- `/api/internal/ascends/ru/general_ledger/trial_balance_monthly/pendukung-arus-kas/pdf`

### hrm (54)

- `/api/internal/ascends/ru/hrm/absence/ketidakhadiran-bulanan/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/absensi-briefing-harian/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/absensi-individu/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/data-peserta-makan-siang-ibadah-aula-per-departemen/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/data-peserta-makan-siang-shalat-jumat-per-departemen/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/kehadiran-kru-bahan-baku/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/kehadiran-kru-racip/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/kehadiran-kru-stick/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/pengabaian-keterlambatan-kehadiran-manual/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/persentase-kehadiran-bulanan/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/persentase-kehadiran-mingguan-per-departemen/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/rekapitulasi-absensi-briefing-harian/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/rekapitulasi-kehadiran-kurang-93-tahunan/pdf`
- `/api/internal/ascends/ru/hrm/attendance-full/rekapitulasi-pengabaian-keterlambatan-tahunan/pdf`
- `/api/internal/ascends/ru/hrm/attendance/keterlambatan-kehadiran-briefing-harian/pdf`
- `/api/internal/ascends/ru/hrm/attendance/perbandingan-kehadiran-per-bulan/pdf`
- `/api/internal/ascends/ru/hrm/custom-reports/diagram-lembur-tahunan/pdf`
- `/api/internal/ascends/ru/hrm/custom-reports/karyawan-keluar-tahunan/pdf`
- `/api/internal/ascends/ru/hrm/custom-reports/karyawan-keluar/pdf`
- `/api/internal/ascends/ru/hrm/custom-reports/karyawan-masuk/pdf`
- `/api/internal/ascends/ru/hrm/custom-reports/lembur-bulanan/pdf`
- `/api/internal/ascends/ru/hrm/custom-reports/surat-peringatan/pdf`
- `/api/internal/ascends/ru/hrm/custom-reports/verifikasi-lembur/pdf`
- `/api/internal/ascends/ru/hrm/daftar-karyawan-berdasarkan-abjad/pdf`
- `/api/internal/ascends/ru/hrm/daftar-karyawan/pdf`
- `/api/internal/ascends/ru/hrm/data-karyawan-status-kerja/pdf`
- `/api/internal/ascends/ru/hrm/employee-list/diagram-karyawan-per-departemen/pdf`
- `/api/internal/ascends/ru/hrm/employee-list/diagram-karyawan-per-divisi/pdf`
- `/api/internal/ascends/ru/hrm/employee-list/diagram-karyawan-per-jenis-kelamin/pdf`
- `/api/internal/ascends/ru/hrm/employee-list/diagram-karyawan-per-level/pdf`
- `/api/internal/ascends/ru/hrm/employee-list/diagram-karyawan-per-masa-kerja/pdf`
- `/api/internal/ascends/ru/hrm/employee-list/diagram-karyawan-per-strata-pendidikan/pdf`
- `/api/internal/ascends/ru/hrm/employee-list/diagram-karyawan-per-usia-generasi/pdf`
- `/api/internal/ascends/ru/hrm/employee-termination/pdf`
- `/api/internal/ascends/ru/hrm/holiday/daftar-libur-cuti-bersama/pdf`
- `/api/internal/ascends/ru/hrm/karyawan-aktif-per-departemen/pdf`
- `/api/internal/ascends/ru/hrm/karyawan-masuk-per-departemen-per-tanggal-masuk/pdf`
- `/api/internal/ascends/ru/hrm/karyawan-per-agama/pdf`
- `/api/internal/ascends/ru/hrm/karyawan-per-departemen-per-jabatan/pdf` — **Diagram + List**
- `/api/internal/ascends/ru/hrm/karyawan-per-etnis/pdf`
- `/api/internal/ascends/ru/hrm/karyawan-per-level/pdf` — **Diagram + List**
- `/api/internal/ascends/ru/hrm/karyawan-per-masa-kerja/pdf` — **Diagram + List**
- `/api/internal/ascends/ru/hrm/karyawan-per-umur/pdf`
- `/api/internal/ascends/ru/hrm/kehadiran-kk-kt-st/pdf`
- `/api/internal/ascends/ru/hrm/late-sign-in/durasi-denda-keterlambatan/pdf`
- `/api/internal/ascends/ru/hrm/list-karyawan-habis-kontrak/pdf`
- `/api/internal/ascends/ru/hrm/list-karyawan/pdf`
- `/api/internal/ascends/ru/hrm/loss-time/pdf`
- `/api/internal/ascends/ru/hrm/other-income-deduction/pendapatan-lain-lain/pdf`
- `/api/internal/ascends/ru/hrm/overtime/lembur-bulanan/pdf`
- `/api/internal/ascends/ru/hrm/perbandingan-jumlah-karyawan-tahunan-per-bulan/pdf`
- `/api/internal/ascends/ru/hrm/thr/pdf`
- `/api/internal/ascends/ru/hrm/usia-generasi-tahun-kelahiran-masa-kerja/pdf` — **Diagram + List**
- `/api/internal/ascends/ru/hrm/warning-notice/surat-peringatan/pdf`

### inventory_analysis (31)

- `/api/internal/ascends/ru/inventory_analysis/adjustment-by-item/adjustment/adjustment-lemari/pdf`
- `/api/internal/ascends/ru/inventory_analysis/adjustment-by-item/adjustment/khusus-kursi/pdf`
- `/api/internal/ascends/ru/inventory_analysis/adjustment-by-item/adjustment/khusus-lemari/pdf`
- `/api/internal/ascends/ru/inventory_analysis/adjustment-by-item/goods-delivery-note/pengiriman-lemari/pdf`
- `/api/internal/ascends/ru/inventory_analysis/adjustment-by-item/goods-delivery-note/rekapan-value-surat-jalan/pdf`
- `/api/internal/ascends/ru/inventory_analysis/adjustment-by-item/penyesuaian-persediaan/pdf`
- `/api/internal/ascends/ru/inventory_analysis/outstanding-undelivery-goods/do-customer-belum-terkirim/pdf`
- `/api/internal/ascends/ru/inventory_analysis/outstanding-undelivery-goods/do-lemari-belum-terkirim/pdf`
- `/api/internal/ascends/ru/inventory_analysis/outstanding-undelivery-goods/do-per-kategori-belum-terkirim/pdf`
- `/api/internal/ascends/ru/inventory_analysis/outstanding-undelivery-goods/list-do-belum-terkirim/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production/hasil-broker-per-hari/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production/hasil-broker-per-kategori/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production/hasil-broker-per-mesin/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production/hasil-cuci-per-hari/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production/hasil-cuci-per-mesin/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production/hasil-cuci-per-supplier/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production/hasil-produksi-per-mesin/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production_by_item/produksi-per-minggu/pdf`
- `/api/internal/ascends/ru/inventory_analysis/production_by_item/produksi/pdf`
- `/api/internal/ascends/ru/inventory_analysis/purchase-by-item/ringkasan-pembelian/pdf`
- `/api/internal/ascends/ru/inventory_analysis/purchase-order-by-item/history-harga-po/pdf`
- `/api/internal/ascends/ru/inventory_analysis/purchase-order-by-item/jangka-waktu-po-pi/pdf`
- `/api/internal/ascends/ru/inventory_analysis/purchase-request-by-item/jangka-waktu-approve-pr-po-detail/pdf`
- `/api/internal/ascends/ru/inventory_analysis/sales-by-item/penjualan-per-group-bulanan/pdf`
- `/api/internal/ascends/ru/inventory_analysis/sales-by-item/persentase-hpp-penjualan-per-item-family/pdf`
- `/api/internal/ascends/ru/inventory_analysis/sales-by-item/target-sales-per-minggu/pdf`
- `/api/internal/ascends/ru/inventory_analysis/stock-activities-summary/aktifitas-stock-gsu-per-gudang/pdf`
- `/api/internal/ascends/ru/inventory_analysis/stock-activities-summary/aktifitas-stock/pdf`
- `/api/internal/ascends/ru/inventory_analysis/stock-activities-summary/khusus-plastik-kabinet/pdf`
- `/api/internal/ascends/ru/inventory_analysis/stock-activities-summary/laporan-hpp-dan-stock/pdf`
- `/api/internal/ascends/ru/inventory_analysis/stock-blanace/saldo-stok-barang-per-gudang/pdf`

### sales (6)

- `/api/internal/ascends/ru/sales/sales-invoice/normal/pdf` — legacy controller
- `/api/internal/ascends/ru/sales/sales-invoice/panjang/pdf` — legacy controller
- `/api/internal/ascends/ru/sales/sales-invoice/pdf` — legacy controller
- `/api/internal/ascends/ru/sales/surat-jalan/normal/pdf` — legacy controller
- `/api/internal/ascends/ru/sales/surat-jalan/panjang/pdf` — legacy controller
- `/api/internal/ascends/ru/sales/surat-jalan/pdf` — legacy controller

## UC (151 endpoint)

### associate (4)

- `/api/internal/ascends/uc/associate/customer-baru-per-tahun/pdf`
- `/api/internal/ascends/uc/associate/customer-baru/pdf`
- `/api/internal/ascends/uc/associate/customer-modifikasi-6-bulan/pdf`
- `/api/internal/ascends/uc/associate/list-customer-per-kota/pdf`

### custom-report (28)

- `/api/internal/ascends/uc/custom-report/bank-account-daily-cash/pdf`
- `/api/internal/ascends/uc/custom-report/biaya-mobil-truk/pdf`
- `/api/internal/ascends/uc/custom-report/budgeting-realisasi-biaya-promosi-penjualan/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-enamel-akun-spesial/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-enamel-all/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-enamel-grosir/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-enamel-retail/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-enamel-semi-grosir/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-furniture-akun-spesial/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-furniture-all/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-furniture-grosir/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-furniture-retail/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-furniture-sales-project/pdf`
- `/api/internal/ascends/uc/custom-report/check-price-group-a/daftar-harga-furniture-semi-grosir/pdf`
- `/api/internal/ascends/uc/custom-report/customer-over-limit/pdf`
- `/api/internal/ascends/uc/custom-report/item-discontinue/pdf`
- `/api/internal/ascends/uc/custom-report/monitoring-so-si-tagihan/pdf`
- `/api/internal/ascends/uc/custom-report/pembelian-kayu-per-periode/pdf`
- `/api/internal/ascends/uc/custom-report/pengiriman-kursi-dan-meja-harian/pdf`
- `/api/internal/ascends/uc/custom-report/pengiriman-lemari-harian/pdf`
- `/api/internal/ascends/uc/custom-report/pengiriman-lemari-tahunan/pdf`
- `/api/internal/ascends/uc/custom-report/pengiriman-per-kategori-harian/pdf`
- `/api/internal/ascends/uc/custom-report/pengiriman-per-kategori-tahunan/pdf`
- `/api/internal/ascends/uc/custom-report/penjualan-per-item-analisa-sku-detail/pdf`
- `/api/internal/ascends/uc/custom-report/penjualan-per-item-analisa-sku/pdf`
- `/api/internal/ascends/uc/custom-report/penjualan-per-kategori-barang-bulanan/pdf`
- `/api/internal/ascends/uc/custom-report/penjualan-vs-biaya-perjalanan-dinas/pdf`
- `/api/internal/ascends/uc/custom-report/sales-summary-per-customer-per-period/pdf`

### finance (10)

- `/api/internal/ascends/uc/finance/receipt_voucher_details/giro-cash-transfer/pdf`
- `/api/internal/ascends/uc/finance/receipt_voucher_details/pelunasan/pdf`
- `/api/internal/ascends/uc/finance/receipt_voucher_details/penerimaan-piutang/pdf`
- `/api/internal/ascends/uc/finance/receipt_voucher_details/penerimaan-voucher/pdf`
- `/api/internal/ascends/uc/finance/receivable_details/piutang-cash-14-hari/pdf`
- `/api/internal/ascends/uc/finance/receivable_details/piutang-diatas-120-hari/pdf`
- `/api/internal/ascends/uc/finance/receivable_details/piutang-diatas-45-hari/pdf`
- `/api/internal/ascends/uc/finance/receivable_details/piutang-diatas-60-hari/pdf`
- `/api/internal/ascends/uc/finance/receivable_details/piutang-semua/pdf`
- `/api/internal/ascends/uc/finance/receivable_details/piutang-tak-tertagih-90-hari/pdf`

### fixed_asset (1)

- `/api/internal/ascends/uc/fixed_asset/asset_summary/penyusutan_aktiva/pdf`

### general-ledger (1)

- `/api/internal/ascends/uc/general-ledger/journal-details/laba-rugi/pdf`

### general_ledger (27)

- `/api/internal/ascends/uc/general_ledger/journal_details/beban-penjualan-summary/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/beban-penjualan/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/beban-umum-baru/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/beban-umum/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/beban/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/biaya-produksi-tidak-langsung/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/biaya-produksi/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/biaya-upah-langsung-detail/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/laba-kotor-per-kategori/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/laba-kotor-ru-12-bulan/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/laporan-laba-rugi/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/pendapatan-dan-biaya-lain-baru/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/pendapatan-dan-biaya-lain/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/pinjaman-karyawan/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/rekap-biaya-laba-rugi/pdf`
- `/api/internal/ascends/uc/general_ledger/journal_details/ringkasan-aktiva/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance/biaya-bayar-dimuka/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance/hutang-lain-lain/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance/hutang/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance/neraca-per-bulan/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance/ringkasan-hutang-bank/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance/saldo-bank/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance_monthly/arus-kas/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance_monthly/financial-rasio/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance_monthly/laba-rugi-multi-periode-tahunan/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance_monthly/laba-rugi-multi-periode/pdf`
- `/api/internal/ascends/uc/general_ledger/trial_balance_monthly/pendukung-arus-kas/pdf`

### hrm (53)

- `/api/internal/ascends/uc/hrm/absence/ketidakhadiran-bulanan/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/absensi-briefing-harian/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/absensi-individu/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/data-peserta-makan-siang-ibadah-aula-per-departemen/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/data-peserta-makan-siang-shalat-jumat-per-departemen/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/kehadiran-kru-bahan-baku/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/kehadiran-kru-racip/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/kehadiran-kru-stick/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/pengabaian-keterlambatan-kehadiran-manual/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/persentase-kehadiran-bulanan/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/persentase-kehadiran-mingguan-per-departemen/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/rekapitulasi-kehadiran-kurang-93-tahunan/pdf`
- `/api/internal/ascends/uc/hrm/attendance-full/rekapitulasi-pengabaian-keterlambatan-tahunan/pdf`
- `/api/internal/ascends/uc/hrm/attendance/keterlambatan-kehadiran-briefing-harian/pdf`
- `/api/internal/ascends/uc/hrm/attendance/perbandingan-kehadiran-per-bulan/pdf`
- `/api/internal/ascends/uc/hrm/custom-reports/diagram-lembur-tahunan/pdf`
- `/api/internal/ascends/uc/hrm/custom-reports/karyawan-keluar-tahunan/pdf`
- `/api/internal/ascends/uc/hrm/custom-reports/karyawan-keluar/pdf`
- `/api/internal/ascends/uc/hrm/custom-reports/karyawan-masuk/pdf`
- `/api/internal/ascends/uc/hrm/custom-reports/lembur-bulanan/pdf`
- `/api/internal/ascends/uc/hrm/custom-reports/surat-peringatan/pdf`
- `/api/internal/ascends/uc/hrm/custom-reports/verifikasi-lembur/pdf`
- `/api/internal/ascends/uc/hrm/daftar-karyawan-berdasarkan-abjad/pdf`
- `/api/internal/ascends/uc/hrm/daftar-karyawan/pdf`
- `/api/internal/ascends/uc/hrm/data-karyawan-status-kerja/pdf`
- `/api/internal/ascends/uc/hrm/employee-list/diagram-karyawan-per-departemen/pdf`
- `/api/internal/ascends/uc/hrm/employee-list/diagram-karyawan-per-divisi/pdf`
- `/api/internal/ascends/uc/hrm/employee-list/diagram-karyawan-per-jenis-kelamin/pdf`
- `/api/internal/ascends/uc/hrm/employee-list/diagram-karyawan-per-level/pdf`
- `/api/internal/ascends/uc/hrm/employee-list/diagram-karyawan-per-masa-kerja/pdf`
- `/api/internal/ascends/uc/hrm/employee-list/diagram-karyawan-per-strata-pendidikan/pdf`
- `/api/internal/ascends/uc/hrm/employee-list/diagram-karyawan-per-usia-generasi/pdf`
- `/api/internal/ascends/uc/hrm/employee-termination/pdf`
- `/api/internal/ascends/uc/hrm/holiday/daftar-libur-cuti-bersama/pdf`
- `/api/internal/ascends/uc/hrm/karyawan-aktif-per-departemen/pdf`
- `/api/internal/ascends/uc/hrm/karyawan-masuk-per-departemen-per-tanggal-masuk/pdf`
- `/api/internal/ascends/uc/hrm/karyawan-per-agama/pdf`
- `/api/internal/ascends/uc/hrm/karyawan-per-departemen-per-jabatan/pdf` — **Diagram + List**
- `/api/internal/ascends/uc/hrm/karyawan-per-etnis/pdf`
- `/api/internal/ascends/uc/hrm/karyawan-per-level/pdf` — **Diagram + List**
- `/api/internal/ascends/uc/hrm/karyawan-per-masa-kerja/pdf` — **Diagram + List**
- `/api/internal/ascends/uc/hrm/karyawan-per-umur/pdf`
- `/api/internal/ascends/uc/hrm/kehadiran-kk-kt-st/pdf`
- `/api/internal/ascends/uc/hrm/late-sign-in/durasi-denda-keterlambatan/pdf`
- `/api/internal/ascends/uc/hrm/list-karyawan-habis-kontrak/pdf`
- `/api/internal/ascends/uc/hrm/list-karyawan/pdf`
- `/api/internal/ascends/uc/hrm/loss-time/pdf`
- `/api/internal/ascends/uc/hrm/other-income-deduction/pendapatan-lain-lain/pdf`
- `/api/internal/ascends/uc/hrm/overtime/lembur-bulanan/pdf`
- `/api/internal/ascends/uc/hrm/perbandingan-jumlah-karyawan-tahunan-per-bulan/pdf`
- `/api/internal/ascends/uc/hrm/thr/pdf`
- `/api/internal/ascends/uc/hrm/usia-generasi-tahun-kelahiran-masa-kerja/pdf` — **Diagram + List**
- `/api/internal/ascends/uc/hrm/warning-notice/surat-peringatan/pdf`

### inventory_analysis (27)

- `/api/internal/ascends/uc/inventory_analysis/adjustment-by-item/adjustment/adjustment-lemari/pdf`
- `/api/internal/ascends/uc/inventory_analysis/adjustment-by-item/adjustment/khusus-kursi/pdf`
- `/api/internal/ascends/uc/inventory_analysis/adjustment-by-item/adjustment/khusus-lemari/pdf`
- `/api/internal/ascends/uc/inventory_analysis/adjustment-by-item/goods-delivery-note/pengiriman-lemari/pdf`
- `/api/internal/ascends/uc/inventory_analysis/adjustment-by-item/goods-delivery-note/rekapan-value-surat-jalan/pdf`
- `/api/internal/ascends/uc/inventory_analysis/adjustment-by-item/penyesuaian-persediaan/pdf`
- `/api/internal/ascends/uc/inventory_analysis/outstanding-undelivery-goods/do-customer-belum-terkirim/pdf`
- `/api/internal/ascends/uc/inventory_analysis/outstanding-undelivery-goods/do-lemari-belum-terkirim/pdf`
- `/api/internal/ascends/uc/inventory_analysis/outstanding-undelivery-goods/do-per-kategori-belum-terkirim/pdf`
- `/api/internal/ascends/uc/inventory_analysis/outstanding-undelivery-goods/list-do-belum-terkirim/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production/hasil-broker-per-hari/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production/hasil-broker-per-kategori/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production/hasil-broker-per-mesin/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production/hasil-cuci-per-hari/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production/hasil-cuci-per-mesin/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production/hasil-cuci-per-supplier/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production/hasil-produksi-per-mesin/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production_by_item/produksi-per-minggu/pdf`
- `/api/internal/ascends/uc/inventory_analysis/production_by_item/produksi/pdf`
- `/api/internal/ascends/uc/inventory_analysis/purchase-order-by-item/history-harga-po/pdf`
- `/api/internal/ascends/uc/inventory_analysis/purchase-order-by-item/jangka-waktu-po-pi/pdf`
- `/api/internal/ascends/uc/inventory_analysis/purchase-request-by-item/jangka-waktu-approve-pr-po-detail/pdf`
- `/api/internal/ascends/uc/inventory_analysis/sales-by-item/target-sales-per-minggu/pdf`
- `/api/internal/ascends/uc/inventory_analysis/stock-activities-summary/aktifitas-stock-gsu-per-gudang/pdf`
- `/api/internal/ascends/uc/inventory_analysis/stock-activities-summary/khusus-plastik-kabinet/pdf`
- `/api/internal/ascends/uc/inventory_analysis/stock-activities-summary/laporan-hpp-dan-stock/pdf`
- `/api/internal/ascends/uc/inventory_analysis/stock-blanace/saldo-stok-barang-per-gudang/pdf`

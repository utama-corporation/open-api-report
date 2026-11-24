<?php

namespace App\Support\Ascends;

use App\Services\Ascends\Shared\Associate\CustomerBaruPerTahunReportService;
use App\Services\Ascends\Shared\Associate\CustomerBaruReportService;
use App\Services\Ascends\Shared\Associate\CustomerModifikasiReportService;
use App\Services\Ascends\Shared\Associate\ListCustomerPerKotaReportService;
use App\Services\Ascends\Shared\CustomReport\BankAccountDailyCashReportService;
use App\Services\Ascends\Shared\CustomReport\BiayaMobilTrukReportService;
use App\Services\Ascends\Shared\CustomReport\BudgetingRealisasiBiayaPromosiPenjualanReportService;
use App\Services\Ascends\Shared\CustomReport\CekProduksiGsuReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaEnamelAkunSpesialReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaEnamelAllReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaEnamelGrosirReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaEnamelRetailReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaEnamelSemiGrosirReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaFurnitureAkunSpesialReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaFurnitureAllReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaFurnitureGrosirReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaFurnitureRetailReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaFurnitureSalesProjectReportService;
use App\Services\Ascends\Shared\CustomReport\CheckPriceGroupA\DaftarHargaFurnitureSemiGrosirReportService;
use App\Services\Ascends\Shared\CustomReport\CustomerOverLimitReportService;
use App\Services\Ascends\Shared\CustomReport\ItemDiscontinueReportService;
use App\Services\Ascends\Shared\CustomReport\MonitoringSoSiTagihanReportService;
use App\Services\Ascends\Shared\CustomReport\PembelianKayuPerPeriodeReportService;
use App\Services\Ascends\Shared\CustomReport\PengirimanKursiDanMejaHarianReportService;
use App\Services\Ascends\Shared\CustomReport\PengirimanLemariHarianReportService;
use App\Services\Ascends\Shared\CustomReport\PengirimanLemariTahunanReportService;
use App\Services\Ascends\Shared\CustomReport\PengirimanPerKategoriHarianReportService;
use App\Services\Ascends\Shared\CustomReport\PengirimanPerKategoriTahunanReportService;
use App\Services\Ascends\Shared\CustomReport\PenjualanPerItemAnalisaSkuDetailReportService;
use App\Services\Ascends\Shared\CustomReport\PenjualanPerItemAnalisaSkuReportService;
use App\Services\Ascends\Shared\CustomReport\PenjualanPerKategoriBarangBulananReportService;
use App\Services\Ascends\Shared\CustomReport\PenjualanVsBiayaPerjalananDinasReportService;
use App\Services\Ascends\Shared\CustomReport\SalesSummaryPerCustomerPerPeriodReportService;
use App\Services\Ascends\Shared\Finance\OpnameKas\OpnameKasReportService;
use App\Services\Ascends\Shared\Finance\OutstandingPayableCheck\HutangGiroRuReportService;
use App\Services\Ascends\Shared\Finance\PayableSummary\SaldoHutangRuReportService;
use App\Services\Ascends\Shared\Finance\ReceiptVoucherDetails\GiroCashTransferReportService;
use App\Services\Ascends\Shared\Finance\ReceiptVoucherDetails\PelunasanReportService;
use App\Services\Ascends\Shared\Finance\ReceiptVoucherDetails\PenerimaanPiutangReportService;
use App\Services\Ascends\Shared\Finance\ReceiptVoucherDetails\PenerimaanVoucherReportService;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangCash14HariReportService;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangDiatas120HariReportService;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangDiatas45HariReportService;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangDiatas60HariReportService;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangSemuaReportService;
use App\Services\Ascends\Shared\Finance\ReceivableDetails\PiutangTakTertagih90HariReportService;
use App\Services\Ascends\Shared\Finance\ReceivableSummary\UmurPiutangRuReportService;
use App\Services\Ascends\Shared\FixedAsset\AssetSummary\PenyusutanAktivaReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BebanPenjualanReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BebanPenjualanSummaryReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BebanReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BebanUmumBaruReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BebanUmumGsuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BebanUmumRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BebanUmumUcReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BiayaProduksiReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BiayaProduksiTidakLangsungReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\BiayaUpahLangsungDetailReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LabaKotorGsuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LabaKotorPerKategoriReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LabaKotorRu12BulanReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LabaKotorRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LabaRugiUCReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LaporanLabaRugiGsuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LaporanLabaRugiRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\LaporanLabaRugiUcReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\PendapatanDanBiayaLainBaruReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\PendapatanDanBiayaLainReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\PerhitunganBungaGsuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\PerhitunganBungaRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\PinjamanKaryawanReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\RekapBiayaLabaRugiReportService;
use App\Services\Ascends\Shared\GeneralLedger\JournalDetails\RingkasanAktivaReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalance\BiayaBayarDimukaReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalance\HutangLainnyaReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalance\HutangUCReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalance\NeracaPerBulanReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalance\RingkasanHutangBankReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalance\SaldoBankReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\ArusKasGsuReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\ArusKasRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\ArusKasUcReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\FinancialRasioGsuReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\FinancialRasioRuReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\FinancialRasioUcReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\LabaRugiMultiPeriodeReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\LabaRugiMultiPeriodeTahunanReportService;
use App\Services\Ascends\Shared\GeneralLedger\TrialBalanceMonthly\PendukungArusKasReportService;
use App\Services\Ascends\Shared\Hrm\AbsensiBriefingHarianGsuReportService;
use App\Services\Ascends\Shared\Hrm\AbsensiBriefingHarianReportService;
use App\Services\Ascends\Shared\Hrm\AbsensiBriefingHarianUcReportService;
use App\Services\Ascends\Shared\Hrm\AbsensiIndividuReportService;
use App\Services\Ascends\Shared\Hrm\CustomReports\DiagramLemburTahunanReportService;
use App\Services\Ascends\Shared\Hrm\CustomReports\KaryawanKeluarReportService;
use App\Services\Ascends\Shared\Hrm\CustomReports\KaryawanKeluarTahunanReportService;
use App\Services\Ascends\Shared\Hrm\CustomReports\KaryawanMasukReportService;
use App\Services\Ascends\Shared\Hrm\CustomReports\LemburBulananReportService as CustomReportsLemburBulananReportService;
use App\Services\Ascends\Shared\Hrm\CustomReports\SuratPeringatanReportService as CustomReportsSuratPeringatanReportService;
use App\Services\Ascends\Shared\Hrm\CustomReports\VerifikasiLemburReportService;
use App\Services\Ascends\Shared\Hrm\DaftarKaryawanBerdasarkanAbjadReportService;
use App\Services\Ascends\Shared\Hrm\DaftarKaryawanReportService;
use App\Services\Ascends\Shared\Hrm\DaftarLiburCutiBersamaReportService;
use App\Services\Ascends\Shared\Hrm\DataKaryawanStatusKerjaReportService;
use App\Services\Ascends\Shared\Hrm\DataPesertaMakanSiangIbadahAulaPerDepartemenReportService;
use App\Services\Ascends\Shared\Hrm\DataPesertaMakanSiangShalatJumatPerDepartemenReportService;
use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerDivisiReportService;
use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerJenisKelaminReportService;
use App\Services\Ascends\Shared\Hrm\DiagramKaryawanPerStrataPendidikanReportService;
use App\Services\Ascends\Shared\Hrm\DurasiDendaKeterlambatanReportService;
use App\Services\Ascends\Shared\Hrm\EmployeeListReportService;
use App\Services\Ascends\Shared\Hrm\EmployeeTerminationReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanAktifPerDepartemenReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanMasukPerDepartemenPerTanggalMasukReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanPerAgamaReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanPerDepartemenPerJabatanReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanPerEtnisReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanPerLevelReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanPerMasaKerjaReportService;
use App\Services\Ascends\Shared\Hrm\KaryawanPerUmurReportService;
use App\Services\Ascends\Shared\Hrm\KehadiranKkKtStReportService;
use App\Services\Ascends\Shared\Hrm\KehadiranKruBahanBakuReportService;
use App\Services\Ascends\Shared\Hrm\KehadiranKruRacipReportService;
use App\Services\Ascends\Shared\Hrm\KehadiranKruStickReportService;
use App\Services\Ascends\Shared\Hrm\KeterlambatanKehadiranBriefingHarianReportService;
use App\Services\Ascends\Shared\Hrm\KetidakhadiranBulananReportService;
use App\Services\Ascends\Shared\Hrm\LemburBulananReportService as HrmLemburBulananReportService;
use App\Services\Ascends\Shared\Hrm\ListKaryawanHabisKontrakReportService;
use App\Services\Ascends\Shared\Hrm\LossTimeReportService;
use App\Services\Ascends\Shared\Hrm\MppTahunanPerDivisiGsuReportService;
use App\Services\Ascends\Shared\Hrm\PendapatanLainLainReportService;
use App\Services\Ascends\Shared\Hrm\PengabaianKeterlambatanKehadiranManualReportService;
use App\Services\Ascends\Shared\Hrm\PerbandinganJumlahKaryawanTahunanPerBulanReportService;
use App\Services\Ascends\Shared\Hrm\PerbandinganKehadiranPerBulanReportService;
use App\Services\Ascends\Shared\Hrm\PersentaseKehadiranBulananReportService;
use App\Services\Ascends\Shared\Hrm\PersentaseKehadiranMingguanPerDepartemenReportService;
use App\Services\Ascends\Shared\Hrm\RekapitulasiAbsensiBriefingHarianGsuReportService;
use App\Services\Ascends\Shared\Hrm\RekapitulasiAbsensiBriefingHarianReportService;
use App\Services\Ascends\Shared\Hrm\RekapitulasiKehadiranKurang93TahunanReportService;
use App\Services\Ascends\Shared\Hrm\RekapitulasiPengabaianKeterlambatanTahunanReportService;
use App\Services\Ascends\Shared\Hrm\SuratPeringatanReportService as HrmSuratPeringatanReportService;
use App\Services\Ascends\Shared\Hrm\ThrReportService;
use App\Services\Ascends\Shared\Hrm\UsiaGenerasiTahunKelahiranMasaKerjaReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\AdjustmentLemariReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\AktifitasStockGsuPerGudangReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\AktifitasStockGsuReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\AktifitasStockRuReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\DOCustomerBelumTerkirimReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\DOLemariBelumTerkirimReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\DOPerKategoriBelumTerkirimReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\HistoryHargaPoReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\JangkaWaktuApprovePrPoDetailReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\JangkaWaktuPoPiReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\KhususPlastikKabinetReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\KursiAdjustmentReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\LaporanHppDanStockReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\LemariAdjustmentReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\ListDOBelumTerkirimReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\PengirimanLemariReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\PenyesuaianPersediaanReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\PurchaseByItem\RingkasanPembelianRuReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\RekapanValueSuratJalanReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\SaldoStokBarangPerGudangGsuReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\SaldoStokBarangPerGudangRuReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\SaldoStokBarangPerGudangUcReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\SalesByItem\PenjualanPerGroupBulananRuReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\SalesByItem\PersentaseHppPenjualanPerItemFamilyRuReportService;
use App\Services\Ascends\Shared\InventoryAnalysis\SalesByItem\TargetSalesPerMingguReportService;
use App\Services\Ascends\Shared\Production\HasilBrokerPerHariReportService;
use App\Services\Ascends\Shared\Production\HasilBrokerPerKategoriReportService;
use App\Services\Ascends\Shared\Production\HasilBrokerPerMesinReportService;
use App\Services\Ascends\Shared\Production\HasilCuciPerHariReportService;
use App\Services\Ascends\Shared\Production\HasilCuciPerMesinReportService;
use App\Services\Ascends\Shared\Production\HasilCuciPerSupplierReportService;
use App\Services\Ascends\Shared\Production\HasilProduksiPerMesinReportService;
use App\Services\Ascends\Shared\ProductionByItem\ProduksiPerMingguReportService;
use App\Services\Ascends\Shared\ProductionByItem\ProduksiReportService;

/**
 * Registry of shared reports published under the gsu, ru and uc route prefixes.
 *
 * Generated by scripts/ascends/generate-company-report-registry.php from the
 * shared routes then reviewed manually. Company suffixes (-gsu, -ru, -uc) are
 * stripped from slugs: the route prefix is the only company marker. Filenames
 * are static strings only when they carry no company label, and override keys
 * are lowercase company codes.
 */
final class CompanyReportRegistry
{
    private const COMPANIES = ['gsu', 'ru', 'uc'];

    /**
     * Short report names of the shared dynamic hrm endpoint; the single source
     * for both its whereIn list and the per-company dynamic route whitelist.
     *
     * @var list<string>
     */
    public const HRM_REPORTS = [
        'list-karyawan',
        'daftar-karyawan',
        'daftar-karyawan-berdasarkan-abjad',
        'data-karyawan-status-kerja',
        'karyawan-aktif-per-departemen',
        'karyawan-masuk-per-departemen-per-tanggal-masuk',
        'karyawan-per-agama',
        'karyawan-per-departemen-per-jabatan',
        'karyawan-per-etnis',
        'karyawan-per-level',
        'karyawan-per-masa-kerja',
        'karyawan-per-umur',
        'kehadiran-kk-kt-st',
        'list-karyawan-habis-kontrak',
        'perbandingan-jumlah-karyawan-tahunan-per-bulan',
        'usia-generasi-tahun-kelahiran-masa-kerja',
    ];

    /**
     * Slugs published to only some companies; every other slug goes to all three.
     *
     * @var array<string, list<string>>
     */
    private const PUBLISHED = [
        'custom-report.cek-produksi' => ['gsu'],
        'finance.outstanding-payable-check.hutang-giro' => ['ru'],
        'finance.payable-summary.saldo-hutang' => ['ru'],
        'finance.receivable-summary.umur-piutang' => ['ru'],
        'general-ledger.journal-details.laba-rugi' => ['uc'],
        'general_ledger.journal_details.laba-kotor' => ['gsu', 'ru'],
        'general_ledger.journal_details.perhitungan-bunga' => ['gsu', 'ru'],
        'general_ledger.trial_balance.hutang' => ['uc'],
        'hrm.attendance-full.rekapitulasi-absensi-briefing-harian' => ['gsu', 'ru'],
        'hrm.employee-list.mpp-tahunan-per-divisi' => ['gsu'],
        'inventory_analysis.purchase-by-item.ringkasan-pembelian' => ['ru'],
        'inventory_analysis.sales-by-item.penjualan-per-group-bulanan' => ['ru'],
        'inventory_analysis.sales-by-item.persentase-hpp-penjualan-per-item-family' => ['ru'],
        'inventory_analysis.stock-activities-summary.aktifitas-stock' => ['gsu', 'ru'],
    ];

    /** @var array<string, CompanyReportDefinition>|null */
    private static ?array $reports = null;

    /**
     * @return array<string, CompanyReportDefinition>
     */
    public static function all(): array
    {
        return self::$reports ??= [
            'associate.customer-baru' => new CompanyReportDefinition(
                slug: 'associate.customer-baru',
                service: CustomerBaruReportService::class,
                view: 'ascends.shared.associate.customer_baru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Associate - Laporan Customer Baru ('.$company.').pdf',
            ),
            'associate.customer-baru-per-tahun' => new CompanyReportDefinition(
                slug: 'associate.customer-baru-per-tahun',
                service: CustomerBaruPerTahunReportService::class,
                view: 'ascends.shared.associate.customer_baru_per_tahun.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Associate - Laporan Penambahan Customer Baru (Periode 1 Tahun) ('.$company.').pdf',
            ),
            'associate.customer-modifikasi-6-bulan' => new CompanyReportDefinition(
                slug: 'associate.customer-modifikasi-6-bulan',
                service: CustomerModifikasiReportService::class,
                view: 'ascends.shared.associate.customer_modifikasi_6_bulan_terakhir.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Associate - Laporan Customer Modifikasi 6 Bulan Terakhir (Periode 1 Tahun) ('.$company.').pdf',
            ),
            'associate.list-customer-per-kota' => new CompanyReportDefinition(
                slug: 'associate.list-customer-per-kota',
                service: ListCustomerPerKotaReportService::class,
                view: 'ascends.shared.associate.list_customer.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Associate - Laporan Data Customer Per Kota ('.$company.').pdf',
            ),
            'custom-report.bank-account-daily-cash' => new CompanyReportDefinition(
                slug: 'custom-report.bank-account-daily-cash',
                service: BankAccountDailyCashReportService::class,
                view: 'ascends.shared.custom_report.bank_account_daily_cash.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Kas Harian ('.$company.').pdf',
            ),
            'custom-report.biaya-mobil-truk' => new CompanyReportDefinition(
                slug: 'custom-report.biaya-mobil-truk',
                service: BiayaMobilTrukReportService::class,
                view: 'ascends.shared.custom_report.biaya_mobil_truk.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Biaya Mobil Truk ('.$company.').pdf',
            ),
            'custom-report.budgeting-realisasi-biaya-promosi-penjualan' => new CompanyReportDefinition(
                slug: 'custom-report.budgeting-realisasi-biaya-promosi-penjualan',
                service: BudgetingRealisasiBiayaPromosiPenjualanReportService::class,
                view: 'ascends.shared.custom_report.budgeting_realisasi_biaya_promosi_penjualan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Budgeting & Realisasi Biaya Promosi Penjualan ('.$company.').pdf',
            ),
            'custom-report.cek-produksi' => new CompanyReportDefinition(
                slug: 'custom-report.cek-produksi',
                service: CekProduksiGsuReportService::class,
                view: 'ascends.shared.custom_report.cek_produksi_gsu.pdf',
                pdf: ['format' => 'A4'],
                filename: fn (array $data, string $company): string => 'Laporan Item Tidak ada Penjualan Dan Tidak Ada Produksi ('.$company.').pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-enamel-akun-spesial' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-enamel-akun-spesial',
                service: DaftarHargaEnamelAkunSpesialReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_enamel_akun_spesial.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Enamel Akun Spesial.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-enamel-all' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-enamel-all',
                service: DaftarHargaEnamelAllReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_enamel_all.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Enamel.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-enamel-grosir' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-enamel-grosir',
                service: DaftarHargaEnamelGrosirReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_enamel_grosir.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Enamel Grosir.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-enamel-retail' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-enamel-retail',
                service: DaftarHargaEnamelRetailReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_enamel_retail.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Enamel Retail.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-enamel-semi-grosir' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-enamel-semi-grosir',
                service: DaftarHargaEnamelSemiGrosirReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_enamel_semi_grosir.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Enamel Semi Grosir.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-furniture-akun-spesial' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-furniture-akun-spesial',
                service: DaftarHargaFurnitureAkunSpesialReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_furniture_akun_spesial.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Furniture Akun Spesial.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-furniture-all' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-furniture-all',
                service: DaftarHargaFurnitureAllReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_furniture_all.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Furniture.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-furniture-grosir' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-furniture-grosir',
                service: DaftarHargaFurnitureGrosirReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_furniture_grosir.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Furniture Grosir.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-furniture-retail' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-furniture-retail',
                service: DaftarHargaFurnitureRetailReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_furniture_retail.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Furniture Retail.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-furniture-sales-project' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-furniture-sales-project',
                service: DaftarHargaFurnitureSalesProjectReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_furniture_sales_project.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Furniture Sales Project.pdf',
            ),
            'custom-report.check-price-group-a.daftar-harga-furniture-semi-grosir' => new CompanyReportDefinition(
                slug: 'custom-report.check-price-group-a.daftar-harga-furniture-semi-grosir',
                service: DaftarHargaFurnitureSemiGrosirReportService::class,
                view: 'ascends.shared.custom_report.check_price_group_a.daftar_harga_furniture_semi_grosir.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: 'Daftar Harga Furniture Semi Grosir.pdf',
            ),
            'custom-report.customer-over-limit' => new CompanyReportDefinition(
                slug: 'custom-report.customer-over-limit',
                service: CustomerOverLimitReportService::class,
                view: 'ascends.shared.custom_report.customer_over_limit.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Customer Over Limit ('.$company.').pdf',
            ),
            'custom-report.item-discontinue' => new CompanyReportDefinition(
                slug: 'custom-report.item-discontinue',
                service: ItemDiscontinueReportService::class,
                view: 'ascends.shared.custom_report.item_discontinue.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Item Discontinue ('.$company.').pdf',
            ),
            'custom-report.monitoring-so-si-tagihan' => new CompanyReportDefinition(
                slug: 'custom-report.monitoring-so-si-tagihan',
                service: MonitoringSoSiTagihanReportService::class,
                view: 'ascends.shared.custom_report.monitoring_so_si_tagihan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Monitoring SO - SI - Tagihan ('.$company.').pdf',
            ),
            'custom-report.pembelian-kayu-per-periode' => new CompanyReportDefinition(
                slug: 'custom-report.pembelian-kayu-per-periode',
                service: PembelianKayuPerPeriodeReportService::class,
                view: 'ascends.shared.custom_report.pembelian_kayu_per_periode.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Pembelian Kayu Per Periode ('.$company.').pdf',
            ),
            'custom-report.pengiriman-kursi-dan-meja-harian' => new CompanyReportDefinition(
                slug: 'custom-report.pengiriman-kursi-dan-meja-harian',
                service: PengirimanKursiDanMejaHarianReportService::class,
                view: 'ascends.shared.custom_report.pengiriman_kursi_dan_meja_harian.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Pengiriman Kursi Makan, Kursi Santai, Kursi Cafe Dan Meja Santai(Harian) ('.$company.').pdf',
            ),
            'custom-report.pengiriman-lemari-harian' => new CompanyReportDefinition(
                slug: 'custom-report.pengiriman-lemari-harian',
                service: PengirimanLemariHarianReportService::class,
                view: 'ascends.shared.custom_report.pengiriman_lemari_harian.pdf',
                pdf: ['format' => 'A4-L', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Pengiriman Lemari (Harian) ('.$company.').pdf',
            ),
            'custom-report.pengiriman-lemari-tahunan' => new CompanyReportDefinition(
                slug: 'custom-report.pengiriman-lemari-tahunan',
                service: PengirimanLemariTahunanReportService::class,
                view: 'ascends.shared.custom_report.pengiriman_lemari_tahunan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Pengiriman Lemari (Tahunan) ('.$company.').pdf',
            ),
            'custom-report.pengiriman-per-kategori-harian' => new CompanyReportDefinition(
                slug: 'custom-report.pengiriman-per-kategori-harian',
                service: PengirimanPerKategoriHarianReportService::class,
                view: 'ascends.shared.custom_report.pengiriman_per_kategori_harian.pdf',
                pdf: ['format' => 'A4-L', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Pengiriman Per Kategori Harian ('.$company.').pdf',
            ),
            'custom-report.pengiriman-per-kategori-tahunan' => new CompanyReportDefinition(
                slug: 'custom-report.pengiriman-per-kategori-tahunan',
                service: PengirimanPerKategoriTahunanReportService::class,
                view: 'ascends.shared.custom_report.pengiriman_per_kategori_tahunan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Pengiriman Per Kategori Tahunan ('.$company.').pdf',
            ),
            'custom-report.penjualan-per-item-analisa-sku' => new CompanyReportDefinition(
                slug: 'custom-report.penjualan-per-item-analisa-sku',
                service: PenjualanPerItemAnalisaSkuReportService::class,
                view: 'ascends.shared.custom_report.penjualan_per_item_analisa_sku.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Penjualan Per Item Barang & Analisa SKU ('.$company.').pdf',
            ),
            'custom-report.penjualan-per-item-analisa-sku-detail' => new CompanyReportDefinition(
                slug: 'custom-report.penjualan-per-item-analisa-sku-detail',
                service: PenjualanPerItemAnalisaSkuDetailReportService::class,
                view: 'ascends.shared.custom_report.penjualan_per_item_analisa_sku_detail.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Penjualan Per Item Barang ('.$company.').pdf',
            ),
            'custom-report.penjualan-per-kategori-barang-bulanan' => new CompanyReportDefinition(
                slug: 'custom-report.penjualan-per-kategori-barang-bulanan',
                service: PenjualanPerKategoriBarangBulananReportService::class,
                view: 'ascends.shared.custom_report.penjualan_per_kategori_barang_bulanan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Penjualan Per Kategori Barang Bulanan ('.$company.').pdf',
            ),
            'custom-report.penjualan-vs-biaya-perjalanan-dinas' => new CompanyReportDefinition(
                slug: 'custom-report.penjualan-vs-biaya-perjalanan-dinas',
                service: PenjualanVsBiayaPerjalananDinasReportService::class,
                view: 'ascends.shared.custom_report.penjualan_vs_biaya_perjalanan_dinas.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Perjalanan Dinas VS Penjualan ('.$company.').pdf',
            ),
            'custom-report.sales-summary-per-customer-per-period' => new CompanyReportDefinition(
                slug: 'custom-report.sales-summary-per-customer-per-period',
                service: SalesSummaryPerCustomerPerPeriodReportService::class,
                view: 'ascends.shared.custom_report.sales_summary_per_customer_per_period.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Sales Summary Per Customer Per Period ('.$company.').pdf',
            ),
            'finance.outstanding-payable-check.hutang-giro' => new CompanyReportDefinition(
                slug: 'finance.outstanding-payable-check.hutang-giro',
                service: HutangGiroRuReportService::class,
                view: 'ascends.shared.finance.outstanding_payable_check.hutang_giro_ru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Outstanding Payable Check - Laporan Hutang Giro ('.$company.').pdf',
            ),
            'finance.outstanding_advance_payable.opname-kas' => new CompanyReportDefinition(
                slug: 'finance.outstanding_advance_payable.opname-kas',
                service: OpnameKasReportService::class,
                view: 'ascends.shared.finance.outstanding_advance_payable.opname_kas.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait', 'disable_chunking' => true],
                filename: fn (array $data, string $company): string => 'Laporan Opname Kas ('.$company.').pdf',
            ),
            'finance.outstanding_advance_receivable.opname-kas' => new CompanyReportDefinition(
                slug: 'finance.outstanding_advance_receivable.opname-kas',
                service: OpnameKasReportService::class,
                view: 'ascends.shared.finance.outstanding_advance_receivable.opname_kas.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait', 'disable_chunking' => true],
                filename: fn (array $data, string $company): string => 'Laporan Opname Kas ('.$company.').pdf',
            ),
            'finance.payable-summary.saldo-hutang' => new CompanyReportDefinition(
                slug: 'finance.payable-summary.saldo-hutang',
                service: SaldoHutangRuReportService::class,
                view: 'ascends.shared.finance.payable_summary.saldo_hutang_ru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Payable Summary - Laporan Saldo Hutang ('.$company.').pdf',
            ),
            'finance.receipt_voucher_details.giro-cash-transfer' => new CompanyReportDefinition(
                slug: 'finance.receipt_voucher_details.giro-cash-transfer',
                service: GiroCashTransferReportService::class,
                view: 'ascends.shared.finance.receipt_voucher_details.giro_cash_transfer.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Pembayaran '.(($data['payment_method'] ?? '') !== '' ? ucfirst(strtolower((string) $data['payment_method'])).' - ' : '').$company.'.pdf',
            ),
            'finance.receipt_voucher_details.pelunasan' => new CompanyReportDefinition(
                slug: 'finance.receipt_voucher_details.pelunasan',
                service: PelunasanReportService::class,
                view: 'ascends.shared.finance.receipt_voucher_details.pelunasan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Pelunasan ('.$company.').pdf',
            ),
            'finance.receipt_voucher_details.penerimaan-piutang' => new CompanyReportDefinition(
                slug: 'finance.receipt_voucher_details.penerimaan-piutang',
                service: PenerimaanPiutangReportService::class,
                view: 'ascends.shared.finance.receipt_voucher_details.penerimaan_piutang.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Penerimaan Piutang ('.$company.').pdf',
            ),
            'finance.receipt_voucher_details.penerimaan-voucher' => new CompanyReportDefinition(
                slug: 'finance.receipt_voucher_details.penerimaan-voucher',
                service: PenerimaanVoucherReportService::class,
                view: 'ascends.shared.finance.receipt_voucher_details.penerimaan_voucher.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Penerimaan Voucher (Intensif Penagihan) ('.$company.').pdf',
            ),
            'finance.receivable-summary.umur-piutang' => new CompanyReportDefinition(
                slug: 'finance.receivable-summary.umur-piutang',
                service: UmurPiutangRuReportService::class,
                view: 'ascends.shared.finance.receivable_summary.umur_piutang_ru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Receivable Summary - Laporan Umur Piutang Dagang ('.$company.').pdf',
            ),
            'finance.receivable_details.piutang-cash-14-hari' => new CompanyReportDefinition(
                slug: 'finance.receivable_details.piutang-cash-14-hari',
                service: PiutangCash14HariReportService::class,
                view: 'ascends.shared.finance.receivable_details.piutang_cash_14_hari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Umur Piutang Cash 14 Hari ('.$company.').pdf',
            ),
            'finance.receivable_details.piutang-diatas-120-hari' => new CompanyReportDefinition(
                slug: 'finance.receivable_details.piutang-diatas-120-hari',
                service: PiutangDiatas120HariReportService::class,
                view: 'ascends.shared.finance.receivable_details.piutang_diatas_120_hari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Umur Piutang Diatas 120 Hari ('.$company.').pdf',
            ),
            'finance.receivable_details.piutang-diatas-45-hari' => new CompanyReportDefinition(
                slug: 'finance.receivable_details.piutang-diatas-45-hari',
                service: PiutangDiatas45HariReportService::class,
                view: 'ascends.shared.finance.receivable_details.piutang_diatas_45_hari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Umur Piutang Diatas 45 Hari ('.$company.').pdf',
            ),
            'finance.receivable_details.piutang-diatas-60-hari' => new CompanyReportDefinition(
                slug: 'finance.receivable_details.piutang-diatas-60-hari',
                service: PiutangDiatas60HariReportService::class,
                view: 'ascends.shared.finance.receivable_details.piutang_diatas_60_hari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Umur Piutang Di Atas 60 Hari ('.$company.').pdf',
            ),
            'finance.receivable_details.piutang-semua' => new CompanyReportDefinition(
                slug: 'finance.receivable_details.piutang-semua',
                service: PiutangSemuaReportService::class,
                view: 'ascends.shared.finance.receivable_details.piutang_semua.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Umur Piutang Semua ('.$company.').pdf',
            ),
            'finance.receivable_details.piutang-tak-tertagih-90-hari' => new CompanyReportDefinition(
                slug: 'finance.receivable_details.piutang-tak-tertagih-90-hari',
                service: PiutangTakTertagih90HariReportService::class,
                view: 'ascends.shared.finance.receivable_details.piutang_tak_tertagih_90_hari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Piutang Tak Tertagih Di Atas 90 Hari ('.$company.').pdf',
            ),
            'fixed_asset.asset_summary.penyusutan_aktiva' => new CompanyReportDefinition(
                slug: 'fixed_asset.asset_summary.penyusutan_aktiva',
                service: PenyusutanAktivaReportService::class,
                view: 'ascends.shared.fixed_asset.asset_summary.penyusutan_aktiva.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Asset Summary - Laporan Daftar Penyusutan Aktiva Tetap ('.$company.').pdf',
            ),
            'general-ledger.journal-details.laba-rugi' => new CompanyReportDefinition(
                slug: 'general-ledger.journal-details.laba-rugi',
                service: LabaRugiUCReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.laporan_laba_rugi_uc.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Laba Rugi ('.$company.').pdf',
            ),
            'general_ledger.journal_details.beban' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.beban',
                service: BebanReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.beban.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Beban ('.$company.').pdf',
            ),
            'general_ledger.journal_details.beban-penjualan' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.beban-penjualan',
                service: BebanPenjualanReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.beban_penjualan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Beban Penjualan ('.$company.').pdf',
            ),
            'general_ledger.journal_details.beban-penjualan-summary' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.beban-penjualan-summary',
                service: BebanPenjualanSummaryReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.beban_penjualan_summary.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Beban Penjualan Summary ('.$company.').pdf',
            ),
            'general_ledger.journal_details.beban-umum' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.beban-umum',
                service: BebanUmumGsuReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.beban_umum_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Beban Umum ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => BebanUmumRuReportService::class, 'view' => 'ascends.shared.general_ledger.journal_details.beban_umum_ru.pdf'],
                    'uc' => ['service' => BebanUmumUcReportService::class, 'view' => 'ascends.shared.general_ledger.journal_details.beban_umum_uc.pdf'],
                ],
            ),
            'general_ledger.journal_details.beban-umum-baru' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.beban-umum-baru',
                service: BebanUmumBaruReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.beban_umum_baru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Biaya Beban Umum ('.$company.').pdf',
            ),
            'general_ledger.journal_details.biaya-produksi' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.biaya-produksi',
                service: BiayaProduksiReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.biaya_produksi.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Biaya Produksi ('.$company.').pdf',
            ),
            'general_ledger.journal_details.biaya-produksi-tidak-langsung' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.biaya-produksi-tidak-langsung',
                service: BiayaProduksiTidakLangsungReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.biaya_produksi_tidak_langsung.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Biaya Produksi Tidak Langsung ('.$company.').pdf',
            ),
            'general_ledger.journal_details.biaya-upah-langsung-detail' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.biaya-upah-langsung-detail',
                service: BiayaUpahLangsungDetailReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.biaya_upah_langsung_detail.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Biaya Upah Langsung ('.$company.').pdf',
            ),
            'general_ledger.journal_details.laba-kotor' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.laba-kotor',
                service: LabaKotorGsuReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.laba_kotor_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Laba Kotor Tahunan ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => LabaKotorRuReportService::class, 'view' => 'ascends.shared.general_ledger.journal_details.laba_kotor_ru.pdf', 'pdf' => ['format' => 'A4', 'orientation' => 'portrait'], 'filename' => fn (array $data, string $company): string => 'Journal Details - Laporan Laba Kotor RU ('.$company.').pdf'],
                ],
            ),
            'general_ledger.journal_details.laba-kotor-per-kategori' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.laba-kotor-per-kategori',
                service: LabaKotorPerKategoriReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.laba_kotor_per_kategori.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Laba Kotor Per Kategori ('.$company.').pdf',
            ),
            'general_ledger.journal_details.laba-kotor-ru-12-bulan' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.laba-kotor-ru-12-bulan',
                service: LabaKotorRu12BulanReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.laba_kotor_ru_12_bulan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Laba Kotor (Periode 12 Bulan/Tahunan) ('.$company.').pdf',
            ),
            'general_ledger.journal_details.laporan-laba-rugi' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.laporan-laba-rugi',
                service: LaporanLabaRugiGsuReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.laporan_laba_rugi_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Laba Rugi ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => LaporanLabaRugiRuReportService::class, 'view' => 'ascends.shared.general_ledger.journal_details.laporan_laba_rugi_ru.pdf'],
                    'uc' => ['service' => LaporanLabaRugiUcReportService::class, 'view' => 'ascends.shared.general_ledger.journal_details.laporan_laba_rugi_uc.pdf'],
                ],
            ),
            'general_ledger.journal_details.pendapatan-dan-biaya-lain' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.pendapatan-dan-biaya-lain',
                service: PendapatanDanBiayaLainReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.pendapatan_dan_biaya_lain.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Pendapatan Dan Biaya Lain-Lain ('.$company.').pdf',
            ),
            'general_ledger.journal_details.pendapatan-dan-biaya-lain-baru' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.pendapatan-dan-biaya-lain-baru',
                service: PendapatanDanBiayaLainBaruReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.pendapatan_dan_biaya_lain_baru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Pendapatan Dan Biaya Lain-Lain Baru ('.$company.').pdf',
            ),
            'general_ledger.journal_details.perhitungan-bunga' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.perhitungan-bunga',
                service: PerhitunganBungaGsuReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.perhitungan_bunga_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Piutang & Perhitungan Bunga GSU ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => PerhitunganBungaRuReportService::class, 'view' => 'ascends.shared.general_ledger.journal_details.perhitungan_bunga_ru.pdf', 'filename' => fn (array $data, string $company): string => 'Journal Details - Laporan Piutang & Perhitungan Bunga RU ('.$company.').pdf'],
                ],
            ),
            'general_ledger.journal_details.pinjaman-karyawan' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.pinjaman-karyawan',
                service: PinjamanKaryawanReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.pinjaman_karyawan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Pinjaman Karyawan ('.$company.').pdf',
            ),
            'general_ledger.journal_details.rekap-biaya-laba-rugi' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.rekap-biaya-laba-rugi',
                service: RekapBiayaLabaRugiReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.rekap_biaya_laba_rugi.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Rekap Biaya Laba Rugi ('.$company.').pdf',
            ),
            'general_ledger.journal_details.ringkasan-aktiva' => new CompanyReportDefinition(
                slug: 'general_ledger.journal_details.ringkasan-aktiva',
                service: RingkasanAktivaReportService::class,
                view: 'ascends.shared.general_ledger.journal_details.ringkasan_aktiva.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Journal Details - Laporan Ringkasan Aktiva Dalam Proses ('.$company.').pdf',
            ),
            'general_ledger.trial_balance.biaya-bayar-dimuka' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance.biaya-bayar-dimuka',
                service: BiayaBayarDimukaReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance.biaya_bayar_dimuka.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Biaya Bayar Dimuka ('.$company.').pdf',
            ),
            'general_ledger.trial_balance.hutang' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance.hutang',
                service: HutangUCReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance.hutang_uc.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Hutang UC ('.$company.').pdf',
            ),
            'general_ledger.trial_balance.hutang-lain-lain' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance.hutang-lain-lain',
                service: HutangLainnyaReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance.hutang_lainnya.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Hutang Lain-Lain ('.$company.').pdf',
            ),
            'general_ledger.trial_balance.neraca-per-bulan' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance.neraca-per-bulan',
                service: NeracaPerBulanReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance.neraca_per_bulan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Neraca Per Bulan ('.$company.').pdf',
            ),
            'general_ledger.trial_balance.ringkasan-hutang-bank' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance.ringkasan-hutang-bank',
                service: RingkasanHutangBankReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance.ringkasan_hutang_bank.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Ringkasan Hutang Bank ('.$company.').pdf',
            ),
            'general_ledger.trial_balance.saldo-bank' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance.saldo-bank',
                service: SaldoBankReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance.saldo_bank.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Saldo Bank ('.$company.').pdf',
            ),
            'general_ledger.trial_balance_monthly.arus-kas' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance_monthly.arus-kas',
                service: ArusKasGsuReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance_monthly.arus_kas_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Arus Kas ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => ArusKasRuReportService::class, 'view' => 'ascends.shared.general_ledger.trial_balance_monthly.arus_kas_ru.pdf'],
                    'uc' => ['service' => ArusKasUcReportService::class, 'view' => 'ascends.shared.general_ledger.trial_balance_monthly.arus_kas_uc.pdf'],
                ],
            ),
            'general_ledger.trial_balance_monthly.financial-rasio' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance_monthly.financial-rasio',
                service: FinancialRasioGsuReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance_monthly.financial_rasio_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Financial Ratio ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => FinancialRasioRuReportService::class, 'view' => 'ascends.shared.general_ledger.trial_balance_monthly.financial_rasio_ru.pdf'],
                    'uc' => ['service' => FinancialRasioUcReportService::class, 'view' => 'ascends.shared.general_ledger.trial_balance_monthly.financial_rasio_uc.pdf', 'filename' => fn (array $data, string $company): string => 'Laporan Financial Rasio ('.$company.').pdf'],
                ],
            ),
            'general_ledger.trial_balance_monthly.laba-rugi-multi-periode' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance_monthly.laba-rugi-multi-periode',
                service: LabaRugiMultiPeriodeReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance_monthly.laba_rugi_multi_periode.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Laba Rugi Multi Periode ('.$company.').pdf',
            ),
            'general_ledger.trial_balance_monthly.laba-rugi-multi-periode-tahunan' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance_monthly.laba-rugi-multi-periode-tahunan',
                service: LabaRugiMultiPeriodeTahunanReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance_monthly.laba_rugi_multi_periode_tahunan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Laba Rugi Multi Periode Tahunan ('.$company.').pdf',
            ),
            'general_ledger.trial_balance_monthly.pendukung-arus-kas' => new CompanyReportDefinition(
                slug: 'general_ledger.trial_balance_monthly.pendukung-arus-kas',
                service: PendukungArusKasReportService::class,
                view: 'ascends.shared.general_ledger.trial_balance_monthly.pendukung_arus_kas.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Pendukung Arus Kas ('.$company.').pdf',
            ),
            'hrm.absence.ketidakhadiran-bulanan' => new CompanyReportDefinition(
                slug: 'hrm.absence.ketidakhadiran-bulanan',
                service: KetidakhadiranBulananReportService::class,
                view: 'ascends.shared.hrm.absence.ketidakhadiran_bulanan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Absence - Laporan Ketidakhadiran Bulanan ('.$company.') - '.(($data['tipe'] ?? '') === 'ST' ? 'Staff' : 'KK/KT').'.pdf',
            ),
            'hrm.attendance-full.absensi-briefing-harian' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.absensi-briefing-harian',
                service: AbsensiBriefingHarianGsuReportService::class,
                view: 'ascends.shared.hrm.attendance_full.absensi_briefing_harian_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Absensi Briefing Harian ('.$company.')'.(($data['group'] ?? '') !== '' ? ' - '.$data['group'] : '').'.pdf',
                overrides: [
                    'ru' => ['service' => AbsensiBriefingHarianReportService::class, 'view' => 'ascends.shared.hrm.attendance_full.absensi_briefing_harian_ru.pdf'],
                    'uc' => ['service' => AbsensiBriefingHarianUcReportService::class, 'view' => 'ascends.shared.hrm.attendance_full.absensi_briefing_harian_uc.pdf'],
                ],
            ),
            'hrm.attendance-full.absensi-individu' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.absensi-individu',
                service: AbsensiIndividuReportService::class,
                view: 'ascends.shared.hrm.attendance_full.absensi_individu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Absensi Individu ('.$company.').pdf',
            ),
            'hrm.attendance-full.data-peserta-makan-siang-ibadah-aula-per-departemen' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.data-peserta-makan-siang-ibadah-aula-per-departemen',
                service: DataPesertaMakanSiangIbadahAulaPerDepartemenReportService::class,
                view: 'ascends.shared.hrm.attendance_full.data_peserta_makan_siang_ibadah_aula_per_departemen.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Data Peserta Penerima Makan Siang Ibadah Di Aula Per Departemen ('.$company.').pdf',
            ),
            'hrm.attendance-full.data-peserta-makan-siang-shalat-jumat-per-departemen' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.data-peserta-makan-siang-shalat-jumat-per-departemen',
                service: DataPesertaMakanSiangShalatJumatPerDepartemenReportService::class,
                view: 'ascends.shared.hrm.attendance_full.data_peserta_makan_siang_shalat_jumat_per_departemen.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Data Peserta Penerima Makan Siang Shalat Jumat Per Departemen ('.$company.').pdf',
            ),
            'hrm.attendance-full.kehadiran-kru-bahan-baku' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.kehadiran-kru-bahan-baku',
                service: KehadiranKruBahanBakuReportService::class,
                view: 'ascends.shared.hrm.attendance_full.kehadiran_kru_bahan_baku.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Kehadiran Kru Bahan Baku ('.$company.').pdf',
            ),
            'hrm.attendance-full.kehadiran-kru-racip' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.kehadiran-kru-racip',
                service: KehadiranKruRacipReportService::class,
                view: 'ascends.shared.hrm.attendance_full.kehadiran_kru_racip.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Kehadiran Kru Racip Dorong Dan Kru Racip Sambut ('.$company.').pdf',
            ),
            'hrm.attendance-full.kehadiran-kru-stick' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.kehadiran-kru-stick',
                service: KehadiranKruStickReportService::class,
                view: 'ascends.shared.hrm.attendance_full.kehadiran_kru_stick.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Kehadiran Kru Stick ('.$company.').pdf',
            ),
            'hrm.attendance-full.pengabaian-keterlambatan-kehadiran-manual' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.pengabaian-keterlambatan-kehadiran-manual',
                service: PengabaianKeterlambatanKehadiranManualReportService::class,
                view: 'ascends.shared.hrm.attendance_full.pengabaian_keterlambatan_kehadiran_manual.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Pengabaian Keterlambatan & Kehadiran Manual Per Departemen ('.($data['status'] ?? 'KK/KT').').pdf',
            ),
            'hrm.attendance-full.persentase-kehadiran-bulanan' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.persentase-kehadiran-bulanan',
                service: PersentaseKehadiranBulananReportService::class,
                view: 'ascends.shared.hrm.attendance_full.persentase_kehadiran_bulanan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Persentase Kehadiran Bulanan ('.($data['type'] ?? 'KK/KT').').pdf',
            ),
            'hrm.attendance-full.persentase-kehadiran-mingguan-per-departemen' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.persentase-kehadiran-mingguan-per-departemen',
                service: PersentaseKehadiranMingguanPerDepartemenReportService::class,
                view: 'ascends.shared.hrm.attendance_full.persentase_kehadiran_mingguan_per_departemen.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Persentase Kehadiran Mingguan Per Departemen ('.$company.').pdf',
            ),
            'hrm.attendance-full.rekapitulasi-absensi-briefing-harian' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.rekapitulasi-absensi-briefing-harian',
                service: RekapitulasiAbsensiBriefingHarianGsuReportService::class,
                view: 'ascends.shared.hrm.attendance_full.rekapitulasi_absensi_briefing_harian_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Rekapitulasi Absensi Briefing Harian ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => RekapitulasiAbsensiBriefingHarianReportService::class, 'view' => 'ascends.shared.hrm.attendance_full.rekapitulasi_absensi_briefing_harian_ru.pdf'],
                ],
            ),
            'hrm.attendance-full.rekapitulasi-kehadiran-kurang-93-tahunan' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.rekapitulasi-kehadiran-kurang-93-tahunan',
                service: RekapitulasiKehadiranKurang93TahunanReportService::class,
                view: 'ascends.shared.hrm.attendance_full.rekapitulasi_kehadiran_kurang_93_tahunan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Rekapitulasi Kehadiran Kurang dari 93 % Tahunan ('.($data['status'] ?? 'KK/KT').').pdf',
            ),
            'hrm.attendance-full.rekapitulasi-pengabaian-keterlambatan-tahunan' => new CompanyReportDefinition(
                slug: 'hrm.attendance-full.rekapitulasi-pengabaian-keterlambatan-tahunan',
                service: RekapitulasiPengabaianKeterlambatanTahunanReportService::class,
                view: 'ascends.shared.hrm.attendance_full.rekapitulasi_pengabaian_keterlambatan_tahunan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance Full - Laporan Rekapitulasi Pengabaian Keterlambatan Tahunan ('.($data['status'] ?? 'KK/KT').').pdf',
            ),
            'hrm.attendance.keterlambatan-kehadiran-briefing-harian' => new CompanyReportDefinition(
                slug: 'hrm.attendance.keterlambatan-kehadiran-briefing-harian',
                service: KeterlambatanKehadiranBriefingHarianReportService::class,
                view: 'ascends.shared.hrm.attendance.keterlambatan_kehadiran_briefing_harian.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance - Laporan Keterlambatan Kehadiran Briefing Harian ('.$company.').pdf',
            ),
            'hrm.attendance.perbandingan-kehadiran-per-bulan' => new CompanyReportDefinition(
                slug: 'hrm.attendance.perbandingan-kehadiran-per-bulan',
                service: PerbandinganKehadiranPerBulanReportService::class,
                view: 'ascends.shared.hrm.attendance.perbandingan_kehadiran_per_bulan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Attendance - Laporan Perbandingan Kehadiran Per Bulan ('.$company.').pdf',
            ),
            'hrm.custom-reports.diagram-lembur-tahunan' => new CompanyReportDefinition(
                slug: 'hrm.custom-reports.diagram-lembur-tahunan',
                service: DiagramLemburTahunanReportService::class,
                view: 'ascends.shared.hrm.custom_reports.diagram_lembur_tahunan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Custom Reports - Laporan Diagram Lembur Tahunan Per Departemen ('.$company.').pdf',
            ),
            'hrm.custom-reports.karyawan-keluar' => new CompanyReportDefinition(
                slug: 'hrm.custom-reports.karyawan-keluar',
                service: KaryawanKeluarReportService::class,
                view: 'ascends.shared.hrm.custom_reports.karyawan_keluar.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Karyawan Keluar Per Departemen Per Tanggal Keluar ('.$company.').pdf',
            ),
            'hrm.custom-reports.karyawan-keluar-tahunan' => new CompanyReportDefinition(
                slug: 'hrm.custom-reports.karyawan-keluar-tahunan',
                service: KaryawanKeluarTahunanReportService::class,
                view: 'ascends.shared.hrm.custom_reports.karyawan_keluar_tahunan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Laporan Karyawan Keluar Per Departemen Tahunan ('.($data['status'] ?? '').') ('.$company.').pdf',
            ),
            'hrm.custom-reports.karyawan-masuk' => new CompanyReportDefinition(
                slug: 'hrm.custom-reports.karyawan-masuk',
                service: KaryawanMasukReportService::class,
                view: 'ascends.shared.hrm.custom_reports.karyawan_masuk.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Karyawan Masuk Per Departemen Per Tanggal Masuk ('.$company.').pdf',
            ),
            'hrm.custom-reports.lembur-bulanan' => new CompanyReportDefinition(
                slug: 'hrm.custom-reports.lembur-bulanan',
                service: CustomReportsLemburBulananReportService::class,
                view: 'ascends.shared.hrm.custom_reports.lembur_bulanan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Custom Reports - Laporan Lembur Bulanan Per Departemen ('.($data['type_label'] ?? 'KK/KT').').pdf',
            ),
            'hrm.custom-reports.surat-peringatan' => new CompanyReportDefinition(
                slug: 'hrm.custom-reports.surat-peringatan',
                service: CustomReportsSuratPeringatanReportService::class,
                view: 'ascends.shared.hrm.custom_reports.surat_peringatan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Custom Reports - Laporan Surat Peringatan ('.$company.').pdf',
            ),
            'hrm.custom-reports.verifikasi-lembur' => new CompanyReportDefinition(
                slug: 'hrm.custom-reports.verifikasi-lembur',
                service: VerifikasiLemburReportService::class,
                view: 'ascends.shared.hrm.custom_reports.verifikasi_lembur.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Custom Reports - Laporan Verifikasi Lembur ('.$company.').pdf',
            ),
            'hrm.daftar-karyawan' => new CompanyReportDefinition(
                slug: 'hrm.daftar-karyawan',
                service: DaftarKaryawanReportService::class,
                view: 'ascends.shared.hrm.employee_list.daftar_karyawan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Daftar Karyawan ('.$company.').pdf',
            ),
            'hrm.daftar-karyawan-berdasarkan-abjad' => new CompanyReportDefinition(
                slug: 'hrm.daftar-karyawan-berdasarkan-abjad',
                service: DaftarKaryawanBerdasarkanAbjadReportService::class,
                view: 'ascends.shared.hrm.employee_list.daftar_karyawan_berdasarkan_abjad.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Daftar Karyawan Berdasarkan Abjad ('.$company.').pdf',
            ),
            'hrm.data-karyawan-status-kerja' => new CompanyReportDefinition(
                slug: 'hrm.data-karyawan-status-kerja',
                service: DataKaryawanStatusKerjaReportService::class,
                view: 'ascends.shared.hrm.employee_list.data_karyawan_status_kerja.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Data Karyawan Staff, Karyawan Tetap & Karyawan Kontrak Berdasarkan Status Kerja ('.$company.').pdf',
            ),
            'hrm.employee-list.diagram-karyawan-per-divisi' => new CompanyReportDefinition(
                slug: 'hrm.employee-list.diagram-karyawan-per-divisi',
                service: DiagramKaryawanPerDivisiReportService::class,
                view: 'ascends.shared.hrm.employee_list.diagram_karyawan_per_divisi.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Diagram Karyawan Per Divisi ('.$company.').pdf',
            ),
            'hrm.employee-list.diagram-karyawan-per-jenis-kelamin' => new CompanyReportDefinition(
                slug: 'hrm.employee-list.diagram-karyawan-per-jenis-kelamin',
                service: DiagramKaryawanPerJenisKelaminReportService::class,
                view: 'ascends.shared.hrm.employee_list.diagram_karyawan_per_jenis_kelamin.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Diagram Karyawan Per Jenis Kelamin ('.$company.').pdf',
            ),
            'hrm.employee-list.diagram-karyawan-per-strata-pendidikan' => new CompanyReportDefinition(
                slug: 'hrm.employee-list.diagram-karyawan-per-strata-pendidikan',
                service: DiagramKaryawanPerStrataPendidikanReportService::class,
                view: 'ascends.shared.hrm.employee_list.diagram_karyawan_per_strata_pendidikan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Diagram Karyawan Per Strata Pendidikan ('.$company.').pdf',
            ),
            'hrm.employee-list.mpp-tahunan-per-divisi' => new CompanyReportDefinition(
                slug: 'hrm.employee-list.mpp-tahunan-per-divisi',
                service: MppTahunanPerDivisiGsuReportService::class,
                view: 'ascends.shared.hrm.employee_list.mpp_tahunan_per_divisi_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan MPP Tahunan Per Divisi '.($data['divisi'] ?? '').' ('.$company.').pdf',
            ),
            'hrm.employee-termination' => new CompanyReportDefinition(
                slug: 'hrm.employee-termination',
                service: EmployeeTerminationReportService::class,
                view: 'ascends.shared.hrm.employee_termination.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee Termination - Laporan Karyawan Keluar Per Departemen Per Tanggal Keluar ('.$company.').pdf',
            ),
            'hrm.holiday.daftar-libur-cuti-bersama' => new CompanyReportDefinition(
                slug: 'hrm.holiday.daftar-libur-cuti-bersama',
                service: DaftarLiburCutiBersamaReportService::class,
                view: 'ascends.shared.hrm.holiday.daftar_libur_cuti_bersama.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Holiday - Daftar Libur Dan Cuti Bersama ('.$company.').pdf',
            ),
            'hrm.karyawan-aktif-per-departemen' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-aktif-per-departemen',
                service: KaryawanAktifPerDepartemenReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_aktif_per_departemen.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Aktif Per Departemen ('.$company.').pdf',
            ),
            'hrm.karyawan-masuk-per-departemen-per-tanggal-masuk' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-masuk-per-departemen-per-tanggal-masuk',
                service: KaryawanMasukPerDepartemenPerTanggalMasukReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_masuk_per_departemen_per_tanggal_masuk.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Masuk Per Departemen Per Tanggal Masuk ('.$company.').pdf',
            ),
            'hrm.karyawan-per-agama' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-per-agama',
                service: KaryawanPerAgamaReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_per_agama.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Per Agama ('.$company.').pdf',
            ),
            'hrm.karyawan-per-departemen-per-jabatan' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-per-departemen-per-jabatan',
                service: KaryawanPerDepartemenPerJabatanReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_per_departemen_per_jabatan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Per Departemen Per Jabatan ('.$company.').pdf',
            ),
            'hrm.karyawan-per-etnis' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-per-etnis',
                service: KaryawanPerEtnisReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_per_etnis.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Per Etnis ('.$company.').pdf',
            ),
            'hrm.karyawan-per-level' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-per-level',
                service: KaryawanPerLevelReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_per_level.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Per Level ('.$company.').pdf',
            ),
            'hrm.karyawan-per-masa-kerja' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-per-masa-kerja',
                service: KaryawanPerMasaKerjaReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_per_masa_kerja.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Per Masa Kerja ('.$company.').pdf',
            ),
            'hrm.karyawan-per-umur' => new CompanyReportDefinition(
                slug: 'hrm.karyawan-per-umur',
                service: KaryawanPerUmurReportService::class,
                view: 'ascends.shared.hrm.employee_list.karyawan_per_umur.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Karyawan Per Umur ('.$company.').pdf',
            ),
            'hrm.kehadiran-kk-kt-st' => new CompanyReportDefinition(
                slug: 'hrm.kehadiran-kk-kt-st',
                service: KehadiranKkKtStReportService::class,
                view: 'ascends.shared.hrm.employee_list.kehadiran_kk_kt_st.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Kehadiran KK KT ST ('.$company.').pdf',
            ),
            'hrm.late-sign-in.durasi-denda-keterlambatan' => new CompanyReportDefinition(
                slug: 'hrm.late-sign-in.durasi-denda-keterlambatan',
                service: DurasiDendaKeterlambatanReportService::class,
                view: 'ascends.shared.hrm.late_sign_in.durasi_denda_keterlambatan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Late Sign In - Laporan Durasi & Denda Keterlambatan Per Departemen '.str_replace('/', ' ', (string) ($data['type'] ?? 'KK KT')).' ('.$company.').pdf',
            ),
            'hrm.list-karyawan' => new CompanyReportDefinition(
                slug: 'hrm.list-karyawan',
                service: EmployeeListReportService::class,
                view: 'ascends.shared.hrm.employee_list.list_karyawan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - List Karyawan ('.$company.').pdf',
            ),
            'hrm.list-karyawan-habis-kontrak' => new CompanyReportDefinition(
                slug: 'hrm.list-karyawan-habis-kontrak',
                service: ListKaryawanHabisKontrakReportService::class,
                view: 'ascends.shared.hrm.employee_list.list_karyawan_habis_kontrak.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan List Karyawan Habis Kontrak ('.$company.').pdf',
            ),
            'hrm.loss-time' => new CompanyReportDefinition(
                slug: 'hrm.loss-time',
                service: LossTimeReportService::class,
                view: 'ascends.shared.hrm.loss_time.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Loss Time - Laporan Loss Time '.($data['type'] ?? '').' ('.$company.').pdf',
            ),
            'hrm.other-income-deduction.pendapatan-lain-lain' => new CompanyReportDefinition(
                slug: 'hrm.other-income-deduction.pendapatan-lain-lain',
                service: PendapatanLainLainReportService::class,
                view: 'ascends.shared.hrm.other_income_deduction.pendapatan_lain_lain.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Other Income Deduction - Laporan Pendapatan Lain-Lain ('.$company.').pdf',
            ),
            'hrm.overtime.lembur-bulanan' => new CompanyReportDefinition(
                slug: 'hrm.overtime.lembur-bulanan',
                service: HrmLemburBulananReportService::class,
                view: 'ascends.shared.hrm.overtime.lembur_bulanan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Overtime - Laporan Lembur Bulanan '.str_replace('/', ' ', (string) ($data['type'] ?? 'KK KT')).' Per Departemen ('.$company.').pdf',
            ),
            'hrm.perbandingan-jumlah-karyawan-tahunan-per-bulan' => new CompanyReportDefinition(
                slug: 'hrm.perbandingan-jumlah-karyawan-tahunan-per-bulan',
                service: PerbandinganJumlahKaryawanTahunanPerBulanReportService::class,
                view: 'ascends.shared.hrm.employee_list.perbandingan_jumlah_karyawan_tahunan_per_bulan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Perbandingan Jumlah Karyawan Tahunan Per Bulan ('.$company.').pdf',
            ),
            'hrm.thr' => new CompanyReportDefinition(
                slug: 'hrm.thr',
                service: ThrReportService::class,
                view: 'ascends.shared.hrm.employee_list.thr.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan THR ('.($data['thr_type'] ?? 'Idul Fitri').') ('.$company.').pdf',
            ),
            'hrm.usia-generasi-tahun-kelahiran-masa-kerja' => new CompanyReportDefinition(
                slug: 'hrm.usia-generasi-tahun-kelahiran-masa-kerja',
                service: UsiaGenerasiTahunKelahiranMasaKerjaReportService::class,
                view: 'ascends.shared.hrm.employee_list.usia_generasi_tahun_kelahiran_masa_kerja.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Employee List - Laporan Usia Generasi Berdasakan Tahun Kelahiran dan Masa Kerja ('.$company.').pdf',
            ),
            'hrm.warning-notice.surat-peringatan' => new CompanyReportDefinition(
                slug: 'hrm.warning-notice.surat-peringatan',
                service: HrmSuratPeringatanReportService::class,
                view: 'ascends.shared.hrm.warning_notice.surat_peringatan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Warning Notice - Laporan Surat Peringatan ('.$company.').pdf',
            ),
            'inventory_analysis.adjustment-by-item.adjustment.adjustment-lemari' => new CompanyReportDefinition(
                slug: 'inventory_analysis.adjustment-by-item.adjustment.adjustment-lemari',
                service: AdjustmentLemariReportService::class,
                view: 'ascends.shared.inventory_analysis.adjustment_by_item.adjustment.adjustment_lemari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Adjustment By Item - Laporan Adjustment Lemari ('.$company.').pdf',
            ),
            'inventory_analysis.adjustment-by-item.adjustment.khusus-kursi' => new CompanyReportDefinition(
                slug: 'inventory_analysis.adjustment-by-item.adjustment.khusus-kursi',
                service: KursiAdjustmentReportService::class,
                view: 'ascends.shared.inventory_analysis.adjustment_by_item.adjustment.khusus_kursi.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Adjustment By Item - Laporan Adjustment Selisih Kursi ('.$company.').pdf',
            ),
            'inventory_analysis.adjustment-by-item.adjustment.khusus-lemari' => new CompanyReportDefinition(
                slug: 'inventory_analysis.adjustment-by-item.adjustment.khusus-lemari',
                service: LemariAdjustmentReportService::class,
                view: 'ascends.shared.inventory_analysis.adjustment_by_item.adjustment.khusus_lemari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Adjustment By Item - Laporan Adjustment Selisih Lemari ('.$company.').pdf',
            ),
            'inventory_analysis.adjustment-by-item.goods-delivery-note.pengiriman-lemari' => new CompanyReportDefinition(
                slug: 'inventory_analysis.adjustment-by-item.goods-delivery-note.pengiriman-lemari',
                service: PengirimanLemariReportService::class,
                view: 'ascends.shared.inventory_analysis.goods_delivery_note.pengiriman_lemari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Goods Delivery Note - Laporan Pengiriman Lemari ('.$company.').pdf',
            ),
            'inventory_analysis.adjustment-by-item.goods-delivery-note.rekapan-value-surat-jalan' => new CompanyReportDefinition(
                slug: 'inventory_analysis.adjustment-by-item.goods-delivery-note.rekapan-value-surat-jalan',
                service: RekapanValueSuratJalanReportService::class,
                view: 'ascends.shared.inventory_analysis.goods_delivery_note.rekapan_value_surat_jalan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Goods Delivery Note - Laporan Rekapan Value Surat Jalan ('.$company.').pdf',
            ),
            'inventory_analysis.adjustment-by-item.penyesuaian-persediaan' => new CompanyReportDefinition(
                slug: 'inventory_analysis.adjustment-by-item.penyesuaian-persediaan',
                service: PenyesuaianPersediaanReportService::class,
                view: 'ascends.shared.inventory_analysis.adjustment_by_item.penyesuaian_persediaan.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Adjustment By Item - Laporan Penyesuaian Persediaan ('.$company.').pdf',
            ),
            'inventory_analysis.outstanding-undelivery-goods.do-customer-belum-terkirim' => new CompanyReportDefinition(
                slug: 'inventory_analysis.outstanding-undelivery-goods.do-customer-belum-terkirim',
                service: DOCustomerBelumTerkirimReportService::class,
                view: 'ascends.shared.inventory_analysis.outstanding_undelivery_goods.do_customer_belum_terkirim.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Outstanding Undelivery Goods - Laporan DO Customer Belum Terkirim ('.$company.').pdf',
            ),
            'inventory_analysis.outstanding-undelivery-goods.do-lemari-belum-terkirim' => new CompanyReportDefinition(
                slug: 'inventory_analysis.outstanding-undelivery-goods.do-lemari-belum-terkirim',
                service: DOLemariBelumTerkirimReportService::class,
                view: 'ascends.shared.inventory_analysis.outstanding_undelivery_goods.do_lemari_belum_terkirim.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Outstanding Undelivery Goods - Laporan DO Lemari Belum Terkirim ('.$company.').pdf',
            ),
            'inventory_analysis.outstanding-undelivery-goods.do-per-kategori-belum-terkirim' => new CompanyReportDefinition(
                slug: 'inventory_analysis.outstanding-undelivery-goods.do-per-kategori-belum-terkirim',
                service: DOPerKategoriBelumTerkirimReportService::class,
                view: 'ascends.shared.inventory_analysis.outstanding_undelivery_goods.do_per_kategori_belum_terkirim.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Outstanding Undelivery Goods - Laporan DO Per Kategori Belum Terkirim ('.$company.').pdf',
            ),
            'inventory_analysis.outstanding-undelivery-goods.list-do-belum-terkirim' => new CompanyReportDefinition(
                slug: 'inventory_analysis.outstanding-undelivery-goods.list-do-belum-terkirim',
                service: ListDOBelumTerkirimReportService::class,
                view: 'ascends.shared.inventory_analysis.outstanding_undelivery_goods.list_do_belum_terkirim.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Outstanding Undelivery Goods - Laporan List DO Belum Terkirim ('.$company.').pdf',
            ),
            'inventory_analysis.production.hasil-broker-per-hari' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production.hasil-broker-per-hari',
                service: HasilBrokerPerHariReportService::class,
                view: 'ascends.shared.inventory_analysis.production.hasil_broker_per_hari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production - Laporan Harian Hasil Broker ('.$company.').pdf',
            ),
            'inventory_analysis.production.hasil-broker-per-kategori' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production.hasil-broker-per-kategori',
                service: HasilBrokerPerKategoriReportService::class,
                view: 'ascends.shared.inventory_analysis.production.hasil_broker_per_kategori.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production - Laporan Hasil Broker Per Kategori ('.$company.').pdf',
            ),
            'inventory_analysis.production.hasil-broker-per-mesin' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production.hasil-broker-per-mesin',
                service: HasilBrokerPerMesinReportService::class,
                view: 'ascends.shared.inventory_analysis.production.hasil_broker_per_mesin.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Production - Laporan Hasil Broker Per Mesin ('.$company.').pdf',
            ),
            'inventory_analysis.production.hasil-cuci-per-hari' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production.hasil-cuci-per-hari',
                service: HasilCuciPerHariReportService::class,
                view: 'ascends.shared.inventory_analysis.production.hasil_cuci_per_hari.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production - Laporan Harian Hasil Cuci ('.$company.').pdf',
            ),
            'inventory_analysis.production.hasil-cuci-per-mesin' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production.hasil-cuci-per-mesin',
                service: HasilCuciPerMesinReportService::class,
                view: 'ascends.shared.inventory_analysis.production.hasil_cuci_per_mesin.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production - Laporan Hasil Cuci Per Mesin ('.$company.').pdf',
            ),
            'inventory_analysis.production.hasil-cuci-per-supplier' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production.hasil-cuci-per-supplier',
                service: HasilCuciPerSupplierReportService::class,
                view: 'ascends.shared.inventory_analysis.production.hasil_cuci_per_supplier.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production - Laporan Hasil Cuci Per Supplier ('.$company.').pdf',
            ),
            'inventory_analysis.production.hasil-produksi-per-mesin' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production.hasil-produksi-per-mesin',
                service: HasilProduksiPerMesinReportService::class,
                view: 'ascends.shared.inventory_analysis.production.hasil_produksi_per_mesin.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production - Laporan Hasil Produksi Per Mesin ('.$company.').pdf',
            ),
            'inventory_analysis.production_by_item.produksi' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production_by_item.produksi',
                service: ProduksiReportService::class,
                view: 'ascends.shared.inventory_analysis.production_by_item.produksi.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production By Item - Laporan Produksi ('.$company.').pdf',
            ),
            'inventory_analysis.production_by_item.produksi-per-minggu' => new CompanyReportDefinition(
                slug: 'inventory_analysis.production_by_item.produksi-per-minggu',
                service: ProduksiPerMingguReportService::class,
                view: 'ascends.shared.inventory_analysis.production_by_item.produksi_per_minggu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Production By Item - Laporan Produksi Per Minggu ('.$company.').pdf',
            ),
            'inventory_analysis.purchase-by-item.ringkasan-pembelian' => new CompanyReportDefinition(
                slug: 'inventory_analysis.purchase-by-item.ringkasan-pembelian',
                service: RingkasanPembelianRuReportService::class,
                view: 'ascends.shared.inventory_analysis.purchase_by_item.ringkasan_pembelian_ru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Purchase By Item - Laporan Ringkasan Pembelian ('.$company.').pdf',
            ),
            'inventory_analysis.purchase-order-by-item.history-harga-po' => new CompanyReportDefinition(
                slug: 'inventory_analysis.purchase-order-by-item.history-harga-po',
                service: HistoryHargaPoReportService::class,
                view: 'ascends.shared.inventory_analysis.purchase_order_by_item.history_harga_po.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Purchase Order By Item - Laporan History Harga Purchase Order ('.$company.').pdf',
            ),
            'inventory_analysis.purchase-order-by-item.jangka-waktu-po-pi' => new CompanyReportDefinition(
                slug: 'inventory_analysis.purchase-order-by-item.jangka-waktu-po-pi',
                service: JangkaWaktuPoPiReportService::class,
                view: 'ascends.shared.inventory_analysis.purchase_order_by_item.jangka_waktu_po_pi.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Purchase Order By Item - Laporan Jangka Waktu P.Order Ke P.Invoice ('.$company.').pdf',
            ),
            'inventory_analysis.purchase-request-by-item.jangka-waktu-approve-pr-po-detail' => new CompanyReportDefinition(
                slug: 'inventory_analysis.purchase-request-by-item.jangka-waktu-approve-pr-po-detail',
                service: JangkaWaktuApprovePrPoDetailReportService::class,
                view: 'ascends.shared.inventory_analysis.purchase_request_by_item.jangka_waktu_approve_pr_po_detail.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Purchase Request By Item - Laporan Jangka Waktu Approved P.Request Dan P.Order ('.$company.').pdf',
            ),
            'inventory_analysis.sales-by-item.penjualan-per-group-bulanan' => new CompanyReportDefinition(
                slug: 'inventory_analysis.sales-by-item.penjualan-per-group-bulanan',
                service: PenjualanPerGroupBulananRuReportService::class,
                view: 'ascends.shared.inventory_analysis.sales_by_item.penjualan_per_group_bulanan_ru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Sales By Item - Laporan Penjualan Per Item Family ('.$company.').pdf',
            ),
            'inventory_analysis.sales-by-item.persentase-hpp-penjualan-per-item-family' => new CompanyReportDefinition(
                slug: 'inventory_analysis.sales-by-item.persentase-hpp-penjualan-per-item-family',
                service: PersentaseHppPenjualanPerItemFamilyRuReportService::class,
                view: 'ascends.shared.inventory_analysis.sales_by_item.persentase_hpp_pernjualan_per_item_family_ru.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Sales By Item - Laporan Persentase HPP Penjualan Per Item Family ('.$company.').pdf',
            ),
            'inventory_analysis.sales-by-item.target-sales-per-minggu' => new CompanyReportDefinition(
                slug: 'inventory_analysis.sales-by-item.target-sales-per-minggu',
                service: TargetSalesPerMingguReportService::class,
                view: 'ascends.shared.inventory_analysis.sales_by_item.target_sales_per_minggu.pdf',
                pdf: ['format' => 'A4-L', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Sales By Item - Laporan Sales Mingguan dan Target ('.$company.').pdf',
            ),
            'inventory_analysis.stock-activities-summary.aktifitas-stock' => new CompanyReportDefinition(
                slug: 'inventory_analysis.stock-activities-summary.aktifitas-stock',
                service: AktifitasStockGsuReportService::class,
                view: 'ascends.shared.inventory_analysis.stock_activities_summary.aktifitas_stock_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Stock Activities Summary - Ringkasan Valuasi Persediaan ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => AktifitasStockRuReportService::class, 'view' => 'ascends.shared.inventory_analysis.stock_activities_summary.aktifitas_stock_ru.pdf'],
                ],
            ),
            'inventory_analysis.stock-activities-summary.aktifitas-stock-gsu-per-gudang' => new CompanyReportDefinition(
                slug: 'inventory_analysis.stock-activities-summary.aktifitas-stock-gsu-per-gudang',
                service: AktifitasStockGsuPerGudangReportService::class,
                view: 'ascends.shared.inventory_analysis.stock_activities_summary.aktifitas_stock_gsu_per_gudang.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Stock Activities Summary - Ringkasan Valuasi Persediaan Per Gudang ('.$company.').pdf',
            ),
            'inventory_analysis.stock-activities-summary.khusus-plastik-kabinet' => new CompanyReportDefinition(
                slug: 'inventory_analysis.stock-activities-summary.khusus-plastik-kabinet',
                service: KhususPlastikKabinetReportService::class,
                view: 'ascends.shared.inventory_analysis.stock_activities_summary.khusus_plastik_kabinet.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'landscape'],
                filename: fn (array $data, string $company): string => 'Stock Activities Summary - Laporan Khusus Plastik Kabinet ('.$company.').pdf',
            ),
            'inventory_analysis.stock-activities-summary.laporan-hpp-dan-stock' => new CompanyReportDefinition(
                slug: 'inventory_analysis.stock-activities-summary.laporan-hpp-dan-stock',
                service: LaporanHppDanStockReportService::class,
                view: 'ascends.shared.inventory_analysis.stock_activities_summary.laporan_hpp_dan_stock.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Stock Activities Summary - Laporan HPP Dan Stock ('.$company.').pdf',
            ),
            'inventory_analysis.stock-blanace.saldo-stok-barang-per-gudang' => new CompanyReportDefinition(
                slug: 'inventory_analysis.stock-blanace.saldo-stok-barang-per-gudang',
                service: SaldoStokBarangPerGudangGsuReportService::class,
                view: 'ascends.shared.inventory_analysis.stock_blanace.saldo_stok_barang_per_gudang_gsu.pdf',
                pdf: ['format' => 'A4', 'orientation' => 'portrait'],
                filename: fn (array $data, string $company): string => 'Laporan Pendukung Stock Opname ('.$company.').pdf',
                overrides: [
                    'ru' => ['service' => SaldoStokBarangPerGudangRuReportService::class, 'view' => 'ascends.shared.inventory_analysis.stock_blanace.saldo_stok_barang_per_gudang_ru.pdf'],
                    'uc' => ['service' => SaldoStokBarangPerGudangUcReportService::class, 'view' => 'ascends.shared.inventory_analysis.stock_blanace.saldo_stok_barang_per_gudang_uc.pdf'],
                ],
            ),
        ];
    }

    public static function get(string $slug): ?CompanyReportDefinition
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * Company codes the slug is published to; an unknown slug is published nowhere.
     *
     * @return list<string>
     */
    public static function allowedCompanies(string $slug): array
    {
        if (! isset(self::all()[$slug])) {
            return [];
        }

        return self::PUBLISHED[$slug] ?? self::COMPANIES;
    }

    /**
     * @return list<string>
     */
    public static function companies(): array
    {
        return self::COMPANIES;
    }
}

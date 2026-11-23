# Batas Request Report Ascends

Dokumen ini untuk membedakan kegagalan di aplikasi Ascend (client .NET) dan di API report.

## Gejala

Client .NET menampilkan:

```text
System.OutOfMemoryException
   at System.Text.Encoding.GetString(...)
   at AnalysisReports.FrontEnd.PrintForm.PrintMPDF(DataSet dataSet, FormRegistrationInfo form)
```

Stack trace berada di `AnalysisReports.FrontEnd` (aplikasi Ascend), bukan di API. Exception terjadi saat client mengubah seluruh XML DataSet menjadi string/JSON sebelum request dikirim.

## Yang sudah disiapkan di API

- `LogAscendsRequest` mencatat setiap request `api/internal/ascends/*` ke `storage/logs/ascends-requests.log`:
  - `content_length`, `content_type`, `status`, `duration_ms`
  - `peak_memory_mb`, `memory_limit`, `post_max_size`, `upload_max_filesize`
- Request di atas `post_max_size` dijawab **413 JSON** dengan nilai limit yang berlaku, bukan error tidak jelas.
- Runtime `memory_limit` untuk route Ascends dapat dinaikkan lewat `ASCENDS_MEMORY_LIMIT` (default `1024M`).
- API menerima XML dalam tiga bentuk:
  - `xml` (string JSON/form)
  - `xml_file` (multipart file)
  - raw body `Content-Type: application/xml`

Contoh baris log request yang berhasil:

```json
{"method":"POST","path":"api/internal/ascends/gsu/finance/receipt_voucher_details/giro-cash-transfer/pdf","content_length":314574,"status":200,"duration_ms":118,"peak_memory_mb":26,"memory_limit":"4G","post_max_size":"512M"}
```

Jika client OOM sebelum mengirim, **tidak ada baris log baru** untuk request tersebut. Itu bukti request tidak sampai ke API.

## Rekomendasi php.ini server API

Nilai default server (terakhir dicek) terlalu kecil untuk XML besar:

```ini
memory_limit = 128M
post_max_size = 8M
upload_max_filesize = 2M
max_execution_time = 30
max_input_time = 60
```

Rekomendasi:

```ini
memory_limit = 1024M
post_max_size = 256M
upload_max_filesize = 256M
max_execution_time = 300
max_input_time = 300
```

`post_max_size` dan `upload_max_filesize` tidak bisa dinaikkan lewat `ini_set`; harus di `php.ini` lalu restart PHP/web server.

## Verifikasi

```bash
php -i | grep -E "memory_limit|post_max_size|upload_max_filesize|max_execution_time"
```

Setelah request:

```bash
tail -n 5 storage/logs/ascends-requests.log
```

## Catatan client

OOM tetap terjadi di client 32-bit (x86). Untuk periode besar, client perlu:

1. Menulis XML DataSet ke file/stream, bukan `MemoryStream` + `Encoding.UTF8.GetString`.
2. Tetap mengirim JSON `{"xml":"..."}` dengan streaming, atau memakai `xml_file`/raw XML yang sudah didukung API.
3. Saat memeriksa response PDF, cukup decode 5 byte pertama untuk `%PDF-`, jangan seluruh PDF.

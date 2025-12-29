# HR Analytics System - Analisis Performa Pemanen

Sistem analisis performa pemanen sawit dengan fitur upload multiple Excel files, akumulasi data, dan export HTML.

## 🚀 Fitur Utama

1. **Upload Multiple Excel Files** - Upload data dari berbagai periode yang akan terakumulasi
2. **Database Storage** - Data tersimpan persistent di PostgreSQL
3. **Dashboard Interaktif** - Visualisasi matriks produktivitas dengan kategorisasi otomatis
4. **Export HTML** - Export snapshot dashboard dengan data yang dipilih untuk dibagikan ke petugas lapangan
5. **Export PDF & PNG** - Export visualisasi untuk laporan
6. **Filter Multi-Dimensi** - Filter berdasarkan periode, AFD, kategori
7. **Simulasi Produksi** - Estimasi potensi kenaikan tonase

## 📋 Requirements

- PHP >= 8.1
- PostgreSQL >= 12
- Composer
- Node.js & NPM (optional, untuk development)

## 🛠️ Instalasi

### 1. Clone & Install Dependencies

```bash
cd C:\laragon\www\MyApp\analisis-performa-pemanen
composer install
```

### 2. Configure Environment

Copy `.env.example` ke `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hr_analytics
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Setup Database

Buat database PostgreSQL:

```sql
CREATE DATABASE hr_analytics;
```

Jalankan migration:

```bash
php artisan migrate
```

### 5. Run Application

**Menggunakan Laravel Development Server:**
```bash
php artisan serve
```

Akses di: `http://localhost:8000`

**Menggunakan Laragon:**
- Pastikan Laragon sudah running
- Akses di: `http://analisis-performa-pemanen.test` (atau sesuai virtual host)

## 📊 Struktur Database

### Table: `karyawan_performances`
Menyimpan data performa individual karyawan

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| nik | string | NIK karyawan (optional) |
| nama | string | Nama karyawan |
| afd | string | Afdeling/divisi |
| hk | decimal(8,2) | Hari Kerja |
| jjg | integer | Jumlah Janjang |
| ton | decimal(10,3) | Tonase |
| kg_per_hk | decimal(10,2) | Produktivitas (Kg/HK) |
| periode | string | Periode data (YYYY-MM) |
| tanggal_upload | date | Tanggal upload |
| uploaded_by | string | User yang upload |
| batch_id | string | ID batch upload |
| notes | text | Catatan |

### Table: `upload_batches`
Menyimpan informasi batch upload

| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| batch_id | string | Unique batch identifier |
| filename | string | Nama file yang diupload |
| periode | string | Periode data |
| total_records | integer | Jumlah record |
| uploaded_by | string | User yang upload |
| metadata | json | Metadata (BJR, dll) |

## 📁 Format Excel Upload

### Kolom Wajib:
- **NAMA** - Nama karyawan
- **HK** atau **HARI_KERJA** - Jumlah hari kerja
- **JJG** atau **JANJANG** - Jumlah janjang

### Kolom Opsional:
- **ID** atau **NIK** - NIK karyawan
- **AFD** atau **AFDELING** atau **DIVISI** - Afdeling
- **TON** atau **TONASE** - Tonase (jika ada, akan override kalkulasi)
- **KG** - Total Kg (jika ada, akan override kalkulasi)
- **PROD** atau **PRODUKTIVITAS** atau **KG/HK** - Produktivitas (jika ada, akan override kalkulasi)

### Contoh Format:

| AFD | NAMA | HK | JJG | TON | PROD | ID |
|-----|------|----|----|-----|------|----|
| I | Budi Santoso | 25 | 1500 | 22.5 | 900 | 1001 |
| II | Agus Salim | 22 | 1300 | 19.5 | 886 | 1002 |

## 🎯 Cara Penggunaan

### A. Upload Data (Halaman Admin)

1. Akses: `http://localhost:8000/admin`
2. Pilih file Excel yang akan diupload
3. Isi periode (format: YYYY-MM atau label custom)
4. Atur BJR (Berat Janjang Rata-rata) sesuai kebutuhan
5. Tambahkan catatan jika diperlukan
6. Klik "Upload & Proses"

### B. Analisis Data (Halaman Dashboard)

1. Akses: `http://localhost:8000/dashboard`
2. Pilih periode yang ingin dianalisis
3. Gunakan filter kategori/AFD/search untuk mempersempit data
4. Lihat visualisasi matriks produktivitas
5. Gunakan simulasi produksi untuk estimasi potensi

### C. Export Data

#### Export HTML (Untuk Petugas Lapangan)
- Klik tombol "Export HTML"
- File HTML standalone akan terdownload
- File ini bisa dibuka offline tanpa koneksi database
- Berisi snapshot data yang sedang ditampilkan

#### Export PNG/PDF (Untuk Laporan)
- Klik tombol "Export PNG" atau "Export PDF"
- Visualisasi dashboard akan diexport
- Cocok untuk presentasi atau laporan

## 🎨 Kategorisasi Otomatis

Sistem secara otomatis mengkategorikan karyawan berdasarkan 2 metrik:
- **Hari Kerja (HK)** - dibandingkan dengan rata-rata populasi
- **Produktivitas (Kg/HK)** - dibandingkan dengan rata-rata populasi

### Kategori:

1. **Star Player** 🌟
   - HK: Tinggi (≥ rata-rata)
   - Produktivitas: Tinggi (≥ rata-rata)
   - *Karyawan terbaik - rajin dan produktif*

2. **Potential** 📈
   - HK: Rendah (< rata-rata)
   - Produktivitas: Tinggi (≥ rata-rata)
   - *Potensi besar - produktif tapi jarang masuk*

3. **Workhorse** 💼
   - HK: Tinggi (≥ rata-rata)
   - Produktivitas: Rendah (< rata-rata)
   - *Rajin tapi perlu training produktivitas*

4. **Underperformer** ⚠️
   - HK: Rendah (< rata-rata)
   - Produktivitas: Rendah (< rata-rata)
   - *Perlu perhatian khusus*

## 🔧 API Endpoints

### GET `/api/performance/data`
Mendapatkan data performa dengan filter

**Query Parameters:**
- `periode[]` - Array periode (optional)
- `afd` - Filter AFD (optional)
- `batch_id` - Filter batch (optional)
- `start_date` - Tanggal mulai (optional)
- `end_date` - Tanggal akhir (optional)

**Response:**
```json
{
  "data": [...],
  "benchmarks": {
    "hk": 19.3,
    "prod": 863.5
  },
  "summary": {
    "total": 150,
    "total_hk": 2895,
    "total_jjg": 225000,
    "total_ton": 3375.0
  }
}
```

### GET `/api/performance/filters`
Mendapatkan daftar filter tersedia

**Response:**
```json
{
  "periodes": ["2024-12", "2024-11", ...],
  "afds": ["I", "II", "III", ...],
  "batches": [...]
}
```

### POST `/api/performance/upload`
Upload file Excel

**Form Data:**
- `file` - File Excel (.xlsx/.xls)
- `periode` - Periode data
- `bjr` - Berat Janjang Rata-rata (default: 15)
- `notes` - Catatan (optional)
- `uploaded_by` - Nama uploader (optional)

### DELETE `/api/performance/batches/{batchId}`
Hapus batch data

### GET `/api/performance/stats`
Mendapatkan statistik umum

### GET `/api/performance/batches`
Mendapatkan daftar batch upload

## 🔐 Security Notes

Saat ini sistem belum memiliki authentication. Untuk production:

1. Implementasikan Laravel authentication
2. Tambahkan middleware ke routes
3. Implement user roles (admin, viewer)
4. Add CSRF protection untuk API calls

## 🐛 Troubleshooting

### Error: "SQLSTATE[08006] Connection refused"
- Pastikan PostgreSQL service sudah running
- Check konfigurasi di `.env` (host, port, username, password)

### Error: "Class 'PhpOffice\PhpSpreadsheet\IOFactory' not found"
- Jalankan: `composer require phpoffice/phpspreadsheet`

### Error: "Permission denied" saat upload
- Check permission folder `storage/app`
- Jalankan: `php artisan storage:link`

### Data tidak muncul di dashboard
- Pastikan sudah upload data di halaman Admin
- Check periode yang dipilih di dashboard
- Buka browser console untuk error messages

## 📝 Development Notes

### Menambah Kolom Baru di Excel Parser

Edit `app/Http/Controllers/PerformanceController.php`:

```php
// Tambahkan mapping kolom baru
$newField = $rowData['NEW_COLUMN'] ?? null;

// Tambahkan ke array insert
$dataToInsert[] = [
    // ... existing fields
    'new_field' => $newField,
];
```

Jangan lupa tambahkan kolom di migration dan model.

### Custom Export Template

Edit `public/detail_analisis_performa.html` untuk customize tampilan export HTML.

## 🤝 Support

Untuk pertanyaan atau issue, silakan hubungi tim development atau buat issue ticket.

## 📄 License

Proprietary - Internal use only.

---

**Version:** 1.0.0  
**Last Updated:** December 2024  
**Developer:** Internal Team


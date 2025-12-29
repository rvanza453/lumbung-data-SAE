# 📖 Setup Instructions - HR Analytics System

Panduan lengkap instalasi dan konfigurasi sistem.

## 🎯 Prerequisites

Sebelum memulai, pastikan sudah terinstall:

- ✅ **Laragon** (atau XAMPP/WAMP dengan PHP 8.1+)
- ✅ **PostgreSQL 12+** 
- ✅ **Composer**
- ✅ **Git** (optional)

## 📦 Step-by-Step Installation

### Step 1: Setup PostgreSQL

#### A. Install PostgreSQL (jika belum)

**Download:**
- Download PostgreSQL dari: https://www.postgresql.org/download/windows/
- Install dengan default settings
- Catat password yang Anda buat untuk user `postgres`

**Atau menggunakan Laragon:**
- Buka Laragon
- Menu → PostgreSQL → Version → Download & Install

#### B. Buat Database

1. Buka **pgAdmin** (terinstall bersama PostgreSQL)
2. Connect ke PostgreSQL server
3. Klik kanan pada "Databases" → Create → Database
4. Nama database: `hr_analytics`
5. Klik Save

**Atau via Command Line:**

```bash
# Masuk ke psql
psql -U postgres

# Buat database
CREATE DATABASE hr_analytics;

# Keluar
\q
```

### Step 2: Configure Laravel

#### A. Setup Environment File

1. Copy file `.env.example` menjadi `.env`:

```bash
copy .env.example .env
```

2. Edit file `.env` dengan text editor (Notepad++, VSCode, dll):

```env
APP_NAME="HR Analytics System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hr_analytics
DB_USERNAME=postgres
DB_PASSWORD=your_password_here  # ⚠️ Ganti dengan password PostgreSQL Anda
```

**Penting:** Ganti `your_password_here` dengan password PostgreSQL yang Anda buat saat instalasi!

#### B. Install Dependencies

Buka terminal/command prompt di folder project:

```bash
cd C:\laragon\www\MyApp\analisis-performa-pemanen

# Install PHP dependencies
composer install
```

**Jika error "composer not found":**
- Download & install Composer dari: https://getcomposer.org/download/
- Restart terminal setelah instalasi

#### C. Generate Application Key

```bash
php artisan key:generate
```

### Step 3: Run Database Migrations

```bash
php artisan migrate
```

Output yang diharapkan:
```
Migration table created successfully.
Migrating: 2024_12_24_000001_create_karyawan_performances_table
Migrated:  2024_12_24_000001_create_karyawan_performances_table (123.45ms)
Migrating: 2024_12_24_000002_create_upload_batches_table
Migrated:  2024_12_24_000002_create_upload_batches_table (67.89ms)
```

**Jika error "could not connect to server":**
- Pastikan PostgreSQL service sudah running
- Check username dan password di file `.env`
- Test koneksi dengan pgAdmin

### Step 4: Run Application

#### Menggunakan Laravel Development Server:

```bash
php artisan serve
```

Buka browser: `http://localhost:8000`

#### Menggunakan Laragon:

1. Buka Laragon
2. Klik **Start All**
3. Tambahkan Virtual Host:
   - Klik kanan icon Laragon → Apache → sites-enabled → Add site
   - Masukkan nama: `analisis-performa-pemanen`
4. Akses: `http://analisis-performa-pemanen.test`

## 🧪 Testing Installation

### 1. Test Database Connection

```bash
php artisan tinker
```

Di tinker console:

```php
DB::connection()->getPdo();
// Jika berhasil, akan muncul PDO object details
exit
```

### 2. Test Admin Page

1. Buka browser: `http://localhost:8000/admin`
2. Pastikan halaman admin muncul dengan benar
3. Stats cards akan menampilkan "0" (karena belum ada data)

### 3. Test Upload

1. Download template Excel: Klik tombol "Download Template" di halaman admin
2. Edit template dengan data dummy (atau gunakan data real)
3. Upload file
4. Jika berhasil, akan muncul notifikasi "Berhasil mengupload X data karyawan"

### 4. Test Dashboard

1. Buka: `http://localhost:8000/dashboard`
2. Pilih periode yang sudah diupload
3. Lihat visualisasi matriks produktivitas
4. Test filter dan search

## 📊 Import Data Awal

### Cara 1: Upload via Web Interface

1. Siapkan file Excel dengan format yang benar
2. Buka halaman Admin
3. Upload file
4. Cek di Dashboard

### Cara 2: Import Bulk via Database (Advanced)

Jika punya banyak data historical:

```sql
-- Connect to database
psql -U postgres -d hr_analytics

-- Import dari CSV (contoh)
\COPY karyawan_performances(nik, nama, afd, hk, jjg, ton, kg_per_hk, periode, batch_id, created_at, updated_at) 
FROM 'C:/data/performances.csv' DELIMITER ',' CSV HEADER;
```

## 🔧 Troubleshooting

### Problem: "Class 'PhpOffice\PhpSpreadsheet\IOFactory' not found"

**Solution:**
```bash
composer require phpoffice/phpspreadsheet
```

### Problem: "SQLSTATE[08006] could not connect to server"

**Solutions:**
1. Check PostgreSQL service:
   - Windows: Services → PostgreSQL → Start
   - Laragon: Menu → PostgreSQL → Start

2. Verify credentials:
   - Open pgAdmin
   - Try to connect with same username/password as in `.env`

3. Check port:
   - PostgreSQL default port: 5432
   - Verify in `.env` and PostgreSQL config

### Problem: "Permission denied" saat upload file

**Solution:**
```bash
# Set permissions (Windows)
icacls storage /grant Users:(OI)(CI)F /T
icacls bootstrap\cache /grant Users:(OI)(CI)F /T
```

### Problem: "419 Page Expired" saat submit form

**Solution:**
1. Clear browser cache
2. Hard refresh (Ctrl+Shift+R)
3. Check `.env` - `APP_KEY` harus terisi
4. Run: `php artisan key:generate`

### Problem: Data tidak muncul di Dashboard

**Checklist:**
- ✅ Ada data yang sudah diupload? (cek di halaman Admin)
- ✅ Periode sudah dipilih dengan benar?
- ✅ Browser console tidak ada error? (F12 → Console tab)
- ✅ API endpoint bisa diakses? Test: `http://localhost:8000/api/performance/stats`

## 🚀 Production Deployment

### Security Checklist

Sebelum deploy ke production:

- [ ] Set `APP_DEBUG=false` di `.env`
- [ ] Set `APP_ENV=production`
- [ ] Implement authentication (Laravel Breeze/Jetstream)
- [ ] Setup HTTPS/SSL certificate
- [ ] Configure proper file permissions
- [ ] Setup database backups
- [ ] Enable error logging
- [ ] Add rate limiting to API endpoints

### Recommended Production Stack

**Option 1: Traditional Server**
- OS: Ubuntu 20.04+
- Web Server: Nginx + PHP-FPM
- Database: PostgreSQL 14+
- Process Manager: Supervisor (for queue workers)

**Option 2: Cloud Platform**
- AWS: EC2 + RDS PostgreSQL
- DigitalOcean: Droplet + Managed PostgreSQL
- Heroku: Web Dyno + Heroku Postgres

## 📝 Maintenance

### Backup Database

**Manual Backup:**
```bash
pg_dump -U postgres hr_analytics > backup_$(date +%Y%m%d).sql
```

**Restore from Backup:**
```bash
psql -U postgres hr_analytics < backup_20241224.sql
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Update Dependencies

```bash
composer update
```

## 🆘 Need Help?

### Quick Checks

1. **Check Logs:**
   ```bash
   # Laravel logs
   tail -f storage/logs/laravel.log
   
   # PostgreSQL logs (Windows)
   C:\Program Files\PostgreSQL\14\data\log\
   ```

2. **Test API Manually:**
   ```bash
   # Test stats endpoint
   curl http://localhost:8000/api/performance/stats
   
   # Test filters endpoint
   curl http://localhost:8000/api/performance/filters
   ```

3. **Check Database:**
   ```sql
   -- Connect
   psql -U postgres hr_analytics
   
   -- Check tables
   \dt
   
   -- Check records
   SELECT COUNT(*) FROM karyawan_performances;
   SELECT COUNT(*) FROM upload_batches;
   
   -- Recent uploads
   SELECT * FROM upload_batches ORDER BY created_at DESC LIMIT 5;
   ```

### Common Commands Cheat Sheet

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (⚠️ will delete all data)
php artisan migrate:fresh

# Check routes
php artisan route:list

# Laravel Tinker (interactive console)
php artisan tinker

# Clear all caches
php artisan optimize:clear
```

## ✅ Verification Checklist

Setelah setup, verifikasi bahwa:

- [ ] Database terbuat dan migrations berhasil
- [ ] Halaman `/admin` dapat diakses
- [ ] Halaman `/dashboard` dapat diakses
- [ ] API endpoint `/api/performance/stats` returns valid JSON
- [ ] Template Excel bisa didownload
- [ ] Upload Excel berhasil
- [ ] Data muncul di dashboard setelah upload
- [ ] Filter dan search berfungsi
- [ ] Export HTML/PNG/PDF berhasil

## 🎉 Selamat!

Jika semua checklist di atas ✅, sistem Anda sudah siap digunakan!

**Next Steps:**
1. Upload data real dari Excel
2. Eksplorasi fitur-fitur dashboard
3. Customize sesuai kebutuhan
4. Backup database secara berkala

---

**Need Support?**  
Contact: Internal IT Team  
Documentation: README_SISTEM.md


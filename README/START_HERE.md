# 🎯 START HERE - HR Analytics System

**Selamat datang!** Sistem HR Analytics untuk analisis performa pemanen sudah siap digunakan.

---

## 📋 Yang Sudah Dibuat

### ✅ Backend (Laravel + PostgreSQL)
- ✅ Database migrations (2 tables)
- ✅ Models (KaryawanPerformance, UploadBatch)
- ✅ API Controller dengan 7 endpoints
- ✅ Routes (web & API)
- ✅ Excel parser dengan format Indonesia support
- ✅ Command untuk generate template Excel

### ✅ Frontend
- ✅ Admin Panel (upload & management)
- ✅ Dashboard Interaktif (React + Recharts)
- ✅ Export HTML/PNG/PDF
- ✅ Responsive design (mobile-friendly)

### ✅ Dokumentasi
- ✅ README_SISTEM.md (dokumentasi lengkap)
- ✅ SETUP_INSTRUCTIONS.md (panduan detail)
- ✅ QUICKSTART.md (quick start 5 menit)
- ✅ CHANGELOG.md (version history)
- ✅ IMPLEMENTATION_SUMMARY.md (technical summary)

---

## 🚀 Langkah Pertama - PILIH SALAH SATU:

### Option A: Quick Start (5 Menit) ⚡
Untuk yang sudah familiar dengan Laravel:
```bash
# 1. Baca panduan cepat
Buka: QUICKSTART.md

# 2. Install & setup
composer install
# Edit .env (sesuaikan DB config)
php artisan key:generate
php artisan migrate
php artisan template:generate
php artisan serve
```

### Option B: Detailed Setup (15 Menit) 📖
Untuk yang perlu panduan lengkap:
```bash
# Baca panduan detail step-by-step
Buka: SETUP_INSTRUCTIONS.md
```

### Option C: Lihat Dokumentasi Dulu 📚
```bash
# Pahami sistem secara menyeluruh
Buka: README_SISTEM.md
```

---

## 🎯 Struktur File Penting

```
analisis-performa-pemanen/
│
├── 📘 START_HERE.md                 ← Anda di sini!
├── 📗 QUICKSTART.md                 ← Quick start guide
├── 📙 SETUP_INSTRUCTIONS.md         ← Setup detail
├── 📕 README_SISTEM.md              ← Dokumentasi lengkap
├── 📓 IMPLEMENTATION_SUMMARY.md     ← Technical summary
├── 📔 CHANGELOG.md                  ← Version history
│
├── app/
│   ├── Http/Controllers/
│   │   └── PerformanceController.php     ← Main controller
│   ├── Models/
│   │   ├── KaryawanPerformance.php       ← Data model
│   │   └── UploadBatch.php               ← Batch model
│   └── Console/Commands/
│       └── GenerateTemplateCommand.php   ← Template generator
│
├── database/migrations/
│   ├── 2024_12_24_000001_create_karyawan_performances_table.php
│   └── 2024_12_24_000002_create_upload_batches_table.php
│
├── resources/views/
│   ├── admin/index.blade.php             ← Admin page
│   └── dashboard/index.blade.php         ← Dashboard page
│
├── routes/
│   ├── web.php                           ← Web routes
│   └── api.php                           ← API routes
│
└── public/
    ├── detail_analisis_performa.html     ← Export template
    └── template-upload.xlsx              ← Excel template (generated)
```

---

## 🔧 Prerequisites Check

Pastikan sudah terinstall:

- [ ] **PostgreSQL** (12+)
  - Test: Buka pgAdmin atau coba `psql --version`
  
- [ ] **PHP** (8.1+)
  - Test: `php --version`
  - Jika di Laragon: Gunakan Laragon Terminal
  
- [ ] **Composer**
  - Test: `composer --version`
  - Download: https://getcomposer.org/download/

---

## 📊 Cara Kerja Sistem

### Flow Data:

```
┌─────────────┐
│ Excel File  │
└──────┬──────┘
       │ Upload via /admin
       ▼
┌─────────────────┐
│  Laravel API    │
│  - Parse Excel  │
│  - Validate     │
│  - Calculate    │
└──────┬──────────┘
       │ Insert
       ▼
┌─────────────────┐
│  PostgreSQL DB  │
│  - karyawan_    │
│    performances │
│  - upload_      │
│    batches      │
└──────┬──────────┘
       │ Query
       ▼
┌─────────────────┐
│   Dashboard     │
│  - Visualize    │
│  - Filter       │
│  - Categorize   │
│  - Simulate     │
└──────┬──────────┘
       │ Export
       ▼
┌─────────────────┐
│  HTML/PNG/PDF   │
│  (Shareable)    │
└─────────────────┘
```

### Kategorisasi Otomatis:

```
                 │
    Tinggi       │  POTENTIAL  │  STAR
 Produktivitas   │  ⬆️ Prod    │  ⭐ Best
                 │  ⬇️ HK      │  ⬆️ Both
   ──────────────┼─────────────┼──────────────
    Rendah       │  UNDER-     │  WORKHORSE
 Produktivitas   │  PERFORMER  │  💼 Loyal
                 │  ⬇️ Both    │  ⬆️ HK, ⬇️ Prod
                 │
              Rendah ──── HK (Kehadiran) ──── Tinggi
```

---

## 🎮 Quick Demo

Setelah setup selesai:

1. **Buka Admin:** `http://localhost:8000/admin`
2. **Generate Template:** Klik "Download Template"
3. **Upload Template:** Upload file yang baru didownload (ada sample data)
4. **Lihat Dashboard:** Klik "Lihat Dashboard"
5. **Pilih Periode:** Select periode dari dropdown
6. **Explore:** Coba filter, search, dan simulation
7. **Export:** Klik tombol export untuk share ke petugas

---

## ⚙️ Configuration Cepat

### File: `.env`

```env
# Database (⚠️ HARUS DIISI)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hr_analytics
DB_USERNAME=postgres
DB_PASSWORD=YOUR_PASSWORD_HERE    # ← Ganti ini!
```

### Buat Database:

```sql
-- Via psql
psql -U postgres
CREATE DATABASE hr_analytics;
\q

-- Atau via pgAdmin
-- Right click Databases → Create → Database
-- Name: hr_analytics
```

---

## 🆘 Troubleshooting Cepat

| Problem | Quick Fix |
|---------|-----------|
| `composer: command not found` | Install Composer dari getcomposer.org |
| `php: command not found` | Gunakan Laragon Terminal atau add PHP to PATH |
| `could not connect to server` | Start PostgreSQL service |
| `Class not found` | Run `composer install` |
| `419 Page Expired` | Run `php artisan key:generate` |
| Upload gagal | Check file format (.xlsx/.xls) dan size (<10MB) |

Untuk troubleshooting lengkap: Lihat **SETUP_INSTRUCTIONS.md**

---

## 📱 Access URLs

Setelah `php artisan serve`:

- **Admin Panel:** http://localhost:8000/admin
- **Dashboard:** http://localhost:8000/dashboard
- **API Stats:** http://localhost:8000/api/performance/stats
- **API Docs:** Lihat README_SISTEM.md → API Endpoints

---

## 🎓 Learning Path

### Hari 1: Setup & Exploration
- [ ] Baca QUICKSTART.md
- [ ] Install & configure
- [ ] Upload sample data
- [ ] Explore dashboard

### Hari 2: Real Data
- [ ] Prepare real Excel data
- [ ] Upload multiple periode
- [ ] Understand categorization
- [ ] Use filters

### Hari 3: Advanced Features
- [ ] Production simulation
- [ ] Export HTML for field workers
- [ ] Export PNG/PDF for reports
- [ ] Batch management

### Hari 4: Production Ready
- [ ] Security checklist
- [ ] Performance optimization
- [ ] User training
- [ ] Go live!

---

## 📞 Support

**Dokumentasi:**
- QUICKSTART.md - Quick start guide
- SETUP_INSTRUCTIONS.md - Detailed setup
- README_SISTEM.md - Complete documentation
- IMPLEMENTATION_SUMMARY.md - Technical details

**Troubleshooting:**
- Check logs: `storage/logs/laravel.log`
- Check database: pgAdmin
- API testing: Postman or curl

**Contact:**
- Internal IT Team
- Development Team

---

## ✅ Ready Checklist

Sebelum mulai menggunakan, pastikan:

- [ ] PostgreSQL sudah running
- [ ] Database `hr_analytics` sudah dibuat
- [ ] File `.env` sudah dikonfigurasi
- [ ] `composer install` sudah dijalankan
- [ ] `php artisan key:generate` sudah dijalankan
- [ ] `php artisan migrate` berhasil
- [ ] `php artisan serve` berjalan
- [ ] Bisa akses `/admin` dan `/dashboard`

---

## 🎉 Selamat Memulai!

Sistem siap digunakan. Pilih langkah berikutnya:

1. **Setup Now:** Buka QUICKSTART.md atau SETUP_INSTRUCTIONS.md
2. **Learn More:** Baca README_SISTEM.md
3. **Technical Deep Dive:** Lihat IMPLEMENTATION_SUMMARY.md

**Pro Tip:** Mulai dengan QUICKSTART.md untuk hasil tercepat! ⚡

---

**Version:** 1.0.0  
**Last Updated:** December 24, 2024  
**Status:** ✅ Production Ready


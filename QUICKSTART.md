# ⚡ Quickstart Guide

Panduan cepat untuk mulai menggunakan HR Analytics System dalam 5 menit!

## 🚀 Quick Setup

### 1. Install Dependencies (2 menit)

```bash
cd C:\laragon\www\MyApp\analisis-performa-pemanen
composer install
```

### 2. Configure Database (1 menit)

Edit `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hr_analytics
DB_USERNAME=postgres
DB_PASSWORD=YOUR_PASSWORD  # ⚠️ Ganti ini!
```

Buat database:

```sql
CREATE DATABASE hr_analytics;
```

### 3. Run Migrations (30 detik)

```bash
php artisan key:generate
php artisan migrate
```

### 4. Generate Template Excel (15 detik)

```bash
php artisan template:generate
```

### 5. Start Server (15 detik)

```bash
php artisan serve
```

Buka: `http://localhost:8000`

## ✅ Verification (1 menit)

1. **Admin Page:** `http://localhost:8000/admin` ✅ 
2. **Download Template** - Klik tombol di admin page ✅
3. **Upload Template** - Upload file template yang baru didownload ✅
4. **Dashboard:** `http://localhost:8000/dashboard` ✅
5. **Pilih Periode** - Lihat data yang baru diupload ✅

## 🎯 First Data Upload

1. **Download template:** Klik "Download Template" di halaman Admin
2. **Edit Excel:** Isi dengan data real atau gunakan data sample
3. **Upload:** 
   - Pilih file Excel
   - Isi periode (contoh: `2024-12`)
   - Klik "Upload & Proses"
4. **View Dashboard:** Buka Dashboard → Pilih periode → Lihat visualisasi

## 📊 Quick Tips

### Format Excel yang Benar

Minimal 3 kolom wajib:

```
| NAMA         | HK | JJG  |
|--------------|----|----- |
| Budi Santoso | 25 | 1500 |
```

### Upload Multiple Periode

```bash
# Upload Januari
Periode: 2024-01

# Upload Februari  
Periode: 2024-02

# Upload Maret
Periode: 2024-03
```

Data akan terakumulasi. Di dashboard, pilih periode yang ingin dianalisis.

### Export untuk Petugas Lapangan

1. Buka Dashboard
2. Pilih periode
3. Set filter sesuai kebutuhan (AFD, kategori, dll)
4. Klik tombol **Export HTML**
5. Kirim file HTML ke petugas via email/WhatsApp
6. File bisa dibuka offline, tidak perlu koneksi database

## 🔥 One-Liner Commands

```bash
# Fresh install
composer install && php artisan key:generate && php artisan migrate && php artisan template:generate && php artisan serve

# Reset everything (⚠️ deletes all data)
php artisan migrate:fresh && php artisan template:generate

# Generate new template only
php artisan template:generate

# Clear all caches
php artisan optimize:clear
```

## 🆘 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| Can't connect to DB | Check PostgreSQL is running |
| "Class not found" | Run `composer install` |
| 419 Page Expired | Run `php artisan key:generate` |
| Upload fails | Check file is .xlsx/.xls |
| No data in dashboard | Select correct periode |

## 📱 Quick Access Links

- **Admin Panel:** `/admin`
- **Dashboard:** `/dashboard`
- **API Stats:** `/api/performance/stats`
- **API Filters:** `/api/performance/filters`

## 🎨 Quick Feature Overview

| Feature | Description |
|---------|-------------|
| 📤 **Upload** | Multiple Excel files, accumulating data |
| 📊 **Visualize** | Interactive scatter plot with categories |
| 🔍 **Filter** | By periode, AFD, category, search name |
| 📈 **Simulate** | Project production potential |
| 💾 **Export HTML** | Standalone file for field workers |
| 🖼️ **Export PNG/PDF** | Visual reports |

## 🏆 Best Practices

1. **Consistent Periode Format:** Use `YYYY-MM` (e.g., 2024-01, 2024-02)
2. **Regular Backups:** Export HTML monthly for archives
3. **Clean Data:** Verify Excel data before upload
4. **Meaningful Notes:** Add notes when uploading special batches
5. **Monitor Stats:** Check admin page stats regularly

## 🎓 Learning Path

1. **Day 1:** Upload sample data → Explore dashboard
2. **Day 2:** Upload real data → Use filters
3. **Day 3:** Try simulations → Export HTML
4. **Day 4:** Upload multiple periodes → Compare trends
5. **Day 5:** Train field workers on HTML export

## 🚀 Production Ready Checklist

- [ ] Change `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Setup database backups (daily)
- [ ] Implement authentication
- [ ] Configure proper server (Nginx/Apache)
- [ ] Setup SSL certificate
- [ ] Test all features thoroughly
- [ ] Train all users
- [ ] Prepare support documentation
- [ ] Monitor logs regularly

---

**Total Time:** ~10 minutes from zero to working system!

**Next:** Read [README_SISTEM.md](README_SISTEM.md) for complete documentation.

**Help:** See [SETUP_INSTRUCTIONS.md](SETUP_INSTRUCTIONS.md) for detailed troubleshooting.


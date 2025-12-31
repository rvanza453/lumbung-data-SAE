# Changelog: Normalisasi Nama Blok

**Tanggal**: 31 Desember 2025  
**Versi**: 1.0.0

## 🎯 Masalah yang Diperbaiki

Di halaman monitoring, blok dengan nama berbeda seperti "B2", "B02", dan "B002" ditampilkan sebagai blok yang terpisah, padahal seharusnya mereka adalah blok yang sama.

## ✨ Perubahan yang Dilakukan

### 1. **Normalisasi Otomatis saat Upload** (`includes/functions.php`)

#### Fungsi Baru: `normalizeBlokName()`
```php
function normalizeBlokName($blok)
```

Fungsi ini menghapus leading zeros dari angka dalam nama blok:
- `B02` → `B2`
- `B002` → `B2`
- `Blok 02` → `Blok 2`
- `B2` → `B2` (tidak berubah)

#### Penerapan di `savePanenData()`
Sebelum data panen disimpan ke database, nama blok akan dinormalisasi terlebih dahulu:

```php
// Normalize block name before saving
$blokName = $item['blok'] ?? ($data['header']['blok'] ?? '');
$normalizedBlok = normalizeBlokName($blokName);

$stmt->execute([
    // ...
    ':blok' => $normalizedBlok,
    // ...
]);
```

#### Penerapan di `savePengirimanData()`
Sama seperti data panen, data pengiriman juga dinormalisasi:

```php
// Normalize block name before saving
$blokName = $item['blok'] ?? '';
$normalizedBlok = normalizeBlokName($blokName);

$stmt->execute([
    // ...
    ':blok' => $normalizedBlok,
    // ...
]);
```

### 2. **Script Normalisasi Data Existing** (`scripts/normalize-blok-names.php`)

Script standalone untuk menormalisasi data yang sudah ada di database:

**Fitur:**
- ✅ Memproses table `data_panen`
- ✅ Memproses table `data_pengiriman`
- ✅ Menampilkan progress detail
- ✅ Aman dijalankan berkali-kali (skip records yang sudah dinormalisasi)
- ✅ Tidak menghapus data, hanya mengubah format

**Cara Menjalankan:**
```bash
cd scripts
php normalize-blok-names.php
```

### 3. **Dokumentasi** (`scripts/README-normalize-blok.md`)

Dokumentasi lengkap berisi:
- Penjelasan masalah dan solusi
- Cara menjalankan script
- Contoh output
- Troubleshooting guide
- Technical details

## 📊 Dampak Perubahan

### Sebelum:
```
Halaman Monitoring menampilkan:
- Blok B2    (Total: 100 janjang)
- Blok B02   (Total: 50 janjang)
- Blok B002  (Total: 25 janjang)

Total 3 blok terpisah dengan 175 janjang
```

### Sesudah:
```
Halaman Monitoring menampilkan:
- Blok B2    (Total: 175 janjang)

Total 1 blok dengan 175 janjang
```

## 🚀 Cara Deploy

### Langkah 1: Upload File yang Diubah
```bash
# Upload file yang sudah dimodifikasi
- includes/functions.php
- scripts/normalize-blok-names.php
- scripts/README-normalize-blok.md
```

### Langkah 2: Jalankan Script Normalisasi
```bash
cd scripts
php normalize-blok-names.php
```

### Langkah 3: Verifikasi
1. Buka halaman monitoring
2. Cek apakah blok yang sebelumnya terpisah (B2, B02) sekarang sudah digabung
3. Upload data baru dan cek apakah normalisasi otomatis berjalan

## 🔍 Testing

### Test Case 1: Upload Data Baru
```
Input JSON: { "blok": "B02" }
Expected: Data disimpan dengan blok = "B2"
```

### Test Case 2: Normalisasi Data Existing
```
Before: data_panen memiliki records dengan blok "B02", "B002"
After: Semua records di-update menjadi "B2"
```

### Test Case 3: Monitoring Page
```
Before: Menampilkan 3 blok terpisah (B2, B02, B002)
After: Menampilkan 1 blok (B2) dengan total yang digabung
```

## ⚠️ Catatan Penting

1. **Backup (Recommended)**
   Walaupun script aman, sebaiknya backup database sebelum menjalankan:
   ```sql
   CREATE TABLE data_panen_backup AS SELECT * FROM data_panen;
   CREATE TABLE data_pengiriman_backup AS SELECT * FROM data_pengiriman;
   ```

2. **Reversibility**
   Perubahan ini **tidak dapat di-reverse secara otomatis**. Jika perlu rollback, restore dari backup.

3. **Data Integrity**
   Normalisasi tidak mengubah data selain nama blok. Semua field lain tetap sama.

4. **Future Uploads**
   Mulai sekarang, semua data yang diupload akan otomatis dinormalisasi. Tidak perlu menjalankan script lagi kecuali ada data baru yang di-import langsung ke database (bypass upload form).

## 📝 File yang Dimodifikasi

1. ✅ `includes/functions.php` - Added normalization function and applied to save functions
2. ✅ `scripts/normalize-blok-names.php` - New script for normalizing existing data
3. ✅ `scripts/README-normalize-blok.md` - Documentation for the normalization script
4. ✅ `BLOK_NORMALIZATION_CHANGELOG.md` - This changelog

## 🐛 Known Issues

None at this time.

## 📞 Support

Jika menemui masalah:
1. Cek file `scripts/README-normalize-blok.md` untuk troubleshooting
2. Lihat PHP error log
3. Hubungi tim developer

---

**Status**: ✅ Ready for Production  
**Breaking Changes**: None  
**Rollback Plan**: Restore from database backup if needed


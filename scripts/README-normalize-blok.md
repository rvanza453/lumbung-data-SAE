# Normalisasi Nama Blok dan TPH

## Masalah yang Diperbaiki

1. **Blok**: Di halaman monitoring, data blok "B2" dan "B02" ditampilkan sebagai blok yang berbeda, padahal seharusnya dianggap sama.

2. **TPH**: Data TPH "1", "01", dan "001" ditampilkan sebagai TPH yang berbeda, padahal seharusnya dianggap sama.

## Solusi

1. **Normalisasi Otomatis untuk Data Baru**: Semua data yang diupload mulai sekarang akan otomatis dinormalisasi sebelum disimpan ke database.

2. **Normalisasi Data yang Sudah Ada**: 
   - Script `normalize-blok-names.php` untuk menormalisasi nama blok
   - Script `normalize-tph-numbers.php` untuk menormalisasi nomor TPH

## Format Normalisasi

### Normalisasi Blok
Fungsi normalisasi akan menghapus leading zeros dari angka di nama blok:

- `B02` → `B2`
- `B002` → `B2`
- `Blok 02` → `Blok 2`
- `B2` → `B2` (tidak berubah)

### Normalisasi TPH
Fungsi normalisasi akan menghapus leading zeros dari nomor TPH:

- `001` → `1`
- `01` → `1`
- `1` → `1` (tidak berubah)
- `TPH001` → `TPH1`
- `TPH 01` → `TPH 1`

## Cara Menjalankan Script Normalisasi

### 1. Normalisasi Nama Blok

#### Via Command Line (Recommended)

```bash
cd scripts
php normalize-blok-names.php
```

#### Via Browser

```
http://your-domain.com/lubung-data-SAE/scripts/normalize-blok-names.php
```

### 2. Normalisasi Nomor TPH

#### Via Command Line (Recommended)

```bash
cd scripts
php normalize-tph-numbers.php
```

#### Via Browser

```
http://your-domain.com/lubung-data-SAE/scripts/normalize-tph-numbers.php
```

### 3. Atau Jalankan Keduanya Sekaligus

```bash
cd scripts
php normalize-blok-names.php
php normalize-tph-numbers.php
```

## Output Script

Setiap script akan menampilkan:
- Jumlah records yang diproses di table `data_panen`
- Jumlah records yang diproses di table `data_pengiriman`
- Detail perubahan untuk setiap record yang dinormalisasi
- Summary total records yang diupdate

## Contoh Output

### Script normalize-blok-names.php

```
=== Normalisasi Nama Blok di Database ===

Started at: 2025-12-31 10:00:00

1. Processing data_panen table...
  - Updated ID 123: 'B02' -> 'B2'
  - Updated ID 456: 'B002' -> 'B2'
  Total records processed: 150
  Updated: 45
  Skipped (already normalized): 105

2. Processing data_pengiriman table...
  - Updated ID 789: 'B02' -> 'B2'
  Total records processed: 100
  Updated: 30
  Skipped (already normalized): 70

=== Summary ===
Total records updated: 75
Total records skipped: 175
Total records processed: 250

Completed at: 2025-12-31 10:00:15
✅ Normalisasi blok selesai!

Note: Dari sekarang, semua data baru yang diupload akan otomatis dinormalisasi.
Contoh: 'B02' akan disimpan sebagai 'B2', 'B002' akan disimpan sebagai 'B2'
```

### Script normalize-tph-numbers.php

```
=== Normalisasi Nomor TPH di Database ===

Started at: 2025-12-31 10:01:00

1. Processing data_panen table...
  - Updated ID 123: '001' -> '1'
  - Updated ID 456: '01' -> '1'
  Total records processed: 150
  Updated: 52
  Skipped (already normalized): 98

2. Processing data_pengiriman table...
  - Updated ID 789: '001' -> '1'
  Total records processed: 100
  Updated: 38
  Skipped (already normalized): 62

=== Summary ===
Total records updated: 90
Total records skipped: 160
Total records processed: 250

Completed at: 2025-12-31 10:01:10
✅ Normalisasi TPH selesai!

Note: Dari sekarang, semua data baru yang diupload akan otomatis dinormalisasi.
Contoh: TPH '001' akan disimpan sebagai '1', TPH '01' akan disimpan sebagai '1'
```

## Keamanan

- Script ini **aman** dijalankan berkali-kali
- Records yang sudah dinormalisasi akan di-skip (tidak diubah lagi)
- **Tidak menghapus** data apapun, hanya **mengubah format** nama blok

## Setelah Menjalankan Script

1. Refresh halaman monitoring
2. Data blok yang sebelumnya terpisah (seperti B2 dan B02) akan digabung menjadi satu blok
3. Upload data baru akan otomatis mengikuti format normalisasi

## Troubleshooting

### Error: "Database connection failed"
- Pastikan file `config/database.php` sudah dikonfigurasi dengan benar
- Cek kredensial database Anda

### Script tidak menampilkan output
- Coba jalankan via command line untuk melihat error message
- Cek PHP error log di server

### Hasil tidak muncul di monitoring
- Clear cache browser (Ctrl+F5)
- Tunggu beberapa detik dan refresh halaman
- Cek apakah script berhasil dijalankan (lihat output)

## Technical Details

### File yang Dimodifikasi

1. **includes/functions.php**
   - Menambahkan fungsi `normalizeBlokName()`
   - Menerapkan normalisasi di `savePanenData()`
   - Menerapkan normalisasi di `savePengirimanData()`

2. **scripts/normalize-blok-names.php** (NEW)
   - Script standalone untuk normalisasi data existing

### Cara Kerja

1. Fungsi `normalizeBlokName()` menggunakan regex untuk:
   - Mencari pattern huruf diikuti angka dengan leading zeros
   - Menghapus leading zeros dari angka tersebut
   - Mempertahankan format asli untuk karakter lainnya

2. Normalisasi diterapkan:
   - **Saat upload**: Sebelum data disimpan ke database
   - **Untuk data lama**: Via script normalisasi

## Backup (Optional)

Jika ingin backup sebelum menjalankan script:

```sql
-- Backup table data_panen
CREATE TABLE data_panen_backup_20251231 AS SELECT * FROM data_panen;

-- Backup table data_pengiriman
CREATE TABLE data_pengiriman_backup_20251231 AS SELECT * FROM data_pengiriman;
```

## Support

Jika ada masalah atau pertanyaan, silakan hubungi tim developer.


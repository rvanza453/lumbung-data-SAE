# Dokumentasi Fitur Koreksi Panen dan Pengiriman

## Gambaran Umum

Sistem Lubung Data SAE telah ditingkatkan dengan fitur **Koreksi Panen dan Koreksi Pengiriman** yang memungkinkan pengguna dengan role khusus untuk melakukan penyesuaian data janjang panen dan pengiriman.

## Role dan Permissions

### Role Baru: `corrector`
- **Username:** `corrector`
- **Password:** `corrector123` 
- **Kemampuan:**
  - Melakukan koreksi data panen
  - Melakukan koreksi data pengiriman
  - Melihat log koreksi
  - Akses semua fitur user biasa

### Role yang Dapat Melakukan Koreksi
- `admin` - Akses penuh
- `corrector` - Khusus untuk koreksi

## Fitur Koreksi

### 1. Koreksi Panen
- **Kolom:** `koreksi_panen` di tabel `data_panen`
- **Rentang:** -999 sampai +999 janjang
- **Fungsi:** Menambah atau mengurangi jumlah janjang panen

### 2. Koreksi Pengiriman
- **Kolom:** `koreksi_kirim` di tabel `data_pengiriman` 
- **Rentang:** -999 sampai +999 janjang
- **Fungsi:** Menambah atau mengurangi jumlah janjang pengiriman

## Perhitungan Restan Baru

**Formula:**
```
Total Panen = Janjang Panen + Koreksi Panen
Total Kirim = Janjang Kirim + Koreksi Kirim
Restan = Total Panen - Total Kirim
```

**Contoh:**
- Janjang Panen: 100
- Koreksi Panen: +5
- Total Panen: 105

- Janjang Kirim: 95
- Koreksi Kirim: -2  
- Total Kirim: 93

- **Restan: 105 - 93 = +12 janjang**

## Interface Monitoring

### Halaman Recap
- Kolom tambahan: **Koreksi P** dan **Koreksi K**
- Tombol **Edit** untuk membuka modal koreksi
- Total koreksi ditampilkan di footer tabel

### Halaman Panen
- Kolom tambahan: **Koreksi** setelah kolom Total Jjg

### Halaman Transport  
- Kolom tambahan: **Koreksi** setelah kolom Muat (Jjg)

## Modal Edit Koreksi

### Field Input:
1. **Koreksi Panen:** Input numerik (-999 to +999)
2. **Koreksi Kirim:** Input numerik (-999 to +999)  
3. **Alasan Koreksi:** Textarea (wajib diisi)

### Validasi:
- Alasan koreksi harus diisi
- Rentang nilai koreksi: -999 sampai +999
- Hanya role `admin` atau `corrector` yang dapat akses

### Preview Perhitungan:
Modal menampilkan preview perhitungan restan secara real-time saat input diubah.

## Database Schema

### Tabel `data_panen` (Tambahan Kolom):
```sql
koreksi_panen INT DEFAULT 0
koreksi_by INT DEFAULT NULL  
koreksi_at TIMESTAMP NULL DEFAULT NULL
koreksi_reason TEXT DEFAULT NULL
```

### Tabel `data_pengiriman` (Tambahan Kolom):
```sql  
koreksi_kirim INT DEFAULT 0
koreksi_by INT DEFAULT NULL
koreksi_at TIMESTAMP NULL DEFAULT NULL
koreksi_reason TEXT DEFAULT NULL
```

### Tabel `koreksi_logs` (Baru):
```sql
CREATE TABLE koreksi_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipe_koreksi ENUM('panen', 'pengiriman') NOT NULL,
    target_id INT NOT NULL,
    user_id INT NOT NULL,
    nilai_lama INT NOT NULL,
    nilai_baru INT NOT NULL,
    alasan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

## API Endpoints

### POST `/api/koreksi.php/panen`
Melakukan koreksi data panen

**Payload:**
```json
{
    "id": 123,
    "koreksi_panen": 5,
    "alasan": "Penyesuaian data karena kesalahan input"
}
```

### POST `/api/koreksi.php/pengiriman`  
Melakukan koreksi data pengiriman

**Payload:**
```json
{
    "id": 456,
    "koreksi_kirim": -3,
    "alasan": "Koreksi berdasarkan verifikasi lapangan"
}
```

### GET `/api/koreksi.php/logs`
Mengambil log semua koreksi

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "tipe_koreksi": "panen",
            "target_id": 123,
            "user_id": 3,
            "nilai_lama": 0,
            "nilai_baru": 5,
            "alasan": "Penyesuaian data",
            "corrected_by_name": "Data Corrector",
            "created_at": "2025-12-13 10:30:00"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 1,
        "total": 1
    }
}
```

## Instalasi

### 1. Update Database
Jalankan script SQL:
```bash
mysql -u username -p database_name < database_add_koreksi.sql
```

### 2. Upload File API
- Upload `api/koreksi.php`
- Update `api/direct_data.php` (sudah diupdate otomatis)

### 3. Update Frontend
- File `monitoring.html` sudah diupdate dengan fitur koreksi

## Keamanan

### Audit Trail
- Setiap koreksi dicatat di tabel `koreksi_logs`
- Menyimpan nilai lama dan baru
- User ID dan timestamp 
- Alasan koreksi wajib diisi

### Access Control  
- Hanya role `admin` dan `corrector` yang dapat melakukan koreksi
- API endpoint dilindungi authentication

### Activity Logs
- Setiap koreksi tercatat di tabel `activity_logs`
- Integrasi dengan sistem logging yang sudah ada

## Contoh Penggunaan

### Skenario 1: Koreksi Kelebihan Input
**Kondisi:** Data panen 120 janjang, seharusnya 115 janjang
**Solusi:** 
- Koreksi Panen: -5
- Alasan: "Koreksi data karena double input di ancak A5"

### Skenario 2: Koreksi Pengiriman Tidak Terdata  
**Kondisi:** Ada pengiriman 25 janjang yang belum terdata
**Solusi:**
- Koreksi Kirim: +25  
- Alasan: "Pengiriman sore hari yang terlewat input"

### Skenario 3: Penyesuaian Lapangan
**Kondisi:** Verifikasi lapangan menemukan selisih -10 janjang panen
**Solusi:**
- Koreksi Panen: -10
- Alasan: "Verifikasi ulang hasil panen berdasarkan timbangan PKS"

## Monitoring dan Pelaporan

### Dashboard Recap
Menampilkan:
- Total koreksi panen 
- Total koreksi pengiriman
- Dampak terhadap perhitungan restan
- Status penyesuaian data

### Export Data  
Data koreksi ikut terexport dalam file Excel dan print report untuk keperluan dokumentasi.

---

**Catatan Penting:**
- Fitur ini menambah transparansi dalam penyesuaian data
- Semua koreksi dapat di-trace kembali melalui log system
- Perhitungan restan otomatis terupdate setelah koreksi dilakukan
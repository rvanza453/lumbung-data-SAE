# Struktur Folder Upload File

## Deskripsi
File Excel/CSV yang diupload akan disimpan di folder `storage/app/public/uploads/` dengan struktur folder berdasarkan tahun dan bulan dari periode data.

## Struktur Folder
```
storage/app/public/uploads/
├── 2024/
│   ├── 01/
│   │   ├── performance_2024_01_a1b2c3d4.xlsx
│   │   └── performance_2024_01_e5f6g7h8.xlsx
│   ├── 02/
│   │   └── performance_2024_02_i9j0k1l2.xlsx
│   └── 12/
│       └── performance_2024_12_m3n4o5p6.xlsx
└── 2025/
    ├── 01/
    │   └── performance_2025_01_q7r8s9t0.xlsx
    └── 12/
        └── performance_2025_12_u1v2w3x4.xlsx
```

## Format Penamaan File
File yang disimpan akan menggunakan format:
```
performance_{tahun}_{bulan}_{hash}.{extension}
```

Contoh:
- `performance_2025_12_a1b2c3d4.xlsx`
- `performance_2025_01_e5f6g7h8.csv`
- `performance_2024_12_i9j0k1l2.xlsx`

## Akses File
File dapat diakses melalui URL:
```
http://your-domain.com/storage/uploads/{year}/{month}/{filename}
```

Contoh:
```
http://localhost/storage/uploads/2025/12/performance_2025_12_a1b2c3d4.xlsx
```

## Database
Informasi file disimpan di tabel `upload_batches` dengan kolom:
- `filename`: Nama file asli yang diupload
- `file_path`: Path relatif file di storage (uploads/YYYY/MM/filename.ext)

## API Response
Saat mengambil data batch, response akan menyertakan:
```json
{
  "batch_id": "BATCH-20251229025928-abc123",
  "filename": "data_karyawan.xlsx",
  "file_path": "uploads/2025/12/performance_2025_12_a1b2c3d4.xlsx",
  "file_url": "http://localhost/storage/uploads/2025/12/performance_2025_12_a1b2c3d4.xlsx",
  "periode": "2025-12",
  ...
}
```

**Catatan:** 
- `filename` tetap menyimpan nama file asli yang diupload
- `file_path` menyimpan path file yang sudah disimpan dengan format baru

## Catatan
- Folder akan dibuat otomatis jika belum ada
- File akan dihapus secara otomatis ketika batch dihapus
- Pastikan `php artisan storage:link` sudah dijalankan untuk membuat symbolic link


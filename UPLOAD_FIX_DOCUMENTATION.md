# Dokumentasi Perbaikan Upload File

## Masalah
File yang diupload tidak tersimpan secara fisik di server dalam folder `uploads/` dengan struktur `YYYY/MM/` sesuai tanggal upload.

## Penyebab
Fungsi `createUploadDirectory()` di `includes/functions.php` menggunakan **path relatif** yang tidak tepat. Ketika dipanggil dari `pages/upload.php`, path `"uploads/"` akan mencari folder di `pages/uploads/` bukan di root `uploads/`.

## Solusi yang Diterapkan

### 1. Update `includes/functions.php`

#### Fungsi `createUploadDirectory()`
- **Sebelum:** Mengembalikan path relatif sederhana
- **Sesudah:** Mengembalikan array dengan 2 path:
  - `absolute`: Path lengkap untuk operasi file system (misal: `C:\laragon\www\MyApp\lubung-data-SAE\uploads\panen\2025\12`)
  - `relative`: Path relatif dari root untuk disimpan ke database (misal: `uploads/panen/2025/12`)

```php
function createUploadDirectory($category, $date = null) {
    if ($date === null) {
        $date = date('Y/m');
    }
    
    // Path relatif dari root project
    $relativePath = "uploads/" . $category . "/" . $date;
    
    // Absolute path untuk operasi file system
    $rootDir = dirname(__DIR__);
    $absolutePath = $rootDir . "/" . $relativePath;
    
    // Buat directory jika belum ada
    if (!is_dir($absolutePath)) {
        mkdir($absolutePath, 0755, true);
    }
    
    // Return array dengan kedua path
    return [
        'absolute' => $absolutePath,
        'relative' => $relativePath
    ];
}
```

#### Fungsi Baru: `getAbsolutePath()`
Helper function untuk mengkonversi path relatif ke absolute path saat mengakses file.

```php
function getAbsolutePath($relativePath) {
    // Jika sudah absolute path, return as is
    if (preg_match('/^[a-zA-Z]:[\\\\\/]|^\//', $relativePath)) {
        return $relativePath;
    }
    
    // Convert relative path to absolute
    $rootDir = dirname(__DIR__);
    $relativePath = str_replace('\\', '/', $relativePath);
    return $rootDir . '/' . $relativePath;
}
```

### 2. Update `pages/upload.php`

**Perubahan:**
- Menggunakan array dari `createUploadDirectory()`
- Menggunakan `$absolutePath` untuk operasi file (`move_uploaded_file`, `unlink`, `parseAndSaveJsonData`)
- Menggunakan `$relativePath` untuk menyimpan ke database

```php
// Create upload directory
$uploadPaths = createUploadDirectory($kategori);

// Generate unique filename
$uniqueFilename = generateUniqueFilename($file['name']);
$absolutePath = $uploadPaths['absolute'] . '/' . $uniqueFilename;
$relativePath = $uploadPaths['relative'] . '/' . $uniqueFilename;

// Move uploaded file (gunakan absolute path)
if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
    // Simpan ke database (gunakan relative path)
    $stmt->bindParam(':file_path', $relativePath);
    
    // Parse JSON (gunakan absolute path)
    $jsonParseResult = parseAndSaveJsonData($absolutePath, $kategori, $uploadId, $conn);
}
```

### 3. Update Script-script Akses File

File-file berikut telah diupdate untuk menggunakan `getAbsolutePath()` saat mengakses file:

- `scripts/download.php`
- `scripts/delete-file.php`
- `scripts/view-file-content.php`
- `scripts/bulk-delete-files.php`
- `scripts/check-import-files.php`

**Contoh perubahan:**
```php
// Sebelum
$filePath = $file['file_path'];
if (file_exists($filePath)) {
    // ...
}

// Sesudah
$filePath = getAbsolutePath($file['file_path']);
if (file_exists($filePath)) {
    // ...
}
```

## Keuntungan Solusi Ini

1. **Konsisten:** Path relatif yang disimpan di database tidak bergantung pada lokasi file yang memanggil
2. **Portabel:** Path relatif di database memudahkan migrasi aplikasi ke server lain
3. **Compatible:** Mendukung Windows (C:\) dan Unix (/var/www/)
4. **Backward Compatible:** Script lama yang sudah menyimpan absolute path tetap bisa bekerja

## Cara Test

1. Akses file test: `http://localhost/MyApp/lubung-data-SAE/test-upload-path.php`
2. Periksa output untuk memastikan:
   - Root directory terdeteksi dengan benar
   - Directory uploads dibuat dengan struktur YYYY/MM
   - Fungsi getAbsolutePath() bekerja dengan benar
3. Coba upload file melalui `pages/upload.php`
4. Verifikasi file tersimpan di `uploads/[kategori]/YYYY/MM/`

## Struktur Directory yang Diharapkan

```
lubung-data-SAE/
├── uploads/
│   ├── panen/
│   │   └── 2025/
│   │       └── 12/
│   │           ├── file1.json
│   │           └── file2.json
│   └── pengiriman/
│       └── 2025/
│           └── 12/
│               ├── file1.json
│               └── file2.json
├── pages/
│   └── upload.php
├── includes/
│   └── functions.php
└── scripts/
    ├── download.php
    ├── delete-file.php
    └── ...
```

## Troubleshooting

### Jika file masih tidak tersimpan:

1. **Cek permission folder uploads:**
   ```bash
   chmod -R 755 uploads/
   ```
   
2. **Cek PHP error log:**
   - Buka php error log untuk melihat error spesifik
   - Biasanya di `C:\laragon\www\laragon.log` atau sesuai konfigurasi

3. **Cek disk space:**
   - Pastikan ada cukup ruang untuk menyimpan file

4. **Cek upload_max_filesize di php.ini:**
   ```ini
   upload_max_filesize = 50M
   post_max_size = 50M
   ```

5. **Test manual dengan test-upload-path.php**

## Tanggal Perbaikan
31 Desember 2025

## Files yang Dimodifikasi
1. `includes/functions.php` - Update createUploadDirectory() dan tambah getAbsolutePath()
2. `pages/upload.php` - Update untuk menggunakan array path
3. `scripts/download.php` - Tambah getAbsolutePath()
4. `scripts/delete-file.php` - Tambah getAbsolutePath()
5. `scripts/view-file-content.php` - Tambah getAbsolutePath()
6. `scripts/bulk-delete-files.php` - Tambah getAbsolutePath()
7. `scripts/check-import-files.php` - Tambah getAbsolutePath()

## Files Baru
1. `test-upload-path.php` - Script untuk test konfigurasi path
2. `UPLOAD_FIX_DOCUMENTATION.md` - Dokumentasi ini


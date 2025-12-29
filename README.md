# Lubung Data SAE - Setup dan Instalasi

Website manajemen data perkebunan dengan fitur upload file dan file manager yang responsif.

## 🚀 Fitur Utama

- **Form Upload**: Input data kategori (panen/pengiriman), nama, NIK, afdeling, dan file JSON
- **Auto-Fill Profile**: Data nama dan NIK otomatis terisi dari profil user
- **Profile Management**: Kelola profil lengkap termasuk NIK dan nomor telepon
- **File Manager**: Kelola, cari, dan download file yang telah diupload
- **User Management**: Kelola user dan role (khusus admin)
- **JSON Format**: Hanya menerima file berformat JSON untuk konsistensi data
- **Autentikasi**: Sistem login dengan session management dan role-based access
- **Responsive Design**: Tampilan optimal di PC dan mobile
- **Organized Storage**: File tersimpan dalam struktur folder `uploads/kategori/tahun/bulan`
- **Database Integration**: Penyimpanan metadata file dan user di MySQL

## 📋 Requirements

- **Web Server**: Apache (Laragon/XAMPP/WAMPP)
- **PHP**: Versi 7.4 atau lebih tinggi
- **MySQL**: Versi 5.7 atau lebih tinggi
- **Browser**: Chrome, Firefox, Safari, Edge (versi terbaru)

## ⚡ Instalasi Cepat

### 1. Persiapan Database

Buka phpMyAdmin atau MySQL command line, lalu jalankan:

```sql
-- Import file database_setup.sql
SOURCE c:/laragon/www/MyApp/lubung-data-SAE/database_setup.sql;
```

Atau copy-paste isi file `database_setup.sql` ke phpMyAdmin.

### 2. Konfigurasi Database

Edit file `config/database.php` sesuai dengan setup MySQL Anda:

```php
private $host = 'localhost';
private $db_name = 'lubung_data_sae';
private $username = 'root';  // Sesuaikan
private $password = '';      // Sesuaikan
```

### 3. Set Permissions (jika di Linux/Mac)

```bash
chmod -R 755 uploads/
chmod -R 644 *.php
```

### 4. Akses Website

Buka browser dan kunjungi:
```
http://localhost/MyApp/lubung-data-SAE
```

## 🔐 Login Default

- **Username**: `admin`
- **Password**: `admin123`

⚠️ **PENTING**: Ubah password default setelah login pertama!

### 🔧 Jika Login Gagal:

1. **Jalankan Reset Password**:
   - Buka browser: `http://localhost/MyApp/lubung-data-SAE/reset-password.php`
   - Script akan otomatis memperbaiki password admin
   
2. **Periksa Database**:
   - Pastikan database `lubung_data_sae` sudah dibuat
   - Import ulang `database_setup.sql` jika diperlukan

## 🗂️ Struktur Project

```
lubung-data-SAE/
├── assets/
│   ├── css/
│   │   └── style.css          # Stylesheet utama
│   └── js/                    # JavaScript files
├── config/
│   └── database.php           # Konfigurasi database
├── includes/
│   └── functions.php          # Helper functions
├── uploads/                   # Folder penyimpanan file
│   ├── panen/
│   │   └── 2025/
│   │       └── 12/           # File bulan ini
│   └── pengiriman/
│       └── 2025/
│           └── 12/           # File bulan ini
├── login.php                  # Halaman login
├── dashboard.php              # Dashboard utama
├── upload.php                 # Form upload
├── profile.php                # Kelola profil user
├── file-manager.php           # Kelola file
├── user-management.php        # Kelola user (admin)
├── get-file-details.php       # Detail file (AJAX)
├── delete-file.php            # Hapus file (AJAX)
├── logout.php                 # Logout
├── reset-password.php         # Reset password admin
├── index.php                  # Redirect ke login
└── database_setup.sql         # Setup database
```

## 🎯 Cara Penggunaan

### 1. Login
- Akses website melalui browser
- Masukkan username dan password
- Klik tombol "Masuk"

### 2. Upload Data
- Klik menu "Upload Data" atau tombol "Upload Data Baru"
- **Data otomatis terisi**: Jika profil sudah lengkap, nama dan NIK akan terisi otomatis
- Pilih kategori (Panen/Pengiriman)
- Isi afdeling sesuai lokasi kerja saat itu
- Pilih file JSON untuk diupload (max 50MB)
- Sistem akan memvalidasi format JSON secara otomatis
- Klik "Upload File"

### 3. Kelola Profil
- Klik dropdown nama user → "Profil Saya"
- Lengkapi nama lengkap, NIK, dan nomor telepon
- **Manfaat**: Nama dan NIK akan otomatis terisi saat upload file
- Ubah password jika diperlukan
- Klik "Simpan Perubahan"

### 3. Kelola File
- Klik menu "File Manager"
- Gunakan filter kategori atau search untuk mencari file
- Klik tombol mata (👁) untuk melihat detail file
- Klik tombol download (⬇) untuk mengunduh file
- Admin dapat menghapus file dengan tombol hapus (🗑)

### 4. Kelola User (Khusus Admin)
- Klik menu "Kelola User"
- Klik tombol "Tambah User" untuk menambah user baru
- Isi username, password, nama lengkap, dan pilih role
- **Role "User"**: Hanya bisa upload file dan kelola profil
- **Role "Administrator"**: Akses penuh ke semua fitur
- User dapat melengkapi NIK dan nomor telepon di profil setelah login
- Edit atau hapus user dengan tombol aksi di setiap row

## 🔧 Konfigurasi Lanjutan

### Mengubah Ukuran Upload Maksimal

Edit `includes/functions.php`:
```php
$maxFileSize = 100 * 1024 * 1024; // 100MB
```

Dan sesuaikan `php.ini`:
```ini
upload_max_filesize = 100M
post_max_size = 100M
```

### Menambah Tipe File yang Diizinkan

Edit `includes/functions.php`:
```php
$allowedFileTypes = [
    'json', 'xml', 'csv' // Tambah format lain jika diperlukan
];
```

**Catatan**: Saat ini sistem hanya menerima file JSON untuk memastikan konsistensi format data.

### Mengubah Struktur Folder

Edit function `createUploadDirectory` di `includes/functions.php`:
```php
$uploadPath = "uploads/" . $category . "/" . date('Y/m/d');
```

## 🌐 Akses dari IP Server

### 1. Cek IP Server
```cmd
ipconfig
```

### 2. Setting Virtual Host (Opsional)

Edit `C:\laragon\etc\apache2\sites-enabled\00-default.conf`:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/MyApp/lubung-data-SAE"
    ServerName lubung-data.local
    
    <Directory "C:/laragon/www/MyApp/lubung-data-SAE">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Akses dari Device Lain

Pastikan firewall mengizinkan port 80, lalu akses:
```
http://IP_SERVER/MyApp/lubung-data-SAE
```

Contoh: `http://192.168.1.100/MyApp/lubung-data-SAE`

## 🛡️ Keamanan

1. **Ubah Password Default**: Segera ganti password admin
2. **File Validation**: Sistem memvalidasi tipe dan ukuran file
3. **SQL Injection Protection**: Menggunakan prepared statements
4. **Session Security**: Timeout otomatis dan secure session handling

## 🐛 Troubleshooting

### Error "Access Denied" atau Login Gagal
- Jalankan `http://localhost/MyApp/lubung-data-SAE/reset-password.php`
- Periksa konfigurasi database di `config/database.php`
- Pastikan MySQL service berjalan
- Import ulang `database_setup.sql` jika diperlukan

### File Upload Gagal
- Periksa permission folder `uploads/`
- Cek ukuran file (max 50MB)
- Pastikan tipe file diizinkan

### Akses Tidak Bisa dari IP Lain
- Periksa firewall Windows/antivirus
- Pastikan Apache bind ke 0.0.0.0, bukan localhost
- Restart Apache service

### Style/CSS Tidak Muncul
- Clear browser cache
- Periksa path file CSS di `assets/css/style.css`

## 📱 Responsive Design

Website ini dioptimalkan untuk:
- **Desktop**: Layout penuh dengan sidebar dan tabel
- **Tablet**: Layout adaptif dengan navigation collapsible
- **Mobile**: Layout stack dengan touch-friendly buttons

## 🎨 Kustomisasi Tampilan

Edit `assets/css/style.css` untuk mengubah:
- Color scheme (CSS variables di `:root`)
- Typography dan spacing
- Component styling
- Responsive breakpoints

## 📞 Support

Jika mengalami masalah:
1. Periksa error log Apache/PHP
2. Cek console browser untuk JavaScript errors
3. Pastikan semua requirements terpenuhi
4. Verifikasi konfigurasi database

## 🔄 Update dan Maintenance

- **Backup Database**: Rutin backup database
- **Clean Old Files**: Hapus file lama secara berkala
- **Monitor Storage**: Pantau penggunaan disk space
- **Update Dependencies**: Update PHP/MySQL jika diperlukan

---

**Lubung Data SAE** - Sistem Manajemen Data Perkebunan Modern dan Responsif 🌱# lumbung-data-SAE

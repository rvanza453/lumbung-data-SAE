# Struktur Project Lubung Data SAE

## Deskripsi Project
Sistem Manajemen Data Perkebunan untuk monitoring data panen dan pengiriman.

---

## Struktur Folder (Reorganized - December 2025)

```
lubung-data-SAE/
├── admin/                          # Halaman Management Admin
│   ├── activity-logs.php          # Log aktivitas sistem
│   ├── file-manager.php           # Pengelolaan file upload
│   └── user-management.php        # Manajemen user
│
├── api/                            # REST API Endpoints
│   ├── auth.php                   # Authentication API
│   ├── BaseAPI.php                # Base API class
│   ├── direct_data.php            # Direct data access
│   ├── koreksi.php                # Data correction API
│   ├── monitoring_restan.php      # Monitoring remaining data
│   ├── panen.php                  # Harvest data API
│   ├── pengiriman.php             # Delivery data API
│   └── README.md                  # API Documentation
│
├── assets/                         # Static Assets
│   ├── css/
│   │   └── style.css              # Main stylesheet
│   └── js/                        # JavaScript files
│
├── config/                         # Configuration Files
│   └── database.php               # Database configuration
│
├── database/                       # Database Scripts
│   ├── database_setup.sql         # Initial database setup
│   ├── database_update.sql        # Database updates
│   ├── database_add_koreksi.sql   # Add correction feature
│   ├── database_add_koreksi_safe.sql  # Safe correction addition
│   ├── database_update_missing_columns.sql  # Column updates
│   └── update_corrector_password.sql  # Password update script
│
├── docs/                           # Documentation
│   ├── api-documentation.md       # API documentation
│   ├── api-status.md              # API status documentation
│   ├── bulk-actions-readme.md     # Bulk actions guide
│   ├── fitur-koreksi-documentation.md  # Correction feature docs
│   └── quick-start.md             # Quick start guide
│
├── includes/                       # PHP Includes
│   └── functions.php              # Common functions
│
├── pages/                          # User Pages
│   ├── upload.php                 # Upload data page
│   ├── profile.php                # User profile page
│   └── monitoring.php             # Data monitoring interface
│
├── scripts/                        # Utility Scripts
│   ├── bulk-delete-files.php     # Bulk file deletion
│   ├── check-import-files.php    # Import file checker
│   ├── delete-file.php           # Single file deletion
│   ├── download.php              # File download handler
│   ├── get-file-details.php      # File details retriever
│   ├── tracking-access.php       # Access tracking
│   └── view-file-content.php     # File content viewer
│
├── temp_imports/                   # Temporary Import Files
│   └── (cleaned - temporary JSON files)
│
├── uploads/                        # Uploaded Files Storage
│   ├── panen/                     # Harvest data files
│   │   └── 2025/12/
│   └── pengiriman/                # Delivery data files
│       └── 2025/12/
│
├── dashboard.php                   # Main dashboard (root)
├── index.php                       # Entry point - redirects to login
├── login.php                       # Login page
├── logout.php                      # Logout handler
├── CHANGELOG_JSON_FORMAT.md        # JSON format changelog
├── PROJECT_STRUCTURE.md            # This file
└── README.md                       # Project readme

```

---

## Perubahan dari Struktur Lama

### Reorganisasi yang Dilakukan:

1. **Folder admin/** (baru)
   - Dipindahkan: `user-management.php`, `activity-logs.php`, `file-manager.php`
   - Tujuan: Memisahkan fitur admin dari user biasa

2. **Folder database/** (baru)
   - Dipindahkan: Semua file `*.sql`
   - Tujuan: Mengorganisir database scripts dalam satu tempat

3. **Folder scripts/** (baru)
   - Dipindahkan: Utility scripts seperti `delete-file.php`, `download.php`, dll
   - Tujuan: Memisahkan utility scripts dari halaman utama

4. **Folder pages/** (sudah ada, ditambahkan isi)
   - Dipindahkan: `upload.php`, `profile.php`, `monitoring.php` (renamed from .html)
   - Tujuan: Mengorganisir halaman user

5. **Cleanup**
   - Dihapus: `monitoring_ref.html` (tidak terpakai)
   - Dihapus: `file-management/` folder (kosong)
   - Dibersihkan: `temp_imports/` dari file JSON lama

---

## Path Updates yang Dilakukan

Semua file telah diupdate dengan path yang benar:

### Includes
- File di `admin/`: `include_once '../includes/functions.php'`
- File di `pages/`: `include_once '../includes/functions.php'`
- File di `scripts/`: `include_once '../includes/functions.php'`

### Assets
- File di `admin/`: `href="../assets/css/style.css"`
- File di `pages/`: `href="../assets/css/style.css"`

### Navigation Links
- Semua navbar telah diupdate dengan relative paths yang benar
- API calls di monitoring.html: `../api/koreksi.php`

---

## Setup Instructions untuk Tim IT

### 1. Database Setup
```bash
# Import database utama
mysql -u root -p lubung_data_sae < database/database_setup.sql

# Jalankan update jika diperlukan
mysql -u root -p lubung_data_sae < database/database_update.sql
mysql -u root -p lubung_data_sae < database/database_add_koreksi_safe.sql
mysql -u root -p lubung_data_sae < database/database_update_missing_columns.sql
```

### 2. Configuration
Edit `config/database.php` sesuai environment:
```php
private $host = 'localhost';
private $db_name = 'lubung_data_sae';
private $username = 'root'; 
private $password = '';
```

### 3. Permissions
Pastikan folder ini writable:
```bash
chmod 755 uploads/
chmod 755 temp_imports/
```

### 4. Default Login
- Username: `admin`
- Password: `admin123`
- **PENTING**: Ubah password setelah first login!

---

## Fitur Utama

### User Roles
1. **Admin**: Full access (user management, activity logs, semua data)
2. **Corrector**: Data correction access
3. **User**: Upload dan view data sendiri

### Modules
1. **Dashboard**: Overview dan statistik
2. **Upload**: Upload file JSON data panen/pengiriman
3. **File Manager**: Kelola file upload dengan filter dan bulk actions
4. **Monitoring**: Visualisasi data real-time dengan koreksi
5. **User Management**: Manajemen user (admin only)
6. **Activity Logs**: Log aktivitas sistem (admin only)

---

## Technical Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5, Tailwind CSS, React (monitoring page)
- **Server**: Apache/Nginx dengan PHP-FPM

---

## Contact & Support
Untuk pertanyaan lebih lanjut mengenai project ini, silakan hubungi developer atau dokumentasi di folder `docs/`.

---

**Last Updated**: December 29, 2025
**Version**: 2.0 (Reorganized Structure)


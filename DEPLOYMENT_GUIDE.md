# Deployment Guide - Lubung Data SAE

## Quick Deployment Checklist

### 1. Prerequisites
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache/Nginx web server
- PHP Extensions: PDO, PDO_MySQL, JSON, mbstring

### 2. Installation Steps

#### Step 1: Clone/Copy Project
```bash
# Copy semua file ke web directory
cp -r lubung-data-SAE /var/www/html/
# atau untuk Laragon/XAMPP
# Taruh di: C:\laragon\www\lubung-data-SAE
```

#### Step 2: Database Setup
```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE lubung_data_sae CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import database utama
mysql -u root -p lubung_data_sae < database/database_setup.sql

# (Opsional) Import updates jika diperlukan
mysql -u root -p lubung_data_sae < database/database_update.sql
mysql -u root -p lubung_data_sae < database/database_add_koreksi_safe.sql
mysql -u root -p lubung_data_sae < database/database_update_missing_columns.sql
```

#### Step 3: Configuration
Edit file `config/database.php`:
```php
<?php
class Database {
    private $host = 'localhost';           // Host database
    private $db_name = 'lubung_data_sae';  // Nama database
    private $username = 'root';             // Username MySQL
    private $password = '';                 // Password MySQL
    ...
}
```

#### Step 4: Set Permissions
```bash
# Linux/Mac
chmod 755 uploads/
chmod 755 temp_imports/
chmod 644 config/database.php

# Windows (via Properties atau icacls)
# Beri write permission ke folder uploads dan temp_imports
```

#### Step 5: Web Server Configuration

**Apache (.htaccess sudah included)**
Pastikan `mod_rewrite` enabled:
```bash
a2enmod rewrite
systemctl restart apache2
```

**Nginx (contoh config)**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/lubung-data-SAE;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(ht|git) {
        deny all;
    }
}
```

### 3. First Login
1. Akses aplikasi via browser: `http://your-domain.com/` atau `http://localhost/lubung-data-SAE/`
2. Login dengan kredensial default:
   - **Username**: `admin`
   - **Password**: `admin123`
3. **SEGERA** ubah password setelah login pertama!

### 4. Post-Deployment Checklist

- [ ] Database terkoneksi dengan baik
- [ ] Login berhasil dengan akun admin
- [ ] Upload file JSON berfungsi
- [ ] File Manager dapat menampilkan data
- [ ] Monitoring page dapat diakses
- [ ] Activity logs mencatat aktivitas
- [ ] Permissions folder uploads dan temp_imports sudah benar
- [ ] Password admin sudah diubah dari default

### 5. Security Recommendations

1. **Ubah kredensial database** dari default
2. **Ubah password admin** setelah first login
3. **Set proper file permissions**:
   ```bash
   find . -type f -exec chmod 644 {} \;
   find . -type d -exec chmod 755 {} \;
   chmod 755 uploads temp_imports
   ```
4. **Enable HTTPS** di production
5. **Backup database** secara berkala
6. **Monitor activity logs** untuk aktivitas mencurigakan

### 6. Troubleshooting

#### Database Connection Error
```
Cek: config/database.php
- Pastikan credentials benar
- Pastikan MySQL service running
- Pastikan database sudah dibuat
```

#### File Upload Error
```
Cek:
- PHP upload_max_filesize di php.ini (minimal 50MB)
- PHP post_max_size di php.ini (minimal 50MB)
- Permissions folder uploads/
```

#### Page Not Found / 404
```
Cek:
- Apache mod_rewrite enabled
- .htaccess file ada di root
- AllowOverride All di Apache config
```

#### Blank Page / PHP Error
```
Cek:
- PHP error log di /var/log/apache2/error.log
- PHP version minimal 7.4
- Required extensions installed (PDO, PDO_MySQL, JSON)
```

### 7. Maintenance

#### Backup Database
```bash
# Backup otomatis via cron (contoh: setiap hari jam 2 pagi)
0 2 * * * mysqldump -u root -pYOURPASSWORD lubung_data_sae > /backup/lubung_$(date +\%Y\%m\%d).sql
```

#### Clean Temp Files
```bash
# Bersihkan temp_imports setiap minggu
0 0 * * 0 find /var/www/html/lubung-data-SAE/temp_imports/ -type f -mtime +7 -delete
```

#### Monitor Disk Space
```bash
# Cek ukuran folder uploads
du -sh uploads/
```

### 8. Contacts

- **Developer**: [Your Contact]
- **Documentation**: Lihat folder `docs/` untuk dokumentasi lengkap
- **Project Structure**: Lihat `PROJECT_STRUCTURE.md`

---

**Last Updated**: December 29, 2025
**Version**: 2.0


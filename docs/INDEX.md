# Dokumentasi Lubung Data SAE

## 📚 Daftar Dokumentasi

### 🚀 Getting Started
- **[Quick Start Guide](quick-start.md)** - Panduan cepat memulai menggunakan sistem
- **[Deployment Guide](DEPLOYMENT_GUIDE.md)** - Panduan lengkap deployment ke production
- **[Project Structure](PROJECT_STRUCTURE.md)** - Struktur folder dan file project

### 📖 User Guides
- **[Quick Start](quick-start.md)** - Panduan cepat untuk pengguna baru
- **[Bulk Actions Guide](bulk-actions-readme.md)** - Panduan menggunakan fitur bulk actions

### 🔧 Feature Documentation
- **[Fitur Koreksi](fitur-koreksi-documentation.md)** - Dokumentasi lengkap fitur koreksi data
- **[Normalisasi Nama Blok & TPH](../scripts/README-normalize-blok.md)** - Panduan normalisasi nama blok dan nomor TPH

### 🌐 API Documentation
- **[API Documentation](api-documentation.md)** - Dokumentasi lengkap REST API
- **[API Status](api-status.md)** - Status dan ketersediaan endpoint API

### 📝 Changelogs
- **[Blok Normalization Changelog](changelogs/BLOK_NORMALIZATION_CHANGELOG.md)** - Perubahan normalisasi nama blok
- **[TPH Normalization Changelog](changelogs/TPH_NORMALIZATION_CHANGELOG.md)** - Perubahan normalisasi nomor TPH
- **[Reorganization Changelog](changelogs/REORGANIZATION_CHANGELOG.md)** - Perubahan struktur project
- **[JSON Format Changelog](changelogs/CHANGELOG_JSON_FORMAT.md)** - Perubahan format JSON

---

## 📂 Struktur Folder Dokumentasi

```
docs/
├── INDEX.md (file ini)                      # Index semua dokumentasi
├── quick-start.md                           # Quick start guide
├── DEPLOYMENT_GUIDE.md                      # Deployment guide
├── PROJECT_STRUCTURE.md                     # Project structure
├── fitur-koreksi-documentation.md          # Fitur koreksi
├── bulk-actions-readme.md                   # Bulk actions guide
├── api-documentation.md                     # API documentation
├── api-status.md                            # API status
└── changelogs/                              # Folder changelogs
    ├── BLOK_NORMALIZATION_CHANGELOG.md     # Blok normalization
    ├── TPH_NORMALIZATION_CHANGELOG.md      # TPH normalization
    ├── REORGANIZATION_CHANGELOG.md          # Project reorganization
    └── CHANGELOG_JSON_FORMAT.md             # JSON format changes
```

---

## 🎯 Dokumentasi untuk Role Tertentu

### Untuk Admin
1. [Deployment Guide](DEPLOYMENT_GUIDE.md) - Setup dan deployment
2. [Project Structure](PROJECT_STRUCTURE.md) - Memahami struktur project
3. [API Documentation](api-documentation.md) - Integrasi dengan sistem lain
4. [Fitur Koreksi](fitur-koreksi-documentation.md) - Mengelola koreksi data

### Untuk User
1. [Quick Start](quick-start.md) - Memulai menggunakan sistem
2. [Bulk Actions Guide](bulk-actions-readme.md) - Operasi multiple files

### Untuk Developer
1. [Project Structure](PROJECT_STRUCTURE.md) - Arsitektur sistem
2. [API Documentation](api-documentation.md) - Menggunakan API
3. [All Changelogs](changelogs/) - History perubahan sistem

---

## 🔄 Update dan Maintenance

### Latest Updates
1. **31 Des 2025** - Normalisasi nomor TPH (001 → 1)
2. **31 Des 2025** - Normalisasi nama blok (B02 → B2)
3. **29 Des 2025** - Reorganisasi struktur project v2.0
4. **Update terakhir** - Lihat folder [changelogs](changelogs/)

### Maintenance Documentation
- Backup database: Lihat [Deployment Guide - Maintenance](DEPLOYMENT_GUIDE.md#7-maintenance)
- Clean temp files: Lihat [Deployment Guide - Maintenance](DEPLOYMENT_GUIDE.md#7-maintenance)

---

## 📞 Support & Contact

Jika menemui masalah atau butuh bantuan:
1. Cek dokumentasi yang relevan
2. Lihat troubleshooting di [Deployment Guide](DEPLOYMENT_GUIDE.md#6-troubleshooting)
3. Hubungi tim developer

---

**Last Updated**: 31 Desember 2025  
**Version**: 2.1


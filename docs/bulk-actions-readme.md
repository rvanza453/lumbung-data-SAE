# Fitur Bulk Actions - File Manager

## Overview
Fitur bulk actions memungkinkan pengguna untuk melakukan operasi pada multiple file sekaligus di File Manager. Tersedia dua aksi utama: Delete dan Import to Monitoring.

## Fitur yang Tersedia

### 1. Selection System
- **Master Checkbox**: Checkbox di header tabel untuk select/deselect semua file
- **Individual Checkboxes**: Checkbox di setiap baris untuk memilih file tertentu
- **Selection Counter**: Menampilkan jumlah file yang dipilih
- **Clear Selection**: Tombol untuk membatalkan semua pilihan

### 2. Bulk Actions Toolbar
Toolbar yang muncul ketika ada file terpilih, berisi:
- **Counter**: Menampilkan "X file dipilih"
- **Batal Pilih**: Tombol untuk clear selection
- **Import ke Monitoring**: Import file JSON ke monitoring dashboard
- **Hapus Terpilih**: Delete multiple file sekaligus

### 3. Bulk Delete
- Menghapus multiple file sekaligus dari database dan storage
- Validation untuk permission (user non-admin hanya bisa hapus file sendiri)
- Transaction rollback jika ada error
- Activity logging untuk audit trail
- Konfirmasi sebelum delete dengan jumlah file

### 4. Import to Monitoring
- Import file JSON yang dipilih ke monitoring dashboard
- Validation format file (harus JSON)
- Validation struktur data (harvest/transport monitoring)
- Buka monitoring dashboard di tab baru dengan data ter-import
- Activity logging untuk audit trail

## File Structure

### Frontend
- **file-manager.php**: UI dengan checkbox dan toolbar
- **Custom CSS**: Animation dan styling untuk bulk actions
- **JavaScript**: Handling selection, AJAX calls, dan UI updates

### Backend
- **bulk-delete-files.php**: API untuk bulk delete operations
- **import-to-monitoring.php**: API untuk import to monitoring
- **Enhanced monitoring.html**: Support untuk menerima imported files

## Permission System
- **Admin**: Dapat melakukan bulk actions pada semua file
- **Regular User**: Hanya dapat melakukan bulk actions pada file sendiri
- **Auto-filtering**: System otomatis filter file berdasarkan permission

## User Experience Features
- **Visual Feedback**: Loading states, animations, success/error messages
- **Progressive Enhancement**: Checkbox states (unchecked, checked, indeterminate)
- **Responsive Design**: Works on mobile and desktop
- **Error Handling**: Graceful handling untuk partial failures
- **Confirmation Dialogs**: Prevent accidental bulk operations

## Usage Instructions

### Selecting Files
1. **Select All**: Klik checkbox di header tabel
2. **Select Individual**: Klik checkbox di setiap baris file
3. **Mixed Selection**: Kombinasi select all dan individual selection

### Bulk Delete
1. Pilih file yang ingin dihapus
2. Klik tombol "Hapus Terpilih" di toolbar
3. Konfirmasi penghapusan
4. System akan menghapus file dan menampilkan hasil

### Import to Monitoring
1. Pilih file JSON yang kompatibel (harvest/transport data)
2. Klik tombol "Import ke Monitoring"
3. Konfirmasi import
4. Monitoring dashboard akan terbuka di tab baru dengan data

## Technical Implementation

### JavaScript Functions
```javascript
toggleSelectAll()        // Handle master checkbox
updateSelection()        // Update UI based on selection
clearSelection()         // Clear all selections
getSelectedFileIds()     // Get array of selected file IDs
bulkDeleteFiles()        // Execute bulk delete
importToMonitoring()     // Execute import to monitoring
```

### API Endpoints
```php
POST /bulk-delete-files.php
POST /import-to-monitoring.php
```

### Response Format
```json
{
    "success": true,
    "deletedCount": 5,
    "totalSelected": 5,
    "message": "Successfully deleted 5 files",
    "warnings": [] // Optional: untuk partial failures
}
```

## Security Features
- CSRF Protection via session validation
- Permission-based access control
- Input validation dan sanitization
- SQL injection prevention
- File system permission checks

## Error Handling
- Database transaction rollback
- Partial failure reporting
- User-friendly error messages
- Comprehensive logging
- Graceful degradation

## Future Enhancements
- Batch upload functionality
- Export selected files
- Archive/restore operations
- Advanced filtering before bulk actions
- Progress bars for large operations
# TPH Normalization Changelog

**Date**: 31 Desember 2025  
**Version**: 1.1.0  
**Related Issue**: Inconsistent TPH number formats causing data fragmentation

## Problem Statement

Di halaman monitoring, data TPH dengan format berbeda seperti "1", "01", dan "001" ditampilkan sebagai TPH yang berbeda, padahal sebenarnya merupakan TPH yang sama. Hal ini menyebabkan:

1. **Data terfragmentasi**: Satu TPH yang sama muncul di beberapa baris berbeda
2. **Perhitungan tidak akurat**: Total restan dan data per TPH menjadi tidak akurat
3. **Kesulitan monitoring**: Sulit melihat status aktual per TPH
4. **Inkonsistensi data**: Tergantung bagaimana user menginput di aplikasi mobile

### Contoh Masalah

**Sebelum normalisasi:**
```
Blok G1:
- TPH 1: Panen 50 Jjg, Kirim 30 Jjg, Restan 20 Jjg
- TPH 01: Panen 40 Jjg, Kirim 25 Jjg, Restan 15 Jjg
- TPH 001: Panen 30 Jjg, Kirim 20 Jjg, Restan 10 Jjg
```

Seharusnya ini semua TPH yang sama, dengan total:
- Panen: 120 Jjg
- Kirim: 75 Jjg
- Restan: 45 Jjg

## Solution

### 1. Backend Normalization Function

Added `normalizeTphNumber()` function in `includes/functions.php`:

```php
function normalizeTphNumber($tph) {
    if (empty($tph)) {
        return $tph;
    }
    
    // Trim whitespace
    $tph = trim($tph);
    
    // If it's purely numeric (like "001", "01", "1"), remove leading zeros
    if (is_numeric($tph)) {
        return (string)((int)$tph);
    }
    
    // If it contains letters and numbers (like "TPH001"), normalize the number part
    $normalized = preg_replace_callback(
        '/^([A-Za-z]*\s*)0+(\d+)$/',
        function($matches) {
            return $matches[1] . $matches[2];
        },
        $tph
    );
    
    return $normalized;
}
```

### 2. Normalization Examples

| Original | Normalized |
|----------|-----------|
| `001`    | `1`       |
| `01`     | `1`       |
| `1`      | `1`       |
| `TPH001` | `TPH1`    |
| `TPH 01` | `TPH 1`   |
| `002`    | `2`       |
| `0123`   | `123`     |

### 3. Integration Points

#### a. Data Panen Upload (functions.php)

```php
// Normalize TPH number before saving
$tphNumber = $item['noTPH'] ?? '';
$normalizedTph = normalizeTphNumber($tphNumber);

$stmt->execute([
    // ... other fields
    ':no_tph' => $normalizedTph,
    // ... other fields
]);
```

#### b. Data Pengiriman Upload (functions.php)

```php
// Normalize TPH number before saving
$tphNumber = $item['noTPH'] ?? '';
$normalizedTph = normalizeTphNumber($tphNumber);

$stmt->execute([
    // ... other fields
    ':no_tph' => $normalizedTph,
    // ... other fields
]);
```

### 4. Database Normalization Script

Created `scripts/normalize-tph-numbers.php` to normalize existing data:

- Processes all records in `data_panen` table
- Processes all records in `data_pengiriman` table
- Shows detailed output of all changes
- Provides summary statistics

## Files Changed

### Modified Files

1. **includes/functions.php**
   - Added `normalizeTphNumber()` function
   - Modified `savePanenData()` to normalize TPH before saving
   - Modified `savePengirimanData()` to normalize TPH before saving

### New Files

1. **scripts/normalize-tph-numbers.php**
   - Script to normalize existing TPH numbers in database
   - Can be run via command line or browser

2. **docs/changelogs/TPH_NORMALIZATION_CHANGELOG.md**
   - This documentation file

### Updated Files

1. **scripts/README-normalize-blok.md**
   - Updated to include TPH normalization information
   - Added examples and instructions for TPH normalization script

## Migration Steps

### For Fresh Installations

No action needed. The normalization is automatic for all new uploads.

### For Existing Installations

1. **Update Code**
   ```bash
   git pull origin main
   ```

2. **Run Normalization Script**
   ```bash
   cd scripts
   php normalize-tph-numbers.php
   ```

3. **Verify Results**
   - Check the monitoring page
   - Verify that TPH numbers are now consistent
   - Confirm that restan calculations are accurate

## Testing

### Test Cases

1. **Upload file with TPH "001"**
   - Expected: Saved as "1" in database

2. **Upload file with TPH "01"**
   - Expected: Saved as "1" in database

3. **Upload file with TPH "1"**
   - Expected: Saved as "1" in database

4. **Upload file with TPH "TPH001"**
   - Expected: Saved as "TPH1" in database

5. **Check monitoring page**
   - Expected: All variations of same TPH number appear in same row

### Manual Testing

```bash
# Before testing, backup database
mysqldump -u root lubung_data > backup_before_tph_norm.sql

# Run normalization
cd scripts
php normalize-tph-numbers.php

# Check results in monitoring page
# If issues found, restore backup:
# mysql -u root lubung_data < backup_before_tph_norm.sql
```

## Impact

### Positive Impacts

1. ✅ **Data Consistency**: All TPH numbers follow same format
2. ✅ **Accurate Calculations**: Restan and totals now calculated correctly
3. ✅ **Better Monitoring**: Easier to track actual status per TPH
4. ✅ **User Experience**: More intuitive data display
5. ✅ **Future-Proof**: All new uploads automatically normalized

### Potential Issues

1. ⚠️ **Historical Reports**: May affect historical data if TPH was used as identifier
   - **Solution**: Run normalization script once to update all historical data

2. ⚠️ **API Responses**: Existing integrations expecting specific format
   - **Solution**: Update API documentation and notify integrators

## Rollback Plan

If issues occur:

1. **Stop using new code**
   ```bash
   git revert HEAD
   ```

2. **Restore database backup**
   ```bash
   mysql -u root lubung_data < backup_before_tph_norm.sql
   ```

3. **Verify system is working**
   - Test uploads
   - Check monitoring page
   - Verify calculations

## Future Improvements

1. **API Normalization**: Add normalization to API endpoints
2. **Validation**: Add frontend validation to prevent invalid TPH formats
3. **Migration Tool**: Create GUI tool for normalization in admin panel
4. **Bulk Update**: Add batch normalization feature in file manager

## Related Documentation

- [Blok Normalization Changelog](./BLOK_NORMALIZATION_CHANGELOG.md)
- [Normalization Scripts README](../../scripts/README-normalize-blok.md)
- [API Documentation](../api-documentation.md)

## Support

For issues or questions regarding TPH normalization:

1. Check [Troubleshooting Guide](../../scripts/README-normalize-blok.md#troubleshooting)
2. Review this changelog for expected behavior
3. Contact development team

---

**Last Updated**: 31 Desember 2025  
**Maintained By**: Development Team


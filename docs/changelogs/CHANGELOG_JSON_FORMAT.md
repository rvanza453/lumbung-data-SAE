# Changelog: JSON Format Updates

## Summary
This update addresses changes in the export JSON format from both harvest (panen) and transport (pengiriman) applications to support new fields and calculations.

## Changes Made

### 1. Database Schema
**Note**: The database schema in `database_setup.sql` already supports the new fields:
- `data_panen` table: `bjr`, `kg_total` fields
- `data_pengiriman` table: `bjr`, `kg_total`, `kg_berondolan` fields

### 2. Upload Processing (`includes/functions.php`)

#### Harvest Data Processing (`savePanenData`)
- **Added support for `bjr` field**: Now extracts and stores BJR (Berat Janjang Rata-rata)
- **Added support for `kgTotal` field**: Now extracts and stores total weight

#### Transport Data Processing (`savePengirimanData`)
- **Updated field extraction**:
  - `kg`: Legacy field (backward compatibility)
  - `bjr`: BJR value
  - `kgBerondolan`/`kg_berondolan`: Loose fruit weight
  - `kgTotal`/`kg_total`: Total weight
- **Added calculation logic**: If `kgTotal` not provided, calculates as `(jumlahJanjang * bjr) + kgBerondolan`

### 3. API Updates

#### Pengiriman API (`api/pengiriman.php`)
- **Updated `formatPengirimanRecord`**:
  - Added `kg_total` field
  - Added `kg_berondolan` field
  - Added `kg_brd` field as alias for compatibility
- **Updated statistics queries**: Changed from `SUM(dp.kg)` to `SUM(COALESCE(dp.kg_total, dp.kg))`
- **Updated weight filters**: Now uses `kg_total` as primary field with `kg` fallback

#### Panen API (`api/panen.php`)
- **Updated `formatPanenRecord`**: Added `bjr` and `kg_total` fields
- **Enhanced statistics**: Added kg_total and bjr statistics in summary queries

### 4. Monitoring Interface (`monitoring.html`)

#### Data Processing
- **Transport data**: Now calculates `kgTotal` as `(jumlahJanjang * bjr) + kgBerondolan` if not provided
- **Harvest data**: Added support for `bjr` and `kgTotal` fields

#### CSV Export Updates
- **Harvest CSV**: Added "BJR" and "Kg Total" columns
- **Transport CSV**: 
  - Changed "Berat (Kg)" to "Kg Total"
  - Added "Kg Berondolan" column
  - Uses `kgTotal` instead of `kg` for export

## Field Mapping

### New Harvest Format
```json
{
  "items": [
    {
      "namaPemanen": "...",
      "jumlahJanjang": "...",
      "bjr": "...",           // NEW: BJR value
      "kgTotal": "..."        // NEW: Total weight
    }
  ]
}
```

### New Transport Format
```json
{
  "items": [
    {
      "jumlahJanjang": "...",
      "bjr": "...",           // NEW: BJR value  
      "kg": "...",            // LEGACY: Still supported
      "kgBerondolan": "...",  // NEW: Loose fruit weight
      "kgTotal": "..."        // NEW: Total weight (janjang*bjr + kgBerondolan)
    }
  ]
}
```

## Backward Compatibility
- All changes maintain backward compatibility with existing JSON formats
- Legacy `kg` field still supported in transport data
- APIs use `COALESCE(kg_total, kg)` for seamless transition

## Testing
Test the changes by:
1. Uploading new format JSON files with bjr/kgTotal fields
2. Uploading legacy format files to ensure compatibility
3. Checking API responses include new fields
4. Verifying monitoring dashboard displays correctly
5. Testing CSV exports include new columns

## Notes
- The calculation `kgTotal = (jumlahJanjang * bjr) + kgBerondolan` is automatically applied when kgTotal is not provided in the JSON
- All weight-related statistics now use kg_total as the primary field
- Monitoring interface now properly uses kg_total for transport calculations instead of the legacy kg field
# Afdeling Normalization Changelog

**Date**: 31 Desember 2025  
**Version**: 1.1.0  
**Related Issue**: Inconsistent Afdeling naming (Roman vs Arabic numerals)

## Problem Statement

Data Afdeling tidak konsisten karena penggunaan format berbeda:
1. Angka Romawi: "I", "II", "III"
2. Leading Zeros: "01", "02"
3. Kombinasi Text: "Afdeling I", "AFD 01"

Hal ini menyebabkan satu afdeling terpecah menjadi beberapa entitas di laporan.

## Solution

### 1. Enhanced Normalization Logic
Updated `normalizeAfdelingName()` to include Roman numeral conversion.

| Original Input | Normalized | Rule Applied |
|----------------|------------|--------------|
| `I` | `1` | Roman Conversion (Exact) |
| `II` | `2` | Roman Conversion (Exact) |
| `IV` | `4` | Roman Conversion (Exact) |
| `Afdeling I` | `Afdeling 1` | Roman Conversion (Partial) |
| `01` | `1` | Remove Leading Zero |
| `AFD 01` | `AFD 1` | Remove Leading Zero |

### 2. Implementation Details
* **Priority**: Exact Roman match -> Partial Roman match -> Leading Zero removal.
* **Scope**: Handles Roman numerals I through XX (1-20).

### 3. Migration
Run the script to update existing data:
```bash
cd scripts
php normalize-afdeling-names.php
<?php
/**
 * Script to normalize existing block names in database
 * Converts "B02", "B002" to "B2", etc.
 * 
 * Run this script once after deploying the new normalization logic
 * to ensure all existing data follows the same format
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Function to normalize block name (same as in functions.php)
function normalizeBlokNameScript($blok) {
    if (empty($blok)) {
        return $blok;
    }
    
    // Trim whitespace
    $blok = trim($blok);
    
    // Remove leading zeros from numbers in the block name
    // Pattern: looks for letters followed by numbers with leading zeros
    // Examples: B02 -> B2, B002 -> B2, Blok 02 -> Blok 2
    $normalized = preg_replace_callback(
        '/([A-Za-z]+)0+(\d+)/',
        function($matches) {
            // $matches[1] = letters (e.g., "B", "Blok ")
            // $matches[2] = number without leading zeros (e.g., "2")
            return $matches[1] . $matches[2];
        },
        $blok
    );
    
    // Also handle spaces: "Blok 02" -> "Blok 2"
    $normalized = preg_replace_callback(
        '/\s+0+(\d+)/',
        function($matches) {
            return ' ' . $matches[1];
        },
        $normalized
    );
    
    return $normalized;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== Normalisasi Nama Blok di Database ===\n\n";
    
    // Get current timestamp for backup reference
    $timestamp = date('Y-m-d H:i:s');
    echo "Started at: $timestamp\n\n";
    
    // 1. Normalize data_panen table
    echo "1. Processing data_panen table...\n";
    $queryPanen = "SELECT id, blok FROM data_panen WHERE blok IS NOT NULL AND blok != ''";
    $stmtPanen = $conn->query($queryPanen);
    $panenRecords = $stmtPanen->fetchAll(PDO::FETCH_ASSOC);
    
    $panenUpdated = 0;
    $panenSkipped = 0;
    
    $updatePanenStmt = $conn->prepare("UPDATE data_panen SET blok = :normalized WHERE id = :id");
    
    foreach ($panenRecords as $record) {
        $original = $record['blok'];
        $normalized = normalizeBlokNameScript($original);
        
        if ($original !== $normalized) {
            $updatePanenStmt->execute([
                ':normalized' => $normalized,
                ':id' => $record['id']
            ]);
            $panenUpdated++;
            echo "  - Updated ID {$record['id']}: '$original' -> '$normalized'\n";
        } else {
            $panenSkipped++;
        }
    }
    
    echo "  Total records processed: " . count($panenRecords) . "\n";
    echo "  Updated: $panenUpdated\n";
    echo "  Skipped (already normalized): $panenSkipped\n\n";
    
    // 2. Normalize data_pengiriman table
    echo "2. Processing data_pengiriman table...\n";
    $queryPengiriman = "SELECT id, blok FROM data_pengiriman WHERE blok IS NOT NULL AND blok != ''";
    $stmtPengiriman = $conn->query($queryPengiriman);
    $pengirimanRecords = $stmtPengiriman->fetchAll(PDO::FETCH_ASSOC);
    
    $pengirimanUpdated = 0;
    $pengirimanSkipped = 0;
    
    $updatePengirimanStmt = $conn->prepare("UPDATE data_pengiriman SET blok = :normalized WHERE id = :id");
    
    foreach ($pengirimanRecords as $record) {
        $original = $record['blok'];
        $normalized = normalizeBlokNameScript($original);
        
        if ($original !== $normalized) {
            $updatePengirimanStmt->execute([
                ':normalized' => $normalized,
                ':id' => $record['id']
            ]);
            $pengirimanUpdated++;
            echo "  - Updated ID {$record['id']}: '$original' -> '$normalized'\n";
        } else {
            $pengirimanSkipped++;
        }
    }
    
    echo "  Total records processed: " . count($pengirimanRecords) . "\n";
    echo "  Updated: $pengirimanUpdated\n";
    echo "  Skipped (already normalized): $pengirimanSkipped\n\n";
    
    // Summary
    echo "=== Summary ===\n";
    echo "Total records updated: " . ($panenUpdated + $pengirimanUpdated) . "\n";
    echo "Total records skipped: " . ($panenSkipped + $pengirimanSkipped) . "\n";
    echo "Total records processed: " . (count($panenRecords) + count($pengirimanRecords)) . "\n\n";
    
    $endTimestamp = date('Y-m-d H:i:s');
    echo "Completed at: $endTimestamp\n";
    echo "✅ Normalisasi selesai!\n\n";
    
    echo "Note: Dari sekarang, semua data baru yang diupload akan otomatis dinormalisasi.\n";
    echo "Contoh: 'B02' akan disimpan sebagai 'B2', 'B002' akan disimpan sebagai 'B2'\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>


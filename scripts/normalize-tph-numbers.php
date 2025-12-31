<?php
/**
 * Script to normalize existing TPH numbers in database
 * Converts "001", "01" to "1", etc.
 * 
 * Run this script once after deploying the new normalization logic
 * to ensure all existing data follows the same format
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Function to normalize TPH number (same as in functions.php)
function normalizeTphNumberScript($tph) {
    if (empty($tph)) {
        return $tph;
    }
    
    // Trim whitespace
    $tph = trim($tph);
    
    // If it's purely numeric (like "001", "01", "1"), remove leading zeros
    if (is_numeric($tph)) {
        return (string)((int)$tph);
    }
    
    // If it contains letters and numbers (like "TPH001", "TPH01"), normalize the number part
    // Pattern: looks for any prefix followed by numbers with leading zeros
    $normalized = preg_replace_callback(
        '/^([A-Za-z]*\s*)0+(\d+)$/',
        function($matches) {
            // $matches[1] = prefix (e.g., "TPH", "TPH ", "")
            // $matches[2] = number without leading zeros (e.g., "1")
            return $matches[1] . $matches[2];
        },
        $tph
    );
    
    return $normalized;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== Normalisasi Nomor TPH di Database ===\n\n";
    
    // Get current timestamp for backup reference
    $timestamp = date('Y-m-d H:i:s');
    echo "Started at: $timestamp\n\n";
    
    // 1. Normalize data_panen table
    echo "1. Processing data_panen table...\n";
    $queryPanen = "SELECT id, no_tph FROM data_panen WHERE no_tph IS NOT NULL AND no_tph != ''";
    $stmtPanen = $conn->query($queryPanen);
    $panenRecords = $stmtPanen->fetchAll(PDO::FETCH_ASSOC);
    
    $panenUpdated = 0;
    $panenSkipped = 0;
    
    $updatePanenStmt = $conn->prepare("UPDATE data_panen SET no_tph = :normalized WHERE id = :id");
    
    foreach ($panenRecords as $record) {
        $original = $record['no_tph'];
        $normalized = normalizeTphNumberScript($original);
        
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
    $queryPengiriman = "SELECT id, no_tph FROM data_pengiriman WHERE no_tph IS NOT NULL AND no_tph != ''";
    $stmtPengiriman = $conn->query($queryPengiriman);
    $pengirimanRecords = $stmtPengiriman->fetchAll(PDO::FETCH_ASSOC);
    
    $pengirimanUpdated = 0;
    $pengirimanSkipped = 0;
    
    $updatePengirimanStmt = $conn->prepare("UPDATE data_pengiriman SET no_tph = :normalized WHERE id = :id");
    
    foreach ($pengirimanRecords as $record) {
        $original = $record['no_tph'];
        $normalized = normalizeTphNumberScript($original);
        
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
    echo "✅ Normalisasi TPH selesai!\n\n";
    
    echo "Note: Dari sekarang, semua data baru yang diupload akan otomatis dinormalisasi.\n";
    echo "Contoh: TPH '001' akan disimpan sebagai '1', TPH '01' akan disimpan sebagai '1'\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>


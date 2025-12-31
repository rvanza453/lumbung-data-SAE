<?php
/**
 * Script to normalize existing Afdeling names in database
 * Converts:
 * - "01" -> "1"
 * - "I" -> "1", "II" -> "2"
 * - "AFD 01" -> "AFD 1"
 */

require_once '../config/database.php';

// Copy function purely for script usage
function normalizeAfdelingNameScript($afdeling) {
    if (empty($afdeling)) return $afdeling;
    $afdeling = trim($afdeling);
    
    // 1. Numeric Check
    if (is_numeric($afdeling)) return (string)((int)$afdeling);
    
    // 2. Roman to Arabic
    $romanMap = [
        'XX' => '20', 'XIX' => '19', 'XVIII' => '18', 'XVII' => '17', 'XVI' => '16',
        'XV' => '15', 'XIV' => '14', 'XIII' => '13', 'XII' => '12', 'XI' => '11',
        'X' => '10', 'IX' => '9', 'VIII' => '8', 'VII' => '7', 'VI' => '6',
        'V' => '5', 'IV' => '4', 'III' => '3', 'II' => '2', 'I' => '1'
    ];
    
    $upperAfdeling = strtoupper($afdeling);
    if (isset($romanMap[$upperAfdeling])) return $romanMap[$upperAfdeling];
    
    foreach ($romanMap as $roman => $arabic) {
        $pattern = '/\b' . $roman . '\b/i';
        if (preg_match($pattern, $afdeling)) {
            $afdeling = preg_replace($pattern, $arabic, $afdeling);
            break; 
        }
    }
    
    // 3. Leading Zeros
    $afdeling = preg_replace_callback('/\s+0+(\d+)/', function($m) { return ' ' . $m[1]; }, $afdeling);
    $afdeling = preg_replace_callback('/^([A-Za-z]+)0+(\d+)$/', function($m) { return $m[1] . $m[2]; }, $afdeling);
    
    return $afdeling;
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=== Normalisasi Nama Afdeling (Romawi & Angka) ===\n\n";
    echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
    
    // 1. Normalize data_panen
    echo "1. Processing data_panen table...\n";
    $stmt = $conn->query("SELECT id, afdeling FROM data_panen WHERE afdeling IS NOT NULL AND afdeling != ''");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0; $skipped = 0;
    $updateStmt = $conn->prepare("UPDATE data_panen SET afdeling = :norm WHERE id = :id");
    
    foreach ($records as $r) {
        $norm = normalizeAfdelingNameScript($r['afdeling']);
        if ($r['afdeling'] !== $norm) {
            $updateStmt->execute([':norm' => $norm, ':id' => $r['id']]);
            echo "  - ID {$r['id']}: '{$r['afdeling']}' -> '{$norm}'\n";
            $updated++;
        } else {
            $skipped++;
        }
    }
    echo "  Updated: $updated, Skipped: $skipped\n\n";
    
    // 2. Normalize data_pengiriman
    echo "2. Processing data_pengiriman table...\n";
    $stmt = $conn->query("SELECT id, afdeling FROM data_pengiriman WHERE afdeling IS NOT NULL AND afdeling != ''");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated2 = 0; $skipped2 = 0;
    $updateStmt = $conn->prepare("UPDATE data_pengiriman SET afdeling = :norm WHERE id = :id");
    
    foreach ($records as $r) {
        $norm = normalizeAfdelingNameScript($r['afdeling']);
        if ($r['afdeling'] !== $norm) {
            $updateStmt->execute([':norm' => $norm, ':id' => $r['id']]);
            echo "  - ID {$r['id']}: '{$r['afdeling']}' -> '{$norm}'\n";
            $updated2++;
        } else {
            $skipped2++;
        }
    }
    echo "  Updated: $updated2, Skipped: $skipped2\n\n";
    
    echo "✅ Selesai! Total Updated: " . ($updated + $updated2) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
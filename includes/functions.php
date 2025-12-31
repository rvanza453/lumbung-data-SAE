<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

// Function to redirect if not logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to create upload directory if not exists
// Returns array with both absolute and relative paths
function createUploadDirectory($category, $date = null) {
    if ($date === null) {
        $date = date('Y/m');
    }
    
    // Path relatif dari root project
    $relativePath = "uploads/" . $category . "/" . $date;
    
    // Absolute path untuk operasi file system
    $rootDir = dirname(__DIR__); // Mendapatkan root directory dari folder includes
    $absolutePath = $rootDir . "/" . $relativePath;
    
    // Buat directory jika belum ada
    if (!is_dir($absolutePath)) {
        mkdir($absolutePath, 0755, true);
    }
    
    // Return array dengan kedua path
    return [
        'absolute' => $absolutePath,
        'relative' => $relativePath
    ];
}

// Helper function to convert relative path to absolute path
function getAbsolutePath($relativePath) {
    // Jika sudah absolute path, return as is (check Windows drive letter or Unix root)
    if (preg_match('/^[a-zA-Z]:[\\\\\/]|^\//', $relativePath)) {
        return $relativePath;
    }
    
    // Convert relative path to absolute
    $rootDir = dirname(__DIR__);
    // Normalize path separator for cross-platform compatibility
    $relativePath = str_replace('\\', '/', $relativePath);
    return $rootDir . '/' . $relativePath;
}

// Function to get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Function to generate unique filename
function generateUniqueFilename($originalName) {
    $extension = getFileExtension($originalName);
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $timestamp = time();
    $random = substr(md5(uniqid(rand(), true)), 0, 8);
    
    return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
}

// Allowed file types
$allowedFileTypes = [
    'json'
];

// Maximum file size (50MB)
$maxFileSize = 50 * 1024 * 1024;

// Function to normalize Afdeling name - removes leading zeros
function normalizeAfdelingName($afdeling) {
    if (empty($afdeling)) {
        return $afdeling;
    }
    
    // Trim whitespace
    $afdeling = trim($afdeling);
    
    // 1. Direct Numeric Check (e.g., "01" -> "1")
    if (is_numeric($afdeling)) {
        return (string)((int)$afdeling);
    }
    
    // 2. Roman to Arabic Conversion (I-XX)
    // Ordered from longest to shortest to prevent partial replacement errors (e.g. VIII becoming V3)
    $romanMap = [
        'XX' => '20', 'XIX' => '19', 'XVIII' => '18', 'XVII' => '17', 'XVI' => '16',
        'XV' => '15', 'XIV' => '14', 'XIII' => '13', 'XII' => '12', 'XI' => '11',
        'X' => '10', 'IX' => '9', 'VIII' => '8', 'VII' => '7', 'VI' => '6',
        'V' => '5', 'IV' => '4', 'III' => '3', 'II' => '2', 'I' => '1'
    ];
    
    $upperAfdeling = strtoupper($afdeling);
    
    // Check exact match first (e.g., input is just "II")
    if (isset($romanMap[$upperAfdeling])) {
        return $romanMap[$upperAfdeling];
    }
    
    // Check partial match (e.g., "Afdeling II" -> "Afdeling 2")
    foreach ($romanMap as $roman => $arabic) {
        // Use word boundaries (\b) to ensure we don't replace parts of words
        $pattern = '/\b' . $roman . '\b/i';
        if (preg_match($pattern, $afdeling)) {
            $afdeling = preg_replace($pattern, $arabic, $afdeling);
            // Break after finding the number to avoid double replacement issues
            break; 
        }
    }
    
    // 3. Remove leading zeros from mixed strings (e.g., "AFD 01" -> "AFD 1")
    // Case A: Space + Leading Zero (e.g., "AFD 01")
    $afdeling = preg_replace_callback(
        '/\s+0+(\d+)/', 
        function($matches) { return ' ' . $matches[1]; },
        $afdeling
    );

    // Case B: Prefix attached to number (e.g., "AFD01")
    $afdeling = preg_replace_callback(
        '/^([A-Za-z]+)0+(\d+)$/',
        function($matches) { return $matches[1] . $matches[2]; },
        $afdeling
    );
    
    return $afdeling;
}

// Function to normalize block name - removes leading zeros from numbers
function normalizeBlokName($blok) {
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

// Function to normalize TPH number - removes leading zeros
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

// Function to log user activity
function logActivity($conn, $user_id, $action, $description, $target_type = null, $target_id = null) {
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $query = "INSERT INTO activity_logs (user_id, action, description, target_type, target_id, ip_address, user_agent) VALUES (:user_id, :action, :description, :target_type, :target_id, :ip_address, :user_agent)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':target_type', $target_type);
        $stmt->bindParam(':target_id', $target_id);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);
        $stmt->execute();
    } catch (PDOException $e) {
        // Log silently, don't break the main functionality
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Function to get user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// Function to format file size in human readable format
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

// Function to parse JSON and save data to appropriate tables
function parseAndSaveJsonData($filePath, $kategori, $uploadId, $conn) {
    try {
        // Tingkatkan memori PHP untuk proses file besar
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300); // 5 menit
        
        // Read JSON file
        $jsonContent = file_get_contents($filePath);
        if ($jsonContent === false) {
            return ['success' => false, 'error' => 'Gagal membaca file JSON', 'count' => 0];
        }
        
        // Parse JSON
        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Format JSON tidak valid: ' . json_last_error_msg(), 'count' => 0];
        }
        
        $savedCount = 0;
        
        // Deteksi tipe file berdasarkan struktur JSON (mirip import-to-monitoring)
        $detectedType = 'unknown';
        if ((isset($data['tipeAplikasi']) && $data['tipeAplikasi'] === "TRANSPORT_MONITORING") ||
            (isset($data['header']) && isset($data['header']['nopol']))) {
            $detectedType = 'pengiriman';
        } elseif (isset($data['items']) && is_array($data['items']) && !empty($data['items']) && 
                  isset($data['items'][0]['namaPemanen'])) {
            $detectedType = 'panen';
        }
        
        // Validasi kategori dengan deteksi otomatis
        if ($kategori === 'panen' && $detectedType === 'panen') {
            $savedCount = savePanenData($data, $uploadId, $conn);
        } elseif ($kategori === 'pengiriman' && $detectedType === 'pengiriman') {
            $savedCount = savePengirimanData($data, $uploadId, $conn);
        } elseif ($detectedType === 'unknown') {
            return ['success' => false, 'error' => 'Format JSON tidak dikenali atau tidak sesuai untuk monitoring', 'count' => 0];
        } else {
            return ['success' => false, 'error' => "Kategori yang dipilih ($kategori) tidak sesuai dengan format JSON ($detectedType)", 'count' => 0];
        }
        
        return ['success' => true, 'error' => '', 'count' => $savedCount];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage(), 'count' => 0];
    }
}

// Function to save panen data to database
function savePanenData($data, $uploadId, $conn) {
    $savedCount = 0;
    
    try {
        // Extract header information
        $namaKerani = $data['header']['namaKerani'] ?? '';
        $tanggalPemeriksaan = $data['header']['tanggalPemeriksaan'] ?? '';
        
        // Normalize Afdeling (Header Level)
        $afdelingRaw = $data['header']['afdeling'] ?? '';
        $afdeling = normalizeAfdelingName($afdelingRaw);
        
        // Convert tanggal format if needed (from 2025-12-01 to proper date)
        $tanggalPemeriksaan = date('Y-m-d', strtotime($tanggalPemeriksaan));
        
        // Prepare insert statement tanpa kolom foto
        $query = "INSERT INTO data_panen (
            upload_id, nama_kerani, tanggal_pemeriksaan, afdeling, nama_pemanen, nik_pemanen, 
            blok, no_ancak, no_tph, jam, last_modified, koordinat, jumlah_janjang, bjr, kg_total, kg_brd,
            matang, mengkal, mentah, lewat_matang, abnormal, serangan_hama,
            tangkai_panjang, janjang_kosong, original_id
        ) VALUES (
            :upload_id, :nama_kerani, :tanggal_pemeriksaan, :afdeling, :nama_pemanen, :nik_pemanen,
            :blok, :no_ancak, :no_tph, :jam, :last_modified, :koordinat, :jumlah_janjang, :bjr, :kg_total, :kg_brd,
            :matang, :mengkal, :mentah, :lewat_matang, :abnormal, :serangan_hama,
            :tangkai_panjang, :janjang_kosong, :original_id
        )";
        $stmt = $conn->prepare($query);
        
        // Process each item
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // Convert jam format if needed
                $jam = isset($item['jam']) ? date('H:i:s', strtotime($item['jam'])) : '00:00:00';
                
                // Handle bjr and kg_total fields (new fields from updated export)
                // Default to 0 instead of null to avoid failing on non-null DB columns
                $bjr = 0;
                if (isset($item['bjr']) && $item['bjr'] !== '') {
                    $bjr = floatval($item['bjr']);
                }
                
                $kg_total = 0;
                if (isset($item['kgTotal']) && $item['kgTotal'] !== '') {
                    $kg_total = floatval($item['kgTotal']);
                } elseif (isset($item['kg_total']) && $item['kg_total'] !== '') {
                    $kg_total = floatval($item['kg_total']);
                }
                
                $kg_brd = 0;
                if (isset($item['kgBerondolan']) && $item['kgBerondolan'] !== '') {
                    $kg_brd = floatval($item['kgBerondolan']);
                } elseif (isset($item['kg_berondolan']) && $item['kg_berondolan'] !== '') {
                    $kg_brd = floatval($item['kg_berondolan']);
                }
                
                // Extract grading data (matang, mengkal, mentah, dll)
                $matang = isset($item['matang']) && $item['matang'] !== '' ? intval($item['matang']) : 0;
                $mengkal = isset($item['mengkal']) && $item['mengkal'] !== '' ? intval($item['mengkal']) : 0;
                $mentah = isset($item['mentah']) && $item['mentah'] !== '' ? intval($item['mentah']) : 0;
                $lewat_matang = isset($item['lewatMatang']) && $item['lewatMatang'] !== '' ? intval($item['lewatMatang']) : 0;
                $abnormal = isset($item['abnormal']) && $item['abnormal'] !== '' ? intval($item['abnormal']) : 0;
                $serangan_hama = isset($item['seranganHama']) && $item['seranganHama'] !== '' ? intval($item['seranganHama']) : 0;
                $tangkai_panjang = isset($item['tangkaiPanjang']) && $item['tangkaiPanjang'] !== '' ? intval($item['tangkaiPanjang']) : 0;
                $janjang_kosong = isset($item['janjangKosong']) && $item['janjangKosong'] !== '' ? intval($item['janjangKosong']) : 0;
                
                $kg_berondolan = isset($item['kgBerondolan']) && $item['kgBerondolan'] !== '' ? floatval($item['kgBerondolan']) : 0;
                
                // Hitung jumlahJanjang dari total grading jika tidak tersedia di JSON
                $jumlah_janjang = 0;
                if (isset($item['jumlahJanjang']) && $item['jumlahJanjang'] !== '') {
                    $jumlah_janjang = intval($item['jumlahJanjang']);
                } else {
                    // Jika jumlahJanjang tidak ada, hitung dari total grading
                    $jumlah_janjang = $matang + $mengkal + $mentah + $lewat_matang + $abnormal + 
                                     $serangan_hama + $tangkai_panjang + $janjang_kosong;
                }
                
                // Normalize block name before saving
                $blokName = $item['blok'] ?? ($data['header']['blok'] ?? '');
                $normalizedBlok = normalizeBlokName($blokName);
                
                // Normalize TPH number before saving
                $tphNumber = $item['noTPH'] ?? '';
                $normalizedTph = normalizeTphNumber($tphNumber);
                
                $stmt->execute([
                    ':upload_id' => $uploadId,
                    ':nama_kerani' => $namaKerani,
                    ':tanggal_pemeriksaan' => $tanggalPemeriksaan,
                    ':afdeling' => $afdeling,
                    ':nama_pemanen' => $item['namaPemanen'] ?? '',
                    ':nik_pemanen' => $item['nikPemanen'] ?? '',
                    // fallback ke header blok bila item tidak punya blok (beberapa file grading baru)
                    ':blok' => $normalizedBlok,
                    ':no_ancak' => $item['noAncak'] ?? '',
                    ':no_tph' => $normalizedTph,
                    ':jam' => $jam,
                    ':last_modified' => $item['lastModified'] ?? '',
                    ':koordinat' => $item['koordinat'] ?? '',
                    ':jumlah_janjang' => $jumlah_janjang,
                    ':bjr' => $bjr,
                    ':kg_total' => $kg_total,
                    ':kg_brd' => $kg_brd,
                    // Data grading tanpa foto
                    ':matang' => $matang,
                    ':mengkal' => $mengkal,
                    ':mentah' => $mentah,
                    ':lewat_matang' => $lewat_matang,
                    ':abnormal' => $abnormal,
                    ':serangan_hama' => $serangan_hama,
                    ':tangkai_panjang' => $tangkai_panjang,
                    ':janjang_kosong' => $janjang_kosong,
                    ':original_id' => $item['id'] ?? null
                ]);
                $savedCount++;
            }
        }
        
    } catch (Exception $e) {
        throw new Exception("Error saving panen data: " . $e->getMessage());
    }
    
    return $savedCount;
}

// Function to save pengiriman data to database
function savePengirimanData($data, $uploadId, $conn) {
    $savedCount = 0;
    
    try {
        // Extract header information
        $tipeAplikasi = $data['tipeAplikasi'] ?? 'TRANSPORT_MONITORING';
        $namaKerani = $data['header']['namaKerani'] ?? '';
        $nikKerani = $data['header']['nikKerani'] ?? '';
        $tanggal = $data['header']['tanggal'] ?? '';
        $afdeling = $data['header']['afdeling'] ?? '';
        $nopol = $data['header']['nopol'] ?? '';
        $nomorKendaraan = $data['header']['nomorKendaraan'] ?? '';

        // Normalize Afdeling (Header Level)
        $afdelingRaw = $data['header']['afdeling'] ?? '';
        $afdeling = normalizeAfdelingName($afdelingRaw);
        
        // Convert tanggal format if needed
        $tanggal = date('Y-m-d', strtotime($tanggal));
        
        // Prepare insert statement
        $query = "INSERT INTO data_pengiriman (upload_id, tipe_aplikasi, nama_kerani, nik_kerani, tanggal, afdeling, nopol, nomor_kendaraan, blok, no_tph, jumlah_janjang, waktu, koordinat, bjr, kg_total, kg_brd, original_id) 
                 VALUES (:upload_id, :tipe_aplikasi, :nama_kerani, :nik_kerani, :tanggal, :afdeling, :nopol, :nomor_kendaraan, :blok, :no_tph, :jumlah_janjang, :waktu, :koordinat, :bjr, :kg_total, :kg_berondolan, :original_id)";
        $stmt = $conn->prepare($query);
        
        // Process each item
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // Convert waktu format if needed
                $waktu = isset($item['waktu']) ? date('H:i:s', strtotime($item['waktu'])) : '00:00:00';
                
                // Handle bjr
                $bjr = null;
                if (isset($item['bjr']) && $item['bjr'] !== '') {
                    $bjr = floatval($item['bjr']);
                }
                
                // Handle kg_berondolan
                $kg_berondolan = null;
                if (isset($item['kgBerondolan']) && $item['kgBerondolan'] !== '') {
                    $kg_berondolan = floatval($item['kgBerondolan']);
                } elseif (isset($item['kg_berondolan']) && $item['kg_berondolan'] !== '') {
                    $kg_berondolan = floatval($item['kg_berondolan']);
                }
                
                // Calculate kg_total from new format: (jumlahJanjang * bjr) + kgBerondolan
                $kg_total = null;
                if (isset($item['kgTotal']) && $item['kgTotal'] !== '') {
                    $kg_total = floatval($item['kgTotal']);
                } elseif (isset($item['kg_total']) && $item['kg_total'] !== '') {
                    $kg_total = floatval($item['kg_total']);
                } elseif ($bjr !== null) {
                    // Calculate if we have bjr
                    $jumlah_janjang = intval($item['jumlahJanjang'] ?? 0);
                    $kg_total = ($jumlah_janjang * $bjr) + ($kg_berondolan ?? 0);
                }
                
                // Normalize block name before saving
                $blokName = $item['blok'] ?? '';
                $normalizedBlok = normalizeBlokName($blokName);
                
                // Normalize TPH number before saving
                $tphNumber = $item['noTPH'] ?? '';
                $normalizedTph = normalizeTphNumber($tphNumber);
                
                $stmt->execute([
                    ':upload_id' => $uploadId,
                    ':tipe_aplikasi' => $tipeAplikasi,
                    ':nama_kerani' => $namaKerani,
                    ':nik_kerani' => $nikKerani,
                    ':tanggal' => $tanggal,
                    ':afdeling' => $afdeling,
                    ':nopol' => $nopol,
                    ':nomor_kendaraan' => $nomorKendaraan,
                    ':blok' => $normalizedBlok,
                    ':no_tph' => $normalizedTph,
                    ':jumlah_janjang' => intval($item['jumlahJanjang'] ?? 0),
                    ':waktu' => $waktu,
                    ':koordinat' => $item['koordinat'] ?? '',
                    ':bjr' => $bjr,
                    ':kg_total' => $kg_total,
                    ':kg_berondolan' => $kg_berondolan,
                    ':original_id' => $item['id'] ?? null
                ]);
                $savedCount++;
            }
        }
        
    } catch (Exception $e) {
        throw new Exception("Error saving pengiriman data: " . $e->getMessage());
    }
    
    return $savedCount;
}
?>
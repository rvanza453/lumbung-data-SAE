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
function createUploadDirectory($category, $date = null) {
    if ($date === null) {
        $date = date('Y/m');
    }
    
    $uploadPath = "uploads/" . $category . "/" . $date;
    
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    return $uploadPath;
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
        $afdeling = $data['header']['afdeling'] ?? '';
        
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
                
                $stmt->execute([
                    ':upload_id' => $uploadId,
                    ':nama_kerani' => $namaKerani,
                    ':tanggal_pemeriksaan' => $tanggalPemeriksaan,
                    ':afdeling' => $afdeling,
                    ':nama_pemanen' => $item['namaPemanen'] ?? '',
                    ':nik_pemanen' => $item['nikPemanen'] ?? '',
                    // fallback ke header blok bila item tidak punya blok (beberapa file grading baru)
                    ':blok' => $item['blok'] ?? ($data['header']['blok'] ?? ''),
                    ':no_ancak' => $item['noAncak'] ?? '',
                    ':no_tph' => $item['noTPH'] ?? '',
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
                
                $stmt->execute([
                    ':upload_id' => $uploadId,
                    ':tipe_aplikasi' => $tipeAplikasi,
                    ':nama_kerani' => $namaKerani,
                    ':nik_kerani' => $nikKerani,
                    ':tanggal' => $tanggal,
                    ':afdeling' => $afdeling,
                    ':nopol' => $nopol,
                    ':nomor_kendaraan' => $nomorKendaraan,
                    ':blok' => $item['blok'] ?? '',
                    ':no_tph' => $item['noTPH'] ?? '',
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
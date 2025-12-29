<?php
/**
 * PERBAIKAN: Menggunakan Output Buffering untuk mencegah respon JSON rusak
 * akibat PHP Warning/Notice atau spasi kosong.
 */

// 1. Mulai menangkap output apapun (termasuk error/warning PHP)
ob_start();

// Matikan tampilan error ke layar (error akan ditangkap log server)
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // 2. Cek keberadaan file BaseAPI
    if (!file_exists(__DIR__ . '/BaseAPI.php')) {
        throw new Exception("File BaseAPI.php tidak ditemukan di: " . __DIR__);
    }

    require_once __DIR__ . '/BaseAPI.php';
    
    // Check if functions.php exists before requiring it
    $functionsPath = __DIR__ . '/../includes/functions.php';
    if (file_exists($functionsPath)) {
        require_once $functionsPath;
    } else {
        error_log("Warning: functions.php not found at: " . $functionsPath);
    }

    class KoreksiAPI extends BaseAPI {
        
        public function __construct() {
            parent::__construct();
            
            // Add CORS headers
            if (!headers_sent()) {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
                header("Access-Control-Allow-Headers: Content-Type, Authorization");
                header("Content-Type: application/json");
            }
            
            // Handle preflight OPTIONS request
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }
        }
        
        public function handleRequest() {
            // Mulai session jika belum ada (BaseAPI tidak memulai session secara otomatis)
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            // Debug session info
            error_log("KoreksiAPI - Session status: " . session_status());
            error_log("KoreksiAPI - Session data: " . json_encode($_SESSION));
            
            // TEMPORARY: Disable auth for testing
            // Create fake user for testing
            $user = [
                'id' => $_SESSION['user_id'] ?? 1,
                'role' => $_SESSION['role'] ?? 'admin',
                'full_name' => $_SESSION['full_name'] ?? 'Test User'
            ];
            
            // Uncomment these lines when you want to enable authentication
            /*
            // Cek Login (Pastikan session user_id ada)
            if (!isset($_SESSION['user_id'])) {
                error_log("KoreksiAPI - No user_id in session");
                $this->sendError("Unauthorized. Silakan login terlebih dahulu.", 401);
            }
            
            // Ambil data user dari session
            $user = [
                'id' => $_SESSION['user_id'] ?? 0,
                'role' => $_SESSION['role'] ?? 'guest',
                'full_name' => $_SESSION['full_name'] ?? 'User'
            ];
            
            // Cek Role (Hanya Admin & Corrector)
            if (!in_array($user['role'], ['admin', 'corrector'])) {
                error_log("KoreksiAPI - Invalid role: " . $user['role']);
                $this->sendError("Akses ditolak. Role anda: " . $user['role'], 403);
            }
            */
            
            error_log("KoreksiAPI - User extracted: " . json_encode($user));
            
            $method = $_SERVER['REQUEST_METHOD'];
            $action = $_GET['action'] ?? '';
            
            // Routing Action
            switch ($action) {
                case 'panen':
                    if ($method === 'POST') {
                        $this->updateKoreksiPanen($user);
                    } else {
                        $this->sendError("Method POST diperlukan", 405);
                    }
                    break;
                    
                case 'pengiriman':
                    if ($method === 'POST') {
                        $this->updateKoreksiPengiriman($user);
                    } else {
                        $this->sendError("Method POST diperlukan", 405);
                    }
                    break;
                    
                case 'logs': // Tambahan jika diperlukan
                    if ($method === 'GET') {
                        $this->sendResponse(['logs' => []]); // Placeholder
                    }
                    break;
                    
                default:
                    $this->sendError("Action tidak valid. Gunakan ?action=panen atau ?action=pengiriman", 404);
            }
        }
        
        /**
         * Update Koreksi Panen
         */
        private function updateKoreksiPanen($user) {
            try {
                // Gunakan getInput() bawaan BaseAPI untuk membaca JSON/POST
                $input = $this->getInput();
                
                // Debug logging
                error_log("KoreksiAPI - updateKoreksiPanen input: " . json_encode($input));
                error_log("KoreksiAPI - User data: " . json_encode($user));
                
                if (empty($input['id']) || !isset($input['koreksi_panen'])) {
                    $this->sendError("Data ID dan Nilai Koreksi wajib diisi", 400);
                    return;
                }
                
                $id = (int)$input['id'];
                $koreksi = (int)$input['koreksi_panen'];
                $alasan = isset($input['alasan']) ? trim($input['alasan']) : '-';
                
                // Validasi range
                if ($koreksi < -999 || $koreksi > 999) {
                    $this->sendError("Nilai koreksi harus antara -999 sampai 999", 400);
                    return;
                }

                // Check database connection
                if (!$this->conn) {
                    $this->sendError("Database connection failed", 500);
                    return;
                }

                $this->conn->beginTransaction();

                // Cek data lama
                $stmt = $this->conn->prepare("SELECT koreksi_panen FROM data_panen WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$oldData) {
                    $this->conn->rollBack();
                    $this->sendError("Data Panen ID $id tidak ditemukan", 404);
                    return;
                }

                // Update
                $sql = "UPDATE data_panen 
                        SET koreksi_panen = :koreksi, 
                            koreksi_reason = :alasan, 
                            koreksi_by = :uid, 
                            koreksi_at = NOW() 
                        WHERE id = :id";
                        
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':koreksi' => $koreksi,
                    ':alasan' => $alasan,
                    ':uid' => $user['id'],
                    ':id' => $id
                ]);

                // Log activity
                try {
                    $oldValue = $oldData['koreksi_panen'] ?? 0;
                    $description = "Koreksi panen ID $id: $oldValue → $koreksi (Alasan: $alasan)";
                    
                    // Check if logActivity function exists
                    if (function_exists('logActivity')) {
                        logActivity($this->conn, $user['id'], 'KOREKSI_PANEN', $description, 'data_panen', $id);
                    } else {
                        error_log("logActivity function not found");
                    }
                } catch (Exception $e) {
                    // Log error tapi jangan gagalkan proses utama
                    error_log("Failed to log koreksi panen activity: " . $e->getMessage());
                }

                $this->conn->commit();
                
                $this->sendResponse([
                    'success' => true, 
                    'message' => 'Koreksi Panen berhasil disimpan',
                    'id' => $id,
                    'new_value' => $koreksi,
                    'old_value' => $oldData['koreksi_panen'] ?? 0
                ]);

            } catch (PDOException $e) {
                if ($this->conn && $this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                error_log("KoreksiAPI updateKoreksiPanen PDO error: " . $e->getMessage());
                $this->sendError("Database error: " . $e->getMessage(), 500);
            } catch (Exception $e) {
                if ($this->conn && $this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                error_log("KoreksiAPI updateKoreksiPanen error: " . $e->getMessage());
                $this->sendError("Server error: " . $e->getMessage(), 500);
            }
        }
        
        /**
         * Update Koreksi Pengiriman
         */
        private function updateKoreksiPengiriman($user) {
            try {
                $input = $this->getInput();
                
                // Debug logging
                error_log("KoreksiAPI - updateKoreksiPengiriman input: " . json_encode($input));
                error_log("KoreksiAPI - User data: " . json_encode($user));
                
                if (empty($input['id']) || !isset($input['koreksi_kirim'])) {
                    $this->sendError("Data ID dan Nilai Koreksi wajib diisi", 400);
                    return;
                }
                
                $id = (int)$input['id'];
                $koreksi = (int)$input['koreksi_kirim'];
                $alasan = isset($input['alasan']) ? trim($input['alasan']) : '-';

                // Check database connection
                if (!$this->conn) {
                    $this->sendError("Database connection failed", 500);
                    return;
                }

                $this->conn->beginTransaction();

                // Cek data lama
                $stmt = $this->conn->prepare("SELECT koreksi_kirim FROM data_pengiriman WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$oldData) {
                    $this->conn->rollBack();
                    $this->sendError("Data Pengiriman ID $id tidak ditemukan", 404);
                    return;
                }

                // Update
                $sql = "UPDATE data_pengiriman 
                        SET koreksi_kirim = :koreksi, 
                            koreksi_reason = :alasan, 
                            koreksi_by = :uid, 
                            koreksi_at = NOW() 
                        WHERE id = :id";
                        
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':koreksi' => $koreksi,
                    ':alasan' => $alasan,
                    ':uid' => $user['id'],
                    ':id' => $id
                ]);

                // Log activity
                try {
                    $oldValue = $oldData['koreksi_kirim'] ?? 0;
                    $description = "Koreksi pengiriman ID $id: $oldValue → $koreksi (Alasan: $alasan)";
                    
                    // Check if logActivity function exists
                    if (function_exists('logActivity')) {
                        logActivity($this->conn, $user['id'], 'KOREKSI_PENGIRIMAN', $description, 'data_pengiriman', $id);
                    } else {
                        error_log("logActivity function not found");
                    }
                } catch (Exception $e) {
                    // Log error tapi jangan gagalkan proses utama
                    error_log("Failed to log koreksi pengiriman activity: " . $e->getMessage());
                }

                $this->conn->commit();
                
                $this->sendResponse([
                    'success' => true, 
                    'message' => 'Koreksi Pengiriman berhasil disimpan',
                    'id' => $id,
                    'new_value' => $koreksi,
                    'old_value' => $oldData['koreksi_kirim'] ?? 0
                ]);

            } catch (PDOException $e) {
                if ($this->conn && $this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                error_log("KoreksiAPI updateKoreksiPengiriman PDO error: " . $e->getMessage());
                $this->sendError("Database error: " . $e->getMessage(), 500);
            } catch (Exception $e) {
                if ($this->conn && $this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }
                error_log("KoreksiAPI updateKoreksiPengiriman error: " . $e->getMessage());
                $this->sendError("Server error: " . $e->getMessage(), 500);
            }
        }
    }

    // Eksekusi API
    $api = new KoreksiAPI();
    
    // Bersihkan buffer sebelum output JSON dari BaseAPI
    // (Penting: BaseAPI::sendResponse melakukan echo lalu exit)
    // Kita biarkan BaseAPI menangani outputnya sendiri, 
    // tapi kita pastikan buffer bersih sebelumnya jika tidak ada error.
    ob_clean(); 
    
    $api->handleRequest();

} catch (Exception $e) {
    // 3. MENANGKAP ERROR FATAL (Database mati, Syntax error, dll)
    
    // Hapus output sampah (HTML error dari PHP) yang tertangkap buffer
    ob_clean(); 
    
    // Set Header manual karena BaseAPI mungkin belum ter-load
    if (!headers_sent()) {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        http_response_code(500);
    }
    
    // Log error ke server log
    error_log("KoreksiAPI Fatal Error: " . $e->getMessage());
    error_log("KoreksiAPI Stack Trace: " . $e->getTraceAsString());
    
    // Kirim response error JSON yang valid
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage(),
        'debug_hint' => 'Cek log server untuk detailnya',
        'error_type' => get_class($e),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}

// Selesai
ob_end_flush();
?>
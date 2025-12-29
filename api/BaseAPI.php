<?php

require_once __DIR__ . '/../config/database.php';

class BaseAPI {
    protected $db;
    protected $conn;
    
    public function __construct() {
        $this->initHeaders();
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Initialize HTTP headers
     */
    protected function initHeaders() {
        if (php_sapi_name() !== 'cli' && !headers_sent()) {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Authorization, X-Auth-Token");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Content-Type: application/json; charset=UTF-8");
            
            // Handle preflight OPTIONS request
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                http_response_code(200);
                exit();
            }
        }
    }
    
    /**
     * Send JSON response
     */
    protected function sendResponse($data = [], $message = "Success", $status_code = 200) {
        http_response_code($status_code);
        
        $response = [
            'success' => $status_code >= 200 && $status_code < 300,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    /**
     * Send error response
     */
    protected function sendError($message = "Error occurred", $status_code = 400, $details = null) {
        http_response_code($status_code);
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($details) {
            $response['details'] = $details;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    /**
     * Get request input (JSON or POST data)
     */
    protected function getInput() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $_POST;
        }
        return $input ? $input : $_POST;
    }
    
    /**
     * Verify JWT token - FIXED VERSION
     */
    protected function verifyToken() {
        $token = null;
        
        // Debug: Log all available headers
        error_log("=== Token Verification Debug ===");
        error_log("HTTP_AUTHORIZATION: " . ($_SERVER['HTTP_AUTHORIZATION'] ?? 'not set'));
        error_log("REDIRECT_HTTP_AUTHORIZATION: " . ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'not set'));
        
        // Method 1: Check direct Authorization header from $_SERVER
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
            error_log("Found HTTP_AUTHORIZATION: " . $auth_header);
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            error_log("Found REDIRECT_HTTP_AUTHORIZATION: " . $auth_header);
        } else {
            $auth_header = null;
        }
        
        // Method 2: Check alternative headers
        if (!$auth_header) {
            if (isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
                $token = $_SERVER['HTTP_X_AUTHORIZATION'];
                error_log("Found HTTP_X_AUTHORIZATION: " . $token);
            } elseif (isset($_SERVER['HTTP_X_AUTH_TOKEN'])) {
                $token = $_SERVER['HTTP_X_AUTH_TOKEN'];
                error_log("Found HTTP_X_AUTH_TOKEN: " . $token);
            }
        }
        
        // Method 3: Check query parameter
        if (!$token && !$auth_header) {
            if (isset($_GET['token'])) {
                $token = $_GET['token'];
                error_log("Found token in query: " . substr($token, 0, 20) . "...");
            }
        }
        
        // Extract Bearer token from Authorization header
        if ($auth_header && !$token) {
            if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                $token = $matches[1];
                error_log("Extracted Bearer token: " . substr($token, 0, 20) . "...");
            }
        }
        
        if (!$token) {
            error_log("No token found in any method");
            $this->sendError("Token not provided", 401);
        }
        
        try {
            error_log("Attempting to decode token: " . substr($token, 0, 20) . "...");
            $decoded = $this->decodeJWT($token);
            error_log("Token decoded successfully");
            return $decoded;
        } catch (Exception $e) {
            error_log("Token decode failed: " . $e->getMessage());
            $this->sendError("Invalid token: " . $e->getMessage(), 401);
        }
    }
    
    /**
     * Simple JWT encode (for demo purposes - use proper JWT library in production)
     */
    protected function createJWT($payload) {
        $secret = "your-secret-key-here-change-this-in-production";
        
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $headerEncoded = $this->base64UrlEncode($header);
        $payloadEncoded = $this->base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }
    
    /**
     * Simple JWT decode (for demo purposes - use proper JWT library in production)
     */
    protected function decodeJWT($token) {
        $secret = "your-secret-key-here-change-this-in-production";
        
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception("Invalid token format");
        }
        
        $header = $this->base64UrlDecode($parts[0]);
        $payload = $this->base64UrlDecode($parts[1]);
        $signature = $this->base64UrlDecode($parts[2]);
        
        $expected_signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $secret, true);
        
        if (!hash_equals($signature, $expected_signature)) {
            throw new Exception("Invalid signature");
        }
        
        $payload_data = json_decode($payload, true);
        
        if ($payload_data['exp'] < time()) {
            throw new Exception("Token expired");
        }
        
        return $payload_data;
    }
    
    /**
     * Base64 URL encode
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode
     */
    private function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Get pagination parameters
     */
    protected function getPaginationParams() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        
        $page = max(1, $page);
        $limit = min(max(1, $limit), 100); // Max 100 items per page
        
        $offset = ($page - 1) * $limit;
        
        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
    
    /**
     * Create pagination response
     */
    protected function createPaginationResponse($data, $total, $page, $limit) {
        $total_pages = ceil($total / $limit);
        
        return [
            'items' => $data,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total,
                'items_per_page' => $limit,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1
            ]
        ];
    }
    
    /**
     * Sanitize string for SQL LIKE
     */
    protected function sanitizeLike($string) {
        return str_replace(['%', '_'], ['\%', '\_'], $string);
    }
    
    /**
     * Validate date format
     */
    protected function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Get user by ID
     */
    protected function getUserById($user_id) {
        $query = "SELECT id, username, full_name, nik, phone, role FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all HTTP headers (compatible with different server configurations) - REMOVED
     * This method was causing issues, so we handle headers directly in verifyToken()
     */
}
?>
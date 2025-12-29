<?php
require_once 'BaseAPI.php';

class AuthAPI extends BaseAPI {
    
    /**
     * Handle authentication requests
     */
    /**
     * Handle authentication requests
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // 1. Coba ambil PATH_INFO standar
        $path = $_SERVER['PATH_INFO'] ?? '';
        
        // 2. Jika kosong, coba teknik parsing manual yang lebih robust
        if (empty($path)) {
            $scriptName = $_SERVER['SCRIPT_NAME']; // contoh: /folder/auth.php
            $requestUri = $_SERVER['REQUEST_URI']; // contoh: /folder/auth.php/verify?id=1
            
            // Buang query string jika ada
            if ($pos = strpos($requestUri, '?')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            
            // Jika Request URI mengandung Script Name, ambil sisanya sebagai path
            if (strpos($requestUri, $scriptName) === 0) {
                $path = substr($requestUri, strlen($scriptName));
            } 
            // Fallback lama: cek hardcoded /api/auth (opsional, untuk backward compatibility)
            elseif (strpos($requestUri, '/api/auth') !== false) {
                $path_parts = explode('/api/auth', $requestUri);
                if (isset($path_parts[1])) {
                    $path = $path_parts[1];
                }
            }
        }
        
        // 3. Normalisasi path (pastikan diawali /)
        if (!empty($path) && strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }

        // 4. Default Routing jika path masih kosong
        if (empty($path) || $path === '/') {
            if ($method === 'POST') {
                $path = '/login';
            } elseif ($method === 'GET') {
                // TAMBAHAN PENTING: Jika GET ke root auth.php, anggap sebagai verify
                $path = '/verify'; 
            }
        }
        
        // Debugging (Opsional: lihat di error log server jika masih error)
        // error_log("Method: $method, Detected Path: $path");

        switch ($path) {
            case '/login':
                if ($method === 'POST') {
                    $this->login();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/verify':
                if ($method === 'GET') {
                    $this->verifyCurrentToken();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            case '/refresh':
                if ($method === 'POST') {
                    $this->refreshToken();
                } else {
                    $this->sendError("Method not allowed", 405);
                }
                break;
                
            default:
                // Sertakan path yang dideteksi dalam pesan error untuk memudahkan debugging
                $this->sendError("Endpoint not found: $path", 404);
        }
    }
    
    /**
     * Login user and return JWT token
     */
    private function login() {
        $input = $this->getInput();
        
        if (!isset($input['username']) || !isset($input['password'])) {
            $this->sendError("Username and password are required", 400);
        }
        
        $username = trim($input['username']);
        $password = $input['password'];
        
        if (empty($username) || empty($password)) {
            $this->sendError("Username and password cannot be empty", 400);
        }
        
        try {
            // Check user credentials
            $query = "SELECT id, username, password, full_name, nik, phone, role 
                     FROM users 
                     WHERE username = :username";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['password'])) {
                $this->sendError("Invalid username or password", 401);
            }
            
            // Create JWT token
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60) // 24 hours
            ];
            
            $token = $this->createJWT($payload);
            
            // Log login activity
            $this->logActivity($user['id'], 'login', 'User logged in via API');
            
            // Return user data and token
            $response_data = [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'nik' => $user['nik'],
                    'phone' => $user['phone'],
                    'role' => $user['role']
                ],
                'expires_at' => date('Y-m-d H:i:s', $payload['exp'])
            ];
            
            $this->sendResponse($response_data, "Login successful");
            
        } catch (Exception $e) {
            $this->sendError("Login failed: " . $e->getMessage(), 500);
        }
    }
    
    /**
     * Verify current token
     */
    private function verifyCurrentToken() {
        try {
            $decoded = $this->verifyToken();
            
            // Get fresh user data
            $user = $this->getUserById($decoded['user_id']);
            
            if (!$user) {
                $this->sendError("User not found", 404);
            }
            
            $response_data = [
                'valid' => true,
                'user' => $user,
                'expires_at' => date('Y-m-d H:i:s', $decoded['exp'])
            ];
            
            $this->sendResponse($response_data, "Token is valid");
            
        } catch (Exception $e) {
            $this->sendError("Token verification failed", 401);
        }
    }
    
    /**
     * Refresh token
     */
    private function refreshToken() {
        try {
            $decoded = $this->verifyToken();
            
            // Get fresh user data
            $user = $this->getUserById($decoded['user_id']);
            
            if (!$user) {
                $this->sendError("User not found", 404);
            }
            
            // Create new token
            $payload = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60) // 24 hours
            ];
            
            $token = $this->createJWT($payload);
            
            $response_data = [
                'token' => $token,
                'user' => $user,
                'expires_at' => date('Y-m-d H:i:s', $payload['exp'])
            ];
            
            $this->sendResponse($response_data, "Token refreshed successfully");
            
        } catch (Exception $e) {
            $this->sendError("Token refresh failed", 401);
        }
    }
    
    /**
     * Log user activity
     */
    private function logActivity($user_id, $action, $description) {
        try {
            $query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (:user_id, :action, :description, :ip_address, :user_agent)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':action', $action);
            $stmt->bindParam(':description', $description);
            
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            
            $stmt->execute();
        } catch (Exception $e) {
            // Log error but don't stop execution
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}

// Initialize and handle request only if file accessed directly
if (basename($_SERVER['SCRIPT_NAME'] ?? __FILE__) === 'auth.php') {
    $api = new AuthAPI();
    $api->handleRequest();
}
?>
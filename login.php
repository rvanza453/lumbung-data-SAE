<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lubung Data SAE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="login-form-container">
                    <div class="text-center mb-4">
                        <i class="fas fa-database fa-3x text-primary mb-3"></i>
                        <h2 class="fw-bold">Lubung Data SAE</h2>
                        <p class="text-muted">Sistem Manajemen Data Perkebunan</p>
                    </div>

                    <?php
                    include_once 'includes/functions.php';
                    
                    $error = '';
                    
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $username = sanitizeInput($_POST['username']);
                        $password = $_POST['password'];
                        
                        if (!empty($username) && !empty($password)) {
                            $database = new Database();
                            $conn = $database->getConnection();
                            
                            if (!$conn) {
                                $error = 'Koneksi database gagal! Periksa konfigurasi database.';
                            } else {
                                try {
                                    $query = "SELECT id, username, password, full_name, role FROM users WHERE username = :username";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bindParam(':username', $username);
                                    $stmt->execute();
                                    
                                    if ($stmt->rowCount() > 0) {
                                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                                        
                                        if (password_verify($password, $user['password'])) {
                                            $_SESSION['user_id'] = $user['id'];
                                            $_SESSION['username'] = $user['username'];
                                            $_SESSION['full_name'] = $user['full_name'];
                                            $_SESSION['role'] = $user['role'];
                                            
                                            // Get API token for monitoring system
                                            try {
                                                $api_url = 'http://192.168.1.219/lubung-data-SAE/api/auth.php';
                                                $login_data = json_encode([
                                                    'username' => $username,
                                                    'password' => $password
                                                ]);
                                                
                                                $context = stream_context_create([
                                                    'http' => [
                                                        'method' => 'POST',
                                                        'header' => 'Content-Type: application/json',
                                                        'content' => $login_data,
                                                        'timeout' => 10
                                                    ]
                                                ]);
                                                
                                                $api_response = file_get_contents($api_url, false, $context);
                                                if ($api_response) {
                                                    $api_result = json_decode($api_response, true);
                                                    if ($api_result && $api_result['success']) {
                                                        $_SESSION['auth_token'] = $api_result['data']['token'];
                                                    }
                                                }
                                            } catch (Exception $e) {
                                                // API token generation failed, but don't break login
                                                error_log('Failed to get API token: ' . $e->getMessage());
                                            }
                                            
                                            // Log successful login
                                            try {
                                                logActivity($conn, $user['id'], 'LOGIN', 'User logged in successfully');
                                            } catch (Exception $e) {
                                                // Handle silently - don't break login process
                                            }
                                            
                                            header('Location: dashboard.php');
                                            exit();
                                        } else {
                                            // Log failed login attempt
                                            try {
                                                logActivity($conn, $user['id'], 'LOGIN', 'Failed login attempt - incorrect password');
                                            } catch (Exception $e) {
                                                // Handle silently
                                            }
                                            
                                            $error = 'Password salah! Jika masalah berlanjut, jalankan reset-password.php';
                                        }
                                    } else {
                                        // Log failed login attempt - user not found
                                        try {
                                            logActivity($conn, 0, 'LOGIN', 'Failed login attempt - username not found: ' . $username);
                                        } catch (Exception $e) {
                                            // Handle silently
                                        }
                                        
                                        $error = 'Username tidak ditemukan! Pastikan database sudah diimport.';
                                    }
                                } catch (PDOException $e) {
                                    $error = 'Error database: ' . $e->getMessage();
                                }
                            }
                        } else {
                            $error = 'Mohon isi username dan password!';
                        }
                    }
                    ?>

                    <form method="POST" action="">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-2"></i>Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Default login: <strong>admin</strong> / <strong>admin123</strong>
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 login-bg d-none d-md-flex align-items-center justify-content-center">
                <div class="text-center text-white">
                    <i class="fas fa-cloud-upload-alt fa-5x mb-4"></i>
                    <h3>Upload & Kelola Data</h3>
                    <p class="lead">Sistem manajemen data perkebunan yang mudah dan efisien</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
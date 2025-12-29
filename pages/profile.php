<?php
include_once '../includes/functions.php';
requireLogin();

$success = '';
$error = '';

$database = new Database();
$conn = $database->getConnection();

// Get current user profile
try {
    $query = "SELECT * FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Gagal mengambil data profil: ' . $e->getMessage();
    $currentUser = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $nik = sanitizeInput($_POST['nik']);
    $phone = sanitizeInput($_POST['phone']);
    $default_kategori = sanitizeInput($_POST['default_kategori']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate required fields
    if (empty($full_name)) {
        $error = 'Nama lengkap wajib diisi!';
    } else {
        try {
            // Check current password if trying to change password
            $updatePassword = false;
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Password saat ini harus diisi untuk mengubah password!';
                } else if ($new_password !== $confirm_password) {
                    $error = 'Konfirmasi password tidak cocok!';
                } else if (strlen($new_password) < 6) {
                    $error = 'Password baru minimal 6 karakter!';
                } else {
                    // Verify current password
                    if (password_verify($current_password, $currentUser['password'])) {
                        $updatePassword = true;
                    } else {
                        $error = 'Password saat ini salah!';
                    }
                }
            }
            
            if (empty($error)) {
                if ($updatePassword) {
                    // Update with new password
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET full_name = :full_name, nik = :nik, phone = :phone, default_kategori = :default_kategori, password = :password WHERE id = :user_id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':password', $hashedPassword);
                } else {
                    // Update without changing password
                    $query = "UPDATE users SET full_name = :full_name, nik = :nik, phone = :phone, default_kategori = :default_kategori WHERE id = :user_id";
                    $stmt = $conn->prepare($query);
                }
                
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':nik', $nik);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':default_kategori', $default_kategori);
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    // Update session data
                    $_SESSION['full_name'] = $full_name;
                    
                    // Log profile update activity
                    try {
                        $details = "Updated profile: $full_name";
                        if ($updatePassword) {
                            $details .= " [password changed]";
                        }
                        logActivity($conn, $_SESSION['user_id'], 'UPDATE_PROFILE', $details);
                    } catch (Exception $e) {
                        // Handle silently
                    }
                    
                    $success = 'Profil berhasil diperbarui!';
                    
                    // Refresh user data
                    $currentUser['full_name'] = $full_name;
                    $currentUser['nik'] = $nik;
                    $currentUser['phone'] = $phone;
                    $currentUser['default_kategori'] = $default_kategori;
                } else {
                    $error = 'Gagal memperbarui profil!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Error database: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Lubung Data SAE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-database me-2"></i>
                Lubung Data SAE
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload.php">
                            <i class="fas fa-upload me-1"></i>Upload Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/file-manager.php">
                            <i class="fas fa-folder-open me-1"></i>File Manager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="monitoring.php">
                            <i class="fas fa-chart-line me-1"></i>Monitoring
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/user-management.php">
                            <i class="fas fa-users me-1"></i>Kelola User
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/activity-logs.php">
                            <i class="fas fa-history me-1"></i>Activity Logs
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">
                                <i class="fas fa-user-edit me-1"></i>Profil Saya
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user-edit me-2"></i>Profil Saya</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="profileForm">
                            <div class="row">
                                <!-- Profile Information -->
                                <div class="col-md-12 mb-4">
                                    <h5 class="text-primary"><i class="fas fa-info-circle me-2"></i>Informasi Profil</h5>
                                    <hr>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user me-2"></i>Username
                                    </label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" readonly>
                                    <div class="form-text">Username tidak dapat diubah</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">
                                        <i class="fas fa-shield-alt me-2"></i>Role
                                    </label>
                                    <input type="text" class="form-control" id="role" value="<?php echo ($currentUser['role'] ?? '') == 'admin' ? 'Administrator' : 'User'; ?>" readonly>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">
                                        <i class="fas fa-id-badge me-2"></i>Nama Lengkap *
                                    </label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nik" class="form-label">
                                        <i class="fas fa-id-card me-2"></i>Nomor Induk Kerja
                                    </label>
                                    <input type="text" class="form-control" id="nik" name="nik" 
                                           value="<?php echo htmlspecialchars($currentUser['nik'] ?? ''); ?>" 
                                           placeholder="Masukkan NIK Anda">
                                    <div class="form-text">NIK ini akan otomatis terisi saat upload file</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-2"></i>No. Telepon
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>" 
                                           placeholder="Contoh: 08123456789">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="default_kategori" class="form-label">
                                        <i class="fas fa-tag me-2"></i>Kategori Default *
                                    </label>
                                    <select class="form-select" id="default_kategori" name="default_kategori" required>
                                        <option value="">Pilih kategori default</option>
                                        <option value="panen" <?php echo (($currentUser['default_kategori'] ?? '') == 'panen') ? 'selected' : ''; ?>>
                                            Panen
                                        </option>
                                        <option value="pengiriman" <?php echo (($currentUser['default_kategori'] ?? '') == 'pengiriman') ? 'selected' : ''; ?>>
                                            Pengiriman
                                        </option>
                                    </select>
                                    <div class="form-text">Kategori ini akan otomatis terisi saat upload file</div>
                                </div>
                            </div>

                                <!-- Password Change Section -->
                                <div class="col-md-12 mb-4">
                                    <h5 class="text-primary"><i class="fas fa-lock me-2"></i>Ubah Password</h5>
                                    <div class="form-text mb-3">Kosongkan jika tidak ingin mengubah password</div>
                                    <hr>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="current_password" class="form-label">Password Saat Ini</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <div class="form-text">Minimal 6 karakter</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-2 mb-4">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary me-4 mb-4">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Profile Benefits Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Manfaat Melengkapi Profil</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-rocket text-success me-2"></i>Kemudahan Upload:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Nama otomatis terisi saat upload</li>
                                    <li><i class="fas fa-check text-success me-2"></i>NIK otomatis terisi saat upload</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Kategori default terisi otomatis</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Proses upload lebih cepat</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Data konsisten dan akurat</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-shield-alt text-info me-2"></i>Keamanan & Identitas:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-info me-2"></i>Data tersimpan aman di sistem</li>
                                    <li><i class="fas fa-check text-info me-2"></i>Konsistensi data upload</li>
                                    <li><i class="fas fa-check text-info me-2"></i>Mudah diidentifikasi admin</li>
                                    <li><i class="fas fa-check text-info me-2"></i>Tracking aktivitas user</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return false;
            }
            
            if (newPassword && !currentPassword) {
                e.preventDefault();
                alert('Password saat ini harus diisi untuk mengubah password!');
                return false;
            }
            
            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
                return false;
            }
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 13) {
                value = value.substring(0, 13);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
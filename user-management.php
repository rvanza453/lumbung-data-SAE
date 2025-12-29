<?php
include_once 'includes/functions.php';
requireLogin();

// Only admin can access this page
if ($_SESSION['role'] != 'admin') {
    header('Location: dashboard.php');
    exit();
}

$success = '';
$error = '';

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = sanitizeInput($_POST['username']);
                $password = $_POST['password'];
                $full_name = sanitizeInput($_POST['full_name']);
                $role = sanitizeInput($_POST['role']);
                
                if (!empty($username) && !empty($password) && !empty($full_name) && !empty($role)) {
                    // Validate role
                    $allowedRoles = ['user', 'corrector', 'admin'];
                    if (!in_array($role, $allowedRoles)) {
                        $error = 'Role tidak valid!';
                        break;
                    }
                    
                    try {
                        // Check if username already exists
                        $checkQuery = "SELECT id FROM users WHERE username = :username";
                        $checkStmt = $conn->prepare($checkQuery);
                        $checkStmt->bindParam(':username', $username);
                        $checkStmt->execute();
                        
                        if ($checkStmt->rowCount() > 0) {
                            $error = 'Username sudah digunakan!';
                        } else {
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            
                            $query = "INSERT INTO users (username, password, full_name, role) VALUES (:username, :password, :full_name, :role)";
                            $stmt = $conn->prepare($query);
                            $stmt->bindParam(':username', $username);
                            $stmt->bindParam(':password', $hashedPassword);
                            $stmt->bindParam(':full_name', $full_name);
                            $stmt->bindParam(':role', $role);
                            
                            if ($stmt->execute()) {
                                // Log user creation activity
                                try {
                                    $newUserId = $conn->lastInsertId();
                                    logActivity($conn, $_SESSION['user_id'], 'CREATE_USER', 
                                              "Created new user: $username (role: $role)", 'user', $newUserId);
                                } catch (Exception $e) {
                                    // Handle silently
                                }
                                
                                $success = 'User berhasil ditambahkan! User dapat melengkapi profil setelah login.';
                                $_POST = array(); // Clear form
                            } else {
                                $error = 'Gagal menambahkan user!';
                            }
                        }
                    } catch (PDOException $e) {
                        $error = 'Error database: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Username, password, nama lengkap, dan role wajib diisi!';
                }
                break;
                
            case 'edit':
                $user_id = (int)$_POST['user_id'];
                $username = sanitizeInput($_POST['username']);
                $full_name = sanitizeInput($_POST['full_name']);
                $role = sanitizeInput($_POST['role']);
                $new_password = $_POST['new_password'];
                
                if (!empty($username) && !empty($full_name) && !empty($role)) {
                    // Validate role
                    $allowedRoles = ['user', 'corrector', 'admin'];
                    if (!in_array($role, $allowedRoles)) {
                        $error = 'Role tidak valid!';
                        break;
                    }
                    
                    try {
                        // Check if username already exists (except for current user)
                        $checkQuery = "SELECT id FROM users WHERE username = :username AND id != :user_id";
                        $checkStmt = $conn->prepare($checkQuery);
                        $checkStmt->bindParam(':username', $username);
                        $checkStmt->bindParam(':user_id', $user_id);
                        $checkStmt->execute();
                        
                        if ($checkStmt->rowCount() > 0) {
                            $error = 'Username sudah digunakan oleh user lain!';
                        } else {
                            if (!empty($new_password)) {
                                // Update with new password
                                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                                $query = "UPDATE users SET username = :username, password = :password, full_name = :full_name, role = :role WHERE id = :user_id";
                                $stmt = $conn->prepare($query);
                                $stmt->bindParam(':password', $hashedPassword);
                            } else {
                                // Update without changing password
                                $query = "UPDATE users SET username = :username, full_name = :full_name, role = :role WHERE id = :user_id";
                                $stmt = $conn->prepare($query);
                            }
                            
                            $stmt->bindParam(':username', $username);
                            $stmt->bindParam(':full_name', $full_name);
                            $stmt->bindParam(':role', $role);
                            $stmt->bindParam(':user_id', $user_id);
                            
                            if ($stmt->execute()) {
                                // Log user update activity
                                try {
                                    $details = "Updated user: $username (role: $role)";
                                    if (!empty($new_password)) {
                                        $details .= " [password changed]";
                                    }
                                    logActivity($conn, $_SESSION['user_id'], 'UPDATE_USER', $details, 'user', $user_id);
                                } catch (Exception $e) {
                                    // Handle silently
                                }
                                
                                $success = 'User berhasil diupdate!';
                            } else {
                                $error = 'Gagal mengupdate user!';
                            }
                        }
                    } catch (PDOException $e) {
                        $error = 'Error database: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Username, nama lengkap, dan role wajib diisi!';
                }
                break;
                
            case 'delete':
                $user_id = (int)$_POST['user_id'];
                
                // Prevent deletion of current user
                if ($user_id == $_SESSION['user_id']) {
                    $error = 'Tidak dapat menghapus akun yang sedang digunakan!';
                } else {
                    try {
                        // Get user info before deletion for logging
                        $getUserQuery = "SELECT username, full_name FROM users WHERE id = :user_id";
                        $getUserStmt = $conn->prepare($getUserQuery);
                        $getUserStmt->bindParam(':user_id', $user_id);
                        $getUserStmt->execute();
                        $deletedUser = $getUserStmt->fetch(PDO::FETCH_ASSOC);
                        
                        $query = "DELETE FROM users WHERE id = :user_id";
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':user_id', $user_id);
                        
                        if ($stmt->execute()) {
                            // Log user deletion activity
                            try {
                                $username = $deletedUser ? $deletedUser['username'] : "ID:$user_id";
                                $full_name = $deletedUser ? $deletedUser['full_name'] : 'unknown';
                                logActivity($conn, $_SESSION['user_id'], 'DELETE_USER', 
                                          "Deleted user: $username ($full_name)", 'user', $user_id);
                            } catch (Exception $e) {
                                // Handle silently
                            }
                            
                            $success = 'User berhasil dihapus!';
                        } else {
                            $error = 'Gagal menghapus user!';
                        }
                    } catch (PDOException $e) {
                        $error = 'Error database: ' . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get all users
try {
    $query = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $error = 'Gagal mengambil data user: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Lubung Data SAE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-database me-2"></i>
                Lubung Data SAE
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upload.php">
                            <i class="fas fa-upload me-1"></i>Upload Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="file-manager.php">
                            <i class="fas fa-folder-open me-1"></i>File Manager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="monitoring.html">
                            <i class="fas fa-chart-line me-1"></i>Monitoring
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="user-management.php">
                            <i class="fas fa-users me-1"></i>Kelola User
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="activity-logs.php">
                            <i class="fas fa-history me-1"></i>Activity Logs
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user-edit me-1"></i>Profil Saya
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
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
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users me-2"></i>Kelola User</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-1"></i>Tambah User
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
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

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar User (<?php echo count($users); ?> user)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Role</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Total Upload</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php foreach ($users as $user): ?>
                                            <?php
                                            // Get upload count for this user
                                            try {
                                                $uploadCountQuery = "SELECT COUNT(*) as total FROM uploads WHERE uploaded_by = :user_id";
                                                $uploadStmt = $conn->prepare($uploadCountQuery);
                                                $uploadStmt->bindParam(':user_id', $user['id']);
                                                $uploadStmt->execute();
                                                $uploadCount = $uploadStmt->fetch(PDO::FETCH_ASSOC)['total'];
                                            } catch (PDOException $e) {
                                                $uploadCount = 0;
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-circle fa-2x text-muted me-2"></i>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                                <small class="badge bg-info">Anda</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    $badgeClass = 'primary';
                                                    $roleText = 'User';
                                                    if ($user['role'] == 'admin') {
                                                        $badgeClass = 'danger';
                                                        $roleText = 'Administrator';
                                                    } elseif ($user['role'] == 'corrector') {
                                                        $badgeClass = 'warning';
                                                        $roleText = 'Corrector';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?>">
                                                        <?php echo $roleText; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?php echo $uploadCount; ?> file
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button class="btn btn-outline-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <button class="btn btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-users fa-3x mb-3"></i><br>
                                                Belum ada user
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="add_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="add_password" name="password" required>
                            <div class="form-text">Minimal 6 karakter</div>
                        </div>
                        <div class="mb-3">
                            <label for="add_full_name" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="add_full_name" name="full_name" required>
                            <div class="form-text">User dapat melengkapi NIK dan nomor telepon di profil setelah login</div>
                        </div>
                        <div class="mb-3">
                            <label for="add_role" class="form-label">Role *</label>
                            <select class="form-select" id="add_role" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="user">User (Upload File)</option>
                                <option value="corrector">Corrector (Koreksi Data)</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="edit_new_password" name="new_password">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role *</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="user">User (Upload File)</option>
                                <option value="corrector">Corrector (Koreksi Data)</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_new_password').value = '';
            
            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        }

        function deleteUser(userId, username) {
            if (confirm(`Apakah Anda yakin ingin menghapus user "${username}"?\n\nSemua data upload yang terkait dengan user ini akan tetap tersimpan.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'user_id';
                userIdInput.value = userId;
                
                form.appendChild(actionInput);
                form.appendChild(userIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Form validation
        document.getElementById('add_password').addEventListener('input', function() {
            if (this.value.length < 6 && this.value.length > 0) {
                this.setCustomValidity('Password minimal 6 karakter');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
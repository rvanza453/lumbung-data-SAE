<?php
include_once 'includes/functions.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lubung Data SAE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-database me-2"></i>
                Lubung Data SAE
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
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
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user-management.php">
                            <i class="fas fa-users me-1"></i>Kelola User
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="activity-logs.php">
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
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
                    <div class="text-muted">
                        Selamat datang, <strong><?php echo $_SESSION['full_name']; ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php
            $database = new Database();
            $conn = $database->getConnection();
            
            // Get statistics
            $totalUploads = 0;
            $totalPanen = 0;
            $totalPengiriman = 0;
            $todayUploads = 0;
            $totalUsers = 0;
            
            try {
                // Total uploads
                $query = "SELECT COUNT(*) as total FROM uploads";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $totalUploads = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Panen count
                $query = "SELECT COUNT(*) as total FROM uploads WHERE kategori = 'panen'";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $totalPanen = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Pengiriman count
                $query = "SELECT COUNT(*) as total FROM uploads WHERE kategori = 'pengiriman'";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $totalPengiriman = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Today's uploads
                $query = "SELECT COUNT(*) as total FROM uploads WHERE DATE(upload_date) = CURDATE()";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $todayUploads = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Total users (for admin only)
                if ($_SESSION['role'] == 'admin') {
                    $query = "SELECT COUNT(*) as total FROM users";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                }
            } catch (PDOException $e) {
                // Handle error silently
            }
            ?>

            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $totalUploads; ?></h4>
                                <p class="mb-0">Total Upload</p>
                            </div>
                            <div>
                                <i class="fas fa-file-upload fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $totalPanen; ?></h4>
                                <p class="mb-0">Data Panen</p>
                            </div>
                            <div>
                                <i class="fas fa-seedling fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0"><?php echo $totalPengiriman; ?></h4>
                                <p class="mb-0">Data Pengiriman</p>
                            </div>
                            <div>
                                <i class="fas fa-shipping-fast fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $totalUsers; ?></h4>
                                    <p class="mb-0">Total User</p>
                                </div>
                                <div>
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo $todayUploads; ?></h4>
                                    <p class="mb-0">Upload Hari Ini</p>
                                </div>
                                <div>
                                    <i class="fas fa-calendar-day fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="upload.php" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-upload me-2"></i>
                                    Upload Data Baru
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="file-manager.php" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-folder-open me-2"></i>
                                    Kelola File
                                </a>
                            </div>
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                            <div class="col-md-4 mb-3">
                                <a href="user-management.php" class="btn btn-warning btn-lg w-100">
                                    <i class="fas fa-users me-2"></i>
                                    Kelola User
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="col-md-4 mb-3">
                                <a href="file-manager.php?kategori=panen" class="btn btn-info btn-lg w-100">
                                    <i class="fas fa-seedling me-2"></i>
                                    Data Panen
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($_SESSION['role'] == 'admin'): ?>
        <!-- Recent Activity (Admin Only) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Aktivitas Terbaru</h5>
                        <a href="activity-logs.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Lihat Semua
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">Waktu</th>
                                        <th width="20%">User</th>
                                        <th width="15%">Action</th>
                                        <th width="50%">Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $activityQuery = "SELECT al.*, u.username, u.full_name 
                                                         FROM activity_logs al 
                                                         LEFT JOIN users u ON al.user_id = u.id 
                                                         ORDER BY al.created_at DESC 
                                                         LIMIT 8";
                                        $activityStmt = $conn->prepare($activityQuery);
                                        $activityStmt->execute();
                                        $activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (count($activities) > 0) {
                                            foreach ($activities as $activity) {
                                                $badgeColor = 'secondary';
                                                switch ($activity['action']) {
                                                    case 'LOGIN': $badgeColor = 'success'; break;
                                                    case 'LOGOUT': $badgeColor = 'warning'; break;
                                                    case 'UPLOAD_FILE': $badgeColor = 'info'; break;
                                                    case 'DELETE_FILE': $badgeColor = 'danger'; break;
                                                    case 'CREATE_USER': $badgeColor = 'success'; break;
                                                    case 'UPDATE_USER': $badgeColor = 'warning'; break;
                                                    case 'DELETE_USER': $badgeColor = 'danger'; break;
                                                    case 'UPDATE_PROFILE': $badgeColor = 'primary'; break;
                                                    case 'EXPORT_DATA': $badgeColor = 'success'; break;
                                                }
                                                
                                                echo "<tr>";
                                                echo "<td><small class='text-muted'>" . date('d/m H:i', strtotime($activity['created_at'])) . "</small></td>";
                                                echo "<td><small><strong>" . htmlspecialchars($activity['full_name'] ?? $activity['username'] ?? 'Unknown') . "</strong></small></td>";
                                                echo "<td><span class='badge bg-$badgeColor'>" . str_replace('_', ' ', $activity['action']) . "</span></td>";
                                                echo "<td><small>" . htmlspecialchars($activity['description']) . "</small></td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center text-muted'><i>Belum ada aktivitas</i></td></tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='4' class='text-center text-danger'><i>Error loading activities</i></td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Uploads -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Upload Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Nama</th>
                                        <th>NIK</th>
                                        <th>Afdeling</th>
                                        <th>File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $query = "SELECT * FROM uploads ORDER BY upload_date DESC LIMIT 10";
                                        $stmt = $conn->prepare($query);
                                        $stmt->execute();
                                        
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($row['upload_date'])) . "</td>";
                                            echo "<td><span class='badge bg-" . ($row['kategori'] == 'panen' ? 'success' : 'info') . "'>" . ucfirst($row['kategori']) . "</span></td>";
                                            echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nomor_induk_kerja']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['afdeling']) . "</td>";
                                            echo "<td><a href='download.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-primary'><i class='fas fa-download me-1'></i>" . $row['original_filename'] . "</a></td>";
                                            echo "</tr>";
                                        }
                                        
                                        if ($stmt->rowCount() == 0) {
                                            echo "<tr><td colspan='6' class='text-center text-muted'>Belum ada data upload</td></tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='6' class='text-center text-danger'>Error: " . $e->getMessage() . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
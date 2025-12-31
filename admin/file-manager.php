<?php
include_once '../includes/functions.php';
requireLogin();

// Get filter parameters
$kategori_filter = isset($_GET['kategori']) ? sanitizeInput($_GET['kategori']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$user_filter = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$afdeling_filter = isset($_GET['afdeling']) ? sanitizeInput($_GET['afdeling']) : '';
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$database = new Database();
$conn = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager - Lubung Data SAE</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* Bulk Actions Styling */
        #bulkActionsBar {
            border-left: 4px solid #0d6efd;
            background: linear-gradient(90deg, rgba(13,110,253,0.1) 0%, rgba(13,110,253,0.05) 100%);
            border-radius: 0.5rem;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .file-checkbox:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .table tbody tr:hover .file-checkbox {
            opacity: 1;
        }
        
        .file-checkbox {
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }
        
        #selectAllFiles:indeterminate {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        
        /* Enhanced button hover effects */
        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: all 0.2s ease;
        }
    </style>
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
                        <a class="nav-link" href="../pages/upload.php">
                            <i class="fas fa-upload me-1"></i>Upload Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="file-manager.php">
                            <i class="fas fa-folder-open me-1"></i>File Manager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/monitoring.php">
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
                            <li><a class="dropdown-item" href="../pages/profile.php">
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
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-folder-open me-2"></i>File Manager</h2>
                    <a href="../pages/upload.php" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Upload Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filter File Manager
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-sm btn-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                        <i class="fas fa-sliders-h me-1" style="color:dodgerblue;"></i>
                                        <span style="color: dodgerblue;">
                                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                                Filter User & Tanggal
                                            <?php else: ?>
                                                Filter Tanggal
                                            <?php endif; ?>
                                        </span>
                                    </button>
                                </div>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <!-- Basic Filters Row -->
                            <div class="col-md-4">
                                <label for="search" class="form-label">
                                    <i class="fas fa-search me-1"></i>Pencarian
                                </label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Nama, NIK, afdeling, atau nama file...">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="kategori" class="form-label">
                                    <i class="fas fa-tags me-1"></i>Kategori
                                </label>
                                <select class="form-select" id="kategori" name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <option value="panen" <?php echo $kategori_filter == 'panen' ? 'selected' : ''; ?>>Panen</option>
                                    <option value="pengiriman" <?php echo $kategori_filter == 'pengiriman' ? 'selected' : ''; ?>>Pengiriman</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="afdeling" class="form-label">
                                    <i class="fas fa-building me-1"></i>Afdeling
                                </label>
                                <select class="form-select" id="afdeling" name="afdeling">
                                    <option value="">Semua Afdeling</option>
                                    <?php
                                    // Get distinct afdeling values
                                    try {
                                        $afdelingQuery = "SELECT DISTINCT afdeling FROM uploads WHERE afdeling IS NOT NULL AND afdeling != '' ORDER BY afdeling";
                                        $afdelingStmt = $conn->prepare($afdelingQuery);
                                        $afdelingStmt->execute();
                                        $afdelings = $afdelingStmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($afdelings as $afd) {
                                            $selected = $afdeling_filter == $afd['afdeling'] ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($afd['afdeling']) . "' $selected>" . 
                                                 htmlspecialchars($afd['afdeling']) . "</option>";
                                        }
                                    } catch (PDOException $e) {
                                        // Handle silently
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                </div>
                            </div>

                            <!-- Filter User & Tanggal Section (Collapsible) -->
                            <div class="collapse <?php echo (($_SESSION['role'] == 'admin' && !empty($user_filter)) || !empty($date_from) || !empty($date_to)) ? 'show' : ''; ?>" id="advancedFilters">
                                <hr class="my-3">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Filter Lanjutan:</strong> Gunakan filter di bawah ini untuk mencari berdasarkan 
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                        user yang mengupload dan rentang tanggal.
                                    <?php else: ?>
                                        rentang tanggal upload.
                                    <?php endif; ?>
                                </div>
                                <div class="row g-3">
                                    <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <div class="col-md-4">
                                        <label for="user" class="form-label">
                                            <i class="fas fa-user me-1"></i>Upload oleh User
                                        </label>
                                        <select class="form-select" id="user" name="user">
                                            <option value="">Semua User</option>
                                            <?php
                                            // Get all users who have uploaded files
                                            try {
                                                $usersQuery = "SELECT DISTINCT u.id, u.username, u.full_name 
                                                              FROM users u 
                                                              INNER JOIN uploads up ON u.id = up.uploaded_by 
                                                              ORDER BY u.full_name, u.username";
                                                $usersStmt = $conn->prepare($usersQuery);
                                                $usersStmt->execute();
                                                $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                foreach ($users as $user) {
                                                    $selected = $user_filter == $user['id'] ? 'selected' : '';
                                                    $displayName = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
                                                    echo "<option value='" . $user['id'] . "' $selected>" . 
                                                         htmlspecialchars($displayName) . "</option>";
                                                }
                                            } catch (PDOException $e) {
                                                // Handle silently
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="<?php echo ($_SESSION['role'] == 'admin') ? 'col-md-3' : 'col-md-5'; ?>">
                                        <label for="date_from" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Dari Tanggal
                                        </label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" 
                                               value="<?php echo htmlspecialchars($date_from); ?>">
                                    </div>
                                    
                                    <div class="<?php echo ($_SESSION['role'] == 'admin') ? 'col-md-3' : 'col-md-5'; ?>">
                                        <label for="date_to" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Sampai Tanggal
                                        </label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" 
                                               value="<?php echo htmlspecialchars($date_to); ?>">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid">
                                            <a href="file-manager.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-1"></i>Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Files List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php
                        // Build query with filters
                        $whereConditions = [];
                        $params = [];

                        // For non-admin users, only show their own uploads
                        if ($_SESSION['role'] != 'admin') {
                            $whereConditions[] = "up.uploaded_by = :current_user_id";
                            $params[':current_user_id'] = $_SESSION['user_id'];
                        }

                        if (!empty($kategori_filter)) {
                            $whereConditions[] = "up.kategori = :kategori";
                            $params[':kategori'] = $kategori_filter;
                        }

                        if (!empty($search)) {
                            $whereConditions[] = "(up.nama LIKE :search OR up.nomor_induk_kerja LIKE :search OR up.afdeling LIKE :search OR up.original_filename LIKE :search)";
                            $params[':search'] = '%' . $search . '%';
                        }

                        // Admin can filter by user, regular users cannot
                        if ($_SESSION['role'] == 'admin' && $user_filter > 0) {
                            $whereConditions[] = "up.uploaded_by = :user_id";
                            $params[':user_id'] = $user_filter;
                        }

                        if (!empty($afdeling_filter)) {
                            $whereConditions[] = "up.afdeling = :afdeling";
                            $params[':afdeling'] = $afdeling_filter;
                        }

                        if (!empty($date_from)) {
                            $whereConditions[] = "DATE(up.upload_date) >= :date_from";
                            $params[':date_from'] = $date_from;
                        }

                        if (!empty($date_to)) {
                            $whereConditions[] = "DATE(up.upload_date) <= :date_to";
                            $params[':date_to'] = $date_to;
                        }

                        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

                        // Get total count for pagination
                        try {
                            $countQuery = "SELECT COUNT(*) as total FROM uploads up 
                                          LEFT JOIN users u ON up.uploaded_by = u.id " . $whereClause;
                            $countStmt = $conn->prepare($countQuery);
                            foreach ($params as $key => $value) {
                                $countStmt->bindValue($key, $value);
                            }
                            $countStmt->execute();
                            $totalFiles = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
                            $totalPages = ceil($totalFiles / $perPage);

                            // Get files with pagination and user info
                            $query = "SELECT up.*, u.username, u.full_name 
                                     FROM uploads up 
                                     LEFT JOIN users u ON up.uploaded_by = u.id 
                                     " . $whereClause . " 
                                     ORDER BY up.upload_date DESC 
                                     LIMIT :limit OFFSET :offset";
                            $stmt = $conn->prepare($query);
                            
                            foreach ($params as $key => $value) {
                                $stmt->bindValue($key, $value);
                            }
                            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                            
                            $stmt->execute();
                            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                            $files = [];
                            $totalFiles = 0;
                            $totalPages = 0;
                        }
                        ?>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong><?php echo number_format($totalFiles); ?> file ditemukan</strong>
                                <?php
                                $hasFilters = false;
                                $filterParams = http_build_query(array_filter([
                                    'kategori' => $kategori_filter,
                                    'search' => $search,
                                    'user' => $user_filter,
                                    'afdeling' => $afdeling_filter,
                                    'date_from' => $date_from,
                                    'date_to' => $date_to
                                ]));
                                
                                if (!empty($filterParams)):
                                    $hasFilters = true;
                                ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Filter aktif:</small>
                                        <?php if (!empty($kategori_filter)): ?>
                                            <span class="badge bg-info me-1">
                                                <i class="fas fa-tags me-1"></i>Kategori: <?php echo ucfirst($kategori_filter); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($search)): ?>
                                            <span class="badge bg-warning me-1">
                                                <i class="fas fa-search me-1"></i>Pencarian: "<?php echo htmlspecialchars($search); ?>"
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($user_filter > 0): ?>
                                            <?php
                                            // Get user name for display
                                            try {
                                                $userQuery = "SELECT full_name, username FROM users WHERE id = :user_id";
                                                $userStmt = $conn->prepare($userQuery);
                                                $userStmt->bindValue(':user_id', $user_filter);
                                                $userStmt->execute();
                                                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                                                $userName = $userData ? ($userData['full_name'] ?: $userData['username']) : "User #$user_filter";
                                            } catch (Exception $e) {
                                                $userName = "User #$user_filter";
                                            }
                                            ?>
                                            <span class="badge bg-success me-1">
                                                <i class="fas fa-user me-1"></i>User: <?php echo htmlspecialchars($userName); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($afdeling_filter)): ?>
                                            <span class="badge bg-primary me-1">
                                                <i class="fas fa-building me-1"></i>Afdeling: <?php echo htmlspecialchars($afdeling_filter); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($date_from) || !empty($date_to)): ?>
                                            <span class="badge bg-secondary me-1">
                                                <i class="fas fa-calendar me-1"></i>
                                                Tanggal: 
                                                <?php 
                                                if (!empty($date_from) && !empty($date_to)) {
                                                    echo date('d/m/Y', strtotime($date_from)) . ' - ' . date('d/m/Y', strtotime($date_to));
                                                } elseif (!empty($date_from)) {
                                                    echo 'dari ' . date('d/m/Y', strtotime($date_from));
                                                } elseif (!empty($date_to)) {
                                                    echo 'sampai ' . date('d/m/Y', strtotime($date_to));
                                                }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                        <a href="file-manager.php" class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="fas fa-times me-1"></i>Reset Semua Filter
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Export/Download Options -->
                            <?php if ($totalFiles > 0): ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportData('csv')">
                                            <i class="fas fa-file-csv me-1"></i>Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="printTable()">
                                            <i class="fas fa-print me-1"></i>Print Table
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (count($files) > 0): ?>
                            <!-- Bulk Actions Toolbar -->
                            <div id="bulkActionsBar" class="alert alert-primary mb-3" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-check-square me-2"></i>
                                        <span id="selectedCount">0</span> file dipilih
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                            <i class="fas fa-times me-1"></i>Batal Pilih
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success" onclick="importToMonitoring()">
                                            <i class="fas fa-chart-line me-1"></i>Import ke Monitoring
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="bulkDeleteFiles()">
                                            <i class="fas fa-trash me-1"></i>Hapus Terpilih
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="selectAllFiles" onchange="toggleSelectAll()" class="form-check-input">
                                            </th>
                                            <th>Tanggal Upload</th>
                                            <th>Kategori</th>
                                            <th>Nama</th>
                                            <th>NIK</th>
                                            <th>Afdeling</th>
                                            <th>File</th>
                                            <th>Ukuran</th>
                                            <th>Upload oleh</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($files as $file): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selectedFiles[]" value="<?php echo $file['id']; ?>" 
                                                           class="form-check-input file-checkbox" onchange="updateSelection()" 
                                                           data-filename="<?php echo htmlspecialchars($file['original_filename']); ?>">
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($file['upload_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $file['kategori'] == 'panen' ? 'success' : 'info'; ?>">
                                                        <?php echo ucfirst($file['kategori']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($file['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($file['nomor_induk_kerja']); ?></td>
                                                <td><?php echo htmlspecialchars($file['afdeling']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-file me-2 text-muted"></i>
                                                        <div>
                                                            <div class="fw-bold"><?php echo htmlspecialchars($file['original_filename']); ?></div>
                                                            <small class="text-muted"><?php echo strtoupper($file['file_type']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo number_format($file['file_size'] / 1024, 2); ?> KB</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-circle me-1 text-muted"></i>
                                                        <small>
                                                            <strong><?php echo htmlspecialchars($file['full_name'] ?: $file['username'] ?: 'Unknown'); ?></strong>
                                                            <?php if ($file['username']): ?>
                                                                <br><span class="text-muted">(@<?php echo htmlspecialchars($file['username']); ?>)</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="../scripts/download.php?id=<?php echo $file['id']; ?>" class="btn btn-outline-primary" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button class="btn btn-outline-info" onclick="viewFileDetails(<?php echo $file['id']; ?>)" title="Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($_SESSION['role'] == 'admin'): ?>
                                                            <button class="btn btn-outline-danger" onclick="deleteFile(<?php echo $file['id']; ?>)" title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <?php 
                                // Build pagination URL parameters
                                $paginationParams = http_build_query(array_filter([
                                    'kategori' => $kategori_filter,
                                    'search' => $search,
                                    'user' => $user_filter,
                                    'afdeling' => $afdeling_filter,
                                    'date_from' => $date_from,
                                    'date_to' => $date_to
                                ]));
                                $paginationBase = $paginationParams ? '&' . $paginationParams : '';
                                ?>
                                <nav aria-label="File pagination">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $paginationBase; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $paginationBase; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $paginationBase; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada file ditemukan</h5>
                                <p class="text-muted">
                                    <?php if (!empty($search) || !empty($kategori_filter)): ?>
                                        Coba ubah filter atau kata kunci pencarian Anda.
                                    <?php else: ?>
                                    Mulai dengan mengupload file pertama Anda.
                                <?php endif; ?>
                            </p>
                            <a href="../pages/upload.php" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>Upload File Pertama
                            </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Details Modal -->
    <div class="modal fade" id="fileDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Detail File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="fileDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportData(format) {
            const params = new URLSearchParams(window.location.search);
            params.set('export', format);
            window.open('export-files.php?' + params.toString(), '_blank');
        }

        function printTable() {
            const printContent = document.querySelector('.table-responsive').outerHTML;
            const printWindow = window.open('', '_blank');
            const currentDate = new Date().toLocaleString('id-ID');
            
            printWindow.document.write(
                '<!DOCTYPE html>' +
                '<html>' +
                '<head>' +
                '<title>File Manager - Print</title>' +
                '<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">' +
                '<style>' +
                '@media print {' +
                '.btn, .btn-group { display: none !important; }' +
                'body { font-size: 12px; }' +
                '}' +
                '</style>' +
                '</head>' +
                '<body>' +
                '<div class="container-fluid">' +
                '<h3 class="mb-4">File Manager - Lubung Data SAE</h3>' +
                '<p><small>Dicetak pada: ' + currentDate + '</small></p>' +
                printContent +
                '</div>' +
                '<script>' +
                'window.onload = function() {' +
                'window.print();' +
                'setTimeout(function() { window.close(); }, 1000);' +
                '}' +
                '<\script>' +
                '</body>' +
                '</html>'
            );
            printWindow.document.close();
        }
        
        function viewFileDetails(fileId) {
            const modal = new bootstrap.Modal(document.getElementById('fileDetailsModal'));
            const content = document.getElementById('fileDetailsContent');
            
            content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            modal.show();
            
            fetch(`../scripts/get-file-details.php?id=${fileId}`)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                })
                .catch(error => {
                    content.innerHTML = '<div class="alert alert-danger">Error loading file details.</div>';
                });
        }

        function deleteFile(fileId) {
            if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
                fetch('../scripts/delete-file.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${fileId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menghapus file: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan saat menghapus file.');
                });
            }
        }
        
        // Bulk Actions JavaScript
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAllFiles');
            const fileCheckboxes = document.querySelectorAll('.file-checkbox');
            
            fileCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            updateSelection();
        }
        
        function updateSelection() {
            const fileCheckboxes = document.querySelectorAll('.file-checkbox');
            const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAllFiles');
            const bulkActionsBar = document.getElementById('bulkActionsBar');
            const selectedCount = document.getElementById('selectedCount');
            
            // Update counter
            selectedCount.textContent = selectedFiles.length;
            
            // Show/hide bulk actions bar
            if (selectedFiles.length > 0) {
                bulkActionsBar.style.display = 'block';
            } else {
                bulkActionsBar.style.display = 'none';
            }
            
            // Update select all checkbox state
            if (selectedFiles.length === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (selectedFiles.length === fileCheckboxes.length) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }
        
        function clearSelection() {
            const fileCheckboxes = document.querySelectorAll('.file-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllFiles');
            
            fileCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            
            updateSelection();
        }
        
        function getSelectedFileIds() {
            const selectedCheckboxes = document.querySelectorAll('.file-checkbox:checked');
            return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
        }
        
        function bulkDeleteFiles() {
            const selectedIds = getSelectedFileIds();
            
            if (selectedIds.length === 0) {
                alert('Pilih file yang ingin dihapus terlebih dahulu.');
                return;
            }
            
            const confirmMessage = `Apakah Anda yakin ingin menghapus ${selectedIds.length} file yang dipilih?\n\nTindakan ini tidak dapat dibatalkan.`;
            
            if (confirm(confirmMessage)) {
                // Show loading state
                const bulkActionsBar = document.getElementById('bulkActionsBar');
                const originalContent = bulkActionsBar.innerHTML;
                bulkActionsBar.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-spinner fa-spin me-2"></i>Menghapus file...</div>';
                
                fetch('../scripts/bulk-delete-files.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({fileIds: selectedIds})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Berhasil menghapus ${data.deletedCount} file.`);
                        location.reload();
                    } else {
                        alert('Gagal menghapus file: ' + data.message);
                        bulkActionsBar.innerHTML = originalContent;
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan saat menghapus file.');
                    bulkActionsBar.innerHTML = originalContent;
                });
            }
        }
        
        function importToMonitoring() {
            const selectedIds = getSelectedFileIds();
            
            if (selectedIds.length === 0) {
                alert('Pilih file yang ingin diimpor terlebih dahulu.');
                return;
            }
            
            if (confirm(`Import ${selectedIds.length} file ke Monitoring?`)) {
                const bulkActionsBar = document.getElementById('bulkActionsBar');
                const originalContent = bulkActionsBar.innerHTML;
                bulkActionsBar.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-spinner fa-spin me-2"></i>Memproses data besar...</div>';
                
                fetch('import-to-monitoring-fixed.php', { // Pastikan nama file PHP sesuai
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({fileIds: selectedIds})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.fileUrl) {
                        // --- SOLUSI FILE BESAR ---
                        // Kita tidak kirim data, tapi kirim ALAMAT FILE-nya
                        const monitoringUrl = `monitoring.html?sourceFile=${encodeURIComponent(data.fileUrl)}`;
                        
                        window.open(monitoringUrl, '_blank');
                        
                        // Restore UI
                        bulkActionsBar.innerHTML = originalContent;
                        clearSelection();
                    } else {
                        throw new Error(data.message || 'Gagal import.');
                    }
                })
                .catch(error => {
                    console.error(error);
                    bulkActionsBar.innerHTML = originalContent;
                    alert('Error: ' + error.message);
                });
            }
        }

        // Auto-submit search form on Enter and initialize other events
        document.addEventListener('DOMContentLoaded', function() {
            // Search form enter key handler
            const searchElement = document.getElementById('search');
            if (searchElement) {
                searchElement.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.form.submit();
                    }
                });
            }
            
            // Initialize tooltips if Bootstrap tooltip is available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>
</body>
</html>
<?php
include_once '../includes/functions.php';
requireLogin();

// Only admin can access this page
if ($_SESSION['role'] != 'admin') {
    header('Location: ../dashboard.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

// Get filter parameters
$action_filter = isset($_GET['action']) ? sanitizeInput($_GET['action']) : '';
$user_filter = isset($_GET['user']) ? (int)$_GET['user'] : 0;
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build where conditions
$whereConditions = [];
$params = [];

if (!empty($action_filter)) {
    $whereConditions[] = "al.action = :action";
    $params[':action'] = $action_filter;
}

if ($user_filter > 0) {
    $whereConditions[] = "al.user_id = :user_id";
    $params[':user_id'] = $user_filter;
}

if (!empty($date_from)) {
    $whereConditions[] = "DATE(al.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to)) {
    $whereConditions[] = "DATE(al.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
try {
    $countQuery = "SELECT COUNT(*) as total FROM activity_logs al $whereClause";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalLogs = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalLogs / $perPage);
} catch (PDOException $e) {
    $totalLogs = 0;
    $totalPages = 0;
}

// Get logs with pagination
try {
    $query = "SELECT al.*, u.username, u.full_name 
              FROM activity_logs al 
              LEFT JOIN users u ON al.user_id = u.id 
              $whereClause 
              ORDER BY al.created_at DESC 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $logs = [];
    $error = 'Error: ' . $e->getMessage();
}

// Get all users for filter
try {
    $usersQuery = "SELECT id, username, full_name FROM users ORDER BY full_name";
    $usersStmt = $conn->prepare($usersQuery);
    $usersStmt->execute();
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

// Get action types - diperluas untuk mencakup semua aktivitas
$actions = [
    'LOGIN', 
    'LOGOUT', 
    'UPLOAD_FILE', 
    'DELETE_FILE', 
    'BULK_DELETE_FILES',
    'DOWNLOAD_FILE',
    'VIEW_FILE_CONTENT',
    'IMPORT_TO_MONITORING',
    'CREATE_USER', 
    'UPDATE_USER', 
    'DELETE_USER', 
    'UPDATE_PROFILE', 
    'VIEW_LOGS', 
    'EXPORT_DATA',
    'EXPORT_EXCEL',
    'PRINT_DATA',
    'KOREKSI_PANEN',
    'KOREKSI_PENGIRIMAN',
    'VIEW_MONITORING',
    'ACCESS_DASHBOARD',
    'ACCESS_FILE_MANAGER',
    'SEARCH_DATA',
    'FILTER_DATA'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Lubung Data SAE</title>
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
                        <a class="nav-link" href="../pages/upload.php">
                            <i class="fas fa-upload me-1"></i>Upload Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="file-manager.php">
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
                        <a class="nav-link active" href="activity-logs.php">
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
                    <h2><i class="fas fa-history me-2"></i>Activity Logs</h2>
                    <div class="badge bg-info fs-6">
                        <i class="fas fa-list me-1"></i><?php echo number_format($totalLogs); ?> total records
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Logs</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-2">
                                <label for="action" class="form-label">Action</label>
                                <select class="form-select form-select-sm" id="action" name="action">
                                    <option value="">Semua Action</option>
                                    <?php foreach ($actions as $action): ?>
                                        <option value="<?php echo $action; ?>" <?php echo $action_filter == $action ? 'selected' : ''; ?>>
                                            <?php echo str_replace('_', ' ', $action); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="user" class="form-label">User</label>
                                <select class="form-select form-select-sm" id="user" name="user">
                                    <option value="">Semua User</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Dari Tanggal</label>
                                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" 
                                       value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Sampai Tanggal</label>
                                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" 
                                       value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <a href="activity-logs.php" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (count($logs) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="15%">Waktu</th>
                                            <th width="10%">User</th>
                                            <th width="12%">Action</th>
                                            <th width="35%">Description</th>
                                            <th width="10%">Target</th>
                                            <th width="10%">IP Address</th>
                                            <th width="8%">Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($log['created_at'])); ?><br>
                                                        <?php echo date('H:i:s', strtotime($log['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-circle text-muted me-1"></i>
                                                        <small>
                                                            <strong><?php echo htmlspecialchars($log['full_name'] ?? $log['username'] ?? 'Unknown'); ?></strong><br>
                                                            <span class="text-muted"><?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?></span>
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeColor = 'secondary';
                                                    $icon = 'fas fa-circle';
                                                    switch ($log['action']) {
                                                        case 'LOGIN':
                                                            $badgeColor = 'success';
                                                            $icon = 'fas fa-sign-in-alt';
                                                            break;
                                                        case 'LOGOUT':
                                                            $badgeColor = 'warning';
                                                            $icon = 'fas fa-sign-out-alt';
                                                            break;
                                                        case 'UPLOAD_FILE':
                                                            $badgeColor = 'info';
                                                            $icon = 'fas fa-upload';
                                                            break;
                                                        case 'DELETE_FILE':
                                                            $badgeColor = 'danger';
                                                            $icon = 'fas fa-trash';
                                                            break;
                                                        case 'BULK_DELETE_FILES':
                                                            $badgeColor = 'danger';
                                                            $icon = 'fas fa-trash-alt';
                                                            break;
                                                        case 'DOWNLOAD_FILE':
                                                            $badgeColor = 'primary';
                                                            $icon = 'fas fa-download';
                                                            break;
                                                        case 'VIEW_FILE_CONTENT':
                                                            $badgeColor = 'secondary';
                                                            $icon = 'fas fa-file-alt';
                                                            break;
                                                        case 'IMPORT_TO_MONITORING':
                                                            $badgeColor = 'info';
                                                            $icon = 'fas fa-database';
                                                            break;
                                                        case 'CREATE_USER':
                                                            $badgeColor = 'success';
                                                            $icon = 'fas fa-user-plus';
                                                            break;
                                                        case 'UPDATE_USER':
                                                            $badgeColor = 'warning';
                                                            $icon = 'fas fa-user-edit';
                                                            break;
                                                        case 'DELETE_USER':
                                                            $badgeColor = 'danger';
                                                            $icon = 'fas fa-user-minus';
                                                            break;
                                                        case 'UPDATE_PROFILE':
                                                            $badgeColor = 'primary';
                                                            $icon = 'fas fa-user-cog';
                                                            break;
                                                        case 'VIEW_LOGS':
                                                            $badgeColor = 'secondary';
                                                            $icon = 'fas fa-eye';
                                                            break;
                                                        case 'EXPORT_DATA':
                                                            $badgeColor = 'success';
                                                            $icon = 'fas fa-download';
                                                            break;
                                                        case 'EXPORT_EXCEL':
                                                            $badgeColor = 'success';
                                                            $icon = 'fas fa-file-excel';
                                                            break;
                                                        case 'PRINT_DATA':
                                                            $badgeColor = 'info';
                                                            $icon = 'fas fa-print';
                                                            break;
                                                        case 'KOREKSI_PANEN':
                                                            $badgeColor = 'warning';
                                                            $icon = 'fas fa-edit';
                                                            break;
                                                        case 'KOREKSI_PENGIRIMAN':
                                                            $badgeColor = 'warning';
                                                            $icon = 'fas fa-edit';
                                                            break;
                                                        case 'VIEW_MONITORING':
                                                            $badgeColor = 'info';
                                                            $icon = 'fas fa-chart-line';
                                                            break;
                                                        case 'ACCESS_DASHBOARD':
                                                            $badgeColor = 'primary';
                                                            $icon = 'fas fa-tachometer-alt';
                                                            break;
                                                        case 'ACCESS_FILE_MANAGER':
                                                            $badgeColor = 'secondary';
                                                            $icon = 'fas fa-folder-open';
                                                            break;
                                                        case 'SEARCH_DATA':
                                                            $badgeColor = 'light';
                                                            $icon = 'fas fa-search';
                                                            break;
                                                        case 'FILTER_DATA':
                                                            $badgeColor = 'light';
                                                            $icon = 'fas fa-filter';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeColor; ?>">
                                                        <i class="<?php echo $icon; ?> me-1"></i>
                                                        <?php echo str_replace('_', ' ', $log['action']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($log['description']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($log['target_type'] && $log['target_id']): ?>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($log['target_type']); ?>#<?php echo $log['target_id']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">-</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted font-monospace">
                                                        <?php echo htmlspecialchars($log['ip_address'] ?? 'unknown'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="showLogDetail(<?php echo htmlspecialchars(json_encode($log)); ?>)" 
                                                            title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav aria-label="Logs pagination">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&action=<?php echo $action_filter; ?>&user=<?php echo $user_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&action=<?php echo $action_filter; ?>&user=<?php echo $user_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&action=<?php echo $action_filter; ?>&user=<?php echo $user_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada log activity ditemukan</h5>
                                <p class="text-muted">
                                    <?php if (!empty($action_filter) || $user_filter > 0 || !empty($date_from) || !empty($date_to)): ?>
                                        Coba ubah filter atau reset filter untuk melihat semua log.
                                    <?php else: ?>
                                        Log activity akan muncul setelah ada aktivitas user.
                                    <?php endif; ?>
                                </p>
                                <a href="activity-logs.php" class="btn btn-outline-primary">
                                    <i class="fas fa-refresh me-1"></i>Refresh
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Detail Modal -->
    <div class="modal fade" id="logDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Log Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="logDetailContent">
                    <!-- Content will be loaded via JS -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function showLogDetail(log) {
            const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
            const content = document.getElementById('logDetailContent');
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">ID:</th>
                                <td>#${log.id}</td>
                            </tr>
                            <tr>
                                <th>User:</th>
                                <td>${log.full_name || log.username || 'Unknown'}</td>
                            </tr>
                            <tr>
                                <th>Action:</th>
                                <td><span class="badge bg-primary">${log.action.replace('_', ' ')}</span></td>
                            </tr>
                            <tr>
                                <th>Waktu:</th>
                                <td>${new Date(log.created_at).toLocaleString('id-ID')}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">IP Address:</th>
                                <td><code>${log.ip_address || 'unknown'}</code></td>
                            </tr>
                            <tr>
                                <th>Target Type:</th>
                                <td>${log.target_type || '-'}</td>
                            </tr>
                            <tr>
                                <th>Target ID:</th>
                                <td>${log.target_id || '-'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>Description:</h6>
                        <div class="alert alert-light">
                            ${log.description}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>User Agent:</h6>
                        <div class="alert alert-light">
                            <small><code>${log.user_agent || 'unknown'}</code></small>
                        </div>
                    </div>
                </div>
            `;
            
            modal.show();
        }

        // Auto-refresh every 30 seconds if no filters applied
        <?php if (empty($action_filter) && $user_filter == 0 && empty($date_from) && empty($date_to) && $page == 1): ?>
        setTimeout(function() {
            location.reload();
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>

<?php
// Log that admin viewed activity logs
try {
    logActivity($conn, $_SESSION['user_id'], 'VIEW_LOGS', 'Admin viewed activity logs page');
} catch (Exception $e) {
    // Handle silently
}
?>
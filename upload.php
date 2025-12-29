<?php
include_once 'includes/functions.php';
requireLogin();

$success = '';
$error = '';

$database = new Database();
$conn = $database->getConnection();

// Get user profile data
$userProfile = [];
try {
    $query = "SELECT full_name, nik, default_kategori FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $userProfile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle silently, user can still fill manually
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kategori = sanitizeInput($_POST['kategori']);
    $nama = sanitizeInput($_POST['nama']);
    $nomor_induk_kerja = sanitizeInput($_POST['nomor_induk_kerja']);
    $afdeling = sanitizeInput($_POST['afdeling']);
    
    // Use profile data if available and form fields are empty
    if (empty($nama) && !empty($userProfile['full_name'])) {
        $nama = $userProfile['full_name'];
    }
    if (empty($nomor_induk_kerja) && !empty($userProfile['nik'])) {
        $nomor_induk_kerja = $userProfile['nik'];
    }
    
    // Validate required fields
    if (empty($kategori) || empty($nama) || empty($nomor_induk_kerja) || empty($afdeling)) {
        $error = 'Semua field wajib diisi!';
    } else if (!isset($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = 'Mohon pilih file untuk diupload!';
    } else {
        $file = $_FILES['file'];
        
        // Check file size
        if ($file['size'] > $maxFileSize) {
            $error = 'Ukuran file terlalu besar! Maksimal 50MB.';
        } else {
            $fileExtension = getFileExtension($file['name']);
            
            // Check file type
            if (!in_array($fileExtension, $allowedFileTypes)) {
                $error = 'Tipe file tidak diizinkan! Hanya: ' . implode(', ', $allowedFileTypes);
            } else {
                // Create upload directory
                $uploadPath = createUploadDirectory($kategori);
                
                // Generate unique filename
                $uniqueFilename = generateUniqueFilename($file['name']);
                $fullPath = $uploadPath . '/' . $uniqueFilename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                    // Save to database
                    $database = new Database();
                    $conn = $database->getConnection();
                    
                    try {
                        $query = "INSERT INTO uploads (kategori, nama, nomor_induk_kerja, afdeling, filename, original_filename, file_path, file_size, file_type, uploaded_by) VALUES (:kategori, :nama, :nomor_induk_kerja, :afdeling, :filename, :original_filename, :file_path, :file_size, :file_type, :uploaded_by)";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':kategori', $kategori);
                        $stmt->bindParam(':nama', $nama);
                        $stmt->bindParam(':nomor_induk_kerja', $nomor_induk_kerja);
                        $stmt->bindParam(':afdeling', $afdeling);
                        $stmt->bindParam(':filename', $uniqueFilename);
                        $stmt->bindParam(':original_filename', $file['name']);
                        $stmt->bindParam(':file_path', $fullPath);
                        $stmt->bindParam(':file_size', $file['size']);
                        $stmt->bindParam(':file_type', $fileExtension);
                        $stmt->bindParam(':uploaded_by', $_SESSION['user_id']);
                        
                        if ($stmt->execute()) {
                            $uploadId = $conn->lastInsertId();
                            
                            // Parse dan simpan data JSON ke tabel yang sesuai
                            $jsonParseResult = parseAndSaveJsonData($fullPath, $kategori, $uploadId, $conn);
                            
                            if ($jsonParseResult['success']) {
                                // Log file upload activity
                                try {
                                    logActivity($conn, $_SESSION['user_id'], 'UPLOAD_FILE', 
                                              "File uploaded: {$file['name']} (kategori: $kategori, nama: $nama). Data tersimpan: {$jsonParseResult['count']} records", 
                                              'upload', $uploadId);
                                } catch (Exception $e) {
                                    // Handle silently - don't break upload process
                                }
                                
                                $success = "File berhasil diupload dan {$jsonParseResult['count']} data berhasil disimpan ke database!";
                                // Clear form data
                                $_POST = array();
                            } else {
                                // File uploaded but JSON parsing failed
                                $success = 'File berhasil diupload, namun gagal memproses data JSON: ' . $jsonParseResult['error'];
                            }
                        } else {
                            $error = 'Gagal menyimpan data ke database!';
                            unlink($fullPath); // Delete uploaded file if database save fails
                        }
                    } catch (PDOException $e) {
                        $error = 'Error database: ' . $e->getMessage();
                        unlink($fullPath); // Delete uploaded file if database save fails
                    }
                } else {
                    $error = 'Gagal mengupload file!';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data - Lubung Data SAE</title>
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
                        <a class="nav-link active" href="upload.php">
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Data Baru</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($userProfile['full_name']) || !empty($userProfile['nik'])): ?>
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Data dari profil Anda:</strong> Nama dan NIK otomatis terisi dari profil. 
                                <a href="profile.php" class="alert-link">Kelola profil</a> jika ingin mengubah data.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Profil belum lengkap:</strong> 
                                <a href="profile.php" class="alert-link">Lengkapi profil Anda</a> 
                                agar nama dan NIK terisi otomatis.
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" id="uploadForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="form-label">
                                        <i class="fas fa-tags me-2"></i>Kategori *
                                    </label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="panen" <?php 
                                            echo (!empty($userProfile['default_kategori']) && $userProfile['default_kategori'] == 'panen') ? 'selected' : 
                                                 ((isset($_POST['kategori']) && $_POST['kategori'] == 'panen') ? 'selected' : ''); 
                                        ?>>Panen</option>
                                        <option value="pengiriman" <?php 
                                            echo (!empty($userProfile['default_kategori']) && $userProfile['default_kategori'] == 'pengiriman') ? 'selected' : 
                                                 ((isset($_POST['kategori']) && $_POST['kategori'] == 'pengiriman') ? 'selected' : ''); 
                                        ?>>Pengiriman</option>
                                    </select>
                                    <?php if (!empty($userProfile['default_kategori'])): ?>
                                        <div class="form-text text-success">
                                            <i class="fas fa-info-circle me-1"></i>Kategori default dari profil Anda
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nama *
                                    </label>
                                    <input type="text" class="form-control" id="nama" name="nama" 
                                           value="<?php echo !empty($userProfile['full_name']) ? htmlspecialchars($userProfile['full_name']) : (isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''); ?>" 
                                           <?php echo !empty($userProfile['full_name']) ? 'readonly' : 'required'; ?>>
                                    <?php if (!empty($userProfile['full_name'])): ?>
                                        <div class="form-text text-success">
                                            <i class="fas fa-check me-1"></i>Diambil dari profil Anda
                                        </div>
                                    <?php else: ?>
                                        <div class="form-text">
                                            <a href="profile.php" class="text-primary">Lengkapi profil</a> untuk auto-fill
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nomor_induk_kerja" class="form-label">
                                        <i class="fas fa-id-card me-2"></i>Nomor Induk Kerja *
                                    </label>
                                    <input type="text" class="form-control" id="nomor_induk_kerja" name="nomor_induk_kerja" 
                                           value="<?php echo !empty($userProfile['nik']) ? htmlspecialchars($userProfile['nik']) : (isset($_POST['nomor_induk_kerja']) ? htmlspecialchars($_POST['nomor_induk_kerja']) : ''); ?>" 
                                           <?php echo !empty($userProfile['nik']) ? 'readonly' : 'required'; ?>>
                                    <?php if (!empty($userProfile['nik'])): ?>
                                        <div class="form-text text-success">
                                            <i class="fas fa-check me-1"></i>Diambil dari profil Anda
                                        </div>
                                    <?php else: ?>
                                        <div class="form-text">
                                            <a href="profile.php" class="text-primary">Lengkapi profil</a> untuk auto-fill
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="afdeling" class="form-label">
                                        <i class="fas fa-building me-2"></i>Afdeling *
                                    </label>
                                    <input type="text" class="form-control" id="afdeling" name="afdeling" 
                                           value="<?php echo isset($_POST['afdeling']) ? htmlspecialchars($_POST['afdeling']) : ''; ?>" 
                                           placeholder="Masukkan afdeling Anda" required>
                                    <div class="form-text">
                                        Isi afdeling sesuai lokasi kerja Anda
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="file" class="form-label">
                                    <i class="fas fa-file me-2"></i>Upload File *
                                </label>
                                <input type="file" class="form-control" id="file" name="file" accept=".json" required>
                                <div class="form-text">
                                    <strong>Tipe file yang diizinkan:</strong> <?php echo implode(', ', $allowedFileTypes); ?>
                                    <br>
                                    <strong>Maksimal ukuran:</strong> 50MB
                                    <br>
                                    <small class="text-info"><i class="fas fa-info-circle me-1"></i>Hanya file JSON yang dapat diupload untuk memastikan format data yang konsisten</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-upload me-2"></i>Upload File
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Upload Guidelines -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Panduan Upload</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-check-circle text-success me-2"></i>Yang Perlu Diperhatikan:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>Pastikan semua field terisi dengan benar</li>
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>Pilih kategori sesuai jenis data</li>
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>Nomor Induk Kerja harus valid</li>
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>File akan disimpan otomatis berdasarkan kategori dan tanggal</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-file text-warning me-2"></i>Format File JSON:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>File harus berformat .json</li>
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>Data harus dalam format JSON yang valid</li>
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>Maksimal ukuran file 50MB</li>
                                    <li><i class="fas fa-dot-circle text-primary me-2"></i>Pastikan struktur data sesuai kebutuhan</li>
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
        // Form validation and file size check
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('file');
            const submitBtn = document.getElementById('submitBtn');
            
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const maxSize = 50 * 1024 * 1024; // 50MB in bytes
                
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar! Maksimal 50MB.');
                    return false;
                }
                
                // Show loading state
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
                submitBtn.disabled = true;
            }
        });

        // File preview and JSON validation
        document.getElementById('file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                const fileExtension = file.name.split('.').pop().toLowerCase();
                
                // Check if it's a JSON file
                if (fileExtension !== 'json') {
                    alert('Hanya file JSON yang diizinkan!');
                    this.value = '';
                    return;
                }
                
                console.log(`File selected: ${file.name} (${fileSize} MB)`);
                
                // Optional: Validate JSON content
                const reader = new FileReader();
                reader.onload = function(event) {
                    try {
                        JSON.parse(event.target.result);
                        console.log('Valid JSON format');
                    } catch (error) {
                        alert('File JSON tidak valid! Pastikan format JSON benar.');
                        document.getElementById('file').value = '';
                    }
                };
                reader.readAsText(file);
            }
        });
    </script>
</body>
</html>
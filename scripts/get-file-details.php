<?php
include_once '../includes/functions.php';
requireLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="alert alert-danger">File ID tidak valid!</div>';
    exit();
}

$fileId = (int)$_GET['id'];

$database = new Database();
$conn = $database->getConnection();

try {
    $query = "SELECT up.*, u.username, u.full_name 
              FROM uploads up 
              LEFT JOIN users u ON up.uploaded_by = u.id 
              WHERE up.id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $fileId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Kategori:</th>
                        <td><span class="badge bg-<?php echo $file['kategori'] == 'panen' ? 'success' : 'info'; ?>"><?php echo ucfirst($file['kategori']); ?></span></td>
                    </tr>
                    <tr>
                        <th>Nama:</th>
                        <td><?php echo htmlspecialchars($file['nama']); ?></td>
                    </tr>
                    <tr>
                        <th>Nomor Induk Kerja:</th>
                        <td><?php echo htmlspecialchars($file['nomor_induk_kerja']); ?></td>
                    </tr>
                    <tr>
                        <th>Afdeling:</th>
                        <td><?php echo htmlspecialchars($file['afdeling']); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Nama File:</th>
                        <td><?php echo htmlspecialchars($file['original_filename']); ?></td>
                    </tr>
                    <tr>
                        <th>Tipe File:</th>
                        <td><?php echo strtoupper($file['file_type']); ?></td>
                    </tr>
                    <tr>
                        <th>Ukuran File:</th>
                        <td><?php echo number_format($file['file_size'] / 1024, 2); ?> KB</td>
                    </tr>
                    <tr>
                        <th>Tanggal Upload:</th>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($file['upload_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>Upload oleh:</th>
                        <td>
                            <strong><?php echo htmlspecialchars($file['full_name'] ?: $file['username'] ?: 'Unknown'); ?></strong>
                            <?php if ($file['username']): ?>
                                <br><small class="text-muted">(@<?php echo htmlspecialchars($file['username']); ?>)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <strong><i class="fas fa-folder me-2"></i>Lokasi File:</strong><br>
                    <code><?php echo htmlspecialchars($file['file_path']); ?></code>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center">
                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Download File
                </a>
                
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <button class="btn btn-danger ms-2" onclick="deleteFileFromModal(<?php echo $file['id']; ?>)">
                        <i class="fas fa-trash me-2"></i>Hapus File
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        function deleteFileFromModal(fileId) {
            if (confirm('Apakah Anda yakin ingin menghapus file ini?')) {
                // Close modal first
                const modal = bootstrap.Modal.getInstance(document.getElementById('fileDetailsModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Then delete file
                setTimeout(function() {
                    deleteFile(fileId);
                }, 500);
            }
        }
        </script>
        <?php
    } else {
        echo '<div class="alert alert-warning">File tidak ditemukan!</div>';
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
}
?>
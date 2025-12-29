-- Update database untuk menambahkan fitur koreksi panen dan koreksi pengiriman (Safe version)
USE lubung_data_sae;

-- 1. Tambahkan role baru 'corrector' untuk role yang dapat melakukan koreksi (Safe)
SET @sql = CONCAT('ALTER TABLE users MODIFY COLUMN role ENUM(''admin'', ''user'', ''corrector'') DEFAULT ''user''');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Tambahkan kolom koreksi_panen di tabel data_panen (Safe)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_panen' 
     AND column_name = 'koreksi_panen') = 0,
    'ALTER TABLE data_panen ADD COLUMN koreksi_panen INT DEFAULT 0 COMMENT ''Koreksi jumlah janjang panen (+/-)''',
    'SELECT ''Column koreksi_panen already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_panen' 
     AND column_name = 'koreksi_by') = 0,
    'ALTER TABLE data_panen ADD COLUMN koreksi_by INT DEFAULT NULL COMMENT ''User ID yang melakukan koreksi''',
    'SELECT ''Column koreksi_by already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_panen' 
     AND column_name = 'koreksi_at') = 0,
    'ALTER TABLE data_panen ADD COLUMN koreksi_at TIMESTAMP NULL DEFAULT NULL COMMENT ''Waktu koreksi dilakukan''',
    'SELECT ''Column koreksi_at already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_panen' 
     AND column_name = 'koreksi_reason') = 0,
    'ALTER TABLE data_panen ADD COLUMN koreksi_reason TEXT DEFAULT NULL COMMENT ''Alasan koreksi''',
    'SELECT ''Column koreksi_reason already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Tambahkan kolom koreksi_kirim di tabel data_pengiriman (Safe)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_pengiriman' 
     AND column_name = 'koreksi_kirim') = 0,
    'ALTER TABLE data_pengiriman ADD COLUMN koreksi_kirim INT DEFAULT 0 COMMENT ''Koreksi jumlah janjang pengiriman (+/-)''',
    'SELECT ''Column koreksi_kirim already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_pengiriman' 
     AND column_name = 'koreksi_by') = 0,
    'ALTER TABLE data_pengiriman ADD COLUMN koreksi_by INT DEFAULT NULL COMMENT ''User ID yang melakukan koreksi''',
    'SELECT ''Column koreksi_by already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_pengiriman' 
     AND column_name = 'koreksi_at') = 0,
    'ALTER TABLE data_pengiriman ADD COLUMN koreksi_at TIMESTAMP NULL DEFAULT NULL COMMENT ''Waktu koreksi dilakukan''',
    'SELECT ''Column koreksi_at already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.columns 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_pengiriman' 
     AND column_name = 'koreksi_reason') = 0,
    'ALTER TABLE data_pengiriman ADD COLUMN koreksi_reason TEXT DEFAULT NULL COMMENT ''Alasan koreksi''',
    'SELECT ''Column koreksi_reason already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Buat tabel untuk log koreksi (Safe)
CREATE TABLE IF NOT EXISTS koreksi_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipe_koreksi ENUM('panen', 'pengiriman') NOT NULL,
    target_id INT NOT NULL,
    user_id INT NOT NULL,
    nilai_lama INT NOT NULL,
    nilai_baru INT NOT NULL,
    alasan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tipe_target (tipe_koreksi, target_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- 5. Tambahkan user corrector sebagai contoh (password: corrector123)
INSERT INTO users (username, password, full_name, role) VALUES 
('corrector', '$2y$10$wZ5Qh9.8rO7Q6pKcHvP.zOJ8K9mL4nP3qR5sT7vU9wX1Y2zA3bC4e', 'Data Corrector', 'corrector')
ON DUPLICATE KEY UPDATE 
password = '$2y$10$wZ5Qh9.8rO7Q6pKcHvP.zOJ8K9mL4nP3qR5sT7vU9wX1Y2zA3bC4e',
role = 'corrector';

-- 6. Tambahkan foreign key constraints jika belum ada
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.table_constraints 
     WHERE constraint_schema = 'lubung_data_sae' 
     AND table_name = 'data_panen' 
     AND constraint_name LIKE '%koreksi_by%') = 0,
    'ALTER TABLE data_panen ADD CONSTRAINT fk_data_panen_koreksi_by FOREIGN KEY (koreksi_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT ''FK koreksi_by for data_panen already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.table_constraints 
     WHERE constraint_schema = 'lubung_data_sae' 
     AND table_name = 'data_pengiriman' 
     AND constraint_name LIKE '%koreksi_by%') = 0,
    'ALTER TABLE data_pengiriman ADD CONSTRAINT fk_data_pengiriman_koreksi_by FOREIGN KEY (koreksi_by) REFERENCES users(id) ON DELETE SET NULL',
    'SELECT ''FK koreksi_by for data_pengiriman already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. Index untuk performa query koreksi (Safe)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.statistics 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_panen' 
     AND index_name = 'idx_koreksi') = 0,
    'ALTER TABLE data_panen ADD INDEX idx_koreksi (koreksi_panen)',
    'SELECT ''Index idx_koreksi for data_panen already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.statistics 
     WHERE table_schema = 'lubung_data_sae' 
     AND table_name = 'data_pengiriman' 
     AND index_name = 'idx_koreksi') = 0,
    'ALTER TABLE data_pengiriman ADD INDEX idx_koreksi (koreksi_kirim)',
    'SELECT ''Index idx_koreksi for data_pengiriman already exists'' AS message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tampilkan status update
SELECT 'Database update for koreksi feature completed successfully!' AS status;
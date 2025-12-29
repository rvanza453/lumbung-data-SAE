-- Update database untuk menambahkan fitur koreksi panen dan koreksi pengiriman
USE lubung_data_sae;

-- 1. Tambahkan role baru 'corrector' untuk role yang dapat melakukan koreksi
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'corrector') DEFAULT 'user';

-- 2. Tambahkan kolom koreksi_panen di tabel data_panen
ALTER TABLE data_panen 
ADD COLUMN koreksi_panen INT DEFAULT 0 COMMENT 'Koreksi jumlah janjang panen (+/-)',
ADD COLUMN koreksi_by INT DEFAULT NULL COMMENT 'User ID yang melakukan koreksi',
ADD COLUMN koreksi_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu koreksi dilakukan',
ADD COLUMN koreksi_reason TEXT DEFAULT NULL COMMENT 'Alasan koreksi',
ADD FOREIGN KEY (koreksi_by) REFERENCES users(id) ON DELETE SET NULL;

-- 3. Tambahkan kolom koreksi_kirim di tabel data_pengiriman
ALTER TABLE data_pengiriman 
ADD COLUMN koreksi_kirim INT DEFAULT 0 COMMENT 'Koreksi jumlah janjang pengiriman (+/-)',
ADD COLUMN koreksi_by INT DEFAULT NULL COMMENT 'User ID yang melakukan koreksi',
ADD COLUMN koreksi_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu koreksi dilakukan',
ADD COLUMN koreksi_reason TEXT DEFAULT NULL COMMENT 'Alasan koreksi',
ADD FOREIGN KEY (koreksi_by) REFERENCES users(id) ON DELETE SET NULL;

-- 4. Buat tabel untuk log koreksi
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

-- 6. Index untuk performa query koreksi
ALTER TABLE data_panen ADD INDEX idx_koreksi (koreksi_panen);
ALTER TABLE data_pengiriman ADD INDEX idx_koreksi (koreksi_kirim);

COMMIT;
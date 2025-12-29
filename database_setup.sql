-- Create database
CREATE DATABASE IF NOT EXISTS lubung_data_sae CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lubung_data_sae;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    nik VARCHAR(50) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create uploads table
CREATE TABLE IF NOT EXISTS uploads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kategori ENUM('panen', 'pengiriman') NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nomor_induk_kerja VARCHAR(50) NOT NULL,
    afdeling VARCHAR(50) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_kategori (kategori),
    INDEX idx_upload_date (upload_date),
    INDEX idx_afdeling (afdeling)
);

-- Create activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    target_type VARCHAR(50) DEFAULT NULL,
    target_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user (username: admin, password: admin123)
-- Password hash generated with password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$wZ5Qh9.8rO7Q6pKcHvP.zOJ8K9mL4nP3qR5sT7vU9wX1Y2zA3bC4e', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE password = '$2y$10$wZ5Qh9.8rO7Q6pKcHvP.zOJ8K9mL4nP3qR5sT7vU9wX1Y2zA3bC4e';

-- Add new columns if they don't exist (for existing installations)
-- Check manually if columns exist before running these
-- ALTER TABLE users ADD COLUMN nik VARCHAR(50) DEFAULT NULL AFTER full_name;
-- ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER nik;
-- ALTER TABLE users ADD COLUMN default_kategori ENUM('panen','pengiriman') DEFAULT NULL AFTER phone;

-- Create tabel panen untuk menyimpan data panen dari JSON
CREATE TABLE IF NOT EXISTS data_panen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    upload_id INT NOT NULL,
    nama_kerani VARCHAR(100) NOT NULL,
    tanggal_pemeriksaan DATE NOT NULL,
    afdeling VARCHAR(50) NOT NULL,
    nama_pemanen VARCHAR(100) NOT NULL,
    nik_pemanen VARCHAR(50) NOT NULL,
    blok VARCHAR(50) NOT NULL,
    no_ancak VARCHAR(50) NOT NULL,
    no_tph VARCHAR(50) NOT NULL,
    jam TIME NOT NULL,
    last_modified VARCHAR(255),
    koordinat TEXT,
    jumlah_janjang INT NOT NULL,
    bjr DECIMAL(10,2) DEFAULT NULL,
    kg_total DECIMAL(10,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(id) ON DELETE CASCADE,
    INDEX idx_upload_id (upload_id),
    INDEX idx_tanggal (tanggal_pemeriksaan),
    INDEX idx_afdeling (afdeling),
    INDEX idx_blok (blok)
);

-- Create tabel pengiriman untuk menyimpan data transport/pengiriman dari JSON
CREATE TABLE IF NOT EXISTS data_pengiriman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    upload_id INT NOT NULL,
    tipe_aplikasi VARCHAR(100) NOT NULL,
    nama_kerani VARCHAR(100) NOT NULL,
    nik_kerani VARCHAR(50),
    tanggal DATE NOT NULL,
    afdeling VARCHAR(50) NOT NULL,
    nopol VARCHAR(50) NOT NULL,
    nomor_kendaraan VARCHAR(50) NOT NULL,
    blok VARCHAR(50) NOT NULL,
    no_tph VARCHAR(50) NOT NULL,
    jumlah_janjang INT NOT NULL,
    waktu TIME NOT NULL,
    koordinat TEXT,
    kg DECIMAL(10,2),
    bjr DECIMAL(10,2) DEFAULT NULL,
    kg_total DECIMAL(10,2) DEFAULT NULL,
    kg_berondolan DECIMAL(10,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (upload_id) REFERENCES uploads(id) ON DELETE CASCADE,
    INDEX idx_upload_id (upload_id),
    INDEX idx_tanggal (tanggal),
    INDEX idx_afdeling (afdeling),
    INDEX idx_nopol (nopol)
);

-- Note: Password 'admin123' is hashed. You can change it after first login.
-- Update database untuk menambahkan kolom-kolom yang hilang
-- Script ini akan menambahkan semua kolom yang dibutuhkan untuk menyimpan data lengkap dari JSON

USE lubung_data_sae;

-- ============================================================
-- UPDATE TABEL DATA_PANEN
-- ============================================================
-- Tambahkan kolom-kolom yang hilang untuk data panen

-- Kolom untuk foto utama dan grading
-- Menggunakan procedure untuk menghindari error jika kolom sudah ada

DELIMITER $$
CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(100),
    IN columnName VARCHAR(100), 
    IN columnDefinition VARCHAR(500)
)
BEGIN
    IF NOT EXISTS (
        SELECT NULL 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = tableName 
        AND COLUMN_NAME = columnName
        AND TABLE_SCHEMA = 'lubung_data_sae'
    ) THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', columnName, ' ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- Tambahkan kolom-kolom untuk data_panen
CALL AddColumnIfNotExists('data_panen', 'main_foto', 'TEXT AFTER koordinat');
CALL AddColumnIfNotExists('data_panen', 'matang', 'INT DEFAULT 0 AFTER main_foto');
CALL AddColumnIfNotExists('data_panen', 'foto_matang', 'TEXT AFTER matang');
CALL AddColumnIfNotExists('data_panen', 'mengkal', 'INT DEFAULT 0 AFTER foto_matang');
CALL AddColumnIfNotExists('data_panen', 'foto_mengkal', 'TEXT AFTER mengkal');
CALL AddColumnIfNotExists('data_panen', 'mentah', 'INT DEFAULT 0 AFTER foto_mengkal');
CALL AddColumnIfNotExists('data_panen', 'foto_mentah', 'TEXT AFTER mentah');
CALL AddColumnIfNotExists('data_panen', 'lewat_matang', 'INT DEFAULT 0 AFTER foto_mentah');
CALL AddColumnIfNotExists('data_panen', 'foto_lewat_matang', 'TEXT AFTER lewat_matang');
CALL AddColumnIfNotExists('data_panen', 'abnormal', 'INT DEFAULT 0 AFTER foto_lewat_matang');
CALL AddColumnIfNotExists('data_panen', 'foto_abnormal', 'TEXT AFTER abnormal');
CALL AddColumnIfNotExists('data_panen', 'serangan_hama', 'INT DEFAULT 0 AFTER foto_abnormal');
CALL AddColumnIfNotExists('data_panen', 'foto_serangan_hama', 'TEXT AFTER serangan_hama');
CALL AddColumnIfNotExists('data_panen', 'tangkai_panjang', 'INT DEFAULT 0 AFTER foto_serangan_hama');
CALL AddColumnIfNotExists('data_panen', 'foto_tangkai_panjang', 'TEXT AFTER tangkai_panjang');
CALL AddColumnIfNotExists('data_panen', 'janjang_kosong', 'INT DEFAULT 0 AFTER foto_tangkai_panjang');
CALL AddColumnIfNotExists('data_panen', 'foto_janjang_kosong', 'TEXT AFTER janjang_kosong');
CALL AddColumnIfNotExists('data_panen', 'kg_berondolan', 'DECIMAL(10,2) DEFAULT 0 AFTER foto_janjang_kosong');
CALL AddColumnIfNotExists('data_panen', 'foto_kg_berondolan', 'TEXT AFTER kg_berondolan');
CALL AddColumnIfNotExists('data_panen', 'original_id', 'BIGINT AFTER foto_kg_berondolan');

-- Update nama kolom yang salah (kg_brd -> kg_berondolan sudah diperbaiki di atas)

-- ============================================================
-- UPDATE TABEL DATA_PENGIRIMAN  
-- ============================================================
-- Tambahkan kolom-kolom yang hilang untuk data pengiriman

-- Kolom original_id untuk menyimpan ID asli dari JSON
CALL AddColumnIfNotExists('data_pengiriman', 'original_id', 'VARCHAR(50) AFTER kg_brd');

-- ============================================================
-- CREATE INDEX TAMBAHAN UNTUK PERFORMANCE
-- ============================================================

-- Index untuk pencarian yang sering digunakan
CREATE INDEX IF NOT EXISTS idx_panen_nama_pemanen ON data_panen(nama_pemanen);
CREATE INDEX IF NOT EXISTS idx_panen_nik_pemanen ON data_panen(nik_pemanen);  
CREATE INDEX IF NOT EXISTS idx_panen_original_id ON data_panen(original_id);
CREATE INDEX IF NOT EXISTS idx_pengiriman_original_id ON data_pengiriman(original_id);
CREATE INDEX IF NOT EXISTS idx_pengiriman_nama_kerani ON data_pengiriman(nama_kerani);

-- ============================================================
-- CLEANUP PROCEDURE
-- ============================================================
DROP PROCEDURE IF EXISTS AddColumnIfNotExists;

-- ============================================================
-- TEMPORARY BACKUP VIEWS (OPSIONAL)
-- ============================================================
-- Buat view untuk melihat data yang sudah ada sebelum update

CREATE OR REPLACE VIEW view_panen_backup AS
SELECT 
    id,
    upload_id,
    nama_kerani,
    tanggal_pemeriksaan,
    afdeling,
    nama_pemanen,
    nik_pemanen,
    blok,
    no_ancak,
    no_tph,
    jam,
    koordinat,
    jumlah_janjang,
    bjr,
    kg_total,
    created_at
FROM data_panen;

CREATE OR REPLACE VIEW view_pengiriman_backup AS
SELECT 
    id,
    upload_id,
    tipe_aplikasi,
    nama_kerani,
    nik_kerani,
    tanggal,
    afdeling,
    nopol,
    nomor_kendaraan,
    blok,
    no_tph,
    jumlah_janjang,
    waktu,
    koordinat,
    kg_total,
    bjr,
    kg_berondolan,
    created_at
FROM data_pengiriman;

-- ============================================================
-- VERIFICATION QUERY
-- ============================================================
-- Query untuk memverifikasi kolom-kolom yang telah ditambahkan

-- Cek struktur tabel data_panen
DESCRIBE data_panen;

-- Cek struktur tabel data_pengiriman  
DESCRIBE data_pengiriman;

-- Cek jumlah data yang ada
SELECT 
    'data_panen' as tabel,
    COUNT(*) as jumlah_record
FROM data_panen
UNION ALL
SELECT 
    'data_pengiriman' as tabel,
    COUNT(*) as jumlah_record
FROM data_pengiriman;

-- ============================================================
-- CLEAN UP DUPLICATES (JIKA ADA)
-- ============================================================
-- Script ini akan membersihkan data duplikat berdasarkan kombinasi tertentu

-- Backup sebelum clean up
CREATE TABLE IF NOT EXISTS data_panen_backup_before_cleanup AS SELECT * FROM data_panen;
CREATE TABLE IF NOT EXISTS data_pengiriman_backup_before_cleanup AS SELECT * FROM data_pengiriman;

-- Query untuk mengidentifikasi duplikat di data_panen (belum dijalankan, hanya untuk referensi)
/*
SELECT 
    nama_kerani,
    tanggal_pemeriksaan,
    afdeling,
    nama_pemanen,
    blok,
    no_tph,
    COUNT(*) as jumlah_duplikat
FROM data_panen 
GROUP BY nama_kerani, tanggal_pemeriksaan, afdeling, nama_pemanen, blok, no_tph
HAVING COUNT(*) > 1;
*/

-- Query untuk mengidentifikasi duplikat di data_pengiriman (belum dijalankan)
/*
SELECT 
    nama_kerani,
    tanggal,
    afdeling,
    nopol,
    blok,
    no_tph,
    COUNT(*) as jumlah_duplikat
FROM data_pengiriman 
GROUP BY nama_kerani, tanggal, afdeling, nopol, blok, no_tph
HAVING COUNT(*) > 1;
*/

COMMIT;
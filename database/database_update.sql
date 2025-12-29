-- Database Update Script for JSON Format Changes
-- Run this script to add missing columns to existing tables

USE lubung_data_sae;

-- Add kg_total column to data_panen table if it doesn't exist
SET @sql = 'ALTER TABLE data_panen ADD COLUMN kg_total DECIMAL(10,2) DEFAULT NULL AFTER bjr';
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns 
WHERE table_name = 'data_panen' AND column_name = 'kg_total' AND table_schema = 'lubung_data_sae';
SET @sql = IF(@col_exists = 0, @sql, 'SELECT "kg_total column already exists in data_panen"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add kg_total column to data_pengiriman table if it doesn't exist
SET @sql = 'ALTER TABLE data_pengiriman ADD COLUMN kg_total DECIMAL(10,2) DEFAULT NULL AFTER kg';
SELECT COUNT(*) INTO @col_exists FROM information_schema.columns 
WHERE table_name = 'data_pengiriman' AND column_name = 'kg_total' AND table_schema = 'lubung_data_sae';
SET @sql = IF(@col_exists = 0, @sql, 'SELECT "kg_total column already exists in data_pengiriman"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add kg_berondolan column to data_pengiriman table if it doesn't exist and kg_brd doesn't exist
SELECT COUNT(*) INTO @kg_berondolan_exists FROM information_schema.columns 
WHERE table_name = 'data_pengiriman' AND column_name = 'kg_berondolan' AND table_schema = 'lubung_data_sae';

SELECT COUNT(*) INTO @kg_brd_exists FROM information_schema.columns 
WHERE table_name = 'data_pengiriman' AND column_name = 'kg_brd' AND table_schema = 'lubung_data_sae';

SET @sql = IF(@kg_berondolan_exists = 0 AND @kg_brd_exists = 0, 
              'ALTER TABLE data_pengiriman ADD COLUMN kg_berondolan DECIMAL(10,2) DEFAULT 0 AFTER kg_total',
              'SELECT "kg_berondolan or kg_brd column already exists"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify the changes
DESCRIBE data_panen;
DESCRIBE data_pengiriman;
-- ============================================
-- SETUP DATABASE - Web Informasi Masjid Al-Furqon
-- Jalankan file ini di phpMyAdmin tab SQL
-- ============================================

CREATE DATABASE IF NOT EXISTS db_masjid;
USE db_masjid;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL
);

-- Tabel Info Jumat
CREATE TABLE IF NOT EXISTS info_jumat (
    id_info INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_jumat DATE NOT NULL,
    nama_khotib VARCHAR(100) NOT NULL,
    asal_instansi VARCHAR(100) NULL,
    nama_imam VARCHAR(100) NOT NULL
);

-- Tabel Kajian
CREATE TABLE IF NOT EXISTS kajian (
    id_kajian INT AUTO_INCREMENT PRIMARY KEY,
    tema_kajian VARCHAR(150) NOT NULL,
    pemateri VARCHAR(100) NOT NULL,
    waktu VARCHAR(50) NOT NULL
);

-- Insert Data Admin (Password: admin123)
-- CATATAN: Hash ini akan di-fix oleh fix_password.php
INSERT INTO admin (username, password, nama_lengkap) 
VALUES ('admin', 'PLACEHOLDER', 'Administrator DKM')
ON DUPLICATE KEY UPDATE username = username;

CREATE DATABASE IF NOT EXISTS db_masjid;
USE db_masjid;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL
);

-- Tabel Kas Masjid
CREATE TABLE IF NOT EXISTS kas_masjid (
    id_kas INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    keterangan VARCHAR(255) NOT NULL,
    tipe ENUM('Pemasukan', 'Pengeluaran') NOT NULL,
    nominal INT NOT NULL
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

-- Insert Data Admin Dummy (Password default: admin123, sudah di-hash untuk keamanan standar PHP)
INSERT INTO admin (username, password, nama_lengkap) 
VALUES ('admin', '$2y$10$tZ2.FhH0pP2h2YgB/Yv/bOQO.Jk/P2xJ/R2H.S6R9H2QO1W9XwK.W', 'Administrator DKM');

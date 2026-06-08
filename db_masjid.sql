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

-- Tabel Minecraft Server
CREATE TABLE IF NOT EXISTS minecraft_server (
    id_server INT AUTO_INCREMENT PRIMARY KEY,
    nama_server VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    server_ip VARCHAR(50) NOT NULL,
    server_port INT DEFAULT 25565,
    status ENUM('Online', 'Offline', 'Maintenance') DEFAULT 'Online',
    player_online INT DEFAULT 0,
    player_max INT DEFAULT 20,
    versi_game VARCHAR(50),
    mode_permainan VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Data Admin Dummy (Password default: admin123, sudah di-hash untuk keamanan standar PHP)
INSERT INTO admin (username, password, nama_lengkap) 
VALUES ('admin', '$2y$10$tZ2.FhH0pP2h2YgB/Yv/bOQO.Jk/P2xJ/R2H.S6R9H2QO1W9XwK.W', 'Administrator DKM');

-- Insert Data Minecraft Server Contoh
INSERT INTO minecraft_server (nama_server, deskripsi, server_ip, server_port, status, player_online, player_max, versi_game, mode_permainan) 
VALUES ('Server Utama', 'Server Minecraft komunitas Masjid Al-Furqon', 'play.masjid-alfurqon.local', 25565, 'Online', 5, 20, '1.20.1', 'Survival');

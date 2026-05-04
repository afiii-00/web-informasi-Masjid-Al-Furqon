# 🕌 Sistem Informasi Masjid Al-Furqon

Sistem Informasi Manajemen Masjid berbasis web yang dirancang dengan antarmuka minimalis dan modern. Proyek ini dibangun sebagai **Minimum Viable Product (MVP)** untuk mendigitalisasi informasi publik masjid dan mempermudah DKM (Dewan Kemakmuran Masjid) dalam mengelola data secara efisien.

## 🚀 Fitur Utama

### 👥 Halaman Publik (Jamaah)
*   **Widget Jadwal Sholat:** Menampilkan 5 waktu sholat harian secara ringkas.
*   **Informasi Kegiatan:** Menampilkan Khotib & Imam Jumat minggu ini, serta jadwal Kajian Rutin Ahad.
*   **Transparansi Keuangan:** Laporan mutasi kas masjid (pemasukan & pengeluaran) dan saldo akhir secara *real-time*.

### 🔐 Panel Admin (DKM)
*   **Autentikasi:** Sistem login aman untuk pengurus masjid.
*   **Manajemen Kas (CRUD):** Pencatatan sirkulasi uang masuk dan keluar.
*   **Manajemen Kegiatan (CRUD):** Pembaruan data petugas Jumat dan pemateri kajian mingguan.

## 💻 Tech Stack

*   **Frontend:** HTML5, Tailwind CSS (via CDN), Vanilla JavaScript.
*   **Backend:** PHP Native (Prosedural).
*   **Database:** MySQL.

## 🛠️ Panduan Instalasi (Local Development)

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di komputer lokal (localhost):

1. **Persiapan Sistem:**
   Pastikan Anda sudah menginstal **XAMPP**, **WAMP**, atau aplikasi *web server* lokal lainnya yang mendukung PHP dan MySQL.

2. **Clone Repository:**
   Buka terminal/Command Prompt dan jalankan perintah ini di dalam folder `htdocs` (jika menggunakan XAMPP):
   ```bash
   git clone [https://github.com/username-lu/nama-repo-lu.git](https://github.com/username-lu/nama-repo-lu.git)

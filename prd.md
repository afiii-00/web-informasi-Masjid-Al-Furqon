
# Product Requirement Document (PRD)

## Project Title: alfurqon.akdmalam.my.id - Portal Komunitas & Dokumentasi CMS v1.0
**Author:** Gemini AI & User  
**Date:** July 3, 2026  
**Status:** Draft / Ready for Design & Dev  

---

## 1. Executive Summary
Dokumen ini merinci kebutuhan pengembangan untuk situs **alfurqon.akdmalam.my.id**. Fokus utama dari pembaruan ini adalah menambahkan sistem manajemen konten (CMS) mandiri untuk galeri dokumentasi kegiatan, integrasi widget keagamaan khusus (Kalender KHGT Muhammadiyah & Jadwal Sholat), serta asisten tanya jawab agama berbasis kecerdasan buatan (AI Chatbot) menggunakan basis data internal Putusan Tarjih Muhammadiyah.

Proyek ini diprioritaskan untuk memiliki tata letak responsif yang sangat solid, memastikan tampilan mobile (HP) memiliki kelengkapan elemen yang sama persis seperti versi desktop tanpa masalah penskalaan elemen yang terlalu besar.

---

## 2. Core Features & Functional Requirements

### 2.1. CMS Dokumentasi Kegiatan (Halaman Utama & Admin)
* **Halaman Utama (Frontend):**
    * **Posisi:** Terletak di bagian paling atas halaman utama, tepat setelah judul dan deskripsi web.
    * **Tampilan Desktop & Mobile:** Grid multi-kolom yang mendukung interaksi geser (Carousel) untuk efisiensi ruang di perangkat seluler.
    * **Elemen Data:** Foto kegiatan, Judul, Tanggal Kegiatan, Deskripsi/Keterangan singkat.
    * **Interaksi:** Klik pada foto/kartu dokumentasi akan membuka jendela pop-up (Modal) detail tanpa memindahkan user ke halaman baru.
* **Halaman Admin (Backend / CMS):**
    * **Autentikasi:** Dilindungi oleh mekanisme *Session Login* yang aman.
    * **Fitur CRUD Lengkap:** Admin dapat Menambah (Create), Melihat list (Read), Mengubah/Mengedit (Update), dan Menghapus (Delete) dokumentasi kegiatan.
    * **Optimasi Gambar Otomatis:** Sistem wajib melakukan *auto-compress* gambar yang diunggah ke format WebP/JPEG dengan resolusi proporsional (aspek rasio dikunci, misal 16:9) dan ukuran maksimal file di-hardlimit sebelum disimpan ke server (untuk mencegah overload hosting).

### 2.2. Jadwal Sholat & Widget Kalender KHGT Muhammadiyah
* **Tata Letak & Posisi:** Terletak tepat di bawah section Dokumentasi Kegiatan di halaman utama.
* **Struktur Visual:**
    * **Section Kiri/Atas:** Penghitung waktu mundur (Countdown) menuju waktu sholat berikutnya, bersanding sejajar secara horizontal dengan informasi Tanggal Kalender Hijriah Global Tunggal (KHGT) Muhammadiyah.
    * **Section Kanan/Bawah:** Tabel vertikal kebawah yang menampilkan jadwal sholat 5 waktu lengkap (Subuh, Dzuhur, Ashar, Maghrib, Isya) beserta Imsak/Syuruk.
* **Logic Kalender:** Menggunakan perhitungan hisab lokal/kriteria KHGT tetap tanpa dependensi penuh pada API eksternal yang tidak stabil guna menjaga performa situs.

### 2.3. Asisten AI Tanya Jawab (Tarjih Chatbot)
* **Mekanisme Backend (RAG):** Menggunakan metode *Retrieval-Augmented Generation* (RAG) dengan basis pengetahuan bersumber eksklusif dari dokumen resmi Putusan Tarjih Muhammadiyah.
* **Keamanan & Pembatasan Biaya (Cost Control):**
    * **Rate Limiting:** Diterapkan di level server di mana 1 IP Address dibatasi maksimal melakukan 5 kali pertanyaan per hari.
    * **Graceful Degradation:** Jika API AI mencapai limit bulanan atau mengalami gangguan, sistem tidak boleh crash atau menampilkan layar putih, melainkan harus memunculkan pesan error penolakan yang ramah secara visual (*"Server AI sedang sibuk, silakan coba beberapa saat lagi"*).
    * **Disclaimer Hukum:** Wajib menyertakan teks sanggahan yang menyatakan bahwa jawaban dihasilkan oleh AI dan memerlukan konfirmasi ulama/ustaz setempat.

---

## 3. UI/UX & Layout Constraints (Sangat Penting)
* **Keseragaman Elemen (PC vs HP):** Seluruh informasi dan komponen yang tampil di versi PC/Desktop **WAJIB** termuat di versi HP/Mobile. Tidak diperbolehkan menyembunyikan tabel jadwal sholat atau komponen kalender pada tampilan seluler.
* **Penskalaan UI (Responsive Scaling):** Komponen pada tampilan HP tidak boleh menjadi terlalu besar atau memakan ruang vertikal yang berlebihan seperti versi saat ini. Tipografi, ukuran tabel, padding, dan margin harus mengecil secara proporsional menggunakan unit responsif (seperti `rem`/`em` atau kalkulasi presentase yang matang di Figma).

---

## 4. Non-Functional Requirements
* **Keandalan Sistem:** Kegagalan pada API AI tidak boleh mengganggu operasional fitur utama web lainnya (CMS, Kalender, dan Jadwal Sholat).
* **Keamanan Data:** Endpoint CRUD admin harus tervalidasi dengan baik untuk mencegah injeksi data dari pihak luar yang tidak memiliki hak akses session.

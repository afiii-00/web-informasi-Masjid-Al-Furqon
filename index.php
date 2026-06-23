<?php
require_once 'config/koneksi.php';

// Detail Kontak Masjid (Silakan isi nilai di bawah ini)
$kontak_wa = "089512386045";
$kontak_web = "https://media-alfurqan.com/";

// Ambil info jumat terdekat (tanggal >= hari ini)
$query_jumat = mysqli_query($conn, "SELECT * FROM info_jumat WHERE tanggal_jumat >= CURDATE() ORDER BY tanggal_jumat ASC LIMIT 1");
$info_jumat = $query_jumat ? mysqli_fetch_assoc($query_jumat) : null;

// Ambil info kajian terdekat
$query_kajian = mysqli_query($conn, "SELECT * FROM kajian ORDER BY id_kajian DESC LIMIT 2");
$list_kajian = [];
if ($query_kajian) {
    while ($row = mysqli_fetch_assoc($query_kajian)) {
        $list_kajian[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Masjid Al-Furqon</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            500: '#10B981',
                            600: '#059669',
                            700: '#047857',
                        },
                        graybg: '#F3F4F6'
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #F3F4F6; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .hero-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('image.png');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="text-gray-800 antialiased">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center gap-3 select-none cursor-default" ondblclick="window.location.href='admin/login.php';">
                    <img src="image copy.png" alt="Logo Masjid" class="w-10 h-10 object-contain rounded-full shadow-lg bg-white p-1">
                    <span class="font-bold text-xl text-white tracking-wide" id="nav-brand">Masjid Al-Furqon</span>
                </div>
                <!-- Tombol Admin disembunyikan agar tidak diakses oleh jamaah/client umum -->
                <!-- 
                <div>
                    <a href="admin/login.php" class="bg-white/20 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-full font-medium transition-all duration-300 backdrop-blur-md border border-white/30 flex items-center gap-2">
                        <i class="ph ph-sign-in"></i> Admin
                    </a>
                </div>
                -->
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg relative pt-32 pb-20 lg:pt-48 lg:pb-32 flex items-center min-h-[70vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-sm font-semibold tracking-wider mb-4">AHLAN WA SAHLAN</span>
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">Web Informasi Jama'ah <br><span class="text-emerald-400">Masjid Al-Furqon</span></h1>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto mb-8">Membangun peradaban umat melalui masjid yang makmur, transparan, dan memberikan manfaat luas bagi lingkungan sekitar.</p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-10">
                <a href="#kegiatan" class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-full font-semibold transition-all duration-300 shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                    <i class="ph ph-calendar-check text-xl"></i> Informasi Kegiatan
                </a>
            </div>
            
            <!-- Jadwal Sholat Widget -->
            <div class="glass-panel max-w-4xl mx-auto rounded-3xl p-6 shadow-2xl translate-y-16 lg:translate-y-24">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 border-b border-gray-200 pb-4">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="ph-fill ph-clock text-emerald-500"></i> Jadwal Sholat Hari Ini
                    </h3>
                    <p class="text-sm font-medium text-gray-500" id="tanggal-masehi-hijriah">Memuat tanggal...</p>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4" id="jadwal-container">
                    <!-- Loading State -->
                    <div class="col-span-2 sm:col-span-3 md:col-span-5 text-center py-4 text-gray-500">
                        <i class="ph ph-spinner animate-spin text-2xl mb-2"></i>
                        <p>Mengambil data dari Kemenag RI...</p>
                    </div>
                </div>
                <p class="text-xs text-gray-400 text-right mt-4 mt-2">*Sumber data: API Kemenag RI (via MyQuran)</p>
            </div>
        </div>
    </section>

    <!-- Spacer for Hero Widget -->
    <div class="h-24 lg:h-32"></div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Info Kegiatan (Jumat & Kajian) -->
        <section id="kegiatan" class="mb-20">
            <div class="flex items-center gap-3 mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Informasi Kegiatan</h2>
                <div class="h-1 flex-1 bg-gradient-to-r from-emerald-500 to-transparent rounded-full opacity-20"></div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Card Jumat -->
                <div class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-md transition-shadow border border-gray-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-emerald-100 text-emerald-600 rounded-2xl">
                                <i class="ph-fill ph-users-three text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Petugas Jumat</h3>
                        </div>
                        <span class="bg-emerald-50 text-emerald-600 text-xs font-bold px-3 py-1 rounded-full">Pekan Ini</span>
                    </div>

                    <?php if($info_jumat): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-1">Tanggal</p>
                            <p class="font-medium text-gray-800"><?= date('d F Y', strtotime($info_jumat['tanggal_jumat'])) ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Khotib</p>
                                <p class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($info_jumat['nama_khotib']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($info_jumat['asal_instansi'] ?? '') ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Imam</p>
                                <p class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($info_jumat['nama_imam']) ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-gray-500">Belum ada data petugas Jumat terdekat.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Card Kajian -->
                <div class="bg-white rounded-3xl p-8 shadow-sm hover:shadow-md transition-shadow border border-gray-100 relative overflow-hidden group">
                     <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -z-10 group-hover:scale-110 transition-transform"></div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-blue-100 text-blue-600 rounded-2xl">
                                <i class="ph-fill ph-book-open-text text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800">Kajian Rutin</h3>
                        </div>
                        <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full">Terbaru</span>
                    </div>

                    <?php if(!empty($list_kajian)): ?>
                        <div class="space-y-4">
                            <?php foreach($list_kajian as $kajian): ?>
                                <div class="flex gap-4 items-start p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-blue-200 transition-colors">
                                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-500 flex-shrink-0">
                                        <i class="ph ph-microphone-stage text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 mb-1 leading-tight"><?= htmlspecialchars($kajian['tema_kajian']) ?></p>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                            <span class="flex items-center gap-1"><i class="ph-fill ph-user text-emerald-500"></i> <?= htmlspecialchars($kajian['pemateri']) ?></span>
                                            <span class="flex items-center gap-1"><i class="ph-fill ph-clock text-blue-500"></i> <?= htmlspecialchars($kajian['waktu']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 bg-gray-50 rounded-2xl border border-gray-100">
                            <p class="text-gray-500">Belum ada data kajian rutin.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Lokasi & Kontak Section -->
        <section id="kontak" class="mb-20">
            <div class="flex items-center gap-3 mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Lokasi & Kontak</h2>
                <div class="h-1 flex-1 bg-gradient-to-r from-emerald-500 to-transparent rounded-full opacity-20"></div>
            </div>

            <div class="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="grid lg:grid-cols-2 gap-10 items-stretch">
                    <!-- Detail Kontak -->
                    <div class="flex flex-col justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="ph-fill ph-envelope-open text-emerald-500"></i> Hubungi Kami
                            </h3>
                            <p class="text-gray-500 mb-8 leading-relaxed">
                                Silakan hubungi kami untuk informasi lebih lanjut mengenai kegiatan masjid, administrasi, dakwah, atau kunjungi langsung lokasi kami.
                            </p>
                            
                            <div class="space-y-6">
                                <!-- Alamat -->
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl flex-shrink-0">
                                        <i class="ph-fill ph-map-pin text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Alamat Masjid</p>
                                        <p class="font-medium text-gray-800 leading-relaxed">Jl. Merdeka No. 118, RT.04/RW.01, Menteng, Kec. Bogor Barat, Kota Bogor, Jawa Barat 16111</p>
                                    </div>
                                </div>

                                <!-- No HP / WhatsApp -->
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl flex-shrink-0">
                                        <i class="ph-fill ph-phone text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">No. WhatsApp / Telepon</p>
                                        <?php if (!empty($kontak_wa)): ?>
                                            <?php 
                                            $wa_link = preg_replace('/[^0-9]/', '', $kontak_wa);
                                            if (strpos($wa_link, '0') === 0) {
                                                $wa_link = '62' . substr($wa_link, 1);
                                            }
                                            ?>
                                            <a href="https://wa.me/<?= $wa_link ?>" target="_blank" class="font-medium text-emerald-600 hover:text-emerald-700 hover:underline transition-colors flex items-center gap-1">
                                                <?= htmlspecialchars($kontak_wa) ?> <i class="ph ph-arrow-square-out text-xs"></i>
                                            </a>
                                        <?php else: ?>
                                            <p class="font-medium text-gray-400 italic">[Belum diatur]</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Halaman Web -->
                                <div class="flex items-start gap-4">
                                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl flex-shrink-0">
                                        <i class="ph-fill ph-globe text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Halaman Web</p>
                                        <?php if (!empty($kontak_web)): ?>
                                            <a href="<?= htmlspecialchars($kontak_web) ?>" target="_blank" class="font-medium text-emerald-600 hover:text-emerald-700 hover:underline transition-colors flex items-center gap-1">
                                                <?= htmlspecialchars($kontak_web) ?> <i class="ph ph-arrow-square-out text-xs"></i>
                                            </a>
                                        <?php else: ?>
                                            <p class="font-medium text-gray-400 italic">[Belum diatur]</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Google Map Embed -->
                    <div class="rounded-3xl overflow-hidden shadow-inner border border-gray-100 min-h-[350px] lg:min-h-full relative group">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3963.3039775373673!2d106.7853782!3d-6.5879799!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69c5b3594ea9d1%3A0x1426a93fe27d0064!2sMasjid%20Jami&#39;%20Al-Furqon%20(Muhammadiyah)!5e0!3m2!1sid!2sid!4v1717862400000!5m2!1sid!2sid" 
                            width="100%" 
                            height="100%" 
                            style="border:0; min-height: 350px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-full object-cover">
                        </iframe>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <img src="image copy.png" alt="Logo Masjid" class="w-8 h-8 object-contain">
                <span class="font-bold text-gray-800">Masjid Al-Furqon</span>
            </div>
            <p class="text-gray-500 text-sm">© <?= date('Y') ?> Sistem Informasi Masjid. All rights reserved.</p>
        </div>
    </footer>

    <!-- Script Interaksi -->
    <script>
        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            const brand = document.getElementById('nav-brand');
            if (window.scrollY > 20) {
                nav.classList.add('bg-white', 'shadow-md');
                nav.classList.remove('py-2');
                brand.classList.remove('text-white');
                brand.classList.add('text-emerald-600');
            } else {
                nav.classList.remove('bg-white', 'shadow-md');
                nav.classList.add('py-2');
                brand.classList.add('text-white');
                brand.classList.remove('text-emerald-600');
            }
        });

        // Ambil Data Jadwal Sholat Kemenag via MyQuran API
        async function fetchJadwalSholat() {
            try {
                // Kota ID 1301 = Jakarta. Bisa diganti sesuai lokasi yang diinginkan.
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const date = String(today.getDate()).padStart(2, '0');
                
                const response = await fetch(`https://api.myquran.com/v2/sholat/jadwal/1301/${year}/${month}/${date}`);
                const result = await response.json();

                if (result.status) {
                    const data = result.data;
                    document.getElementById('tanggal-masehi-hijriah').innerText = data.jadwal.tanggal;

                    const times = [
                        { name: 'Subuh', time: data.jadwal.subuh },
                        { name: 'Dzuhur', time: data.jadwal.dzuhur },
                        { name: 'Ashar', time: data.jadwal.ashar },
                        { name: 'Maghrib', time: data.jadwal.maghrib },
                        { name: 'Isya', time: data.jadwal.isya }
                    ];

                    let html = '';
                    times.forEach((t) => {
                        html += `
                            <div class="bg-gray-50 hover:bg-emerald-50 rounded-2xl p-4 text-center border border-gray-100 hover:border-emerald-200 transition-all duration-300 group flex flex-col justify-center min-h-[100px]">
                                <p class="text-sm font-semibold text-emerald-600 uppercase tracking-wider mb-2">${t.name}</p>
                                <p class="text-3xl font-bold text-gray-800">${t.time}</p>
                            </div>
                        `;
                    });
                    document.getElementById('jadwal-container').innerHTML = html;
                }
            } catch (error) {
                document.getElementById('jadwal-container').innerHTML = `
                    <div class="col-span-2 sm:col-span-3 md:col-span-5 text-center py-4 text-red-500 text-sm">
                        Gagal memuat jadwal sholat. Pastikan Anda terhubung ke internet.
                    </div>
                `;
            }
        }

        // Load data saat pertama kali buka
        fetchJadwalSholat();
    </script>
</body>
</html>

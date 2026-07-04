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

// Ambil data dokumentasi kegiatan terbaru
$query_dok = mysqli_query($conn, "SELECT * FROM dokumentasi ORDER BY tanggal DESC, id_dok DESC LIMIT 6");
$list_dok = [];
if ($query_dok) {
    while ($row = mysqli_fetch_assoc($query_dok)) {
        $list_dok[] = $row;
    }
}

// Ambil Kalender KHGT dan ID Kota
$khgt_date = get_khgt_date_today($conn);
$current_kota_id = get_config($conn, 'kota_id');
$current_nama_kota = get_config($conn, 'nama_kota');
if (empty($current_kota_id)) $current_kota_id = '1222';
if (empty($current_nama_kota)) $current_nama_kota = 'KOTA BOGOR';
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
        /* Chatbot floating widget styles */
        #chatbot-panel {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .chat-message-user {
            background-color: #10B981;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .chat-message-bot {
            background-color: #F3F4F6;
            color: #1F2937;
            border-bottom-left-radius: 4px;
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
            <div class="glass-panel max-w-5xl mx-auto rounded-3xl p-6 md:p-8 shadow-2xl translate-y-16 lg:translate-y-24 text-left">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                    <!-- Section Kiri (Tanggal KHGT & Countdown) -->
                    <div class="lg:col-span-5 flex flex-col justify-between gap-6 border-b lg:border-b-0 lg:border-r border-gray-200/85 pb-6 lg:pb-0 lg:pr-8">
                        <div>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-wider">Lokasi: <?= htmlspecialchars($current_nama_kota) ?></span>
                            <h3 class="text-2xl font-extrabold text-gray-800 mt-3 flex items-center gap-2">
                                <i class="ph-fill ph-clock text-emerald-500"></i> Jadwal Sholat
                            </h3>
                        </div>
                        
                        <!-- Kalender KHGT & Gregorian -->
                        <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-100/50">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Kalender KHGT Muhammadiyah</p>
                            <p class="text-lg font-bold text-emerald-700 mt-1" id="tanggal-hijriah"><?= htmlspecialchars($khgt_date) ?></p>
                            <p class="text-sm text-gray-500 mt-1" id="tanggal-masehi">Memuat tanggal...</p>
                        </div>
                        
                        <!-- Countdown Sholat Berikutnya -->
                        <div class="bg-emerald-600 text-white rounded-2xl p-5 shadow-lg shadow-emerald-600/20 flex flex-col justify-center">
                            <p class="text-[10px] font-semibold text-emerald-200 uppercase tracking-wider" id="countdown-title">Menuju waktu sholat berikutnya</p>
                            <p class="text-3xl font-extrabold mt-1 tracking-tight" id="countdown-timer">--:--:--</p>
                            <p class="text-sm text-emerald-100 mt-1" id="countdown-prayer">Memuat waktu...</p>
                        </div>
                    </div>
                    
                    <!-- Section Kanan (Jadwal 5 Waktu + Imsak/Syuruk) -->
                    <div class="lg:col-span-7 flex flex-col justify-center">
                        <div class="divide-y divide-gray-100" id="jadwal-container">
                            <!-- Loading State -->
                            <div class="text-center py-12 text-gray-500">
                                <i class="ph ph-spinner animate-spin text-3xl mb-2 text-emerald-500"></i>
                                <p class="text-sm">Menghubungkan ke API Kemenag RI...</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 text-right mt-4 italic">*Sumber data: Kemenag RI (ID Kota: <?= htmlspecialchars($current_kota_id) ?>)</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Spacer for Hero Widget -->
    <div class="h-24 lg:h-32"></div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Dokumentasi Kegiatan -->
        <section id="dokumentasi-kegiatan" class="mb-20">
            <div class="flex items-center gap-3 mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Dokumentasi Kegiatan</h2>
                <div class="h-1 flex-1 bg-gradient-to-r from-emerald-500 to-transparent rounded-full opacity-20"></div>
            </div>

            <?php if (!empty($list_dok)): ?>
                <!-- Grid on Desktop, Horizontal Scroll (Carousel-like) on Mobile -->
                <div class="flex overflow-x-auto md:grid md:grid-cols-3 gap-6 pb-4 md:pb-0 snap-x scrollbar-thin scrollbar-thumb-gray-200">
                    <?php foreach ($list_dok as $dok): ?>
                        <div onclick="openDokumentasiModal('<?= htmlspecialchars($dok['judul'], ENT_QUOTES) ?>', '<?= date('d F Y', strtotime($dok['tanggal'])) ?>', '<?= htmlspecialchars($dok['deskripsi'], ENT_QUOTES) ?>', 'uploads/<?= htmlspecialchars($dok['foto']) ?>')" 
                             class="flex-shrink-0 w-[280px] md:w-auto snap-start bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-100 transition-all duration-300 cursor-pointer group">
                            <div class="relative overflow-hidden aspect-[16/10]">
                                <img src="uploads/<?= htmlspecialchars($dok['foto']) ?>" 
                                     alt="<?= htmlspecialchars($dok['judul']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                    <span class="text-white text-xs font-semibold flex items-center gap-1">
                                        <i class="ph ph-magnifying-glass-plus text-base"></i> Lihat Detail
                                    </span>
                                </div>
                            </div>
                            <div class="p-5">
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                                    <?= date('d M Y', strtotime($dok['tanggal'])) ?>
                                </span>
                                <h3 class="font-bold text-gray-800 text-lg mt-3 mb-2 line-clamp-1 group-hover:text-emerald-600 transition-colors">
                                    <?= htmlspecialchars($dok['judul']) ?>
                                </h3>
                                <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars($dok['deskripsi']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-3xl border border-gray-100">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 mx-auto mb-3">
                        <i class="ph ph-image text-3xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">Belum ada dokumentasi kegiatan yang diunggah.</p>
                </div>
            <?php endif; ?>
        </section>

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
 
    <!-- Modal Detail Dokumentasi -->
    <div id="dokModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDokumentasiModal()"></div>
        <!-- Content Card -->
        <div id="dokModalCard" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden z-10 transition-all duration-300 scale-95 opacity-0">
            <button onclick="closeDokumentasiModal()" class="absolute top-4 right-4 w-10 h-10 bg-black/40 hover:bg-black/60 text-white rounded-full flex items-center justify-center transition-colors z-20">
                <i class="ph ph-x text-xl"></i>
            </button>
            <div class="aspect-[16/10] w-full bg-gray-100 relative">
                <img src="" id="modalDokImg" alt="" class="w-full h-full object-cover">
            </div>
            <div class="p-6 md:p-8">
                <div class="flex items-center gap-3 mb-3">
                    <span id="modalDokTanggal" class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full"></span>
                </div>
                <h3 id="modalDokJudul" class="text-2xl font-bold text-gray-800 mb-4 leading-tight"></h3>
                <div class="max-h-[150px] overflow-y-auto pr-2 text-gray-600 text-sm leading-relaxed whitespace-pre-line" id="modalDokDeskripsi"></div>
            </div>
        </div>
    </div>

    <!-- Floating AI Chatbot Widget -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        <!-- Chat Panel -->
        <div id="chatbot-panel" class="hidden w-[320px] sm:w-[360px] h-[480px] bg-white rounded-3xl overflow-hidden border border-gray-100 flex flex-col mb-4 shadow-2xl transition-all duration-300 scale-95 opacity-0 origin-bottom-right">
            <!-- Header -->
            <div class="bg-emerald-600 p-4 text-white flex justify-between items-center shadow-md flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-emerald-500 rounded-2xl flex items-center justify-center text-white">
                        <i class="ph-fill ph-robot text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">Tanya Jawab AI Tarjih</h4>
                        <span class="text-[10px] text-emerald-200">Masjid Al-Furqon</span>
                    </div>
                </div>
                <button onclick="toggleChatbot()" class="w-8 h-8 rounded-lg hover:bg-emerald-700/50 flex items-center justify-center transition-colors">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
            
            <!-- Message Area -->
            <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto space-y-4 text-xs bg-gray-50/50 scrollbar-thin">
                <!-- Welcome Msg -->
                <div class="flex items-start gap-2 max-w-[85%]">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="ph-fill ph-robot text-base"></i>
                    </div>
                    <div class="chat-message-bot p-3 rounded-2xl">
                        <p class="leading-relaxed">Ahlan wa Sahlan! Saya Asisten AI Masjid Al-Furqon. Tanyakan apa saja seputar keputusan keagamaan atau Putusan Tarjih Muhammadiyah.</p>
                    </div>
                </div>
            </div>
            
            <!-- Input Form -->
            <form id="chatbot-form" onsubmit="submitChat(event)" class="p-3 border-t border-gray-100 bg-white flex-shrink-0">
                <div class="flex gap-2 items-center">
                    <input type="text" id="chatbot-input" placeholder="Tulis pertanyaan Anda..."
                        class="flex-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs">
                    <button type="submit" class="w-8 h-8 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl flex items-center justify-center shadow-md shadow-emerald-500/20 transition-colors flex-shrink-0">
                        <i class="ph-fill ph-paper-plane-tilt text-base"></i>
                    </button>
                </div>
                <p class="text-[9px] text-gray-400 text-center mt-2 leading-tight">
                    *Jawaban AI. Untuk fatwa hukum final, silakan berkonsultasi dengan ustaz/ulama setempat.
                </p>
            </form>
        </div>

        <!-- Float Trigger Button -->
        <button id="chatbot-trigger" onclick="toggleChatbot()" class="w-14 h-14 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30 transition-all duration-300 hover:scale-105">
            <i class="ph ph-chat-circle-dots text-3xl" id="chatbot-trigger-icon"></i>
        </button>
    </div>

    <!-- Script Interaksi -->
    <script>
        // Modal Dokumentasi Handlers
        function openDokumentasiModal(judul, tanggal, deskripsi, fotoPath) {
            document.getElementById('modalDokImg').src = fotoPath;
            document.getElementById('modalDokJudul').innerText = judul;
            document.getElementById('modalDokTanggal').innerText = tanggal;
            document.getElementById('modalDokDeskripsi').innerText = deskripsi;
            
            const modal = document.getElementById('dokModal');
            const card = document.getElementById('dokModalCard');
            
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            });
        }
        
        function closeDokumentasiModal() {
            const modal = document.getElementById('dokModal');
            const card = document.getElementById('dokModalCard');
            
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        // Chatbot Panel Toggles
        function toggleChatbot() {
            const panel = document.getElementById('chatbot-panel');
            const icon = document.getElementById('chatbot-trigger-icon');
            
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                requestAnimationFrame(() => {
                    panel.classList.remove('scale-95', 'opacity-0');
                    panel.classList.add('scale-100', 'opacity-100');
                });
                icon.className = "ph ph-x text-3xl";
            } else {
                panel.classList.remove('scale-100', 'opacity-100');
                panel.classList.add('scale-95', 'opacity-0');
                icon.className = "ph ph-chat-circle-dots text-3xl";
                setTimeout(() => {
                    panel.classList.add('hidden');
                }, 300);
            }
        }

        async function submitChat(e) {
            e.preventDefault();
            const input = document.getElementById('chatbot-input');
            const message = input.value.trim();
            if (!message) return;
            
            input.value = '';
            appendMessage(message, 'user');
            
            const loadingId = 'loading-' + Date.now();
            appendLoading(loadingId);
            
            try {
                const formData = new FormData();
                formData.append('message', message);
                
                const response = await fetch('chat_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                removeLoading(loadingId);
                
                if (result.status === 'success') {
                    appendMessage(result.data, 'bot');
                } else {
                    appendMessage(result.message, 'bot', true);
                }
            } catch (error) {
                removeLoading(loadingId);
                appendMessage('Server AI sedang mengalami gangguan koneksi. Silakan coba beberapa saat lagi.', 'bot', true);
            }
        }

        function appendMessage(text, sender, isError = false) {
            const container = document.getElementById('chatbot-messages');
            const msgDiv = document.createElement('div');
            
            if (sender === 'user') {
                msgDiv.className = 'flex justify-end';
                msgDiv.innerHTML = `
                    <div class="chat-message-user p-3 rounded-2xl max-w-[85%]">
                        <p class="leading-relaxed">${escapeHtml(text)}</p>
                    </div>
                `;
            } else {
                msgDiv.className = 'flex items-start gap-2 max-w-[85%]';
                const textFormatted = text.replace(/\n/g, '<br>');
                const bgClass = isError ? 'bg-red-50 text-red-700 border border-red-100' : 'chat-message-bot';
                const iconClass = isError ? 'ph-fill ph-warning-circle text-red-500' : 'ph-fill ph-robot text-emerald-600';
                const iconBg = isError ? 'bg-red-50' : 'bg-emerald-100';
                
                msgDiv.innerHTML = `
                    <div class="w-8 h-8 ${iconBg} rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="${iconClass} text-base"></i>
                    </div>
                    <div class="${bgClass} p-3 rounded-2xl">
                        <p class="leading-relaxed">${textFormatted}</p>
                    </div>
                `;
            }
            
            container.appendChild(msgDiv);
            container.scrollTop = container.scrollHeight;
        }

        function appendLoading(id) {
            const container = document.getElementById('chatbot-messages');
            const loadDiv = document.createElement('div');
            loadDiv.id = id;
            loadDiv.className = 'flex items-start gap-2 max-w-[85%]';
            loadDiv.innerHTML = `
                <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="ph-fill ph-robot text-base"></i>
                </div>
                <div class="chat-message-bot p-3 rounded-2xl flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </div>
            `;
            container.appendChild(loadDiv);
            container.scrollTop = container.scrollHeight;
        }

        function removeLoading(id) {
            const elem = document.getElementById(id);
            if (elem) elem.remove();
        }

        function escapeHtml(string) {
            return String(string).replace(/[&<>"']/g, function (s) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[s];
            });
        }


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
        let prayerSchedule = {};
        
        async function fetchJadwalSholat() {
            try {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const date = String(today.getDate()).padStart(2, '0');
                
                const response = await fetch(`https://api.myquran.com/v2/sholat/jadwal/<?= $current_kota_id ?>/${year}/${month}/${date}`);
                const result = await response.json();

                if (result.status) {
                    const data = result.data;
                    document.getElementById('tanggal-masehi').innerText = data.jadwal.tanggal;

                    prayerSchedule = {
                        'Imsak': data.jadwal.imsak,
                        'Subuh': data.jadwal.subuh,
                        'Terbit': data.jadwal.terbit,
                        'Dzuhur': data.jadwal.dzuhur,
                        'Ashar': data.jadwal.ashar,
                        'Maghrib': data.jadwal.maghrib,
                        'Isya': data.jadwal.isya
                    };

                    renderPrayerSchedule();
                    startCountdown();
                }
            } catch (error) {
                document.getElementById('jadwal-container').innerHTML = `
                    <div class="text-center py-8 text-red-500 text-sm">
                        Gagal memuat jadwal sholat. Pastikan koneksi internet aktif.
                    </div>
                `;
            }
        }

        function renderPrayerSchedule() {
            let html = '';
            for (const [name, time] of Object.entries(prayerSchedule)) {
                html += `
                    <div id="row-${name}" class="flex justify-between items-center py-2.5 px-4 rounded-xl transition-colors">
                        <span class="font-semibold text-gray-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-gray-300 status-dot"></span>
                            ${name}
                        </span>
                        <span class="font-bold text-lg text-gray-800">${time}</span>
                    </div>
                `;
            }
            document.getElementById('jadwal-container').innerHTML = html;
        }

        function startCountdown() {
            setInterval(updateCountdown, 1000);
            updateCountdown();
        }

        function updateCountdown() {
            const now = new Date();
            const currentTime = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
            
            let nextPrayerName = '';
            let nextPrayerTimeStr = '';
            let nextPrayerSeconds = 0;
            
            const list = [];
            for (const [name, timeStr] of Object.entries(prayerSchedule)) {
                if (name === 'Terbit') continue;
                const [h, m] = timeStr.split(':').map(Number);
                const seconds = h * 3600 + m * 60;
                list.push({ name, seconds, timeStr });
            }
            
            let found = false;
            for (let i = 0; i < list.length; i++) {
                if (list[i].seconds > currentTime) {
                    nextPrayerName = list[i].name;
                    nextPrayerTimeStr = list[i].timeStr;
                    nextPrayerSeconds = list[i].seconds;
                    found = true;
                    break;
                }
            }
            
            if (!found) {
                nextPrayerName = 'Subuh';
                nextPrayerTimeStr = prayerSchedule['Subuh'];
                const [h, m] = nextPrayerTimeStr.split(':').map(Number);
                nextPrayerSeconds = h * 3600 + m * 60 + 24 * 3600;
            }
            
            let diff = nextPrayerSeconds - currentTime;
            
            const hours = String(Math.floor(diff / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const seconds = String(diff % 60).padStart(2, '0');
            
            document.getElementById('countdown-timer').innerText = `${hours}:${minutes}:${seconds}`;
            document.getElementById('countdown-prayer').innerText = `Menuju waktu ${nextPrayerName} (${nextPrayerTimeStr})`;
            
            for (const name of Object.keys(prayerSchedule)) {
                const row = document.getElementById(`row-${name}`);
                if (row) {
                    row.className = "flex justify-between items-center py-2.5 px-4 rounded-xl transition-colors";
                    const dot = row.querySelector('.status-dot');
                    if (dot) dot.className = "w-2 h-2 rounded-full bg-gray-300 status-dot";
                }
            }
            
            const activeRow = document.getElementById(`row-${nextPrayerName}`);
            if (activeRow) {
                activeRow.className = "flex justify-between items-center py-2.5 px-4 rounded-xl bg-emerald-50 border border-emerald-100/50 text-emerald-950 font-bold transition-colors";
                const dot = activeRow.querySelector('.status-dot');
                if (dot) dot.className = "w-2 h-2 rounded-full bg-emerald-500 animate-pulse status-dot";
            }
        }

        fetchJadwalSholat();
    </script>
</body>
</html>

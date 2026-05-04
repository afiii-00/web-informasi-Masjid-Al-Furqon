<?php
require_once 'config/koneksi.php';

// Ambil 5 transaksi terakhir untuk tabel keuangan
$query_kas = mysqli_query($conn, "SELECT * FROM kas_masjid ORDER BY tanggal DESC, id_kas DESC LIMIT 5");
$kas_terakhir = [];
while ($row = mysqli_fetch_assoc($query_kas)) {
    $kas_terakhir[] = $row;
}

// Ambil ringkasan saldo keuangan
$query_saldo = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN tipe = 'Pemasukan' THEN nominal ELSE 0 END) as total_masuk,
        SUM(CASE WHEN tipe = 'Pengeluaran' THEN nominal ELSE 0 END) as total_keluar
    FROM kas_masjid
");
$saldo_data = mysqli_fetch_assoc($query_saldo);
$total_masuk = $saldo_data['total_masuk'] ?? 0;
$total_keluar = $saldo_data['total_keluar'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

// Ambil info jumat terdekat (tanggal >= hari ini)
$query_jumat = mysqli_query($conn, "SELECT * FROM info_jumat WHERE tanggal_jumat >= CURDATE() ORDER BY tanggal_jumat ASC LIMIT 1");
$info_jumat = mysqli_fetch_assoc($query_jumat);

// Ambil info kajian terdekat
$query_kajian = mysqli_query($conn, "SELECT * FROM kajian ORDER BY id_kajian DESC LIMIT 2");
$list_kajian = [];
while ($row = mysqli_fetch_assoc($query_kajian)) {
    $list_kajian[] = $row;
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
                <div class="flex items-center gap-3">
                    <img src="image copy.png" alt="Logo Masjid" class="w-10 h-10 object-contain rounded-full shadow-lg bg-white p-1">
                    <span class="font-bold text-xl text-white tracking-wide" id="nav-brand">Masjid Al-Furqon</span>
                </div>
                <div>
                    <a href="admin/login.php" class="bg-white/20 hover:bg-emerald-500 text-white px-5 py-2.5 rounded-full font-medium transition-all duration-300 backdrop-blur-md border border-white/30 flex items-center gap-2">
                        <i class="ph ph-sign-in"></i> Admin
                    </a>
                </div>
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
                <a href="#keuangan" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-6 py-3 rounded-full font-semibold transition-all duration-300 backdrop-blur-md flex items-center gap-2">
                    <i class="ph ph-wallet text-xl"></i> Transparansi Keuangan
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

        <!-- Transparansi Keuangan -->
        <section id="keuangan" class="mb-20">
             <div class="flex items-center gap-3 mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Transparansi Keuangan</h2>
                <div class="h-1 flex-1 bg-gradient-to-r from-emerald-500 to-transparent rounded-full opacity-20"></div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Ringkasan Saldo -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-emerald-500 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
                        <i class="ph-fill ph-wallet absolute -right-6 -bottom-6 text-9xl text-white opacity-20"></i>
                        <p class="text-emerald-100 font-medium mb-1">Total Saldo Kas</p>
                        <h3 class="text-4xl font-bold mb-6 tracking-tight">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center bg-white/20 p-3 rounded-2xl backdrop-blur-sm">
                                <span class="text-sm flex items-center gap-2"><i class="ph-fill ph-arrow-circle-down text-green-300"></i> Pemasukan</span>
                                <span class="font-semibold">Rp <?= number_format($total_masuk, 0, ',', '.') ?></span>
                            </div>
                            <div class="flex justify-between items-center bg-white/20 p-3 rounded-2xl backdrop-blur-sm">
                                <span class="text-sm flex items-center gap-2"><i class="ph-fill ph-arrow-circle-up text-red-300"></i> Pengeluaran</span>
                                <span class="font-semibold">Rp <?= number_format($total_keluar, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Riwayat 5 Transaksi -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <h3 class="font-bold text-gray-800">5 Transaksi Terakhir</h3>
                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                <i class="ph ph-receipt"></i>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-xs uppercase text-gray-500 bg-gray-50/80 border-b border-gray-100">
                                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                                        <th class="px-6 py-4 font-semibold">Keterangan</th>
                                        <th class="px-6 py-4 font-semibold">Tipe</th>
                                        <th class="px-6 py-4 font-semibold text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-sm">
                                    <?php if(empty($kas_terakhir)): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat transaksi.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($kas_terakhir as $kas): ?>
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?= date('d/m/Y', strtotime($kas['tanggal'])) ?></td>
                                                <td class="px-6 py-4 text-gray-800 font-medium"><?= htmlspecialchars($kas['keterangan']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <?php if($kas['tipe'] == 'Pemasukan'): ?>
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Pemasukan
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Pengeluaran
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right font-semibold <?= $kas['tipe'] == 'Pemasukan' ? 'text-green-600' : 'text-red-600' ?>">
                                                    <?= $kas['tipe'] == 'Pemasukan' ? '+' : '-' ?> Rp <?= number_format($kas['nominal'], 0, ',', '.') ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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

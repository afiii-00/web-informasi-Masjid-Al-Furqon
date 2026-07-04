<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';

// Ambil data konfigurasi saat ini
$current_api_key = get_config($conn, 'gemini_api_key', true); // decrypted
$current_kota_id = get_config($conn, 'kota_id');
$current_nama_kota = get_config($conn, 'nama_kota');

if (isset($_POST['simpan_pengaturan'])) {
    $kota_id = mysqli_real_escape_string($conn, $_POST['kota_id']);
    $nama_kota = mysqli_real_escape_string($conn, $_POST['nama_kota']);
    $api_key = $_POST['gemini_api_key']; // raw input, we will encrypt it
    
    // Simpan Kota ID & Nama Kota
    set_config($conn, 'kota_id', $kota_id);
    set_config($conn, 'nama_kota', $nama_kota);
    
    // Hanya simpan API Key jika diinputkan (tidak dikosongkan)
    if (!empty($api_key)) {
        set_config($conn, 'gemini_api_key', $api_key, true); // true = encrypt
        $current_api_key = $api_key;
    }
    
    $current_kota_id = $kota_id;
    $current_nama_kota = $nama_kota;
    
    $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Pengaturan berhasil diperbarui!</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Portal - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>body { font-family: 'Outfit', sans-serif; background-color: #F3F4F6; }</style>
</head>
<body class="flex bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-64 bg-white h-screen shadow-sm border-r border-gray-100 fixed">
        <div class="h-20 flex items-center px-8 border-b border-gray-100">
            <h1 class="text-xl font-bold text-emerald-600 flex items-center gap-2">
                <i class="ph-fill ph-mosque"></i> Admin DKM
            </h1>
        </div>
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-squares-four text-xl"></i> Dashboard
            </a>
            <a href="dokumentasi.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-image text-xl"></i> Dokumentasi
            </a>
            <a href="jumat.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-users-three text-xl"></i> Petugas Jumat
            </a>
            <a href="kajian.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-book-open-text text-xl"></i> Kajian Rutin
            </a>
            <a href="tarjih.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-chat-circle-text text-xl"></i> Tanya Jawab AI
            </a>
            <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-medium">
                <i class="ph-fill ph-gear text-xl"></i> Pengaturan
            </a>
            <hr class="my-4 border-gray-100 font-medium">
            <a href="logout.php" onclick="return confirm('Yakin ingin logout?')" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-sign-out text-xl"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 p-8 w-full min-h-screen">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Pengaturan Portal Web</h2>
            <p class="text-gray-500">Konfigurasi kunci API kecerdasan buatan (Gemini) dan ID lokasi jadwal sholat Kemenag.</p>
        </div>

        <?= $pesan ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Form Pengaturan -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2 text-lg border-b border-gray-100 pb-4">
                        <i class="ph-fill ph-gear text-emerald-500"></i> Konfigurasi Sistem
                    </h3>
                    
                    <form action="" method="POST" class="space-y-6">
                        <!-- Gemini API Key -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Gemini API Key</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="ph ph-key"></i>
                                </span>
                                <input type="password" name="gemini_api_key" id="api_key_input"
                                    class="w-full pl-10 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"
                                    placeholder="<?= !empty($current_api_key) ? '••••••••••••••••••••••••••••••••' : 'Masukkan Gemini API Key baru' ?>">
                                <button type="button" onclick="toggleApiKeyVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="ph ph-eye" id="api_key_eye"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                <?php if (!empty($current_api_key)): ?>
                                    <span class="text-green-600 font-medium flex items-center gap-1 mt-1">
                                        <i class="ph-fill ph-check-circle"></i> Kunci API sudah tersimpan dan di-enkripsi dengan aman.
                                    </span>
                                <?php else: ?>
                                    <span class="text-amber-600 font-medium flex items-center gap-1 mt-1">
                                        <i class="ph-fill ph-warning"></i> Kunci API belum tersimpan. Fitur chatbot AI tidak akan berfungsi.
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- ID & Nama Kota -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">ID Kota Kemenag</label>
                                <input type="text" name="kota_id" id="kota_id" required value="<?= htmlspecialchars($current_kota_id) ?>"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kota/Kabupaten</label>
                                <input type="text" name="nama_kota" id="nama_kota" required value="<?= htmlspecialchars($current_nama_kota) ?>"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-100">
                            <button type="submit" name="simpan_pengaturan" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-6 py-2.5 rounded-xl transition-colors shadow-lg shadow-emerald-500/20">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Widget Pencarian ID Kota -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ph ph-magnifying-glass text-emerald-500"></i> Cari ID Kota Kemenag
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">Gunakan widget ini untuk mencari ID Kota berdasarkan database Kementerian Agama RI.</p>
                    
                    <div class="space-y-4">
                        <div class="flex gap-2">
                            <input type="text" id="search_city_input" placeholder="Cth: Bogor / Bandung"
                                class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                            <button type="button" onclick="searchCity()" id="btn_search_city"
                                class="bg-emerald-50 text-emerald-600 hover:bg-emerald-100 font-semibold px-4 py-2 rounded-xl text-sm transition-colors">
                                Cari
                            </button>
                        </div>
                        
                        <div id="search_results" class="border border-gray-100 rounded-xl max-h-[220px] overflow-y-auto divide-y divide-gray-100 text-sm hidden">
                            <!-- Hasil pencarian dimasukkan di sini via JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Toggle visibility API key
        function toggleApiKeyVisibility() {
            const input = document.getElementById('api_key_input');
            const eye = document.getElementById('api_key_eye');
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('ph-eye');
                eye.classList.add('ph-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.remove('ph-eye-slash');
                eye.classList.add('ph-eye');
            }
        }

        // AJAX search kota kemenag
        async function searchCity() {
            const keyword = document.getElementById('search_city_input').value.trim();
            const resultsDiv = document.getElementById('search_results');
            const btn = document.getElementById('btn_search_city');
            
            if (keyword.length < 2) {
                alert('Masukkan minimal 2 karakter kota!');
                return;
            }
            
            btn.disabled = true;
            btn.innerText = 'Proses...';
            resultsDiv.classList.remove('hidden');
            resultsDiv.innerHTML = '<div class="p-4 text-center text-xs text-gray-500"><i class="ph ph-spinner animate-spin text-lg mb-1 inline-block"></i><br>Mencari kota...</div>';
            
            try {
                const response = await fetch(`https://api.myquran.com/v2/sholat/kota/cari/${encodeURIComponent(keyword)}`);
                const result = await response.json();
                
                if (result.status && result.data && result.data.length > 0) {
                    let html = '';
                    result.data.forEach(item => {
                        html += `
                            <div onclick="selectCity('${item.id}', '${item.lokasi}')" 
                                 class="p-3 hover:bg-emerald-50 cursor-pointer transition-colors flex justify-between items-center">
                                <span class="font-medium text-gray-700">${item.lokasi}</span>
                                <span class="font-mono text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">${item.id}</span>
                            </div>
                        `;
                    });
                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = '<div class="p-4 text-center text-xs text-gray-500">Kota tidak ditemukan.</div>';
                }
            } catch (error) {
                resultsDiv.innerHTML = '<div class="p-4 text-center text-xs text-red-500">Gagal mengambil data dari API Kemenag!</div>';
            } finally {
                btn.disabled = false;
                btn.innerText = 'Cari';
            }
        }
        
        function selectCity(id, nama) {
            document.getElementById('kota_id').value = id;
            document.getElementById('nama_kota').value = nama;
            document.getElementById('search_results').classList.add('hidden');
            document.getElementById('search_results').innerHTML = '';
            document.getElementById('search_city_input').value = '';
        }
    </script>
</body>
</html>

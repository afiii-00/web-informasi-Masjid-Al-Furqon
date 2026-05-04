<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';

// Proses Tambah Data
if (isset($_POST['tambah_kajian'])) {
    $tema_kajian = mysqli_real_escape_string($conn, $_POST['tema_kajian']);
    $pemateri = mysqli_real_escape_string($conn, $_POST['pemateri']);
    $waktu = mysqli_real_escape_string($conn, $_POST['waktu']);

    $query = "INSERT INTO kajian (tema_kajian, pemateri, waktu) VALUES ('$tema_kajian', '$pemateri', '$waktu')";
    if (mysqli_query($conn, $query)) {
        $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Data kajian berhasil ditambahkan!</div>";
    } else {
        $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal menambahkan data: " . mysqli_error($conn) . "</div>";
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kajian WHERE id_kajian = '$id'");
    header("Location: kajian.php");
    exit;
}

// Ambil Data Kajian
$result = mysqli_query($conn, "SELECT * FROM kajian ORDER BY id_kajian DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kajian - Admin</title>
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
            <a href="kas.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-wallet text-xl"></i> Kas Masjid
            </a>
            <a href="jumat.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-users-three text-xl"></i> Petugas Jumat
            </a>
            <a href="kajian.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-medium">
                <i class="ph-fill ph-book-open-text text-xl"></i> Kajian Rutin
            </a>
            <hr class="my-4 border-gray-100">
            <a href="logout.php" onclick="return confirm('Yakin ingin logout?')" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-sign-out text-xl"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 p-8 w-full">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Kajian Rutin</h2>
            <p class="text-gray-500">Kelola informasi jadwal dan tema kajian di masjid.</p>
        </div>

        <?= $pesan ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Form Tambah -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ph-fill ph-plus-circle text-emerald-500"></i> Tambah Kajian
                    </h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tema Kajian</label>
                            <input type="text" name="tema_kajian" required placeholder="Cth: Tafsir Al-Baqarah"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pemateri</label>
                            <input type="text" name="pemateri" required placeholder="Ust. Fulan, Lc"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Waktu</label>
                            <input type="text" name="waktu" required placeholder="Ba'da Maghrib"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <button type="submit" name="tambah_kajian" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 rounded-xl transition-colors">
                            Simpan Data
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs uppercase text-gray-500 bg-gray-50/80 border-b border-gray-100">
                                    <th class="px-6 py-4 font-semibold">Tema Kajian</th>
                                    <th class="px-6 py-4 font-semibold">Pemateri</th>
                                    <th class="px-6 py-4 font-semibold">Waktu</th>
                                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(mysqli_num_rows($result) == 0): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada data kajian.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4 font-bold text-gray-800"><?= htmlspecialchars($row['tema_kajian']) ?></td>
                                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($row['pemateri']) ?></td>
                                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($row['waktu']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="?hapus=<?= $row['id_kajian'] ?>" onclick="return confirm('Hapus kajian ini?')" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-colors">
                                                    <i class="ph ph-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';

// Proses Tambah Data
if (isset($_POST['tambah_kas'])) {
    $tanggal = $_POST['tanggal'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tipe = $_POST['tipe'];
    $nominal = str_replace(['Rp', '.', ' '], '', $_POST['nominal']);

    $query = "INSERT INTO kas_masjid (tanggal, keterangan, tipe, nominal) VALUES ('$tanggal', '$keterangan', '$tipe', '$nominal')";
    if (mysqli_query($conn, $query)) {
        $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Data kas berhasil ditambahkan!</div>";
    } else {
        $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal menambahkan data: " . mysqli_error($conn) . "</div>";
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kas_masjid WHERE id_kas = '$id'");
    header("Location: kas.php");
    exit;
}

// Ambil Data Kas
$result = mysqli_query($conn, "SELECT * FROM kas_masjid ORDER BY tanggal DESC, id_kas DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kas - Admin</title>
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
            <a href="kas.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-medium">
                <i class="ph-fill ph-wallet text-xl"></i> Kas Masjid
            </a>
            <a href="jumat.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-users-three text-xl"></i> Petugas Jumat
            </a>
            <a href="kajian.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-book-open-text text-xl"></i> Kajian Rutin
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
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Kas Masjid</h2>
            <p class="text-gray-500">Kelola pemasukan dan pengeluaran dana kas masjid.</p>
        </div>

        <?= $pesan ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Form Tambah -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ph-fill ph-plus-circle text-emerald-500"></i> Tambah Transaksi
                    </h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                            <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <input type="text" name="keterangan" required placeholder="Cth: Kotak Amal Jumat"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Transaksi</label>
                            <select name="tipe" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                                <option value="Pemasukan">Pemasukan (+)</option>
                                <option value="Pengeluaran">Pengeluaran (-)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                            <input type="number" name="nominal" required placeholder="500000"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <button type="submit" name="tambah_kas" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 rounded-xl transition-colors">
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
                                    <th class="px-6 py-4 font-semibold">Tanggal</th>
                                    <th class="px-6 py-4 font-semibold">Keterangan</th>
                                    <th class="px-6 py-4 font-semibold">Tipe</th>
                                    <th class="px-6 py-4 font-semibold">Nominal</th>
                                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(mysqli_num_rows($result) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data transaksi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                            <td class="px-6 py-4 text-gray-800 font-medium"><?= htmlspecialchars($row['keterangan']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if($row['tipe'] == 'Pemasukan'): ?>
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                                        Pemasukan
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                                        Pengeluaran
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-semibold <?= $row['tipe'] == 'Pemasukan' ? 'text-green-600' : 'text-red-600' ?>">
                                                Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="?hapus=<?= $row['id_kas'] ?>" onclick="return confirm('Hapus transaksi ini?')" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-colors">
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

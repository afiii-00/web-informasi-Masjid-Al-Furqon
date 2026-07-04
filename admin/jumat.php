<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';
$today = date('Y-m-d');

// Proses Tambah Data
if (isset($_POST['tambah_jumat'])) {
    $tanggal_jumat = $_POST['tanggal_jumat'];
    $nama_khotib = mysqli_real_escape_string($conn, $_POST['nama_khotib']);
    $asal_instansi = mysqli_real_escape_string($conn, $_POST['asal_instansi']);
    $nama_imam = mysqli_real_escape_string($conn, $_POST['nama_imam']);

    $query = "INSERT INTO info_jumat (tanggal_jumat, nama_khotib, asal_instansi, nama_imam) 
              VALUES ('$tanggal_jumat', '$nama_khotib', '$asal_instansi', '$nama_imam')";
    if (mysqli_query($conn, $query)) {
        $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Data petugas Jumat berhasil ditambahkan!</div>";
    } else {
        $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal menambahkan data: " . mysqli_error($conn) . "</div>";
    }
}

// Proses Edit Data
if (isset($_POST['edit_jumat'])) {
    $id_edit = intval($_POST['id_edit']);
    $tanggal_jumat = $_POST['tanggal_jumat_edit'];
    $nama_khotib = mysqli_real_escape_string($conn, $_POST['nama_khotib_edit']);
    $asal_instansi = mysqli_real_escape_string($conn, $_POST['asal_instansi_edit']);
    $nama_imam = mysqli_real_escape_string($conn, $_POST['nama_imam_edit']);

    $query = "UPDATE info_jumat SET tanggal_jumat='$tanggal_jumat', nama_khotib='$nama_khotib', asal_instansi='$asal_instansi', nama_imam='$nama_imam' WHERE id_info='$id_edit'";
    if (mysqli_query($conn, $query)) {
        $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Data jadwal berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM info_jumat WHERE id_info='$id'");
    header("Location: jumat.php");
    exit;
}

// Ambil Data Jumat
$result = mysqli_query($conn, "SELECT * FROM info_jumat ORDER BY tanggal_jumat DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jumat - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #F3F4F6; }

        /* Modal backdrop */
        #editModal {
            transition: opacity 0.2s ease;
        }
        #editModal.hidden { display: none; }

        /* Modal card animation */
        #modalCard {
            transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease;
        }
        #editModal.hidden #modalCard {
            transform: scale(0.9);
            opacity: 0;
        }
        #editModal:not(.hidden) #modalCard {
            transform: scale(1);
            opacity: 1;
        }

        /* Past-date row styling */
        .row-past {
            opacity: 0.6;
        }
        .row-past td {
            background-color: #f9fafb;
        }
        .btn-disabled {
            pointer-events: none;
            opacity: 0.35;
            cursor: not-allowed;
        }
    </style>
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
            <a href="jumat.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-medium">
                <i class="ph-fill ph-users-three text-xl"></i> Petugas Jumat
            </a>
            <a href="kajian.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-book-open-text text-xl"></i> Kajian Rutin
            </a>
            <a href="tarjih.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-chat-circle-text text-xl"></i> Tanya Jawab AI
            </a>
            <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-gear text-xl"></i> Pengaturan
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
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Petugas Jumat</h2>
            <p class="text-gray-500">Kelola jadwal Khotib dan Imam sholat Jumat.</p>
        </div>

        <?= $pesan ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Form Tambah -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ph-fill ph-plus-circle text-emerald-500"></i> Tambah Jadwal
                    </h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Jumat</label>
                            <input type="date" name="tanggal_jumat" required
                                min="<?= $today ?>"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Khotib</label>
                            <input type="text" name="nama_khotib" required placeholder="Ust. Fulan, Lc"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Asal Instansi (Opsional)</label>
                            <input type="text" name="asal_instansi" placeholder="MUI Kota Bogor"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Imam</label>
                            <input type="text" name="nama_imam" required placeholder="Ust. Fulan, Lc"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <button type="submit" name="tambah_jumat" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 rounded-xl transition-colors">
                            Simpan Data
                        </button>
                    </form>
                </div>

                <!-- Legend -->
                <div class="mt-4 bg-white rounded-2xl p-4 shadow-sm border border-gray-100 text-sm text-gray-500 space-y-2">
                    <p class="font-semibold text-gray-700">Keterangan:</p>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span>Jadwal mendatang — dapat diedit</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-gray-300 inline-block"></span>
                        <span>Jadwal sudah lewat — hanya bisa dihapus</span>
                    </div>
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
                                    <th class="px-6 py-4 font-semibold">Khotib</th>
                                    <th class="px-6 py-4 font-semibold">Instansi</th>
                                    <th class="px-6 py-4 font-semibold">Imam</th>
                                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(mysqli_num_rows($result) == 0): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada jadwal Jumat.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <?php $isPast = ($row['tanggal_jumat'] < $today); ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors <?= $isPast ? 'row-past' : '' ?>">
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                                <?= date('d/m/Y', strtotime($row['tanggal_jumat'])) ?>
                                                <?php if($isPast): ?>
                                                    <span class="ml-1 text-xs bg-gray-200 text-gray-500 px-2 py-0.5 rounded-full">Lewat</span>
                                                <?php else: ?>
                                                    <span class="ml-1 text-xs bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">Mendatang</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-gray-800 font-medium"><?= htmlspecialchars($row['nama_khotib']) ?></td>
                                            <td class="px-6 py-4 text-gray-500"><?= htmlspecialchars($row['asal_instansi'] ?? '-') ?></td>
                                            <td class="px-6 py-4 text-gray-800 font-medium"><?= htmlspecialchars($row['nama_imam']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <!-- Tombol Edit (dinonaktifkan jika tanggal sudah lewat) -->
                                                    <button
                                                        type="button"
                                                        onclick="openEditModal(
                                                            '<?= $row['id_info'] ?>',
                                                            '<?= $row['tanggal_jumat'] ?>',
                                                            '<?= htmlspecialchars($row['nama_khotib'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($row['asal_instansi'] ?? '', ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($row['nama_imam'], ENT_QUOTES) ?>'
                                                        )"
                                                        title="<?= $isPast ? 'Jadwal sudah lewat, tidak bisa diedit' : 'Edit jadwal' ?>"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white transition-colors <?= $isPast ? 'btn-disabled' : '' ?>">
                                                        <i class="ph ph-pencil-simple"></i>
                                                    </button>
                                                    <!-- Tombol Hapus -->
                                                    <a href="?hapus=<?= $row['id_info'] ?>" onclick="return confirm('Hapus jadwal ini?')"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-colors">
                                                        <i class="ph ph-trash"></i>
                                                    </a>
                                                </div>
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

    <!-- ===== MODAL EDIT ===== -->
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeEditModal()"></div>

        <!-- Card -->
        <div id="modalCard" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                    <i class="ph-fill ph-pencil-simple text-blue-500"></i> Edit Jadwal Jumat
                </h3>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="id_edit" id="edit_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Jumat</label>
                    <input type="date" name="tanggal_jumat_edit" id="edit_tanggal" required
                        min="<?= $today ?>"
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                    <p class="text-xs text-amber-600 mt-1 flex items-center gap-1">
                        <i class="ph ph-warning"></i> Hanya tanggal mendatang yang bisa dipilih
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Khotib</label>
                    <input type="text" name="nama_khotib_edit" id="edit_khotib" required
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Asal Instansi (Opsional)</label>
                    <input type="text" name="asal_instansi_edit" id="edit_instansi"
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Imam</label>
                    <input type="text" name="nama_imam_edit" id="edit_imam" required
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 font-medium transition-colors text-sm">
                        Batal
                    </button>
                    <button type="submit" name="edit_jumat"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                        <i class="ph ph-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, tanggal, khotib, instansi, imam) {
            document.getElementById('edit_id').value       = id;
            document.getElementById('edit_tanggal').value  = tanggal;
            document.getElementById('edit_khotib').value   = khotib;
            document.getElementById('edit_instansi').value = instansi;
            document.getElementById('edit_imam').value     = imam;

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            // Trigger animation
            requestAnimationFrame(() => {
                document.getElementById('modalCard').style.transform = 'scale(1)';
                document.getElementById('modalCard').style.opacity   = '1';
            });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
    </script>
</body>
</html>

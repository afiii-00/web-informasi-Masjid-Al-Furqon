<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Proses Tambah Data
if (isset($_POST['tambah_dokumentasi'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $tanggal = $_POST['tanggal'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Upload & Kompresi Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $filename = 'dok_' . time() . '.' . $ext;
        $target_path = $upload_dir . $filename;
        
        $saved_filename = compress_and_save_image($tmp_name, $target_path, 75, 1200);
        
        if ($saved_filename) {
            $query = "INSERT INTO dokumentasi (foto, judul, tanggal, deskripsi) 
                      VALUES ('$saved_filename', '$judul', '$tanggal', '$deskripsi')";
            if (mysqli_query($conn, $query)) {
                $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Dokumentasi berhasil ditambahkan!</div>";
            } else {
                $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal menyimpan ke database: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal mengompresi dan menyimpan foto!</div>";
        }
    } else {
        $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Pilih foto kegiatan terlebih dahulu!</div>";
    }
}

// Proses Edit Data
if (isset($_POST['edit_dokumentasi'])) {
    $id_edit = intval($_POST['id_edit']);
    $judul = mysqli_real_escape_string($conn, $_POST['judul_edit']);
    $tanggal = $_POST['tanggal_edit'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi_edit']);
    
    // Ambil info foto lama
    $q_old = mysqli_query($conn, "SELECT foto FROM dokumentasi WHERE id_dok = $id_edit");
    $old_data = mysqli_fetch_assoc($q_old);
    $filename = $old_data['foto'];
    
    $upload_ok = true;
    // Jika ada foto baru diunggah
    if (isset($_FILES['foto_edit']) && $_FILES['foto_edit']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto_edit']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['foto_edit']['name'], PATHINFO_EXTENSION));
        $new_filename = 'dok_' . time() . '.' . $ext;
        $target_path = $upload_dir . $new_filename;
        
        $saved_filename = compress_and_save_image($tmp_name, $target_path, 75, 1200);
        
        if ($saved_filename) {
            // Hapus foto lama
            if (!empty($filename) && file_exists($upload_dir . $filename)) {
                unlink($upload_dir . $filename);
            }
            $filename = $saved_filename;
        } else {
            $upload_ok = false;
            $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal mengompresi foto baru!</div>";
        }
    }
    
    if ($upload_ok) {
        $query = "UPDATE dokumentasi SET foto='$filename', judul='$judul', tanggal='$tanggal', deskripsi='$deskripsi' 
                  WHERE id_dok=$id_edit";
        if (mysqli_query($conn, $query)) {
            $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Dokumentasi berhasil diperbarui!</div>";
        } else {
            $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal memperbarui database: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    // Hapus file fisik
    $q_del = mysqli_query($conn, "SELECT foto FROM dokumentasi WHERE id_dok = $id");
    if (mysqli_num_rows($q_del) > 0) {
        $data_del = mysqli_fetch_assoc($q_del);
        $foto_del = $data_del['foto'];
        if (!empty($foto_del) && file_exists($upload_dir . $foto_del)) {
            unlink($upload_dir . $foto_del);
        }
    }
    
    mysqli_query($conn, "DELETE FROM dokumentasi WHERE id_dok = $id");
    header("Location: dokumentasi.php");
    exit;
}

// Ambil Data Dokumentasi
$result = mysqli_query($conn, "SELECT * FROM dokumentasi ORDER BY tanggal DESC, id_dok DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Dokumentasi - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #F3F4F6; }
        
        #editModal { transition: opacity 0.2s ease; }
        #editModal.hidden { display: none; }
        
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
            <a href="dokumentasi.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-medium">
                <i class="ph-fill ph-image text-xl"></i> Dokumentasi
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
    <main class="ml-64 p-8 w-full min-h-screen">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Dokumentasi Kegiatan</h2>
            <p class="text-gray-500">Kelola dokumentasi galeri foto kegiatan Masjid Al-Furqon.</p>
        </div>

        <?= $pesan ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Form Tambah -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ph-fill ph-plus-circle text-emerald-500"></i> Tambah Dokumentasi
                    </h3>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Kegiatan</label>
                            <input type="text" name="judul" required placeholder="Cth: Santunan Anak Yatim"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
                            <input type="date" name="tanggal" required
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
                            <textarea name="deskripsi" required rows="3" placeholder="Jelaskan secara singkat mengenai jalannya kegiatan..."
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Kegiatan</label>
                            <input type="file" name="foto" required accept="image/*"
                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="text-xs text-gray-400 mt-1">*Gambar akan dikompresi otomatis demi menghemat penyimpanan.</p>
                        </div>
                        <button type="submit" name="tambah_dokumentasi" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 rounded-xl transition-colors">
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
                                    <th class="px-6 py-4 font-semibold w-24">Foto</th>
                                    <th class="px-6 py-4 font-semibold">Kegiatan</th>
                                    <th class="px-6 py-4 font-semibold">Tanggal</th>
                                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(mysqli_num_rows($result) == 0): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada dokumentasi kegiatan.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <img src="../uploads/<?= htmlspecialchars($row['foto']) ?>" 
                                                     alt="Foto" class="w-16 h-10 object-cover rounded-lg shadow-sm border border-gray-100">
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-800 leading-tight"><?= htmlspecialchars($row['judul']) ?></div>
                                                <div class="text-xs text-gray-400 mt-1 max-w-xs truncate"><?= htmlspecialchars($row['deskripsi']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                                <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <!-- Tombol Edit -->
                                                    <button
                                                        type="button"
                                                        onclick="openEditModal(
                                                            '<?= $row['id_dok'] ?>',
                                                            '<?= htmlspecialchars($row['judul'], ENT_QUOTES) ?>',
                                                            '<?= $row['tanggal'] ?>',
                                                            '<?= htmlspecialchars($row['deskripsi'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($row['foto'], ENT_QUOTES) ?>'
                                                        )"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white transition-colors">
                                                        <i class="ph ph-pencil-simple"></i>
                                                    </button>
                                                    <!-- Tombol Hapus -->
                                                    <a href="?hapus=<?= $row['id_dok'] ?>" onclick="return confirm('Hapus dokumentasi ini?')"
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
                    <i class="ph-fill ph-pencil-simple text-blue-500"></i> Edit Dokumentasi
                </h3>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id_edit" id="edit_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul Kegiatan</label>
                    <input type="text" name="judul_edit" id="edit_judul" required
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kegiatan</label>
                    <input type="date" name="tanggal_edit" id="edit_tanggal" required
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Singkat</label>
                    <textarea name="deskripsi_edit" id="edit_deskripsi" required rows="3"
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ubah Foto (Opsional)</label>
                    <div class="flex items-center gap-3 mb-2">
                        <img src="" id="edit_preview" alt="Preview" class="w-16 h-10 object-cover rounded-lg border border-gray-100">
                        <span class="text-xs text-gray-400" id="edit_filename"></span>
                    </div>
                    <input type="file" name="foto_edit" accept="image/*"
                        class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 font-medium transition-colors text-sm">
                        Batal
                    </button>
                    <button type="submit" name="edit_dokumentasi"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                        <i class="ph ph-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, judul, tanggal, deskripsi, foto) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_judul').value = judul;
            document.getElementById('edit_tanggal').value = tanggal;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_preview').src = '../uploads/' + foto;
            document.getElementById('edit_filename').innerText = foto;

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                document.getElementById('modalCard').style.transform = 'scale(1)';
                document.getElementById('modalCard').style.opacity = '1';
            });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
    </script>
</body>
</html>

<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';

// Proses Tambah Data
if (isset($_POST['tambah_tarjih'])) {
    $tema = mysqli_real_escape_string($conn, $_POST['tema']);
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    $sumber = mysqli_real_escape_string($conn, $_POST['sumber']);

    $query = "INSERT INTO tarjih_kb (tema, pertanyaan, jawaban, sumber) 
              VALUES ('$tema', '$pertanyaan', '$jawaban', '$sumber')";
    if (mysqli_query($conn, $query)) {
        $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Basis pengetahuan Tarjih berhasil ditambahkan!</div>";
    } else {
        $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal menambah data: " . mysqli_error($conn) . "</div>";
    }
}

// Proses Edit Data
if (isset($_POST['edit_tarjih'])) {
    $id_edit = intval($_POST['id_edit']);
    $tema = mysqli_real_escape_string($conn, $_POST['tema_edit']);
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan_edit']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban_edit']);
    $sumber = mysqli_real_escape_string($conn, $_POST['sumber_edit']);

    $query = "UPDATE tarjih_kb SET tema='$tema', pertanyaan='$pertanyaan', jawaban='$jawaban', sumber='$sumber' 
              WHERE id_kb=$id_edit";
    if (mysqli_query($conn, $query)) {
        $pesan = "<div class='p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-xl'>Basis pengetahuan Tarjih berhasil diperbarui!</div>";
    } else {
        $pesan = "<div class='p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-xl'>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
    }
}

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM tarjih_kb WHERE id_kb=$id");
    header("Location: tarjih.php");
    exit;
}

// Ambil Data Tarjih KB
$result = mysqli_query($conn, "SELECT * FROM tarjih_kb ORDER BY id_kb DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tarjih KB - Admin</title>
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
            <a href="dokumentasi.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-image text-xl"></i> Dokumentasi
            </a>
            <a href="jumat.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-users-three text-xl"></i> Petugas Jumat
            </a>
            <a href="kajian.php" class="flex items-center gap-3 px-4 py-3 text-gray-500 hover:bg-gray-50 rounded-xl font-medium transition-colors">
                <i class="ph ph-book-open-text text-xl"></i> Kajian Rutin
            </a>
            <a href="tarjih.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-medium">
                <i class="ph-fill ph-chat-circle-text text-xl"></i> Tanya Jawab AI
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
            <h2 class="text-2xl font-bold text-gray-800">Manajemen Basis Pengetahuan AI Chatbot</h2>
            <p class="text-gray-500">Kelola kumpulan fatwa & putusan resmi Tarjih Muhammadiyah untuk referensi chatbot.</p>
        </div>

        <?= $pesan ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Form Tambah -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ph-fill ph-plus-circle text-emerald-500"></i> Tambah Referensi
                    </h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tema / Topik</label>
                            <input type="text" name="tema" required placeholder="Cth: Zakat Fitrah"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan / Kasus</label>
                            <textarea name="pertanyaan" required rows="3" placeholder="Pertanyaan umum yang sering ditanyakan..."
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban / Ketetapan Resmi</label>
                            <textarea name="jawaban" required rows="6" placeholder="Ketetapan resmi hasil putusan Tarjih Muhammadiyah..."
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sumber Putusan</label>
                            <input type="text" name="sumber" required placeholder="Cth: Himpunan Putusan Tarjih (HPT) Jilid 1"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-sm">
                        </div>
                        <button type="submit" name="tambah_tarjih" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 rounded-xl transition-colors">
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
                                    <th class="px-6 py-4 font-semibold w-1/4">Tema</th>
                                    <th class="px-6 py-4 font-semibold">Isi Ringkas</th>
                                    <th class="px-6 py-4 font-semibold">Sumber</th>
                                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <?php if(mysqli_num_rows($result) == 0): ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada basis pengetahuan Tarjih.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <span class="font-bold text-gray-800"><?= htmlspecialchars($row['tema']) ?></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-gray-700 truncate max-w-xs">Q: <?= htmlspecialchars($row['pertanyaan']) ?></div>
                                                <div class="text-xs text-gray-400 mt-1 truncate max-w-xs">A: <?= htmlspecialchars($row['jawaban']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                                <?= htmlspecialchars($row['sumber']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <!-- Tombol Edit -->
                                                    <button
                                                        type="button"
                                                        onclick="openEditModal(
                                                            '<?= $row['id_kb'] ?>',
                                                            '<?= htmlspecialchars($row['tema'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($row['pertanyaan'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($row['jawaban'], ENT_QUOTES) ?>',
                                                            '<?= htmlspecialchars($row['sumber'], ENT_QUOTES) ?>'
                                                        )"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white transition-colors">
                                                        <i class="ph ph-pencil-simple"></i>
                                                    </button>
                                                    <!-- Tombol Hapus -->
                                                    <a href="?hapus=<?= $row['id_kb'] ?>" onclick="return confirm('Hapus referensi ini?')"
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
                    <i class="ph-fill ph-pencil-simple text-blue-500"></i> Edit Referensi Tarjih
                </h3>
                <button onclick="closeEditModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="id_edit" id="edit_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tema / Topik</label>
                    <input type="text" name="tema_edit" id="edit_tema" required
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm font-medium">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pertanyaan / Kasus</label>
                    <textarea name="pertanyaan_edit" id="edit_pertanyaan" required rows="3"
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jawaban / Ketetapan Resmi</label>
                    <textarea name="jawaban_edit" id="edit_jawaban" required rows="6"
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sumber Putusan</label>
                    <input type="text" name="sumber_edit" id="edit_sumber" required
                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 font-medium transition-colors text-sm">
                        Batal
                    </button>
                    <button type="submit" name="edit_tarjih"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                        <i class="ph ph-floppy-disk"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, tema, pertanyaan, jawaban, sumber) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_tema').value = tema;
            document.getElementById('edit_pertanyaan').value = pertanyaan;
            document.getElementById('edit_jawaban').value = jawaban;
            document.getElementById('edit_sumber').value = sumber;

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

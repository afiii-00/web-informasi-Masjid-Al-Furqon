<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Informasi Masjid</title>
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
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-emerald-50 text-emerald-600 rounded-xl font-medium">
                <i class="ph-fill ph-squares-four text-xl"></i> Dashboard
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
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                <p class="text-gray-500">Selamat datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?></p>
            </div>
            <a href="../index.php" target="_blank" class="bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors flex items-center gap-2">
                <i class="ph ph-arrow-square-out"></i> Lihat Web
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                        <i class="ph-fill ph-users-three text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Petugas Jumat</h3>
                        <p class="text-sm text-gray-500">Kelola jadwal khotib & imam</p>
                    </div>
                </div>
                <a href="jumat.php" class="mt-4 block text-center py-2 bg-gray-50 hover:bg-emerald-50 text-emerald-600 rounded-xl text-sm font-medium transition-colors">Kelola Data</a>
            </div>
            
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                        <i class="ph-fill ph-book-open-text text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Kajian Rutin</h3>
                        <p class="text-sm text-gray-500">Kelola tema & pemateri kajian</p>
                    </div>
                </div>
                <a href="kajian.php" class="mt-4 block text-center py-2 bg-gray-50 hover:bg-blue-50 text-blue-600 rounded-xl text-sm font-medium transition-colors">Kelola Data</a>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-8 border border-gray-100 text-center">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-500 mx-auto mb-4">
                <i class="ph-fill ph-check-circle text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Sistem Siap Digunakan</h3>
            <p class="text-gray-500 max-w-md mx-auto">Anda dapat mengelola informasi Petugas Jumat dan Kajian Rutin melalui menu di samping atau card di atas.</p>
        </div>
    </main>
</body>
</html>

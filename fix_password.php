<?php
require_once 'config/koneksi.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
mysqli_query($conn, "UPDATE admin SET password = '$hash' WHERE username = 'admin'");
echo "Password berhasil direset menjadi admin123";
?>

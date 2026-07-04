<?php
/**
 * Helper & Utilitas Portal Masjid Al-Furqon
 */

// 1. Fungsi Enkripsi & Dekripsi AES-256-CBC untuk Kunci API
function encrypt_data($data) {
    if (empty($data)) return '';
    $key = hash('sha256', 'alfurqon_dkm_secret_key_2026');
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . '::' . $encrypted);
}

function decrypt_data($data) {
    if (empty($data)) return '';
    $key = hash('sha256', 'alfurqon_dkm_secret_key_2026');
    $decoded = base64_decode($data);
    if (strpos($decoded, '::') === false) return '';
    list($iv, $encrypted) = explode('::', $decoded, 2);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

// 2. Fungsi Kompresi & Resize Gambar (mempertahankan aspek rasio asli)
function compress_and_save_image($source_path, $target_path, $quality = 75, $max_width = 1200) {
    list($width, $height, $type) = getimagesize($source_path);
    if (!$width || !$height) return false;

    // Hitung dimensi baru agar aspek rasio terjaga
    $new_width = $width;
    $new_height = $height;
    if ($width > $max_width) {
        $new_width = $max_width;
        $new_height = floor($height * ($max_width / $width));
    }

    // Buat image dari source berdasarkan tipe file
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src_image = @imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $src_image = @imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_WEBP:
            $src_image = @imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }

    if (!$src_image) return false;

    // Buat canvas gambar baru
    $dst_image = imagecreatetruecolor($new_width, $new_height);

    // Tangani transparansi untuk PNG/WEBP
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
        imagealphablending($dst_image, false);
        imagesavealpha($dst_image, true);
        $transparent = imagecolorallocatealpha($dst_image, 255, 255, 255, 127);
        imagefilledrectangle($dst_image, 0, 0, $new_width, $new_height, $transparent);
    }

    // Resize
    imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Tentukan format simpan (Utamakan WebP jika disupport oleh GD php, atau sesuaikan extensi target)
    $ext = strtolower(pathinfo($target_path, PATHINFO_EXTENSION));
    $success = false;

    if ($ext === 'webp' && function_exists('imagewebp')) {
        $success = imagewebp($dst_image, $target_path, $quality);
    } else {
        // Fallback ke JPEG
        if ($ext === 'webp') {
            $target_path = preg_replace('/\.webp$/i', '.jpg', $target_path);
        }
        $success = imagejpeg($dst_image, $target_path, $quality);
    }

    imagedestroy($src_image);
    imagedestroy($dst_image);

    return $success ? basename($target_path) : false;
}

// 3. Fungsi Caching Scraper KHGT Muhammadiyah
function calculate_local_hijri() {
    // Algoritma standar konversi Masehi ke Hijriah (fallback offline)
    $today = time();
    $m_day = date('d', $today);
    $m_month = date('m', $today);
    $m_year = date('Y', $today);
    
    if (($m_year < 1582) || (($m_year == 1582) && ($m_month < 10)) || (($m_year == 1582) && ($m_month == 10) && ($m_day <= 14))) {
        $jd = intval((1461 * ($m_year + 4800 + intval(($m_month - 14) / 12))) / 4) + intval((367 * ($m_month - 2 - 12 * (intval(($m_month - 14) / 12)))) / 12) - intval((3 * (intval(($m_year + 4900 + intval(($m_month - 14) / 12)) / 100))) / 4) + $m_day - 32075;
    } else {
        $jd = intval(367 * $m_year - intval((7 * ($m_year + 5001 + intval(($m_month - 9) / 7))) / 4) + intval((275 * $m_month) / 9) + $m_day + 1729777);
    }
    
    $l = $jd - 1948440 + 385;
    $n = intval(($l - 1) / 10631);
    $l = $l - 10631 * $n + 354;
    $j = (intval((10985 - $l) / 5316)) * (intval((50 * $l) / 17719)) + (intval($l / 5670)) * (intval((43 * $l) / 15238));
    $l = $l - (intval((30 - $j) / 15)) * (intval((17719 * $j) / 50)) - (intval($j / 16)) * (intval((15238 * $j) / 43)) + 29;
    
    $month_hijri = intval((24 * $l) / 709);
    $day_hijri = $l - intval((709 * $month_hijri) / 24);
    $year_hijri = 30 * $n + $j - 30;
    
    $hijri_months = ["", "Muharam", "Safar", "Rabiulawal", "Rabiulakhir", "Jumadilawal", "Jumadilakhir", "Rajab", "Syakban", "Ramadan", "Syawal", "Zulkaidah", "Zulhijah"];
    
    return $day_hijri . " " . $hijri_months[$month_hijri] . " " . $year_hijri . " H";
}

function get_khgt_date_today($conn) {
    $today_str = date('Y-m-d');
    
    // Cek cache
    $cached_date = get_config($conn, 'khgt_cached_date');
    $last_fetched = get_config($conn, 'khgt_last_fetched');
    
    if ($last_fetched === $today_str && !empty($cached_date)) {
        return $cached_date;
    }
    
    // Scrape web KHGT
    $url = "https://khgt.muhammadiyah.or.id";
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
            "timeout" => 5
        ]
    ];
    $context = stream_context_create($options);
    $html = @file_get_contents($url, false, $context);
    
    $extracted_hijri = '';
    if ($html !== false) {
        // Regex untuk mencari <strong>18 Muharam 1448 H</strong> atau sejenisnya
        if (preg_match('/<strong>\s*([0-9]+)\s+([A-Za-z]+)\s+([0-9]+)\s+H\s*<\/strong>/i', $html, $matches)) {
            $extracted_hijri = trim($matches[1]) . " " . trim($matches[2]) . " " . trim($matches[3]) . " H";
        } else {
            // Regex alternatif
            if (preg_match('/<strong>([^<]+H)<\/strong>/i', $html, $matches)) {
                $extracted_hijri = trim(preg_replace('/\s+/', ' ', $matches[1]));
            }
        }
    }
    
    if (!empty($extracted_hijri)) {
        // Simpan cache ke DB
        set_config($conn, 'khgt_cached_date', $extracted_hijri);
        set_config($conn, 'khgt_last_fetched', $today_str);
        return $extracted_hijri;
    }
    
    // Fallback ke cache lama atau hitungan lokal jika gagal
    if (!empty($cached_date)) {
        return $cached_date;
    }
    return calculate_local_hijri();
}

// 4. Auto-Delete Chatbot Logs (> 14 hari)
function cleanup_chatbot_logs($conn) {
    $limit_date = date('Y-m-d H:i:s', strtotime('-14 days'));
    $limit_date_esc = mysqli_real_escape_string($conn, $limit_date);
    mysqli_query($conn, "DELETE FROM chatbot_logs WHERE waktu < '$limit_date_esc'");
}

// 5. Fungsi Dapatkan & Simpan Konfigurasi Database
function get_config($conn, $key, $decrypt = false) {
    $key_esc = mysqli_real_escape_string($conn, $key);
    $query = mysqli_query($conn, "SELECT nilai FROM konfigurasi WHERE kunci = '$key_esc'");
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        $val = $row['nilai'];
        if ($decrypt && !empty($val)) {
            return decrypt_data($val);
        }
        return $val;
    }
    return '';
}

function set_config($conn, $key, $val, $encrypt = false) {
    $key_esc = mysqli_real_escape_string($conn, $key);
    if ($encrypt && !empty($val)) {
        $val = encrypt_data($val);
    }
    $val_esc = mysqli_real_escape_string($conn, $val);
    $query = "INSERT INTO konfigurasi (kunci, nilai) VALUES ('$key_esc', '$val_esc')
              ON DUPLICATE KEY UPDATE nilai = '$val_esc'";
    return mysqli_query($conn, $query);
}
?>

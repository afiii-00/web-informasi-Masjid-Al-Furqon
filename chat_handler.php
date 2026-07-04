<?php
header('Content-Type: application/json');
require_once 'config/koneksi.php';

// Pastikan method adalah POST dan parameter message ada
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['message'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Permintaan tidak valid!'
    ]);
    exit;
}

$user_message = trim($_POST['message']);
if (empty($user_message)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Pertanyaan tidak boleh kosong!'
    ]);
    exit;
}

// 1. Bersihkan log lama (> 14 hari)
cleanup_chatbot_logs($conn);

// 2. Dapatkan IP Address User
$ip = $_SERVER['REMOTE_ADDR'];
// Tangani IP dibelakang proxy cloudflare/loadbalancer
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
}

// 3. Cek Rate Limiting (Maks 5 chat per IP per hari sejak jam 00:00)
$today_start = date('Y-m-d 00:00:00');
$ip_esc = mysqli_real_escape_string($conn, $ip);
$q_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM chatbot_logs WHERE ip_address = '$ip_esc' AND waktu >= '$today_start'");
$count_data = mysqli_fetch_assoc($q_count);

if ($count_data && $count_data['total'] >= 5) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Batas kuota tanya jawab gratis Anda hari ini (5 kali) telah habis. Kuota Anda akan di-reset otomatis besok pagi.'
    ]);
    exit;
}

// 4. RAG: Dapatkan konteks relevan dari tarjih_kb
$user_message_esc = mysqli_real_escape_string($conn, $user_message);
$context = "";

// Coba Full-Text Search (FTS) dahulu
$fts_query = "SELECT *, MATCH(tema, pertanyaan, jawaban) AGAINST('$user_message_esc') AS score 
              FROM tarjih_kb 
              WHERE MATCH(tema, pertanyaan, jawaban) AGAINST('$user_message_esc') 
              ORDER BY score DESC LIMIT 3";
$res = mysqli_query($conn, $fts_query);

if ($res && mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $context .= "Tema: " . $row['tema'] . "\nPertanyaan: " . $row['pertanyaan'] . "\nJawaban: " . $row['jawaban'] . "\nSumber: " . $row['sumber'] . "\n\n";
    }
} else {
    // Fallback: LIKE query jika FTS tidak membuahkan hasil
    $words = explode(' ', $user_message);
    $like_clauses = [];
    foreach ($words as $word) {
        $word_clean = trim($word);
        if (strlen($word_clean) > 3) {
            $word_esc = mysqli_real_escape_string($conn, $word_clean);
            $like_clauses[] = "tema LIKE '%$word_esc%' OR pertanyaan LIKE '%$word_esc%' OR jawaban LIKE '%$word_esc%'";
        }
    }
    
    if (!empty($like_clauses)) {
        $like_query = "SELECT * FROM tarjih_kb WHERE (" . implode(' OR ', $like_clauses) . ") LIMIT 2";
        $res_like = mysqli_query($conn, $like_query);
        if ($res_like && mysqli_num_rows($res_like) > 0) {
            while ($row = mysqli_fetch_assoc($res_like)) {
                $context .= "Tema: " . $row['tema'] . "\nPertanyaan: " . $row['pertanyaan'] . "\nJawaban: " . $row['jawaban'] . "\nSumber: " . $row['sumber'] . "\n\n";
            }
        }
    }
}

// 5. Ambil API Key Gemini
$api_key = get_config($conn, 'gemini_api_key', true); // decrypted
if (empty($api_key)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Layanan Asisten AI belum diaktifkan/dikonfigurasi oleh Administrator.'
    ]);
    exit;
}

// 6. Buat Prompt Instruksi
$system_instruction = "Anda adalah asisten tanya jawab keagamaan resmi berbasis AI untuk website Masjid Al-Furqon. Tugas Anda adalah membantu menjawab pertanyaan jamaah dengan sopan, santun, dan menyejukkan.

Rujukan utama Anda adalah keputusan resmi Tarjih Muhammadiyah. Berikut adalah basis pengetahuan Tarjih yang relevan dari database internal kami:
===
" . (!empty($context) ? $context : "Tidak ditemukan kutipan keputusan Tarjih yang spesifik di database untuk pertanyaan ini. Jawablah berdasarkan pengetahuan umum Anda mengenai keputusan Tarjih Muhammadiyah jika yakin, namun sampaikan secara halus jika tidak ada putusan tertulis.") . "
===

Aturan Penting:
1. Jawablah dengan bahasa Indonesia yang baik, ramah, dan mendidik.
2. Jika Anda mengutip dari basis pengetahuan di atas, Anda WAJIB mencantumkan sumbernya (contoh: \"Sumber: Himpunan Putusan Tarjih Jilid 1\") di bagian akhir jawaban Anda secara terpisah dan jelas.
3. Jika pertanyaan di luar konteks agama Islam atau tidak pantas, sampaikan secara sopan bahwa Anda hanya melayani tanya jawab seputar ajaran Islam dan Putusan Tarjih Muhammadiyah.
4. Anda harus selalu menyertakan pengingat bahwa jawaban Anda bersifat referensi informasi dari putusan Tarjih Muhammadiyah, dan untuk keputusan syariah yang mendalam dianjurkan berkonsultasi dengan ustaz/ulama secara langsung.";

// 7. Panggil API Gemini 3.5 Flash
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-3.5-flash:generateContent?key=" . $api_key;

$full_prompt = $system_instruction . "\n\nPertanyaan Jamaah:\n" . $user_message;

$request_data = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $full_prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.2,
        'maxOutputTokens' => 8000
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 12); // timeout 12 detik

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

// Log debug info
file_put_contents('chat_debug.log', "[" . date('Y-m-d H:i:s') . "] HTTP Code: $http_code | Curl Error: $curl_err | Response: $response\n", FILE_APPEND);

// 8. Proses Respons Gemini
if ($http_code === 200 && $response) {
    $res_json = json_decode($response, true);
    
    if (isset($res_json['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_answer = $res_json['candidates'][0]['content']['parts'][0]['text'];
        
        // Simpan log chat sukses ke DB
        $user_message_esc_log = mysqli_real_escape_string($conn, $user_message);
        mysqli_query($conn, "INSERT INTO chatbot_logs (ip_address, pertanyaan, waktu) VALUES ('$ip_esc', '$user_message_esc_log', NOW())");
        
        echo json_encode([
            'status' => 'success',
            'data' => $ai_answer
        ]);
        exit;
    }
}

// Graceful Degradation jika API error atau timeout
echo json_encode([
    'status' => 'error',
    'message' => 'Server AI sedang sibuk atau mengalami gangguan koneksi. Silakan coba beberapa saat lagi.'
]);
?>

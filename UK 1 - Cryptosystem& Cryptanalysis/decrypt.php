<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include __DIR__ . '/ciphers.php';

$cipher = $_POST['cipher'] ?? '';
$key = $_POST['key'] ?? '';
$result = '';

// teks input - hapus semua karakter non-alfabet dan ubah ke uppercase
$message = strtoupper($_POST['ciphertext'] ?? '');
$message = preg_replace("/[^A-Z]/", "", $message);

// siapkan folder uploads
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (empty($message) && empty($_FILES['cipherfile']['tmp_name'])) {
    echo "Ciphertext kosong atau tidak ada file yang diupload!";
    exit;
}

// kalau teks biasa
if (!empty($message)) {
    switch ($cipher) {
        case 'shift':
            if (!is_numeric($key)) {
                echo "Error: Kunci Shift harus berupa angka!";
                exit;
            }
            $result = shift_decrypt($message, intval($key));
            break;

        case 'substitution':
            if (strlen($key) != 26) {
                echo "Kunci Substitution harus 26 huruf unik!";
                exit;
            }
            $key = strtoupper($key);
            if (!ctype_alpha($key)) {
                echo "Kunci Substitution harus hanya berisi huruf A-Z!";
                exit;
            }
            if (count(array_unique(str_split($key))) != 26) {
                echo "Kunci Substitution harus berisi 26 huruf yang berbeda!";
                exit;
            }
            $result = substitution_decrypt($message, $key);
            break;

        case 'affine':
            $parts = explode(',', $key);
            if (count($parts) != 2) {
                echo "Kunci Affine harus 2 angka a,b dipisah koma!";
                exit;
            }
            if (!is_numeric($parts[0]) || !is_numeric($parts[1])) {
                echo "Kunci Affine harus berisi 2 angka!";
                exit;
            }
            $a = intval($parts[0]);
            $b = intval($parts[1]);
            if (gcd($a, 26) != 1) {
                echo "Error: Nilai a ($a) harus coprime dengan 26! (a harus ganjil dan bukan kelipatan 13)";
                exit;
            }
            $result = affine_decrypt($message, $a, $b);
            break;

        case 'vigenere':
            $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
            if (empty($key)) {
                echo "Kunci Vigenere harus berisi huruf A-Z!";
                exit;
            }
            $result = vigenere_decrypt($message, $key);
            break;

        case 'hill':
            $matrix = explode(',', $key);
            $n = sqrt(count($matrix));
            if ($n != intval($n)) {
                echo "Kunci Hill harus kuadrat sempurna (contoh: 4 angka untuk matriks 2x2, 9 angka untuk matriks 3x3)!";
                exit;
            }
            foreach ($matrix as $val) {
                if (!is_numeric($val)) {
                    echo "Semua elemen matriks Hill harus berupa angka!";
                    exit;
                }
            }
            $keyMatrix = array_chunk($matrix, $n);
            // Convert to integers
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $keyMatrix[$i][$j] = intval($keyMatrix[$i][$j]);
                }
            }
            $result = hill_decrypt($message, $keyMatrix);
            break;

        case 'permutation':
            $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
            if (empty($key)) {
                echo "Kunci Permutation harus berisi huruf A-Z!";
                exit;
            }
            break;
    }

    // Cek apakah hasil dekripsi error
    if (strpos($result, "Error:") === 0) {
        echo $result;
        exit;
    }

    echo "<div class='result-section'>";
    echo "<h3>Hasil Dekripsi ($cipher cipher):</h3>";
    echo "<div class='result-text'>";
    echo "<p><strong>Plaintext:</strong><br>" . htmlspecialchars(chunk_split($result, 5, ' ')) . "</p>";
    echo "</div></div>";
}
// kalau file diupload
elseif (!empty($_FILES['cipherfile']['tmp_name'])) {
    $raw = file_get_contents($_FILES['cipherfile']['tmp_name']);
    $payload = @unserialize($raw);

    if (!is_array($payload) || !isset($payload['ext']) || !isset($payload['data'])) {
        echo "<div class='error-section'>";
        echo "<h3>Error:</h3>";
        echo "<p>File tidak valid! Pastikan file yang diupload adalah file .dat hasil enkripsi dari sistem ini.</p>";
        echo "<p>Metadata ekstensi tidak ditemukan atau file rusak.</p>";
        echo "</div>";
        exit;
    }

    $cipher_bytes = base64_decode($payload['data']);
    $file_cipher = $payload['cipher'] ?? $cipher; // Gunakan cipher dari metadata jika ada
    $original_filename = $payload['original_filename'] ?? 'unknown';
    $file_ext = $payload['ext'];

    // Validasi kunci untuk dekripsi
    switch ($file_cipher) {
        case 'shift':
            if (!is_numeric($key)) {
                echo "Error: Kunci Shift harus berupa angka!";
                exit;
            }
            break;

        case 'substitution':
            if (strlen($key) != 26) {
                echo "Kunci Substitution harus 26 huruf unik!";
                exit;
            }
            $key = strtoupper($key);
            if (!ctype_alpha($key)) {
                echo "Kunci Substitution harus hanya berisi huruf A-Z!";
                exit;
            }
            if (count(array_unique(str_split($key))) != 26) {
                echo "Kunci Substitution harus berisi 26 huruf yang berbeda!";
                exit;
            }
            break;

        case 'affine':
            $parts = explode(',', $key);
            if (count($parts) != 2) {
                echo "Kunci Affine harus 2 angka a,b dipisah koma!";
                exit;
            }
            if (!is_numeric($parts[0]) || !is_numeric($parts[1])) {
                echo "Kunci Affine harus berisi 2 angka!";
                exit;
            }
            if (gcd(intval($parts[0]), 256) != 1) {
                echo "Error: Untuk file, nilai a harus coprime dengan 256!";
                exit;
            }
            break;

        case 'vigenere':
            $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
            if (empty($key)) {
                echo "Kunci Vigenere harus berisi huruf A-Z!";
                exit;
            }
            break;

        case 'hill':
            $matrix = explode(',', $key);
            $n = sqrt(count($matrix));
            if ($n != intval($n)) {
                echo "Kunci Hill harus kuadrat sempurna!";
                exit;
            }
            foreach ($matrix as $val) {
                if (!is_numeric($val)) {
                    echo "Semua elemen matriks Hill harus berupa angka!";
                    exit;
                }
            }
            break;

        case 'permutation':
            $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
            if (empty($key)) {
                echo "Kunci Permutation harus berisi huruf A-Z!";
                exit;
            }
            $result = permutation_decrypt($message, $key);
            break;
    }

    $plain_bytes = '';

    switch ($file_cipher) {
        case 'shift':
            $plain_bytes = shift_decrypt_file($cipher_bytes, intval($key));
            break;

        case 'substitution':
            $plain_bytes = substitution_decrypt_file($cipher_bytes, $key);
            if ($plain_bytes === false) {
                echo "Error dalam dekripsi Substitution file!";
                exit;
            }
            break;

        case 'affine':
            $parts = explode(',', $key);
            $plain_bytes = affine_decrypt_file($cipher_bytes, intval($parts[0]), intval($parts[1]));
            if ($plain_bytes === false) {
                echo "Error dalam dekripsi Affine file! Pastikan kunci valid dan a coprime dengan 256.";
                exit;
            }
            break;

        case 'vigenere':
            $plain_bytes = vigenere_decrypt_file($cipher_bytes, $key);
            if ($plain_bytes === false) {
                echo "Kunci Vigenere tidak valid untuk file!";
                exit;
            }
            break;

        case 'hill':
            $matrix = explode(',', $key);
            $n = sqrt(count($matrix));
            $keyMatrix = array_chunk($matrix, $n);
            // Convert to integers
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $keyMatrix[$i][$j] = intval($keyMatrix[$i][$j]);
                }
            }
            $plain_bytes = hill_decrypt_file($cipher_bytes, $keyMatrix);
            if ($plain_bytes === false) {
                echo "Error dalam dekripsi Hill file! Matriks tidak dapat di-invers atau kunci salah.";
                exit;
            }
            break;

        case 'permutation':
            $plain_bytes = permutation_decrypt_file($cipher_bytes, $key);
            if ($plain_bytes === false) {
                echo "Error dalam dekripsi Permutation file!";
                exit;
            }
            break;

        default:
            echo "Cipher '$file_cipher' tidak dikenal atau tidak didukung untuk file!";
            exit;
    }

    // simpan kembali dengan ekstensi asli
    $timestamp = date('Y-m-d_H-i-s');
    $saveFile = $uploadDir . 'decrypted_' . $file_cipher . '_' . $timestamp . '.' . $file_ext;
    file_put_contents($saveFile, $plain_bytes);

    echo "<div class='result-section'>";
    echo "<h3>File Berhasil Didekripsi!</h3>";
    echo "<p><strong>File Hasil:</strong> <code>$saveFile</code></p>";
}

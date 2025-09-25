<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
include __DIR__.'/ciphers.php';

$cipher = $_POST['cipher'] ?? '';
$key = $_POST['key'] ?? '';
$result = '';

// teks input - hapus semua karakter non-alfabet dan ubah ke uppercase
$message = strtoupper($_POST['message'] ?? '');
$message = preg_replace("/[^A-Z]/","",$message);

// siapkan folder uploads
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if(empty($message) && empty($_FILES['file']['tmp_name'])){
    echo "Pesan kosong atau tidak ada huruf A-Z, atau tidak ada file yang diupload!";
    exit;
}

// kalau teks biasa
if(!empty($message)){
    switch($cipher){
        case 'shift': 
            if (!is_numeric($key)) {
                echo "Error: Kunci Shift harus berupa angka!";
                exit;
            }
            $result = shift_encrypt($message,intval($key)); 
            break;
            
        case 'substitution': 
            if(strlen($key)!=26) { 
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
            $result = substitution_encrypt($message,$key); 
            break;
            
        case 'affine':
            $parts=explode(',',$key); 
            if(count($parts)!=2){ 
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
            $result = affine_encrypt($message,$a,$b); 
            break;
            
        case 'vigenere': 
            $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
            if (empty($key)) {
                echo "Kunci Vigenere harus berisi huruf A-Z!";
                exit;
            }
            $result = vigenere_encrypt($message,$key); 
            break;
            
        case 'hill':
            $matrix=explode(',',$key); 
            $n=sqrt(count($matrix)); 
            if($n!=intval($n)){ 
                echo "Kunci Hill harus kuadrat sempurna (contoh: 4 angka untuk matriks 2x2, 9 angka untuk matriks 3x3)!"; 
                exit;
            }
            foreach ($matrix as $val) {
                if (!is_numeric($val)) {
                    echo "Semua elemen matriks Hill harus berupa angka!";
                    exit;
                }
            }
            $keyMatrix=array_chunk($matrix,$n);
            // Convert to integers
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $keyMatrix[$i][$j] = intval($keyMatrix[$i][$j]);
                }
            }
            $result = hill_encrypt($message,$keyMatrix); 
            break;
            
        case 'permutation':
            if(strlen($key)!=26){ 
                echo "Kunci Permutation harus 26 huruf!"; 
                exit;
            }
            $key = strtoupper($key);
            if (!ctype_alpha($key)) {
                echo "Kunci Permutation harus hanya berisi huruf A-Z!";
                exit;
            }
            if (count(array_unique(str_split($key))) != 26) {
                echo "Kunci Permutation harus berisi 26 huruf yang berbeda (A-Z semua harus ada)!";
                exit;
            }
            $result = permutation_encrypt($message,$key); 
            break;
            
        default: 
            $result="Cipher tidak dikenal!";
    }
    
    // Cek apakah hasil enkripsi error
    if (strpos($result, "Error:") === 0) {
        echo $result;
        exit;
    }
    
    $output_format = $_POST['output_format'] ?? 'grouped';

    echo "<div class='result-section'>";
    echo "<h3>Hasil Enkripsi ($cipher cipher):</h3>";
    echo "<div class='result-text'>";

    if ($output_format === 'no_space') {
        echo "<p><strong>Tanpa Spasi:</strong><br>" . htmlspecialchars($result) . "</p>";
    } else {
        echo "<p><strong>Kelompok 5 Huruf:</strong><br>" . htmlspecialchars(chunk_split($result, 5, ' ')) . "</p>";
    }
    
    echo "</div></div>";
}
// kalau file diupload
elseif(!empty($_FILES['file']['tmp_name'])){
    // Validasi kunci untuk setiap cipher
    switch($cipher) {
        case 'shift':
            if (!is_numeric($key)) {
                echo "Error: Kunci Shift harus berupa angka!";
                exit;
            }
            break;
            
        case 'substitution':
            if(strlen($key)!=26) { 
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
            $parts = explode(',',$key); 
            if(count($parts)!=2){ 
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
            $original_key = $key;
            $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
            if (empty($key)) {
                echo "Kunci Vigenere harus berisi huruf A-Z!";
                exit;
            }
            break;
            
        case 'hill':
            $matrix = explode(',',$key); 
            $n = sqrt(count($matrix)); 
            if($n != intval($n)){ 
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
            if(strlen($key)!=26){ 
                echo "Kunci Permutation harus 26 huruf!"; 
                exit;
            }
            $key = strtoupper($key);
            if (!ctype_alpha($key)) {
                echo "Kunci Permutation harus hanya berisi huruf A-Z!";
                exit;
            }
            if (count(array_unique(str_split($key))) != 26) {
                echo "Kunci Permutation harus berisi 26 huruf yang berbeda!";
                exit;
            }
            break;
    }
    
    $data = file_get_contents($_FILES['file']['tmp_name']);
    $original_filename = $_FILES['file']['name'];
    $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
    $cipher_bytes = '';

    switch($cipher) {
        case 'shift':
            $cipher_bytes = shift_encrypt_file($data, intval($key));
            break;
            
        case 'substitution':
            $cipher_bytes = substitution_encrypt_file($data, $key);
            if($cipher_bytes === false) { 
                echo "Error dalam enkripsi Substitution file!"; 
                exit; 
            }
            break;
            
        case 'affine':
            $parts = explode(',',$key); 
            $cipher_bytes = affine_encrypt_file($data, intval($parts[0]), intval($parts[1]));
            if($cipher_bytes === false) { 
                echo "Error dalam enkripsi Affine file! Pastikan nilai a coprime dengan 256."; 
                exit; 
            }
            break;
            
        case 'vigenere':
            $cipher_bytes = vigenere_encrypt_file($data, $key);
            if($cipher_bytes === false) { 
                echo "Kunci Vigenere tidak valid untuk file!"; 
                exit; 
            }
            break;
            
        case 'hill':
            $matrix = explode(',',$key); 
            $n = sqrt(count($matrix)); 
            $keyMatrix = array_chunk($matrix, $n);
            // Convert to integers
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $keyMatrix[$i][$j] = intval($keyMatrix[$i][$j]);
                }
            }
            $cipher_bytes = hill_encrypt_file($data, $keyMatrix);
            if($cipher_bytes === false) { 
                echo "Error dalam enkripsi Hill file! Matriks mungkin tidak dapat di-invers."; 
                exit; 
            }
            break;
            
        case 'permutation':
            $cipher_bytes = permutation_encrypt_file($data, $key);
            if($cipher_bytes === false) { 
                echo "Error dalam enkripsi Permutation file!"; 
                exit; 
            }
            break;
            
        default: 
            echo "Cipher tidak dikenal atau tidak didukung untuk file!"; 
            exit;
    }

    // buat payload dengan metadata lengkap
    $payload = [
        'cipher' => $cipher,
        'key_info' => $key, // Simpan info kunci untuk referensi
        'original_filename' => $original_filename,
        'ext' => $ext,
        'file_size' => strlen($data),
        'encrypted_size' => strlen($cipher_bytes),
        'timestamp' => time(),
        'data' => base64_encode($cipher_bytes)
    ];
    
    $timestamp = date('Y-m-d_H-i-s');
    $saveFile = $uploadDir . 'encrypted_' . $cipher . '_' . $timestamp . '.dat';
    file_put_contents($saveFile, serialize($payload));
    
    echo "<div class='result-section'>";
    echo "<h3>File Berhasil Dienkripsi!</h3>";
    echo "<p><strong>File Hasil:</strong> <code>$saveFile</code></p>";
}
?>
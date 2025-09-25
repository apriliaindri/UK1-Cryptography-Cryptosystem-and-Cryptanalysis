<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
include __DIR__.'/ciphers.php';

$cipher = $_POST['cipher'] ?? '';
$key = $_POST['key'] ?? '';
$result = '';

// teks input
$message = strtoupper($_POST['ciphertext'] ?? '');
$message = preg_replace("/[^A-Z]/","",$message);

// siapkan folder uploads
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if(empty($message) && empty($_FILES['cipherfile']['tmp_name'])){
    echo "Ciphertext kosong!";
    exit;
}

// kalau teks biasa
if(!empty($message)){
    switch($cipher){
        case 'shift': $result = shift_decrypt($message,intval($key)); break;
        case 'substitution': $result = substitution_decrypt($message,$key); break;
        case 'affine':
            $parts=explode(',',$key); $result = affine_decrypt($message,intval($parts[0]),intval($parts[1])); break;
        case 'vigenere': $result = vigenere_decrypt($message,$key); break;
        case 'hill':
            $matrix=explode(',',$key); $n=sqrt(count($matrix));
            $keyMatrix=array_chunk($matrix,$n);
            $result = hill_decrypt($message,$keyMatrix); break;
        case 'permutation': $result = permutation_decrypt($message,$key); break;
        default: $result="Cipher tidak dikenal!";
    }
    echo "<b>Hasil Dekripsi:</b><br>".chunk_split($result,5,' ');
}
// kalau file diupload
elseif(!empty($_FILES['cipherfile']['tmp_name'])){
    $raw = file_get_contents($_FILES['cipherfile']['tmp_name']);
    $payload = @unserialize($raw);

    if(!is_array($payload) || !isset($payload['ext']) || !isset($payload['data'])){
        echo "Metadata ekstensi tidak ditemukan atau file rusak!";
        exit;
    }

    $cipher_bytes = base64_decode($payload['data']);
    $file_cipher = $payload['cipher'] ?? $cipher; // Gunakan cipher dari metadata jika ada
    $plain_bytes = '';

    switch($file_cipher) {
        case 'shift':
            $plain_bytes = shift_decrypt_file($cipher_bytes, intval($key));
            break;
        case 'substitution':
            if(strlen($key)!=26) { echo "Kunci Substitution harus 26 huruf unik!"; exit;}
            $plain_bytes = substitution_decrypt_file($cipher_bytes, $key);
            if($plain_bytes === false) { echo "Error dalam dekripsi Substitution file!"; exit; }
            break;
        case 'affine':
            $parts = explode(',',$key); 
            if(count($parts)!=2){ echo "Kunci Affine harus 2 angka a,b"; exit;}
            $plain_bytes = affine_decrypt_file($cipher_bytes, intval($parts[0]), intval($parts[1]));
            if($plain_bytes === false) { echo "Error dalam dekripsi Affine file! Pastikan kunci valid."; exit; }
            break;
        case 'vigenere':
            $plain_bytes = vigenere_decrypt_file($cipher_bytes, $key);
            if($plain_bytes === false) { echo "Kunci Vigenere tidak valid untuk file!"; exit; }
            break;
        case 'hill':
            $matrix = explode(',',$key); 
            $n = sqrt(count($matrix));
            if($n != intval($n)){ echo "Kunci Hill harus kuadrat sempurna"; exit;}
            $keyMatrix = array_chunk($matrix, $n);
            $plain_bytes = hill_decrypt_file($cipher_bytes, $keyMatrix);
            if($plain_bytes === false) { echo "Error dalam dekripsi Hill file! Matriks tidak dapat di-invers."; exit; }
            break;
        case 'permutation':
            if(strlen($key)!=26){ echo "Kunci Permutation harus 26 huruf!"; exit;}
            $plain_bytes = permutation_decrypt_file($cipher_bytes, $key);
            if($plain_bytes === false) { echo "Error dalam dekripsi Permutation file!"; exit; }
            break;
        default: 
            echo "Cipher tidak dikenal atau tidak didukung untuk file!"; 
            exit;
    }

    // simpan kembali dengan ekstensi asli
    $saveFile = $uploadDir . 'decrypted_' . $file_cipher . '_file.' . $payload['ext'];
    file_put_contents($saveFile, $plain_bytes);

    echo "File berhasil didekripsi dengan <b>$file_cipher cipher</b>: <b>$saveFile</b>";
}
?>
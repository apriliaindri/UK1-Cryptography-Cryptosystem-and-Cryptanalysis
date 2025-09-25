<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);
include __DIR__.'/ciphers.php';

$cipher = $_POST['cipher'] ?? '';
$key = $_POST['key'] ?? '';
$result = '';

// teks input
$message = strtoupper($_POST['message'] ?? '');
$message = preg_replace("/[^A-Z]/","",$message);

// siapkan folder uploads
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if(empty($message) && empty($_FILES['file']['tmp_name'])){
    echo "Pesan kosong atau tidak ada huruf A-Z!";
    exit;
}

// kalau teks biasa
if(!empty($message)){
    switch($cipher){
        case 'shift': $result = shift_encrypt($message,intval($key)); break;
        case 'substitution': 
            if(strlen($key)!=26) { echo "Kunci Substitution harus 26 huruf unik!"; exit;}
            $result = substitution_encrypt($message,$key); break;
        case 'affine':
            $parts=explode(',',$key); if(count($parts)!=2){ echo "Kunci Affine harus 2 angka a,b"; exit;}
            $result = affine_encrypt($message,intval($parts[0]),intval($parts[1])); break;
        case 'vigenere': $result = vigenere_encrypt($message,$key); break;
        case 'hill':
            $matrix=explode(',',$key); $n=sqrt(count($matrix)); 
            if($n!=intval($n)){ echo "Kunci Hill harus kuadrat sempurna"; exit;}
            $keyMatrix=array_chunk($matrix,$n);
            $result = hill_encrypt($message,$keyMatrix); break;
        case 'permutation':
            if(strlen($key)!=26){ echo "Kunci Permutation harus 26 huruf!"; exit;}
            $result = permutation_encrypt($message,$key); break;
        default: $result="Cipher tidak dikenal!";
    }
    
    $output_format = $_POST['output_format'] ?? 'grouped';

    echo "<b>Hasil Enkripsi:</b><br>";

    if ($output_format === 'no_space') {
        echo htmlspecialchars($result);
    } else {
        echo htmlspecialchars(chunk_split($result, 5, ' '));
    }
}
// kalau file diupload
elseif(!empty($_FILES['file']['tmp_name'])){
    $data = file_get_contents($_FILES['file']['tmp_name']);
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION); // ambil ekstensi
    $cipher_bytes = '';

    switch($cipher) {
        case 'shift':
            $cipher_bytes = shift_encrypt_file($data, intval($key));
            break;
        case 'substitution':
            if(strlen($key)!=26) { echo "Kunci Substitution harus 26 huruf unik!"; exit;}
            $cipher_bytes = substitution_encrypt_file($data, $key);
            if($cipher_bytes === false) { echo "Error dalam enkripsi Substitution file!"; exit; }
            break;
        case 'affine':
            $parts = explode(',',$key); 
            if(count($parts)!=2){ echo "Kunci Affine harus 2 angka a,b"; exit;}
            $cipher_bytes = affine_encrypt_file($data, intval($parts[0]), intval($parts[1]));
            if($cipher_bytes === false) { echo "Error dalam enkripsi Affine file!"; exit; }
            break;
        case 'vigenere':
            $cipher_bytes = vigenere_encrypt_file($data, $key);
            if($cipher_bytes === false) { echo "Kunci Vigenere tidak valid untuk file!"; exit; }
            break;
        case 'hill':
            $matrix = explode(',',$key); 
            $n = sqrt(count($matrix)); 
            if($n != intval($n)){ echo "Kunci Hill harus kuadrat sempurna"; exit;}
            $keyMatrix = array_chunk($matrix, $n);
            $cipher_bytes = hill_encrypt_file($data, $keyMatrix);
            if($cipher_bytes === false) { echo "Error dalam enkripsi Hill file!"; exit; }
            break;
        case 'permutation':
            if(strlen($key)!=26){ echo "Kunci Permutation harus 26 huruf!"; exit;}
            $cipher_bytes = permutation_encrypt_file($data, $key);
            if($cipher_bytes === false) { echo "Error dalam enkripsi Permutation file!"; exit; }
            break;
        default: 
            echo "Cipher tidak dikenal atau tidak didukung untuk file!"; 
            exit;
    }

    // buat payload dengan metadata
    $payload = [
        'cipher' => $cipher,
        'ext' => $ext,
        'data' => base64_encode($cipher_bytes)
    ];
    $saveFile = $uploadDir . 'encrypted_' . $cipher . '.dat';
    file_put_contents($saveFile, serialize($payload));

    echo "File berhasil dienkripsi dengan <b>$cipher cipher</b>: <b>$saveFile</b> (ekstensi asli .$ext)";
}
?>
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
    echo "<b>Hasil Enkripsi:</b><br>".chunk_split($result,5,' ');
}
// kalau file diupload
elseif(!empty($_FILES['file']['tmp_name'])){
    $data = file_get_contents($_FILES['file']['tmp_name']);
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION); // ambil ekstensi

    if($cipher=='shift') {
        $cipher_bytes = shift_encrypt_file($data, intval($key));
    } else { 
        echo "Hanya Shift Cipher untuk file binary"; 
        exit; 
    }

    // buat payload dengan metadata
    $payload = [
        'ext' => $ext,
        'data' => base64_encode($cipher_bytes)
    ];
    $saveFile = $uploadDir . 'encrypted.dat';
    file_put_contents($saveFile, serialize($payload));

    echo "File berhasil dienkripsi: <b>$saveFile</b> (ekstensi asli .$ext)";
}
?>

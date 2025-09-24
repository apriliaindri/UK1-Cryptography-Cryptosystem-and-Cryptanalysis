<?php
// ===================== SHIFT CIPHER =====================
function shift_encrypt($text, $key) {
    $text = strtoupper($text);
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $char = $text[$i];
        $result .= chr((ord($char) - 65 + $key) % 26 + 65);
    }
    return $result;
}

function shift_decrypt($text, $key) {
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $char = $text[$i];
        $result .= chr((ord($char) - 65 - $key + 26) % 26 + 65);
    }
    return $result;
}

function shift_encrypt_file($data, $key) {
    $cipher_bytes = '';
    for($i=0;$i<strlen($data);$i++){
        $cipher_bytes .= chr((ord($data[$i]) + $key) % 256);
    }
    return $cipher_bytes;
}

function shift_decrypt_file($data, $key) {
    $plain_bytes = '';
    for($i=0;$i<strlen($data);$i++){
        $plain_bytes .= chr((ord($data[$i]) - $key + 256) % 256);
    }
    return $plain_bytes;
}

// ===================== SUBSTITUTION CIPHER =====================
function substitution_encrypt($text, $key) {
    $text = strtoupper($text);
    $alphabet = range('A','Z');
    $map = array_combine($alphabet, str_split(strtoupper($key)));
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $result .= $map[$text[$i]];
    }
    return $result;
}

function substitution_decrypt($text, $key) {
    $alphabet = range('A','Z');
    $map = array_combine(str_split(strtoupper($key)), $alphabet);
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $result .= $map[$text[$i]];
    }
    return $result;
}

// ===================== AFFINE CIPHER =====================
function affine_encrypt($text, $a, $b) {
    $text = strtoupper($text);
    $result = '';
    for($i=0;$i<strlen($text);$i++){
        $result .= chr((($a*(ord($text[$i])-65)+$b)%26)+65);
    }
    return $result;
}

function affine_decrypt($text, $a, $b) {
    $text = strtoupper($text);
    $result = '';
    $a_inv = mod_inverse($a,26);
    if($a_inv===false) die("Tidak ada invers modulo untuk a=$a");
    for($i=0;$i<strlen($text);$i++){
        $result .= chr((($a_inv*(ord($text[$i])-65-$b+26))%26)+65);
    }
    return $result;
}

// ===================== VIGENERE CIPHER =====================
function vigenere_encrypt($text, $key) {
    $text = strtoupper(preg_replace("/[^A-Z]/","",$text));
    $key  = strtoupper(preg_replace("/[^A-Z]/","",$key));
    $result = '';
    $keyLen = strlen($key);

    if ($keyLen === 0) {
        return "Error: Kunci Vigenere tidak boleh kosong atau non-huruf.";
    }

    for ($i=0; $i<strlen($text); $i++) {
        $shift = ord($key[$i % $keyLen]) - 65;
        $result .= chr((ord($text[$i]) - 65 + $shift) % 26 + 65);
    }
    return $result;
}

function vigenere_decrypt($text, $key) {
    $text = strtoupper(preg_replace("/[^A-Z]/","",$text));
    $key  = strtoupper(preg_replace("/[^A-Z]/","",$key));
    $result = '';
    $keyLen = strlen($key);

    if ($keyLen === 0) {
        return "Error: Kunci Vigenere tidak boleh kosong atau non-huruf.";
    }

    for ($i=0; $i<strlen($text); $i++) {
        $shift = ord($key[$i % $keyLen]) - 65;
        $result .= chr((ord($text[$i]) - 65 - $shift + 26) % 26 + 65);
    }
    return $result;
}

// ===================== PERMUTATION CIPHER =====================
function permutation_encrypt($text, $key){
    $text = strtoupper($text);
    $alphabet = range('A','Z');
    $map = array_combine($alphabet,str_split(strtoupper($key)));
    $result = '';
    for($i=0;$i<strlen($text);$i++) $result .= $map[$text[$i]];
    return $result;
}

function permutation_decrypt($text,$key){
    $alphabet = range('A','Z');
    $map = array_combine(str_split(strtoupper($key)),$alphabet);
    $result = '';
    for($i=0;$i<strlen($text);$i++) $result .= $map[$text[$i]];
    return $result;
}

// ===================== MOD INVERSE =====================
function mod_inverse($a,$m){
    $a = $a % $m;
    for($x=1;$x<$m;$x++){
        if(($a*$x)%$m==1) return $x;
    }
    return false;
}
?>

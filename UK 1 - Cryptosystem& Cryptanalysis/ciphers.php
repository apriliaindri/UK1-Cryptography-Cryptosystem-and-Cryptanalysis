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

// ===================== HILL CIPHER =====================

function hill_transform($text, $matrix) {
    $result = '';
    $n = count($matrix);
    $text_len = strlen($text);

    for ($i = 0; $i < $text_len; $i += $n) {
        $block = substr($text, $i, $n);
        $vector = [];
        for ($j = 0; $j < $n; $j++) {
            $vector[$j] = ord($block[$j]) - 65;
        }

        $result_vector = array_fill(0, $n, 0);

        for ($row = 0; $row < $n; $row++) {
            for ($col = 0; $col < $n; $col++) {
                $result_vector[$row] += $matrix[$row][$col] * $vector[$col];
            }
            $result_vector[$row] %= 26;
        }

        for ($j = 0; $j < $n; $j++) {
            $result .= chr($result_vector[$j] + 65);
        }
    }
    return $result;
}

function hill_encrypt($text, $keyMatrix) {
    $n = count($keyMatrix);
    if (strlen($text) % $n != 0) {
        $text .= str_repeat('X', $n - (strlen($text) % $n));
    }
    return hill_transform($text, $keyMatrix);
}

function hill_decrypt($text, $keyMatrix) {
    $invMatrix = matrix_inverse_mod($keyMatrix, 26);
    if ($invMatrix === false) {
        return "Error: Matriks kunci tidak dapat di-invers. Dekripsi tidak mungkin dilakukan.";
    }
    return hill_transform($text, $invMatrix);
}

function matrix_inverse_mod($matrix, $m) {
    $n = count($matrix);
    $det = 0;
    
    if ($n == 2) {
        $det = $matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0];
    } elseif ($n == 3) {
        $det = $matrix[0][0] * ($matrix[1][1] * $matrix[2][2] - $matrix[1][2] * $matrix[2][1]) -
               $matrix[0][1] * ($matrix[1][0] * $matrix[2][2] - $matrix[1][2] * $matrix[2][0]) +
               $matrix[0][2] * ($matrix[1][0] * $matrix[2][1] - $matrix[1][1] * $matrix[2][0]);
    } else {
        return false; 
    }

    $det = (($det % $m) + $m) % $m;

    $det_inv = mod_inverse($det, $m);
    if ($det_inv === false) {
        return false;
    }

    $adj = array_fill(0, $n, array_fill(0, $n, 0));
    $inv = array_fill(0, $n, array_fill(0, $n, 0));

    if ($n == 2) {
        $adj[0][0] = $matrix[1][1];
        $adj[0][1] = -$matrix[0][1];
        $adj[1][0] = -$matrix[1][0];
        $adj[1][1] = $matrix[0][0];
    } elseif ($n == 3) {
        $adj[0][0] = ($matrix[1][1] * $matrix[2][2] - $matrix[1][2] * $matrix[2][1]);
        $adj[0][1] = -($matrix[0][1] * $matrix[2][2] - $matrix[0][2] * $matrix[2][1]); 
        $adj[0][2] = ($matrix[0][1] * $matrix[1][2] - $matrix[0][2] * $matrix[1][1]); 

        $adj[1][0] = -($matrix[1][0] * $matrix[2][2] - $matrix[1][2] * $matrix[2][0]);
        $adj[1][1] = ($matrix[0][0] * $matrix[2][2] - $matrix[0][2] * $matrix[2][0]);
        $adj[1][2] = -($matrix[0][0] * $matrix[1][2] - $matrix[0][2] * $matrix[1][0]);

        $adj[2][0] = ($matrix[1][0] * $matrix[2][1] - $matrix[1][1] * $matrix[2][0]); 
        $adj[2][1] = -($matrix[0][0] * $matrix[2][1] - $matrix[0][1] * $matrix[2][0]);
        $adj[2][2] = ($matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0]);
    }

    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $inv[$i][$j] = (($adj[$i][$j] * $det_inv) % $m + $m) % $m;
        }
    }

    return $inv;
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

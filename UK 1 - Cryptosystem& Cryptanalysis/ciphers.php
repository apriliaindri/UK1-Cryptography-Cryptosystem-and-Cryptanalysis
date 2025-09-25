<?php
// ===================== SHIFT CIPHER =====================
function shift_encrypt($text, $key) {
    $text = strtoupper($text);
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $result .= chr((ord($char) - 65 + $key) % 26 + 65);
        }
    }
    return $result;
}

function shift_decrypt($text, $key) {
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $result .= chr((ord($char) - 65 - $key + 26) % 26 + 65);
        }
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
    $key = strtoupper($key);
    
    // Validasi kunci harus 26 huruf unik
    if (strlen($key) != 26) {
        return "Error: Kunci substitution harus 26 huruf unik!";
    }
    
    $key_chars = str_split($key);
    if (count(array_unique($key_chars)) != 26) {
        return "Error: Kunci substitution harus berisi 26 huruf yang berbeda!";
    }
    
    $map = array_combine($alphabet, $key_chars);
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $result .= $map[$char];
        }
    }
    return $result;
}

function substitution_decrypt($text, $key) {
    $alphabet = range('A','Z');
    $key = strtoupper($key);
    
    if (strlen($key) != 26) {
        return "Error: Kunci substitution harus 26 huruf unik!";
    }
    
    $key_chars = str_split($key);
    if (count(array_unique($key_chars)) != 26) {
        return "Error: Kunci substitution harus berisi 26 huruf yang berbeda!";
    }
    
    $map = array_combine($key_chars, $alphabet);
    $result = '';
    for ($i=0; $i<strlen($text); $i++) {
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $result .= $map[$char];
        }
    }
    return $result;
}

function substitution_encrypt_file($data, $key) {
    $lookup = create_byte_permutation_table($key);
    if ($lookup === false) return false;

    $cipher_bytes = '';
    for($i = 0; $i < strlen($data); $i++){
        $byte = ord($data[$i]);
        $cipher_bytes .= chr($lookup[$byte]);
    }
    return $cipher_bytes;
}

function substitution_decrypt_file($data, $key) {
    $lookup = create_byte_permutation_table($key);
    if ($lookup === false) return false;
    
    $reverse_lookup = array_flip($lookup);
    
    $plain_bytes = '';
    for($i = 0; $i < strlen($data); $i++){
        $byte = ord($data[$i]);
        $plain_bytes .= chr($reverse_lookup[$byte]);
    }
    return $plain_bytes;
}

function create_byte_permutation_table($key) {
    if (strlen($key) != 26) return false;
    $key = strtoupper($key);
    $key_chars = str_split($key);
    if (count(array_unique($key_chars)) != 26) return false;

    $bytes = range(0, 255);
    
    mt_srand(crc32($key)); 
    
    // Algoritma shuffle (Fisher-Yates)
    for ($i = count($bytes) - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i);
        $tmp = $bytes[$i];
        $bytes[$i] = $bytes[$j];
        $bytes[$j] = $tmp;
    }
    
    return array_combine(range(0, 255), $bytes);
}

// ===================== AFFINE CIPHER =====================
function affine_encrypt($text, $a, $b) {
    $text = strtoupper($text);
    $result = '';
    
    // Cek apakah a coprime dengan 26
    if (gcd($a, 26) != 1) {
        return "Error: Nilai a ($a) harus coprime dengan 26!";
    }
    
    for($i=0;$i<strlen($text);$i++){
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $result .= chr((($a*(ord($char)-65)+$b)%26)+65);
        }
    }
    return $result;
}

function affine_decrypt($text, $a, $b) {
    $text = strtoupper($text);
    $result = '';
    
    // Cek apakah a coprime dengan 26
    if (gcd($a, 26) != 1) {
        return "Error: Nilai a ($a) harus coprime dengan 26!";
    }
    
    $a_inv = mod_inverse($a,26);
    if($a_inv===false) return "Error: Tidak ada invers modulo untuk a=$a";
    
    for($i=0;$i<strlen($text);$i++){
        $char = $text[$i];
        if (ctype_alpha($char)) {
            $result .= chr((($a_inv*(ord($char)-65-$b+26))%26)+65);
        }
    }
    return $result;
}

function affine_encrypt_file($data, $a, $b) {
    if (gcd($a, 256) != 1) return false; // a harus coprime dengan 256
    
    $cipher_bytes = '';
    for($i = 0; $i < strlen($data); $i++){
        $byte = ord($data[$i]);
        $cipher_bytes .= chr(($a * $byte + $b) % 256);
    }
    return $cipher_bytes;
}

function affine_decrypt_file($data, $a, $b) {
    if (gcd($a, 256) != 1) return false;
    
    $a_inv = mod_inverse($a, 256);
    if($a_inv === false) return false;
    
    $plain_bytes = '';
    for($i = 0; $i < strlen($data); $i++){
        $byte = ord($data[$i]);
        $plain_bytes .= chr(($a_inv * ($byte - $b + 256)) % 256);
    }
    return $plain_bytes;
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

function vigenere_encrypt_file($data, $key) {
    $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
    if(strlen($key) === 0) return false;
    
    $cipher_bytes = '';
    $keyLen = strlen($key);
    
    for($i = 0; $i < strlen($data); $i++){
        $shift = ord($key[$i % $keyLen]) - 65;
        $cipher_bytes .= chr((ord($data[$i]) + $shift) % 256);
    }
    return $cipher_bytes;
}

function vigenere_decrypt_file($data, $key) {
    $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
    if(strlen($key) === 0) return false;
    
    $plain_bytes = '';
    $keyLen = strlen($key);
    
    for($i = 0; $i < strlen($data); $i++){
        $shift = ord($key[$i % $keyLen]) - 65;
        $plain_bytes .= chr((ord($data[$i]) - $shift + 256) % 256);
    }
    return $plain_bytes;
}

// ===================== HILL CIPHER =====================

function hill_transform($text, $matrix) {
    $result = '';
    $n = count($matrix);
    $text_len = strlen($text);

    for ($i = 0; $i < $text_len; $i += $n) {
        $block = substr($text, $i, $n);
        // Pastikan blok memiliki panjang yang cukup
        while (strlen($block) < $n) {
            $block .= 'X'; // Padding dengan X
        }
        
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
    
    // Validasi matriks harus persegi
    for ($i = 0; $i < $n; $i++) {
        if (count($keyMatrix[$i]) != $n) {
            return "Error: Matriks kunci harus persegi!";
        }
    }
    
    if (strlen($text) % $n != 0) {
        $text .= str_repeat('X', $n - (strlen($text) % $n));
    }
    
    return hill_transform($text, $keyMatrix);
}

function hill_decrypt($text, $keyMatrix) {
    $n = count($keyMatrix);
    
    // Validasi matriks harus persegi
    for ($i = 0; $i < $n; $i++) {
        if (count($keyMatrix[$i]) != $n) {
            return "Error: Matriks kunci harus persegi!";
        }
    }
    
    $invMatrix = matrix_inverse_mod($keyMatrix, 26);
    if ($invMatrix === false) {
        return "Error: Matriks kunci tidak dapat di-invers. Dekripsi tidak mungkin dilakukan.";
    }
    return hill_transform($text, $invMatrix);
}

function hill_transform_file($data, $matrix) {
    $result = '';
    $n = count($matrix);
    $data_len = strlen($data);
    
    // Pad data jika perlu
    while($data_len % $n != 0) {
        $data .= "\0";
        $data_len++;
    }

    for ($i = 0; $i < $data_len; $i += $n) {
        $block = substr($data, $i, $n);
        $vector = [];
        for ($j = 0; $j < $n; $j++) {
            $vector[$j] = ord($block[$j]);
        }

        $result_vector = array_fill(0, $n, 0);

        for ($row = 0; $row < $n; $row++) {
            for ($col = 0; $col < $n; $col++) {
                $result_vector[$row] += $matrix[$row][$col] * $vector[$col];
            }
            $result_vector[$row] %= 256;
        }

        for ($j = 0; $j < $n; $j++) {
            $result .= chr($result_vector[$j]);
        }
    }
    return $result;
}

function hill_encrypt_file($data, $keyMatrix) {
    return hill_transform_file($data, $keyMatrix);
}

function hill_decrypt_file($data, $keyMatrix) {
    $invMatrix = matrix_inverse_mod_256($keyMatrix, 256);
    if ($invMatrix === false) {
        return false;
    }
    return hill_transform_file($data, $invMatrix);
}

function matrix_determinant($matrix) {
    $n = count($matrix);
    
    if ($n == 1) {
        return $matrix[0][0];
    } elseif ($n == 2) {
        return $matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0];
    } elseif ($n == 3) {
        return $matrix[0][0] * ($matrix[1][1] * $matrix[2][2] - $matrix[1][2] * $matrix[2][1]) -
               $matrix[0][1] * ($matrix[1][0] * $matrix[2][2] - $matrix[1][2] * $matrix[2][0]) +
               $matrix[0][2] * ($matrix[1][0] * $matrix[2][1] - $matrix[1][1] * $matrix[2][0]);
    }
    return false;
}

function matrix_inverse_mod($matrix, $m) {
    $n = count($matrix);
    $det = matrix_determinant($matrix);
    
    if ($det === false) return false;
    
    $det = (($det % $m) + $m) % $m;

    $det_inv = mod_inverse($det, $m);
    if ($det_inv === false) {
        return false;
    }

    $adj = array_fill(0, $n, array_fill(0, $n, 0));

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
    } else {
        return false;
    }

    $inv = array_fill(0, $n, array_fill(0, $n, 0));
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $inv[$i][$j] = (($adj[$i][$j] * $det_inv) % $m + $m) % $m;
        }
    }

    return $inv;
}

function matrix_inverse_mod_256($matrix, $m) {
    return matrix_inverse_mod($matrix, $m);
}


// ===================== PERMUTATION CIPHER =====================

function get_transposition_key_order($key) {
    $key = strtoupper($key);
    $key_chars = str_split($key);
    $sorted_key_chars = $key_chars;
    sort($sorted_key_chars);
    
    $order = [];
    $used_indices = [];
    
    foreach ($sorted_key_chars as $char) {
        for ($i = 0; $i < count($key_chars); $i++) {
            if ($key_chars[$i] == $char && !in_array($i, $used_indices)) {
                $order[] = $i;
                $used_indices[] = $i;
                break;
            }
        }
    }
    
    $read_order = array_fill(0, count($order), 0);
    foreach ($order as $i => $index) {
        $read_order[$index] = $i;
    }
    
    return $read_order;
}


function permutation_encrypt($text, $key) {
    $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
    $text = strtoupper(preg_replace("/[^A-Z]/", "", $text));
    
    if (strlen($key) == 0) return "Error: Kunci tidak boleh kosong!";
    
    $key_order = get_transposition_key_order($key);
    $num_cols = strlen($key);
    
    $text_len = strlen($text);
    if ($text_len % $num_cols != 0) {
        $text .= str_repeat('X', $num_cols - ($text_len % $num_cols));
    }
    
    $columns = array_fill(0, $num_cols, '');
    for ($i = 0; $i < strlen($text); $i++) {
        $columns[$i % $num_cols] .= $text[$i];
    }
    
    $result = '';
    $sorted_order = $key_order;
    sort($sorted_order);
    
    foreach ($sorted_order as $order_val) {
        $col_index = array_search($order_val, $key_order);
        $result .= $columns[$col_index];
    }
    
    return $result;
}


function permutation_decrypt($ciphertext, $key) {
    $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
    $ciphertext = strtoupper(preg_replace("/[^A-Z]/", "", $ciphertext));
    
    if (strlen($key) == 0) return "Error: Kunci tidak boleh kosong!";
    
    $key_order = get_transposition_key_order($key);
    $num_cols = strlen($key);
    $cipher_len = strlen($ciphertext);
    $num_rows = ceil($cipher_len / $num_cols);
    
    $columns = array_fill(0, $num_cols, '');
    $cipher_pointer = 0;
    
    $sorted_order = $key_order;
    sort($sorted_order);

    foreach ($sorted_order as $order_val) {
        $col_index = array_search($order_val, $key_order);
        $columns[$col_index] = substr($ciphertext, $cipher_pointer, $num_rows);
        $cipher_pointer += $num_rows;
    }
    
    $result = '';
    for ($i = 0; $i < $num_rows; $i++) {
        for ($j = 0; $j < $num_cols; $j++) {
            if (isset($columns[$j][$i])) {
                $result .= $columns[$j][$i];
            }
        }
    }
    
    return rtrim($result, 'X');
}

function permutation_encrypt_file($data, $key) {
    $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
    if (strlen($key) == 0) return false;
    
    $key_order = get_transposition_key_order($key);
    $num_cols = strlen($key);
    
    // Padding: Tambahkan null byte '\0' jika perlu (standar untuk file biner)
    $data_len = strlen($data);
    if ($data_len % $num_cols != 0) {
        $data .= str_repeat("\0", $num_cols - ($data_len % $num_cols));
    }
    
    $columns = array_fill(0, $num_cols, '');
    for ($i = 0; $i < strlen($data); $i++) {
        $columns[$i % $num_cols] .= $data[$i];
    }
    
    $result = '';
    $sorted_order = $key_order;
    sort($sorted_order);
    
    foreach ($sorted_order as $order_val) {
        $col_index = array_search($order_val, $key_order);
        $result .= $columns[$col_index];
    }
    
    return $result;
}

function permutation_decrypt_file($data, $key) {
    $key = strtoupper(preg_replace("/[^A-Z]/", "", $key));
    if (strlen($key) == 0) return false;
    
    $key_order = get_transposition_key_order($key);
    $num_cols = strlen($key);
    $data_len = strlen($data);
    
    if ($data_len % $num_cols != 0) {
        return "Error: Data terenkripsi tidak valid untuk kunci ini.";
    }
    
    $num_rows = $data_len / $num_cols;
    
    $columns = array_fill(0, $num_cols, '');
    $data_pointer = 0;
    
    $sorted_order = $key_order;
    sort($sorted_order);

    foreach ($sorted_order as $order_val) {
        $col_index = array_search($order_val, $key_order);
        $columns[$col_index] = substr($data, $data_pointer, $num_rows);
        $data_pointer += $num_rows;
    }
    
    $result = '';
    for ($i = 0; $i < $num_rows; $i++) {
        for ($j = 0; $j < $num_cols; $j++) {
            if (isset($columns[$j][$i])) {
                $result .= $columns[$j][$i];
            }
        }
    }
    
    return rtrim($result, "\0");
}


// ===================== HELPER FUNCTIONS =====================
function mod_inverse($a,$m){
    $a = $a % $m;
    for($x=1;$x<$m;$x++){
        if(($a*$x)%$m==1) return $x;
    }
    return false;
}

function gcd($a, $b) {
    while ($b != 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }
    return $a;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kriptosistem Sederhana</title>
<link rel="stylesheet" href="assets/style.css">
<style>
body { font-family: Arial; background:#f0f0f0; padding:20px; }
h1 { color: #333; }
form { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow:0 0 10px #ccc; }
textarea, input[type=text], select { width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 4px; border:1px solid #ccc; }
button { padding: 10px 15px; border:none; border-radius: 4px; background:#007bff; color:#fff; cursor:pointer; }
button:hover { background:#0056b3; }
#encryptResult, #decryptResult { background:#fff; padding:10px; border-radius:5px; box-shadow:0 0 5px #ccc; }
</style>
</head>
<body>
<h1>Kriptosistem Sederhana</h1>

<h2>Enkripsi</h2>
<form id="encryptForm" enctype="multipart/form-data">
    <label>Pilih Cipher:</label>
    <select name="cipher">
        <option value="shift">Shift Cipher</option>
        <option value="substitution">Substitution Cipher</option>
        <option value="affine">Affine Cipher</option>
        <option value="vigenere">Vigenere Cipher</option>
        <option value="hill">Hill Cipher</option>
        <option value="permutation">Permutation Cipher</option>
    </select><br>

    <label>Pesan (Text):</label><br>
    <textarea name="message" rows="5"></textarea><br>

    <label>Atau Unggah File:</label>
    <input type="file" name="file"><br>

    <label>Kunci:</label>
    <input type="text" name="key" placeholder="Shift: angka, Substitution/Permutation: 26 huruf, Vigenere: teks, Hill: angka koma misal 3,3,2,5"><br>

    <button type="submit">Enkripsi</button>
</form>
<div id="encryptResult"></div>

<h2>Dekripsi</h2>
<form id="decryptForm" enctype="multipart/form-data">
    <label>Unggah File Cipher atau Masukkan Ciphertext:</label><br>
    <input type="file" name="cipherfile"><br>
    <textarea name="ciphertext" rows="5"></textarea><br>

    <label>Kunci:</label>
    <input type="text" name="key"><br>

    <label>Pilih Cipher:</label>
    <select name="cipher">
        <option value="shift">Shift Cipher</option>
        <option value="substitution">Substitution Cipher</option>
        <option value="affine">Affine Cipher</option>
        <option value="vigenere">Vigenere Cipher</option>
        <option value="hill">Hill Cipher</option>
        <option value="permutation">Permutation Cipher</option>
    </select><br>

    <button type="submit">Dekripsi</button>
</form>
<div id="decryptResult"></div>

<script>
document.getElementById('encryptForm').addEventListener('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);
    fetch('encrypt.php', { method:'POST', body: formData })
        .then(resp=>resp.text())
        .then(data => { document.getElementById('encryptResult').innerHTML = data; });
});

document.getElementById('decryptForm').addEventListener('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);
    fetch('decrypt.php', { method:'POST', body: formData })
        .then(resp=>resp.text())
        .then(data => { document.getElementById('decryptResult').innerHTML = data; });
});
</script>
</body>
</html>

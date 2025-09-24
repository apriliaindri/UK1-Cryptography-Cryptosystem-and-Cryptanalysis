<!DOCTYPE html> 
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Kriptosistem Sederhana</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
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
      </select>

      <label>Pesan (Text):</label>
      <textarea name="message" rows="5"></textarea>

      <label>Atau Unggah File:</label>
      <input type="file" name="file">

      <label>Kunci:</label>
      <input type="text" name="key" placeholder="Shift: angka, Substitution/Permutation: 26 huruf, Vigenere: teks, Hill: angka koma misal 3,3,2,5">

      <button type="submit">Enkripsi</button>
  </form>
  <div id="encryptResult" class="output"></div>

  <h2>Dekripsi</h2>
  <form id="decryptForm" enctype="multipart/form-data">
      <label>Unggah File Cipher atau Masukkan Ciphertext:</label>
      <input type="file" name="cipherfile">
      <textarea name="ciphertext" rows="5"></textarea>

      <label>Kunci:</label>
      <input type="text" name="key">

      <label>Pilih Cipher:</label>
      <select name="cipher">
          <option value="shift">Shift Cipher</option>
          <option value="substitution">Substitution Cipher</option>
          <option value="affine">Affine Cipher</option>
          <option value="vigenere">Vigenere Cipher</option>
          <option value="hill">Hill Cipher</option>
          <option value="permutation">Permutation Cipher</option>
      </select>

      <button type="submit">Dekripsi</button>
  </form>
  <div id="decryptResult" class="output"></div>
</div>

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

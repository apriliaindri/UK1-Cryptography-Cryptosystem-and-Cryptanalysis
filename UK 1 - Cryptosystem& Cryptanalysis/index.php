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
      <small>
  <ul>
    <li>Shift: masukkan angka, misal <b>3</b></li>
    <li>Substitution: masukkan 26 huruf unik</li>
    <li>Vigenere: masukkan teks, misal <b>KUNCI</b></li>
    <li>Hill: matriks dalam format angka koma, misal <b>3,3,2,5</b></li>
    <li>Permutation: masukkan 26 huruf untuk susunan abjad</li>
  </ul>
</small>
      <input type="text" name="key">

      <button type="submit">Enkripsi</button>
  </form>
  <div id="encryptResult" class="output"></div>

  <h2>Dekripsi</h2>
  <form id="decryptForm" enctype="multipart/form-data">
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
      <textarea name="ciphertext" rows="5"></textarea>


      <label>Atau Unggah File:</label>
      <input type="file" name="cipherfile">

      <label>Kunci:</label>
      <small>
        <ul>
    <li>Shift: masukkan angka, misal <b>3</b></li>
    <li>Substitution: masukkan 26 huruf unik</li>
    <li>Vigenere: masukkan teks, misal <b>KUNCI</b></li>
    <li>Hill: matriks dalam format angka koma, misal <b>3,3,2,5</b></li>
    <li>Permutation: masukkan 26 huruf untuk susunan abjad</li>
  </ul>
</small>
      <input type="text" name="key">

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

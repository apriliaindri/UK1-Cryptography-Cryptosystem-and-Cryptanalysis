# Kriptosistem Sederhana

Sebuah aplikasi web sederhana untuk melakukan enkripsi dan dekripsi teks atau file menggunakan berbagai algoritma kriptografi klasik.

## Deskripsi

Proyek ini adalah implementasi dari beberapa metode kriptografi dasar dalam format antarmuka web yang mudah digunakan. Pengguna dapat memasukkan pesan, memilih metode cipher, memberikan kunci, dan mendapatkan hasilnya secara langsung.

**Algoritma yang Didukung:**
- Shift Cipher
- Substitution Cipher
- Affine Cipher
- Vigenere Cipher
- Hill Cipher
- Permutation Cipher

## Prasyarat

Sebelum menjalankan program ini, pastikan Anda memiliki lingkungan server web lokal yang sudah terpasang dan berjalan, karena aplikasi ini menggunakan PHP untuk proses *backend*.

# Cara Mnejalankan Program

1. Clone atau Unduk Repositori
Clone repositori dengan perintah berikut

` git clone https://github.com/nama-anda/nama-repositori.git `

2. Pindahkan Folder Proyek 
Pindahkan seluruh folder proyek ini ke dalam direktori htdocs di dalam folder instalasi XAMPP Anda.

3. Jalankan XAMPP
Buka **XAMPP Control Panel** dan klik tombol **"Start"** pada modul **Apache**.

4. Buka di Browser
Buka browser web Anda (Chrome, Firefox, dll.) dan akses aplikasi melalui alamat berikut. Ganti nama-folder-proyek dengan nama file index.
` http://localhost/UK1-Cryptography-Cryptosystem-and-Cryptanalysis/UK%201%20-%20Cryptosystem&%20Cryptanalysis/index.php `

# Cara Menggunakan

Aplikasi ini memiliki dua fungsi utama.

**Enkripsi**
1. Pilih salah satu algoritma cipher dari menu dropdown.
2. Ketik pesan (*plaintext*) Anda di dalam kotak teks atau unggah sebuah file.
3. Masukkan Kunci yang sesuai dengan petunjuk yang diberikan.
4. Klik tombol **Enkripsi"**.
5. Hasil enkripsi (*ciphertext*) akan muncul di kotak output di bawahnya.

**Dekripsi**
1. Pilih algoritma cipher yang sama dengan yang digunakan untuk enkripsi.
2. Ketik ciphertext Anda di dalam kotak teks atau unggah file yang sudah terenkripsi.
3. Masukkan Kunci yang sama persis dengan yang digunakan saat enkripsi.
4. Klik tombol **"Dekripsi"**.
5. Hasil dekripsi (plaintext) akan kembali muncul di kotak output.
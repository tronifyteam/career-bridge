## **Job Board Chat Translation Flow** 

_Job Board Chat Translation Spec | Version 1.0 | Confidential Draft_ 

## **Tujuan Fitur** 

- Membantu employer dan job seeker berkomunikasi di chat interview tanpa keluar dari aplikasi. 

- Chat tetap bisa digunakan seperti chat biasa. Translation hanya aktif ketika user menyalakan fitur tersebut. 

- Arah penerjemahan mengikuti bahasa yang dipilih user di awal / di profile. 

## **Flow Utama** 

1. Job seeker apply ke sebuah job. 

2. Employer menyetujui kandidat untuk tahap interview / invite interview. 

3. Sistem membuat atau membuka chat room 1-on-1 antara employer dan job seeker. 

4. Di chat room, tombol Translate / Terjemahkan muncul setelah status interview aktif. 

5. Kedua user tetap bisa chat normal ketika translation OFF. 

6. Jika translation ON, pesan akan diterjemahkan otomatis ke bahasa pilihan penerima. 

7. User tetap bisa melihat original message dan copy-paste original maupun hasil translation. 

## **Contoh Arah Penerjemahan** 

|**Pengirim**|**Bahasa Pengirim**|**Penerima**|**Bahasa Tujuan**|**Yang Dilihat**<br>**Penerima**|
|---|---|---|---|---|
|Employer|Mandarin / zh-TW|Job seeker|Indonesia / id|Pesan employer<br>diterjemahkan ke<br>Bahasa Indonesia.|
|Job seeker|Indonesia / id|Employer|Mandarin / zh-TW|Pesan job seeker<br>diterjemahkan ke<br>Traditional Mandarin.|



## **Perilaku di Chat Window** 

|**Komponen**|**Requirement Flow**|
|---|---|
|Translate Button|Muncul hanya setelah employer approve / invite interview.<br>Tombol bisa ON/OFF.|
|Translation OFF|Chat berjalan normal. Pesan tampil sesuai teks yang<br>dikirim user.|
|Translation ON|Pesan receiver tampil sebagai hasil translation ke<br>preferred_language receiver.|
|View Original|<br>Receiver bisa membuka teks asli agar tidak ada salah<br>paham.|
|Copy Text|Long press message bubble harus tetap bisa Copy<br>Original dan Copy Translation.|
|Retry Translation|<br>Jika translation gagal, tampilkan original message +<br>tombol retry. Jangan block chat.|



## **Logic Penerjemahan Sederhana** 

- Setiap user memiliki preferred_language di profile, misalnya employer = zh-TW dan job seeker = id. 

- Saat user mengirim pesan, backend menyimpan original message terlebih dahulu. 

- Jika translation aktif untuk penerima, backend menerjemahkan original message ke preferred_language penerima. 

- Jika kedua user memilih bahasa yang sama, sistem tidak perlu memanggil translation API. 

Job Board Chat Translation Spec | Version 1.0 | Confidential Draft 

- Untuk mengurangi error ketika user mengetik campuran bahasa, source language sebaiknya auto-detect jika provider mendukung. Target language tetap mengikuti bahasa penerima. 

## **Anti-OCR Ringan, Tetapi Copy-Paste Tetap Bisa** 

**Requirement utama:** buat perlindungan ringan agar isi chat tidak mudah dibaca dari screenshot/OCR biasa, tetapi jangan menghilangkan kemampuan user untuk copy-paste teks. 

|**Item**|**Requirement**|
|---|---|
|Copy-paste|Wajib tetap aktif untuk original text dan translated text.<br>Jangan disable text selection/copy.|
|Jangan ubah teks jadi gambar|Jangan render chat text sebagai image/canvas hanya<br>demi anti-OCR, karena akan merusak copy-paste.|
|Watermark ringan|<br>Tampilkan watermark tipis di area chat interview, misalnya<br>user ID pendek / job ID / timestamp.|
|App switcher privacy|Blur atau tutup isi chat ketika app masuk background /<br>recent-app preview.|
|Screen capture hardening|<br>Jika memungkinkan, tambahkan konfigurasi optional<br>untuk Android secure screen. Untuk iOS, cukup<br>detection/warning saat screen recording/mirroring<br>terdeteksi.|
|Logging|Jangan kirim full chat message ke analytics/debug log<br>client-side.|



_Catatan untuk freelancer: tidak perlu membuat sistem 100% anti-OCR. Fokusnya adalah light protection tanpa mengorbankan copy-paste dan UX chat._ 

## **Edge Case Penting** 

|**Case**|**Expected Behavior**|
|---|---|
|Userbelum memilihbahasa|Minta user memilihbahasa sebelumtranslationbisa ON.|
|Bahasa kedua user sama|Tidak perlu translate; tampilkan original.|
|Translation API error|<br>Original message tetap tampil; sediakan retry.|
|Network lambat|Original message terkirim dulu; translation boleh muncul<br>setelahselesai.|
|Employer belum approve interview|Windows chat tidak muncul.|



## **Definition of Done** 

- Employer approve candidate -> chat room terbuka -> tombol Translate muncul. 

- Translation OFF: chat tetap normal. 

- Translation ON: pesan employer zh-TW diterjemahkan ke id untuk worker, dan pesan worker id diterjemahkan ke zh-TW untuk employer. 

- User bisa View Original, Copy Original, dan Copy Translation. 

- Jika translation gagal, chat tidak berhenti dan user bisa retry. 

- Anti-OCR ringan aktif: watermark, app-switcher blur, optional screen-capture hardening, dan tidak ada full-message debug log. 

Job Board Chat Translation Spec | Version 1.0 | Confidential Draft 


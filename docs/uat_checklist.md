# Checklist UAT (User Acceptance Testing) - Pembaruan Sistem 2ne5

Berikut adalah lembar kerja pengujian (UAT Checklist) untuk seluruh fitur, tambalan keamanan, konten legalitas, dan konfigurasi server yang telah diselesaikan hari ini.

---

## 1. Validasi Gaji Hukum Taiwan (Employment Service Act Pasal 5)
Menjamin kepatuhan terhadap hukum ketenagakerjaan Taiwan di mana lowongan dengan gaji di bawah NT$40.000 wajib mencantumkan nominal kisaran secara eksplisit (tidak boleh menggunakan istilah nego).

| ID Tes | Deskripsi Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :---: |
| **VAL-01** | Membuat lowongan baru via `POST /api/jobs` dengan gaji di bawah NT$40k dan mencantumkan kata "Nego"/"面議". | Sistem menolak dengan error `invalid_salary_hidden` (HTTP 422) dan pesan edukasi hukum. | **LULUS** |
| **VAL-02** | Membuat lowongan baru via `POST /api/jobs` dengan kisaran eksplisit (misal: "NT$ 25,000 - 28,000") tanpa kata nego. | Sistem menerima lowongan dan menyimpannya ke basis data. | **LULUS** |
| **VAL-03** | Memperbarui lowongan via `PUT /api/jobs/{id}` dengan menurunkan gaji di bawah NT$40k dan menyembunyikannya (nego). | Sistem menolak pembaruan dengan error `invalid_salary_hidden` (HTTP 422). | **LULUS** |
| **VAL-04** | Memasukkan nominal di atas NT$40k dengan opsi nego (misal: "NT$ 45,000 Nego"). | Sistem mengizinkan pembuatan/pembaruan lowongan karena di atas batas minimum hukum. | **LULUS** |

---

## 2. Sistem Notifikasi Email Transaksional (Milestone 15 & 18)
Mengintegrasikan pengantrean email otomatis saat terjadi aktivitas penting di dalam platform.

| ID Tes | Deskripsi Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :---: |
| **EML-01** | Pekerja melamar pekerjaan lewat aplikasi mobile (`POST /api/jobs/{id}/apply`). | Email `JobApplicationReceivedMail` masuk antrean basis data dan dikirim ke kotak masuk email Majikan (Mailtrap). | **LULUS** |
| **EML-02** | Admin menyetujui (Approve) lowongan kerja tertunda milik Majikan lewat panel admin. | Email `JobStatusUpdatedMail` dikirim ke Majikan dengan status "Disetujui & Dipublikasikan". | **LULUS** |
| **EML-03** | Admin menolak (Reject) lowongan kerja Majikan dengan alasan penolakan tertentu. | Email `JobStatusUpdatedMail` dikirim ke Majikan menampilkan label merah "Ditolak" beserta alasan penolakan dari admin. | **LULUS** |
| **EML-04** | Kecepatan respons pengiriman email pada aplikasi mobile (Queueing check). | Pengiriman didelegasikan ke antrean latar belakang sehingga proses klik "Lamar" di ponsel instan tanpa delay koneksi SMTP. | **LULUS** |

---

## 3. Integrasi & Status Server Produksi (VPS 130.94.34.24)
Memastikan seluruh modul backend telah terpasang secara permanen di server produksi.

| ID Tes | Deskripsi Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :---: |
| **SRV-01** | Sinkronisasi kode backend terbaru di VPS. | Perintah `git status` di direktori `/var/www/migrant_work_tw_be` menunjukkan repositori sinkron dengan commit terbaru GitHub. | **LULUS** |
| **SRV-02** | Status pemroses antrean Supervisor di VPS. | Proses daemon `migrant-worker-queue:migrant-worker-queue_00` terdeteksi berjalan (`RUNNING`) secara aktif untuk mengonsumsi antrean email. | **LULUS** |
| **SRV-03** | Keamanan Pengujian Email di Produksi. | Berkas `.env` server terkonfigurasi ke Mailtrap Sandbox, mencegah email testing terkirim ke email asli pengguna secara tidak sengaja. | **LULUS** |
| **SRV-04** | Migrasi basis data di VPS. | Seluruh migrasi terbaru telah selesai dieksekusi (`Nothing to migrate`). | **LULUS** |

---

## 4. Lokalisasi UI & Pembersihan Hardcoded Text (i18n)
Merapikan antarmuka agar semua label teks mematuhi sistem lokalisasi multi-bahasa.

| ID Tes | Deskripsi Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :---: |
| **LAN-01** | Menelusuri halaman pencarian pekerja (`Browse Workers`) dalam bahasa Indonesia & Inggris. | Label "Verified Badge" dan "Ready to Work" berubah bahasa secara otomatis sesuai preferensi pengguna. | **LULUS** |
| **LAN-02** | Menampilkan halaman detail profil pekerja di sisi majikan. | Tidak ada sisa komentar `TODO i18n` dan semua label utama terjemahan bekerja dengan GetX (`.tr`). | **LULUS** |
| **LAN-03** | Halaman verifikasi selfie saat pendaftaran wizard. | Petunjuk pengambilan gambar tampil dalam bahasa yang dipilih tanpa teks hardcoded Inggris. | **LULUS** |

---

## 5. Keamanan Chat (Anti-Screenshot/Anti-OCR)
Mencegah kebocoran data percakapan sensitif antara majikan dan pekerja migran.

| ID Tes | Deskripsi Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :---: |
| **SEC-01** | Membuka ruang chat di perangkat ponsel Android/iOS. | Flag proteksi layar aktif, layar menjadi hitam saat direkam/di-screenshot guna mencegah aplikasi pihak ketiga mencuri data via OCR. | **LULUS** |

---

## 6. Halaman Hukum & Edukasi Keamanan (Milestone 16 & 20)
Penyediaan panduan resmi dan dokumen hukum wajib rilis publik.

| ID Tes | Deskripsi Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :---: |
| **LGL-01** | Membuka Ketentuan Layanan (Terms of Service) dari menu pengaturan. | Menampilkan berkas hukum dwibahasa (EN & ID) berisi hak-kewajiban pengguna dan aturan penahanan dokumen resmi. | **LULUS** |
| **LGL-02** | Membuka Kebijakan Privasi (Privacy Policy) dari menu pengaturan. | Menampilkan teks dwibahasa yang menjelaskan transparansi penyimpanan berkas identitas sensitif (paspor/ARC). | **LULUS** |
| **LGL-03** | Membuka halaman Pusat Panduan & Edukasi (Guides & Education). | Terdapat 3 opsi panduan statis terpisah: Panduan Pekerja (Safety), Panduan Majikan, dan Panduan Agensi. | **LULUS** |

---

## 7. Pembersihan Warning & Kompilasi Flutter (QA)
Memastikan kesehatan struktur kode aplikasi Flutter menjelang rilis.

| ID Tes | Deskripsi Pengujian | Hasil yang Diharapkan | Status |
| :--- | :--- | :--- | :---: |
| **FLT-01** | Eksekusi analisis statis kode Flutter (`flutter analyze`). | Output menyatakan **`No issues found!`**. Semua deprecated warnings pada Widget `Radio` di paywall berlangganan telah sukses direfaktor menggunakan `RadioGroup`. | **LULUS** |

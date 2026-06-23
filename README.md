# 🪄 ContentAnalyzer AI

**ContentAnalyzer AI** adalah aplikasi analisis performa media sosial dan perencana konten otomatis berbasis web yang dibangun dengan framework **Laravel**. Aplikasi ini dirancang khusus untuk konten kreator, *affiliate marketer*, dan brand partner untuk meriset data akun sosial media (terutama TikTok), mengidentifikasi karakteristik persona akun, menganalisis postingan viral, serta menyusun strategi kalender konten 3 bulan secara otomatis menggunakan kecerdasan buatan (**Gemini AI**).

---

## 🎯 Tujuan Aplikasi
Aplikasi ini bertujuan untuk membantu kreator konten dan *affiliate* meningkatkan angka penjualan produk (*conversion*) secara halus (*soft-selling*). Dengan menganalisis konten-konten terpopuler (*viral posts*) yang sudah ada di akun mereka, AI akan mereplikasi formula sukses tersebut ke dalam rencana konten mingguan baru tanpa memaksa kreator melakukan operasional di luar ranah mereka (seperti mengemas atau mengirimkan paket produk).

---

## 🚀 Fitur Utama

1. **Scraping Real-Time (Data Asli)**:
   * Mengintegrasikan API TikWM untuk menarik data video asli dari akun TikTok (termasuk caption, tanggal post, thumbnail asli, statistik penonton, likes, dan komentar).
   * Paginasi dinamis untuk menarik hingga **50 video terakhir** dalam hitungan detik.

2. **Detektor Konten Viral**:
   * Menyaring dan menyajikan video terpopuler (Top Video) berdasarkan jumlah penonton dan engagement rate (ER).
   * Menganalisis pola tagar (Hashtags) paling sering digunakan yang memicu performa tinggi.

3. **Analisis Karakter & Persona Kreator**:
   * AI mendeteksi tipe arketipe persona kreator (misal: *Aesthetic Reviewer & Style Curator*, *Informative Educator*) berdasarkan nada bicara dan interaksi di videonya.

4. **Perencana Konten 3 Bulan (12 Minggu) Adaptif**:
   * Merancang ide video mingguan lengkap dengan **Topik Konten, Hook Pembuka (3 detik pertama), Konsep Visual/Transisi, dan Draf Caption**.
   * Strategi dioptimalkan khusus untuk promosi *Affiliate* (mengarahkan ke keranjang kuning/link bio, unboxing PR package, honest review) dan diselaraskan secara logis dengan niche (Kecantikan, Kuliner, dll).

5. **Ekspor PDF & CSV**:
   * **Cetak PDF**: Cetak laporan analisis lengkap ke dalam satu lembar PDF yang bersih secara instan (mengadaptasi CSS khusus cetak berwarna putih minimalis agar hemat tinta).
   * **Ekspor CSV**: Unduh data daftar video teranalisis dan tabel kalender konten 12 minggu ke berkas spreadsheet (Excel/CSV).

6. **Rantai Cadangan AI Gemini (Triple-Redundancy)**:
   * Menghubungkan pemanggilan model API secara berantai (`gemini-2.0-flash` ➔ `gemini-1.5-flash` ➔ `gemini-1.5-pro` ➔ `gemini-2.5-flash`) untuk menghindari error overload 503.
   * Dilengkapi simulator cadangan program cerdas berbasis niche dan data video viral asli jika sambungan internet/API terputus.

---

## 🛠️ Spesifikasi Teknologi
* **Core Framework**: Laravel 12
* **Database**: SQLite (Ringan dan tidak memerlukan setup server database tambahan)
* **Frontend**: HTML5, Vanilla CSS (Dark Mode Glassmorphism)
* **Visualisasi**: Chart.js UMD (Tren grafik engagement rate per video)
* **API Integration**: Gemini AI API (Google GenAI) & TikWM API (TikTok Scraper)

---

## 📦 Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi di komputer lokal Anda:

### 1. Prasyarat (Prerequisites)
Pastikan komputer Anda sudah menginstal:
* PHP 8.2 atau lebih tinggi
* Composer
* Node.js & NPM

### 2. Clone Repositori
```bash
git clone <url-repository-github>
cd content-analyzer
```

### 3. Instalasi PHP Dependencies (Composer)
```bash
composer install
```

### 4. Setup File Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Generate kunci enkripsi aplikasi Laravel:
```bash
php artisan key:generate
```

### 5. Masukkan Kunci API Gemini
Buka file `.env` baru Anda menggunakan teks editor, lalu cari baris berikut dan masukkan API Key Gemini Anda (Dapatkan gratis di [Google AI Studio](https://aistudio.google.com/)):
```env
GEMINI_API_KEY=kunci_api_gemini_anda
```

### 6. Jalankan Migrasi Database
Buat file database SQLite kosong di dalam direktori proyek, lalu jalankan migrasi tabel:
```bash
# Windows (PowerShell)
New-Item -ItemType File -Path database/database.sqlite -Force

# Linux / MacOS
touch database/database.sqlite

# Jalankan migrasi
php artisan migrate
```

### 7. Instalasi NPM Dependencies & Build Assets
```bash
npm install
npm run build
```

### 8. Jalankan Server Lokal
Nyalakan server Laravel Artisan:
```bash
php artisan serve
```
Aplikasi Anda sekarang aktif dan dapat diakses melalui browser di alamat: [**http://127.0.0.1:8000**](http://127.0.0.1:8000).

---

## 📖 Cara Penggunaan Aplikasi
1. **Dashboard Awal**:
   * Masukkan **Username** akun TikTok yang ingin dianalisis (tanpa simbol `@`).
   * Pilih **Kategori / Niche Utama** (misalnya *Kecantikan & Fashion*).
   * (Opsional) Ketik **Produk / Jasa yang Ditawarkan** (misalnya *Lipstik Matte Velvet* atau *Layanan Coding Course*).
   * Pilih batas video (**20** atau **50 video** terakhir).
   * Klik **Analisis Sekarang**.
2. **Membaca Hasil**:
   * Lihat ringkasan followers, engagement rate, dan **Persona Kreator** di sidebar kiri.
   * Baca **Laporan Strategis AI** dan **3-Month Content Planner** untuk ide konten harian Anda.
   * Klik **Cetak Laporan PDF** di kanan atas untuk mengunduh versi PDF, atau klik **Unduh Content Planner** untuk menyimpannya sebagai file Excel/CSV.

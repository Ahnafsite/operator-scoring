# Product Requirements Document (PRD)
**Nama Produk:** PM2 Arena Controller (GUI Panel)
**Versi:** 1.1
**Status:** Draft Proyek Lokal

---

## 1. Ringkasan Produk (Executive Summary)
PM2 Arena Controller adalah aplikasi *web-based* lokal yang dirancang untuk mengelola dan memantau ekosistem aplikasi scoring Pencak Silat. Aplikasi ini memberikan antarmuka grafis (GUI) di atas PM2, memungkinkan operator untuk mengonfigurasi, menjalankan, dan memantau layanan *server*, *reverb*, dan *queue* untuk banyak gelanggang tanpa perlu menyentuh terminal saat kejuaraan berlangsung.

## 2. Tujuan & Sasaran (Objectives & Goals)
* **Efisiensi Operasional:** Menyederhanakan proses *startup* dan *shutdown* aplikasi scoring yang kompleks menjadi satu klik.
* **Akurasi Konfigurasi:** Mengatur IP Host dan Port secara visual untuk memastikan perangkat juri (tablet) terhubung dengan benar ke server lokal.
* **Monitoring Real-time & Resource:** Memberikan kepastian status layanan (hidup/mati) dan memantau penggunaan resource (CPU & RAM) secara *real-time* bagi operator.
* **Sangat Ringan (Ultra-Lightweight):** Aplikasi panel kontrol harus beroperasi dengan jejak memori dan CPU yang sangat kecil, sehingga tidak memberikan beban tambahan pada perangkat (seperti MacBook atau Mac Mini) yang sedang menjalankan banyak proses gelanggang.

## 3. Spesifikasi Teknologi (Tech Stack)
* **Backend:** Laravel 12 & PHP 8.4 (Native Execution, tanpa *overhead* virtualisasi).
* **Frontend:** Vue.js 3, Inertia.js, & Tailwind CSS.
* **Database:** SQLite (Portabel & ringan, nol konfigurasi *service* tambahan).
* **Process Manager:** PM2 (Node.js) sebagai *engine* utama di sistem operasi.
* **System Bridge:** `Symfony\Component\Process\Process` untuk komunikasi PHP ke CLI (dengan mekanisme *query* yang dioptimalkan agar ringan).

## 4. Fitur Utama (Core Features)

### 4.1. Dashboard Monitoring Real-time
* Tampilan berbentuk *Grid Card* yang mewakili setiap Gelanggang.
* **Status Indikator Real-time:** Memantau langsung apakah layanan Online, Stopped, atau Errored:
    * **Serve:** `php artisan serve`
    * **Reverb:** `php artisan reverb:start`
    * **Queue:** `php artisan queue:work`
* **Resource Monitoring:** Menampilkan metrik penggunaan **CPU** dan **RAM** untuk setiap proses yang sedang berjalan, diambil langsung dari *engine* PM2.
* **Polling Super Ringan:** Sinkronisasi data GUI dan PM2 menggunakan metode *AJAX polling* via Inertia.js yang diset dengan interval cepat namun direkayasa agar *payload* data sangat kecil, meminimalkan *I/O block*.

### 4.2. Manajemen Konfigurasi Dinamis
* Pengaturan per Gelanggang melalui *modal* GUI:
    * **Nama Gelanggang:** Identitas (Contoh: Gelanggang A).
    * **Project Path Selector:** Memilih lokasi folder menggunakan **Native Directory/Folder Picker** (`<input type="file" webkitdirectory>` pada web), bukan mengetik *path* manual untuk menghindari *typo* dan mempermudah pencarian direktori.
    * **IP Address:** Menentukan Host IP (misal: 192.168.1.10).
    * **Port Config:** Menentukan port unik untuk Web dan Reverb.

### 4.3. Kontrol Eksekusi (One-Click Action)
* **Start All:** Men-generate file konfigurasi JSON PM2 secara otomatis dan menjalankan seluruh servis gelanggang tersebut.
* **Stop All:** Memberhentikan seluruh proses terkait gelanggang tersebut dari PM2.
* **Individual Toggle:** Menjalankan atau menghentikan servis tertentu.

## 5. Kebutuhan Antarmuka (UI/UX)
* **Style:** Pendekatan *minimalist, professional light mode aesthetics* untuk menjaga fokus dan keterbacaan tinggi.
* **Optimasi DOM:** Antarmuka dirancang untuk meminimalkan *re-render* pada elemen yang tidak berubah untuk menjaga aplikasi tetap super ringan di *browser*.
* **Palet Warna:**
    * *Success:* Hijau lembut (Background) & Hijau tua (Teks).
    * *Danger:* Merah muda (Background) & Merah tua (Teks).
    * *Surface:* Putih bersih dengan *shadow* halus untuk *card*.

## 6. Arsitektur Data (SQLite Schema)

### Tabel: `gelanggang_configs`
| Field | Tipe | Deskripsi |
| :--- | :--- | :--- |
| `id` | Integer (PK) | ID Unik |
| `name` | String | Nama Gelanggang |
| `target_path` | String | Path absolut folder project |
| `serve_host` | String | IP Address (LAN/Local) |
| `serve_port` | Integer | Port untuk aplikasi web |
| `reverb_port` | Integer | Port untuk websocket reverb |
| `created_at` | Timestamp | Waktu pembuatan |
| `updated_at` | Timestamp | Waktu pembaruan |

## 7. Prasyarat Sistem (System Prerequisites)
Untuk menjaga agar tetap *ultra-lightweight*, lingkungan hanya membutuhkan:
1.  **PHP 8.4+** terinstal secara global (tanpa Herd atau kontainer tambahan).
2.  **Node.js & PM2** terinstal secara global.
3.  Izin akses baca/tulis pada direktori *project* sasaran.

---
*Dokumen ini dibuat sebagai panduan pengembangan aplikasi lokal PM2 Arena Controller.*

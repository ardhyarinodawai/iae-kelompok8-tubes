# AI Usage Report & Project Activity Documentation

**Peran:** AI Documentation Analyst & Conversation Archivist  
**Proyek:** Service Manajemen Tiket Tenant – Tugas 2 Integrasi Aplikasi Enterprise (BBK2HAB3)

Berikut adalah laporan rekapitulasi komprehensif, terstruktur, dan analitis mengenai seluruh aktivitas *prompting* dan proses pengembangan yang telah terjadi di dalam *room chat* ini.

---

## 1. Ringkasan Umum Percakapan

* **Tujuan Utama Penggunaan AI:** Membantu menerjemahkan kebutuhan bisnis (*business requirements*) dari dokumen tugas kuliah menjadi arsitektur perangkat lunak yang fungsional, menghasilkan *source code* boilerplate, melakukan *troubleshooting* (pencarian kutu/bug) secara instan dari *error log* terminal, dan menyusun laporan akhir secara terotomatisasi.
* **Topik Besar yang Dibahas:** Arsitektur *Microservices* (komunikasi antar-service/REST API), *Containerization* (Docker), Dokumentasi *Endpoint* (Swagger/OpenAPI), dan implementasi GraphQL (Lighthouse) di dalam ekosistem Laravel.
* **Jenis Proyek:** Pembuatan modul backend mandiri bernama **Service Manajemen Tiket Tenant** yang berfungsi untuk menerima keluhan penghuni, dan melakukan validasi ke luar sistem (*Service Listing* dan *Service Kontrak*) sebelum menyimpan data.

---

## 2. Daftar Seluruh Prompt yang Pernah Digunakan

| No | Intensi/Isi Prompt | Kategori | Teknologi/Tools | Output Diminta | Bahasa |
|:---|:---|:---|:---|:---|:---|
| 1 | Menganalisis dokumen PDF Tugas 2 | Research & Analysis | REST, GraphQL, Docker, Swagger | Penjelasan instruksi tugas | Indonesia |
| 2 | Membedah skenario integrasi tiket tenant | System Architecture | HTTP Client, Microservices | Desain arsitektur & logic integrasi | Indonesia |
| 3 | Menanyakan penjelasan konsep Swagger UI | Research / Learning | L5-Swagger, OpenAPI | Penjelasan konseptual | Indonesia |
| 4 | Meminta *coding* Migration, Seeder, & Controller | Coding | Laravel, PHP | *Source code* lengkap dengan Swagger | Indonesia |
| 5 | Meminta detail Model & Migration spesifik | System Architecture | Laravel Eloquent, Database | Desain skema relasi antar-service | Indonesia |
| 6 | *Error Log:* `UNIQUE constraint failed: users.email` | Debugging | SQLite, Laravel Seeder | Solusi *database conflict* | Raw Log |
| 7 | *Error Log:* `Cannot declare class Database\Seeders\Ticket` | Debugging | PHP, Laravel | Solusi *naming collision* | Raw Log |
| 8 | Menanyakan lokasi folder `api` di Laravel | Research / Learning | Laravel 11 Structure | Petunjuk navigasi folder | Indonesia |
| 9 | *Error Log:* `'npx' is not recognized` saat `composer run dev` | Debugging | Node.js, Vite, npm, Composer | Solusi gagal *start server* | Raw Log |
| 10 | *Error Image:* 500 Internal Server Error (no such table) | Debugging | Swagger UI, SQLite | Identifikasi penyebab error API | Image/Log |
| 11 | Meminta skema tabel untuk integrasi GraphQL | Coding | GraphQL, Lighthouse | File `schema.graphql` | Indonesia |
| 12 | Menanyakan beda `CMD` *shell form* vs *exec form* di Docker | Research / Learning | Dockerfile | Penjelasan teknis operasional | Indonesia |
| 13 | *Error Log:* `exit code: 1` pada proses `chown` direktori | Debugging | Docker, Linux CLI | Solusi gagal *build image* | Raw Log |
| 14 | *Error Log:* `failed to connect to the docker API` | Debugging | Docker Desktop, Docker Compose | Solusi *engine not running* | Raw Log |
| 15 | Meminta *reverse-engineering* dari teks terstruktur | Prompt Engineering | Prompting Techniques | *Meta-prompt* terstruktur | Indonesia |
| 16 | Meminta *meta-prompt* untuk rekap log *evidence* tugas | Prompt Engineering | Markdown, Prompting | *Prompt* rekap dokumentasi | Indonesia |
| 17 | Mengeksekusi *prompt* log *evidence* (Tugas 2) | Documentation | Markdown | Laporan tugas akhir (.md) | Indonesia |
| 18 | Mengeksekusi *prompt* "AI Usage Report" (Chat ini) | Documentation/Analysis | Analytics | Laporan Audit Komprehensif | Indonesia |
| 19 | Mengubah format rekap menjadi file Markdown | Documentation | Markdown | File teks `.md` murni | Indonesia |

*(Catatan: Pengelompokan prompt berurutan sesuai alur diskusi aktual)*

---

## 3. Rekap Teknologi dan Stack yang Digunakan

* **Bahasa Pemrograman:** PHP, SQL, GraphQL.
* **Framework:** Laravel (menggunakan struktur Laravel 11, ditandai dari penggunaan `php artisan install:api`).
* **Library/Packages:**
    * `darkaonline/l5-swagger` (OpenAPI Documentation).
    * `nuwave/lighthouse` (GraphQL Server).
    * `mll-lab/laravel-graphql-playground` (GraphQL UI).
    * Laravel HTTP Client (`Illuminate\Support\Facades\Http`).
* **Database:** SQLite (digunakan untuk *local development/testing*).
* **DevOps & Tools:** Docker, Docker Compose, Dockerfile, Git (menyebutkan *repository* GitLab/GitHub), Terminal (PowerShell/VS Code).

---

## 4. Identifikasi Aktivitas User

Sepanjang sesi, user melakukan serangkaian aktivitas krusial untuk *software development lifecycle* (SDLC):
1. **Requirement Gathering:** Menganalisis kebutuhan rubrik penilaian tugas dan aturan bisnis integrasi antar-mahasiswa.
2. **Fitur Creation:** Membangun *endpoint* REST API, memproteksi jalur dengan *API Key*, dan membangun *Query* fungsional di GraphQL.
3. **Troubleshooting (Intensif):** Menelusuri error terkait integritas *database*, duplikasi nama kelas, *environment dependencies* (Node.js/npx), dan sinkronisasi struktur kontainer Docker.
4. **Dokumentasi Teknis:** Menghasilkan anotasi Swagger dan menyusun laporan riwayat interaksi AI sebagai *evidence* administratif.
5. **Prompt Engineering:** Merancang *meta-prompt* untuk memaksa AI menghasilkan struktur dokumen yang spesifik dan terkontrol.

---

## 5. Timeline Aktivitas (Evolusi Proyek)

* **Fase Inisiasi (Prompt 1-3):** Eksplorasi domain proyek. Pemahaman batas-batas tugas antara Dawai (Service Tiket), Rafsan (Listing), dan Akhdan (Kontrak).
* **Fase Implementasi Inti (Prompt 4-5 & 11):** Konstruksi struktur utama aplikasi. Pembuatan File Migration, Eloquent Model, Controller API yang menembak servis eksternal, serta Skema GraphQL.
* **Fase Iterasi & Debugging Kode (Prompt 6-10):** Menghadapi rintangan saat inisialisasi lokal. Kesalahan pada Seeder, konfigurasi *framework* baru (Vite/Laravel 11), dan *state database* yang tidak sinkron saat pengujian Swagger. Seluruh error berhasil diatasi.
* **Fase Deployment/Dockerisasi (Prompt 12-14):** Peralihan dari *local server* (`php artisan serve`) menuju *container*. Menghadapi isu fundamental Docker (*syntax* CMD, urutan eksekusi layer `chown`, dan koneksi Daemon).
* **Fase Finalisasi & Pelaporan (Prompt 15-19):** Fokus bergeser dari koding menjadi dokumentasi administratif. Merancang *template* prompt tingkat lanjut untuk otomatisasi penyusunan laporan akademis dan log pemakaian AI.

---

## 6. Statistik Prompting

* **Total Prompt:** 19
* **Kategori Paling Sering:** **Debugging** (merupakan lebih dari 30% dari total percakapan).
* **Teknologi Paling Sering Muncul:** Laravel & Docker.
* **Jenis Task Terbanyak:** *Error resolution* (menganalisis raw log dari terminal).
* **Bahasa Paling Sering Dipakai:** Bahasa Indonesia dengan terminologi rekayasa perangkat lunak (Inggris).

---

## 7. Insight dan Pola Penggunaan AI

* **Pola Problem Solving (Sangat Efektif):** User menggunakan pola **"Raw Paste"** (menyalin *error* terminal/gambar dan langsung menempelkannya ke *prompt* tanpa penjelasan panjang). Ini adalah praktik terbaik (*best practice*) dalam *debugging* AI karena AI mendapatkan *stack trace* absolut tanpa bias/asumsi dari user. Seluruh *error log* yang diunggah (mulai dari *SQLite error* hingga *Docker Daemon*) berhasil diselesaikan pada percobaan pertama.
* **Workflow Terstruktur:** Alur kerja linear dan logis: *Planning -> Skeleton Code -> Local Test -> Debug -> Containerize -> Document*.
* **Tingkat Technical Depth:** Menengah - Lanjut (*Intermediate-Advanced*). Keterlibatan arsitektur seperti `Http::withHeaders()`, komunikasi *inter-service*, dan *layering* Docker menunjukkan pemahaman teknis sistem terdistribusi yang baik.
* **Pola High-Impact Prompt:** Meminta *reverse engineering* instruksi untuk mendapatkan format *output* tertentu menunjukkan kompetensi *Prompt Engineering* tingkat lanjut.

---

## 8. Ringkasan Akhir

* **Kesimpulan Keseluruhan:** *Room chat* ini merupakan sesi *Pair-Programming* tingkat tinggi yang berfokus pada penyelesaian tugas komprehensif. Sesi berjalan efisien dengan siklus *error-resolution* yang cepat.
* **Progress Proyek:** Target proyek sesuai PDF Tugas 2 dapat dikatakan mencapai **100% tersimulasikan di lingkungan lokal**. Kode berhasil dikonstruksi, *error database/Docker* tuntas ditangani, dan *endpoint* terbukti siap menerima interaksi.
* **Kompetensi yang Terlihat:** Kemampuan beradaptasi dengan *error*, pemahaman operasional *framework* (Laravel), serta kepiawaian dalam memanfaatkan model bahasa (LLM) untuk *code generation* dan *technical writing*.
* **Rekomendasi Next Step:** 1. Melakukan uji integrasi (*Integration Testing*) sesungguhnya dengan URL *endpoint* riil milik Rafsan dan Akhdan saat kontainer dijalankan di jaringan internal yang sama (Docker Network).
    2. Menyimpan semua parameter keamanan (seperti API Key dan URL eksternal) ke dalam *environment variables* (`.env`) agar *service* siap di-*deploy* ke server (VPS/Cloud).

    https://gemini.google.com/share/89f2d78b7f67

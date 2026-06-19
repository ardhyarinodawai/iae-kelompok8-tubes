# 📋 Prompt Engineering Log Terintegrasi — Tugas 3

**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise  
**Tugas:** Tugas 3 - Progress Individual (The Enterprise Digital City)  
**Nama:** Ardhyarino Dawai F    
**AI Collaborators:** Gemini & Claude  
---

## 🗺️ FASE 1: Pemahaman Tugas & Perencanaan Arsitektur

### Sesi 1 — Analisis Kebutuhan & Penentuan Transaksi Kritis
* **Tanggal:** 13 Juni 2026  
* **Tujuan:** Memahami keseluruhan rubrikasi penilaian Tugas 3 (Komponen Analisis 33.33% & Teknis 66.67%) serta menentukan alur kerja integrasi 3 lapis eksternal (SSO Dosen -> SOAP Audit -> RabbitMQ).
* **Prompt yang digunakan:**
  > *"jelaskan tugas 3 dan beri urutan yang harus dikerjakan"*

* **Konteks:** Diberikan dokumen referensi API IAE SSO, modul instruksi, serta slide tugas besar dari dosen (Ekky Novriza Alam). Tugas 3 mewajibkan implementasi 3 komponen backend utama: Federated SSO, SOAP XML Client, dan AMQP Publisher.
* **Hasil & Insight:**
  * Transaksi kritis harus bersifat **state-changing** (mengubah data/stok/keuangan). Berdasarkan cakupan domain *Tenant Management*, ditentukan dua kandidat utama: `POST /api/v1/tenants` (registrasi tenant baru) dan `POST /api/v1/tickets` (pembuatan tiket komplain/layanan tenant yang melibatkan relasi lintas service: *Listing* & *Kontrak*).
  * Memahami arsitektur Federated SSO di mana Cloud Dosen bertindak sebagai *Identity Provider* (IdP) yang mengeluarkan token JWT RS256, dan aplikasi lokal bertindak sebagai *Service Provider* (SP).
  * Diperlukan dokumen awal `analisis_tugas_3.md` untuk memetakan skenario *sequence diagram* internal sebelum melangkah ke tahap pengetikan kode.

### Sesi 2 — Eksplorasi Konsep & Struktur SOAP XML Client
* **Tanggal:** 13 Juni 2026  
* **Tujuan:** Memahami mekanisme pertukaran data kaku menggunakan protokol SOAP XML untuk diintegrasikan dengan sistem audit *legacy* milik pusat.
* **Prompt yang digunakan:**
  > *"apa itu SOAP XML Client dan bagaimana cara kerjanya"*
* **Hasil & Insight:**
  * SOAP wajib menggunakan format data XML yang dibungkus secara hierarkis dan ketat oleh elemen-elemen seperti `<soap:Envelope>`, `<soap:Header>`, dan `<soap:Body>`.
  * Aplikasi backend modern harus mampu melakukan transformasi data internal (JSON/Array) ke dalam struktur XML Envelope tersebut sebelum ditembakkan via metode HTTP POST.

### Sesi 3 — Eksplorasi Mekanisme Federated SSO & Role Mapping
* **Tanggal:** 13 Juni 2026  
* **Tujuan:** Memahami konsep Federasi Keamanan dan cara aplikasi lokal memetakan token dari Cloud Dosen.
* **Prompt yang digunakan:**
  > *"apa itu federated sso"*
* **Hasil & Insight:**
  * Memahami interaksi 3 aktor penting: IdP (Cloud Dosen), SP (App Lokal), dan Security Token (JWT Payload).
  * Memahami kewajiban proses *Role Mapping* di sisi lokal, yaitu mengurai *payload* klaim dari token eksternal untuk dicocokkan ke dalam tabel hak akses (*roles*) lokal di database aplikasi individu.

---

## 🛠️ FASE 2: Implementasi Backend & Setup Environment

### Sesi 4 — Struktur Kode & Pemilihan Library di Laravel
* **Tanggal:** 13 Juni 2026  
* **Tujuan:** Menentukan struktur *service class* dan dependensi package PHP yang diperlukan pada ekosistem Laravel.
* **Prompt yang digunakan:**
  > *"aku mengerjakan di laravel, bagaimana cara implementasinya?"*
* **Hasil & Insight:**
  * Direkomendasikan menginstal package `firebase/php-jwt ^6.0` untuk proses dekode dan validasi token Asymmetric RS256 dari JWKS endpoint milik pusat.
  * Logika integrasi pihak ketiga dipisah ke dalam layer *Service Class* terisolasi: `SoapAuditService` dan `AmqpPublisherService` agar *Controller* tetap bersih (*clean code* / *single responsibility principle*).
  * Untuk efisiensi performa, penyusunan XML SOAP dapat menggunakan teknik *heredoc* PHP dengan membungkus data JSON di dalam blok `<![CDATA[...]]>` agar karakter spesial tidak merusak XML parser eksternal.

### Sesi 5 — Setup Bootstrap & Registrasi Middleware (Laravel 11)
* **Tanggal:** 13 Juni 2026  
* **Tujuan:** Mendaftarkan middleware keamanan global untuk menyaring token JWT pada rute transaksi.
* **Hasil & Insight:**
  * Pada arsitektur Laravel 11, pendaftaran alias middleware tidak lagi dilakukan di berkas `Kernel.php`, melainkan langsung dideklarasikan di file `bootstrap/app.php` menggunakan fungsi callback `$middleware->alias()`.
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->alias([
          'verify.sso' => \App\Http\Middleware\VerifySSOToken::class,
      ]);
  })

### esi 06 - Update Credentials dari Dosen (M2M)

*Prompt:
"*soap dan rabbitmq harus menggunakan api key: akun warga: warga36@ktp.iae.id & API-KEY: KEY-MHS-280"

**Insight yang Didapat:**


*SOAP & RabbitMQ wajib pakai M2M token (dari api_key), bukan JWT user biasa
Dibuat service baru: SsoM2MService.php

*Login M2M ke /api/v1/auth/token dengan api_key
Token di-cache 3300 detik (55 menit) pakai Cache::put() agar tidak login berulang
SoapAuditService dan RabbitMQService direfactor:
*Constructor inject SsoM2MService
*Parameter $jwtToken dihapus, diganti $ssoM2M->getToken()

TicketController disederhanakan — tidak perlu lagi passing JWT ke kedua service
.env ditambah: IAE_API_KEY=KEY-MHS-280, IAE_TEAM_ID=TEAM-08, IAE_SSO_EMAIL=warga36@ktp.iae.id, dll
.env.example diupdate dengan key yang sama tapi value kosong (template)

### Refleksi Penggunaan AI

**Hal yang efektif:**


AI sangat membantu dalam debugging iteratif: kirim screenshot/log error → AI analisis root cause → berikan fix spesifik → test ulang
AI membantu menerjemahkan requirement dosen yang berubah (dari JWT user ke M2M) menjadi perubahan kode di banyak file secara konsisten
AI membantu memahami konsep dasar (.env, namespace PHP, middleware Laravel 11) dengan analogi yang mudah dipahami

**Tantangan yang ditemui:**

Perubahan requirement di tengah jalan (M2M) membutuhkan refactor di 4 file sekaligus — AI membantu menjaga konsistensi antar file
Dokumentasi API dari dosen tidak selalu lengkap (contoh: struktur payload RabbitMQ baru diketahui setelah trial-error dari pesan error)


**Pembelajaran:**

Pentingnya membaca log error secara teliti sebelum menyimpulkan penyebab masalah
Memisahkan service-service eksternal (SoapAuditService, RabbitMQService, SsoM2MService) membuat kode lebih mudah di-maintain dan di-refactor saat ada perubahan requirement
# Prompt Engineering Log - Room Chat 1

## Ringkasan Penggunaan AI

Selama proses pengerjaan Tugas 3 mata kuliah Integrasi Aplikasi Enterprise, AI digunakan sebagai mitra eksplorasi teknis untuk memahami dan mengimplementasikan tiga modul integrasi utama: Federated SSO (Modul 1), SOAP XML Client (Modul 2), dan AMQP Publisher (Modul 3).

Penggunaan AI mencakup lima area utama:

1. **Pemahaman arsitektur** — AI digunakan untuk membedah dokumentasi teknis server Pa Eki dan memahami peran setiap pintu integrasi (SSO, SOAP, RabbitMQ) sebelum memulai implementasi.
2. **Perancangan kode** — AI menghasilkan kerangka awal untuk service, model, migration, middleware, dan controller yang kemudian dievaluasi dan disesuaikan dengan kondisi nyata project.
3. **Validasi dengan data asli** — Setelah mendapatkan token nyata dari server Pa Eki, AI diminta memperbarui kode agar sesuai dengan struktur payload JWT yang sebenarnya.
4. **Debugging bertahap** — Tiga error berbeda ditemukan dan diselesaikan secara sistematis dengan bantuan AI: konflik middleware pada route, kegagalan koneksi Docker, dan penolakan JWT akibat _clock skew_.
5. **Dokumentasi akademik** — AI membantu menyusun `analisis_tugas_3.md` beserta sequence diagram ASCII dan visualisasi HTML yang menjadi luaran wajib Tugas 3.

Seluruh output AI diverifikasi secara manual, diuji via Postman, dan disesuaikan berdasarkan kondisi nyata sebelum diintegrasikan ke dalam repositori.

---

## Daftar Prompt yang Digunakan

### Prompt #1

**Tujuan**
Memahami keseluruhan arsitektur integrasi antara project Laravel pribadi dengan server mock Pa Eki sebelum memulai implementasi teknis apapun.

**Prompt Pengguna**

> "Saya melampirkan 2 readme dari 2 project yang berbeda, bantu saya untuk memahami README pa eki, yang mana saya ditugaskan untuk menyambungkan aplikasi saya di yang README.md, ke aplikasi pa eki (README pa eki.md), bantu saya untuk memahami alur dan lain halnya."

**Ringkasan Respons AI**
AI menjelaskan bahwa server Pa Eki adalah simulasi sistem korporat pusat dengan tiga pintu integrasi: (1) REST SSO untuk autentikasi M2M dan end-user menggunakan JWT RS256, (2) SOAP/XML Audit untuk pengiriman log aktivitas ke sistem legacy, dan (3) RabbitMQ sebagai event bus asinkron antar departemen. Laravel berperan sebagai **konsumen** sedangkan server Pa Eki adalah **penyedia pusat**. AI juga membuat diagram alur visual interaktif beserta penjelasan urutan integrasi yang harus diikuti.

**Hasil atau Keputusan**

- Dipahami bahwa urutan integrasi bersifat **sequential**: SSO login → simpan token → gunakan token untuk SOAP dan RabbitMQ.
- JWT dari server Pa Eki **tidak menyertakan field `role`**, sehingga logika RBAC harus diimplementasikan sepenuhnya di sisi Laravel.
- Dipahami perbedaan antara M2M token (`api_key`) dan user token (`email` + `password`).

---

### Prompt #2

**Tujuan**
Membaca dan memahami ketentuan teknis Tugas 3 dari dokumen PDF yang diunggah untuk menentukan prioritas pengerjaan modul.

**Prompt Pengguna**

> "Ini adalah ketentuan yang diberikan oleh dosen saya, yang didalamnya ada penjelasan terkait tugas besar dan juga tugas 3, namun saya ingin mengerjakan tugas 3 terlebih dahulu, saya ingin mengerjakan modul pertama terlebih dahulu ke proyek saya."

**Ringkasan Respons AI**
AI membaca PDF spesifikasi tugas dan mengidentifikasi indikator keberhasilan Modul 1: _"Aplikasi sukses menangkap payload JWT dari Cloud Dosen dan berhasil memetakan user ke tabel roles lokal."_ AI kemudian merinci dua hal yang harus diimplementasikan: (1) request token JWT ke server Pa Eki, dan (2) penyimpanan/pemetaan user ke tabel `roles` lokal di database Laravel.

**Hasil atau Keputusan**

- Ditetapkan bahwa Modul 1 harus diselesaikan terlebih dahulu karena menjadi fondasi autentikasi untuk Modul 2 dan 3.
- AI diminta informasi tambahan mengenai tema aplikasi dan struktur role yang ada sebelum menghasilkan kode.

---

### Prompt #3

**Tujuan**
Menghasilkan seluruh kode dasar Modul 1 (Federated SSO) sesuai konteks aplikasi sewa/rental.

**Prompt Pengguna**

> _(Jawaban dari pertanyaan klarifikasi AI)_
> Tema aplikasi: Sewa/rental kontrak.
> Role lokal: Masih belum ada role sama sekali.

**Ringkasan Respons AI**
AI menghasilkan 7 file sekaligus:

| File                           | Lokasi                       | Fungsi                                                                              |
| ------------------------------ | ---------------------------- | ----------------------------------------------------------------------------------- |
| `create_local_roles_table.php` | `database/migrations/`       | Tabel role lokal dengan seed default (admin, owner, tenant)                         |
| `create_sso_users_table.php`   | `database/migrations/`       | Tabel mapping JWT payload ke user lokal                                             |
| `LocalRole.php`                | `app/Models/`                | Model dengan helper `isAdmin()`                                                     |
| `SsoUser.php`                  | `app/Models/`                | Model dengan helper `hasRole()`, `isAdmin()`, `isOwner()`, `isTenant()`             |
| `SsoService.php`               | `app/Services/`              | Service utama: `loginM2M()`, `loginUser()`, `decodeAndVerify()`, `mapToLocalRole()` |
| `VerifyJwtToken.php`           | `app/Http/Middleware/`       | Middleware proteksi route dengan opsional parameter role                            |
| `SsoController.php`            | `app/Http/Controllers/Auth/` | Endpoint `POST /login` dan `GET /me`                                                |

Logika pemetaan role: `token_type = "m2m"` → `admin`, default → `tenant`.

**Hasil atau Keputusan**

- Seluruh file diterima sebagai kerangka awal dan ditempatkan di lokasi yang sesuai.
- Ditemukan perbedaan struktur payload saat menguji dengan token asli sehingga diperlukan revisi (lihat Prompt #4).

---

### Prompt #4

**Tujuan**
Menyesuaikan kode `SsoService` dengan struktur token JWT asli yang dikembalikan oleh server Pa Eki.

**Prompt Pengguna**

> "Ini adalah token dari server pa eki jika saya request POST dengan KEY-MHS-01:
> `{"status":"success","token_type":"m2m","token":"eyJ...","app":{"client_id":"KEY-MHS-01","name":"Laravel Service — Smart Logistics","team":"TEAM-01"}}`
>
> Ini adalah jika menggunakan email warga01@ktp.iae.id dan sandi KtpDigital2026!:
> `{"status":"success","token_type":"user","token":"eyJ...","profile":{"name":"Ahmad Rizki Pratama","nim":"2026000001","email":"warga01@ktp.iae.id"}}`"

**Ringkasan Respons AI**
AI mengidentifikasi empat perbedaan kritis antara asumsi awal dengan struktur token nyata:

| Asumsi Sebelumnya              | Kenyataan Token Asli                      |
| ------------------------------ | ----------------------------------------- |
| Key response: `access_token`   | Key response: `token`                     |
| Field `nik` di profile         | Tidak ada `nik`, yang ada adalah `nim`    |
| Email langsung di root payload | Email berada di `profile.email`           |
| `full_name` di profile         | Field-nya adalah `name` bukan `full_name` |
| M2M `sub` berupa ID angka      | `sub` = `"KEY-MHS-01"` (string api_key)   |

AI memperbarui method `mapToLocalRole()`, migration (kolom `nik` → `nim`), model `SsoUser`, dan `SsoController` sesuai struktur asli.

**Hasil atau Keputusan**

- Kode diperbarui mengikuti struktur token nyata dari server Pa Eki.
- Parsing M2M token kini membaca dari `payload['app']`, sedangkan user token dari `payload['profile']`.

---

### Prompt #5

**Tujuan**
Mendiagnosis dan memperbaiki error pertama saat pengujian: endpoint login mengembalikan pesan middleware padahal request belum memiliki token.

**Prompt Pengguna**

> "Kenapa saat post http://localhost:8000/api/v1/auth/sso/login dengan email dan sandi yang sudah benar, hasilnya:
> `{"success":false,"message":"Token tidak ditemukan. Harap login via SSO terlebih dahulu."}`"

**Ringkasan Respons AI**
AI mendiagnosis bahwa pesan tersebut berasal dari middleware `VerifyJwtToken`, bukan dari controller. Ini terjadi karena route `/login` ikut terbungkus dalam group `Route::middleware('central.jwt')` di bagian paling luar `routes/api.php`. AI meminta mahasiswa menjalankan `php artisan route:list --path=auth/sso` untuk memverifikasi middleware yang aktif pada setiap route.

**Hasil atau Keputusan**
Route distrukturisasi ulang: route publik (`/login`) dipisahkan ke luar dari semua group middleware, sementara route terproteksi (`/me`) tetap di dalam group `jwt.verify`.

```php
Route::prefix('v1')->group(function () {
    Route::post('/auth/sso/login', [SsoController::class, 'login']); // publik
    Route::middleware('central.jwt')->group(function () { /* tenants, contracts */ });
    Route::middleware('jwt.verify')->group(function () { /* /me */ });
});
```

---

### Prompt #6

**Tujuan**
Mendiagnosis error kedua: Laravel tidak dapat terhubung ke server Pa Eki di port 8080.

**Prompt Pengguna**

> "Saya sudah coba lagi, kenapa hasilnya:
> `{"success":false,"message":"cURL error 7: Failed to connect to localhost port 8080 after 2229 ms: Couldn't connect to server"}`"

**Ringkasan Respons AI**
AI mendiagnosis bahwa Docker container server Pa Eki belum dijalankan. AI memberikan langkah verifikasi bertahap: jalankan `docker ps` untuk melihat status container, jalankan `docker compose up -d` di folder project Pa Eki jika belum berjalan, lalu verifikasi dengan `curl http://localhost:8080/health`. AI juga memberikan solusi alternatif jika server Pa Eki berjalan di komputer/server lain (mengubah `IAE_CENTRAL_URL` di `.env`).

**Hasil atau Keputusan**

- Docker container Pa Eki dijalankan terlebih dahulu sebelum pengujian dilanjutkan.
- Dipahami pentingnya memastikan semua dependensi eksternal aktif sebelum melakukan integrasi test.

---

### Prompt #7

**Tujuan**
Mendiagnosis error ketiga: JWT ditolak karena perbedaan waktu antara server Pa Eki dan mesin lokal.

**Prompt Pengguna**

> "Kenapa response nya:
> `{"success":false,"message":"Token tidak valid: Cannot handle token with iat prior to 2026-06-10T01:34:10+00:00"}`"

**Ringkasan Respons AI**
AI menjelaskan bahwa library `firebase/php-jwt` secara default menolak token yang nilai `iat` (issued at)-nya lebih baru dari jam lokal mesin Laravel, yang terjadi akibat _clock skew_ antara server Pa Eki dan komputer lokal. Solusinya adalah menambahkan `JWT::$leeway = 300` (toleransi 5 menit) sebelum pemanggilan `JWT::decode()` di dalam method `decodeAndVerify()` pada `SsoService`.

**Hasil atau Keputusan**
Satu baris ditambahkan ke `SsoService::decodeAndVerify()`:

```php
JWT::$leeway = 300; // toleransi 5 menit untuk clock skew
```

Setelah perubahan ini, proses verifikasi JWT berjalan normal.

---

### Prompt #8

**Tujuan**
Merancang dan mengimplementasikan Modul 2: SOAP XML Client untuk transaksi kritis pembuatan kontrak.

**Prompt Pengguna**

> "Bagaimana cara saya mengerjakan modul 2? Yaitu melakukan request SOAP dari JSON dan menyimpan ReceiptNumber dari Dosen."
>
> _(Jawaban klarifikasi)_: Transaksi kritis: Pembuatan kontrak sewa baru. Tabel yang ada: Tenants, contracts & payments, migration dari modul SSO.

**Ringkasan Respons AI**
AI merancang arsitektur Modul 2 dengan lima komponen:

1. **Migration `soap_audit_logs`** — menyimpan `receipt_number`, `status`, `xml_payload`, `log_content`, dan relasi polimorfik ke model manapun.
2. **Model `SoapAuditLog`** — dengan relasi `morphTo()` untuk fleksibilitas audit di masa depan.
3. **`SoapAuditService`** — inti transformasi: method `buildSoapEnvelope()` mengonversi array JSON ke XML SOAP Envelope dengan tag wajib `<TeamID>`, `<ActivityName>`, `<LogContent><![CDATA[...]]>`, lalu `parseReceiptNumber()` mengekstrak nilai dengan regex dari response XML.
4. **Integrasi ke `ContractController`** — method `store()` memanggil `auditContractCreated()` otomatis setelah kontrak tersimpan, dengan mekanisme _fail-safe_ (error SOAP tidak menggagalkan response 201).
5. **Konfigurasi** — penambahan `IAE_TEAM_ID` di `.env` dan `services.iae_central.team_id` di `config/services.php`.

**Hasil atau Keputusan**

- Dipilih `regex` untuk parse XML response agar tidak menambah dependensi library baru.
- Desain _fail-safe_: kegagalan SOAP audit dicatat di `soap_audit_logs` dengan `status = FAILED` namun tidak menghentikan proses bisnis utama.
- Relasi polimorfik memungkinkan tabel `soap_audit_logs` digunakan kembali untuk model lain di masa mendatang.

---

### Prompt #9

**Tujuan**
Membuat dokumen analisis dan sequence diagram ASCII untuk memenuhi luaran wajib Tugas 3.

**Prompt Pengguna**

> "Buat sequence diagram dalam bentuk ASCII lalu simpan ke file .md, yang mana sequence ini untuk memenuhi syarat tugas 3. Diagram ini menggambarkan seluruh alur komunikasi sistem kamu dengan API dosen secara end-to-end, mencakup ketiga modul sesuai rubrik Tugas 3:
> Modul 1 (step 3–7) — `SsoService` melakukan M2M login...
> Modul 2 (step 8–14) — `SoapAuditService` membangun XML SOAP Envelope...
> Modul 3 (step 15–19) — `AmqpPublisherService` mengirim event..."

**Ringkasan Respons AI**
AI menghasilkan file `analisis_tugas_3.md` lengkap yang mencakup: (1) justifikasi pemilihan `CONTRACT_CREATED` sebagai transaksi kritis berdasarkan kriteria _state-changing transaction_, (2) tabel skema role lokal dengan logika pemetaan dari JWT, (3) sequence diagram ASCII dengan 7 aktor dan kotak transformasi payload di setiap titik kritis, (4) penjelasan naratif per modul, dan (5) tabel ringkasan capaian teknis.

**Hasil atau Keputusan**

- `CONTRACT_CREATED` dikonfirmasi sebagai transaksi kritis karena: mengubah status sistem secara permanen, melibatkan nilai keuangan (`rent_amount`), mengikat dua pihak secara hukum, dan tidak dapat di-rollback tanpa audit trail.
- Sequence diagram mencakup detail payload transformasi JSON→XML dan struktur event RabbitMQ sebagai referensi implementasi.

---

### Prompt #10

**Tujuan**
Membuat visualisasi sequence diagram dalam format HTML untuk dokumentasi tambahan yang lebih mudah dibaca.

**Prompt Pengguna**

> "Sekarang coba buat visual sequence diagramnya menggunakan html sederhana."

**Ringkasan Respons AI**
AI menghasilkan file HTML dengan SVG diagram yang menampilkan 7 lifeline aktor dengan warna berbeda, panah berlabel (solid untuk request, dashed untuk response), empat section berwarna untuk memisahkan tiap modul, dan kotak note detail yang menampilkan contoh payload di setiap titik transformasi penting.

**Hasil atau Keputusan**

- Diagram HTML digunakan sebagai lampiran visual pada dokumentasi tugas.
- Warna section yang berbeda (hijau untuk SSO, oranye untuk SOAP, ungu untuk RabbitMQ) memudahkan pembacaan alur ketiga modul secara sekilas.

---

## Kategori Eksplorasi

### Analisis Sistem

- Prompt #1 — Memahami arsitektur integrasi dari dua README project
- Prompt #2 — Membaca dan menginterpretasikan ketentuan teknis Tugas 3 dari PDF

### Sequence Diagram

- Prompt #9 — Membuat sequence diagram ASCII 7 aktor end-to-end dalam file `analisis_tugas_3.md`
- Prompt #10 — Membuat visualisasi sequence diagram dalam format HTML interaktif

### API Integration

- Prompt #1 — Memahami tiga pintu integrasi server Pa Eki (REST, SOAP, AMQP)
- Prompt #8 — Merancang integrasi endpoint `POST /soap/v1/audit` dari `ContractController`

### SSO Authentication

- Prompt #2 — Menentukan kebutuhan implementasi Modul 1 dari rubrik tugas
- Prompt #3 — Menghasilkan kerangka kode SSO: migration, model, service, middleware, controller
- Prompt #4 — Menyesuaikan parsing payload JWT dengan struktur token asli server Pa Eki
- Prompt #5 — Debugging: route login terblokir middleware `central.jwt`
- Prompt #6 — Debugging: koneksi ke server Pa Eki gagal (Docker belum dijalankan)
- Prompt #7 — Debugging: JWT ditolak akibat _clock skew_ antara server dan mesin lokal

### SOAP/XML Integration

- Prompt #8 — Merancang `SoapAuditService`: transformasi JSON → XML Envelope, pengiriman ke `/soap/v1/audit`, parsing `ReceiptNumber`, penyimpanan ke `soap_audit_logs`

### RabbitMQ / AMQP

- Prompt #1 — Memahami peran RabbitMQ sebagai event bus dengan exchange `iae.central.exchange`
- Prompt #9 — Mendeskripsikan alur `AmqpPublisherService` dalam sequence diagram (Modul 3)

### Backend Development

- Prompt #3 — Implementasi migration, model, service, middleware, controller untuk Modul 1
- Prompt #4 — Refactoring `SsoService::mapToLocalRole()` sesuai struktur payload nyata
- Prompt #8 — Implementasi `SoapAuditService`, `SoapAuditLog`, integrasi ke `ContractController`

### Debugging dan Troubleshooting

- Prompt #5 — Error: `"Token tidak ditemukan"` pada route `/login` yang seharusnya publik → solusi: restrukturisasi group middleware di `routes/api.php`
- Prompt #6 — Error: `cURL error 7: Failed to connect to localhost port 8080` → solusi: menjalankan Docker container Pa Eki
- Prompt #7 — Error: `Cannot handle token with iat prior to ...` → solusi: menambahkan `JWT::$leeway = 300` di `SsoService`

---

## Insight Pembelajaran

### Konsep Baru yang Ditemukan

**1. Dual-mode JWT dari satu endpoint**
Server Pa Eki menggunakan satu endpoint `/api/v1/auth/token` untuk dua jenis token yang memiliki struktur payload berbeda. Token M2M menyimpan metadata aplikasi di `payload['app']`, sementara token user menyimpan profil di `payload['profile']`. Pemahaman ini penting untuk parsing yang benar di sisi konsumen.

**2. JWKS sebagai mekanisme distribusi public key**
Alih-alih mendistribusikan public key secara statis, server Pa Eki menggunakan JWKS (JSON Web Key Set) yang dapat diambil via endpoint publik. Hal ini memungkinkan rotasi key tanpa perlu mengubah konfigurasi di sisi konsumen. Implementasi cache selama 1 jam di Laravel mencegah request JWKS yang berlebihan.

**3. Clock skew dalam validasi JWT**
Library `firebase/php-jwt` memvalidasi klaim `iat` (issued at) secara ketat. Perbedaan jam antara server yang mengissue token dengan server yang memverifikasi dapat menyebabkan penolakan token yang sebenarnya valid. Parameter `leeway` adalah solusi standar untuk kondisi ini.

**4. SOAP Envelope sebagai "amplop" XML**
Protokol SOAP menggunakan struktur XML berlapis (Envelope → Header → Body) sebagai standar komunikasi lintas platform. Tag `<![CDATA[...]]>` di dalam `<LogContent>` memungkinkan penyisipan JSON arbitrer tanpa konflik karakter XML, sehingga transformasi JSON→XML dapat dilakukan tanpa kehilangan data.

**5. Fail-safe pattern dalam orkestrasi service**
Dalam arsitektur microservice, kegagalan service pendukung (SOAP audit, RabbitMQ publisher) tidak boleh menghentikan transaksi bisnis utama. Pola yang tepat adalah: tangkap exception, catat kegagalan ke log, kembalikan response sukses ke client dengan informasi status audit.

### Solusi Teknis yang Berhasil Diterapkan

| Masalah                                     | Solusi                                                             |
| ------------------------------------------- | ------------------------------------------------------------------ |
| Route publik terblokir middleware           | Pisahkan deklarasi route publik ke luar semua group middleware     |
| JWT ditolak akibat clock skew               | `JWT::$leeway = 300` sebelum `JWT::decode()`                       |
| Parse XML response tanpa library            | Regex `/<(?:iae:)?ReceiptNumber>(.*?)<\/(?:iae:)?ReceiptNumber>/s` |
| Struktur payload JWT berbeda per token_type | Kondisional `if ($tokenType === 'm2m')` sebelum parsing            |
| SOAP error menggagalkan kontrak             | Bungkus `auditContractCreated()` dalam `try-catch` di controller   |

---

## Kesimpulan

Selama pengerjaan Tugas 3, AI berperan sebagai **akselerator eksplorasi teknis** yang membantu mahasiswa memahami konsep integrasi enterprise (SSO, SOAP, AMQP) secara lebih cepat melalui contoh kode konkret dan penjelasan bertahap. AI sangat efektif dalam dua kondisi: (1) ketika mahasiswa memberikan konteks yang spesifik seperti tema aplikasi, struktur tabel, dan token asli dari server Pa Eki, dan (2) ketika digunakan sebagai "papan diskusi" untuk mendiagnosis error dengan menyertakan pesan error lengkap.

Namun demikian, **seluruh keputusan implementasi tetap dilakukan dan diverifikasi oleh mahasiswa**, mencakup:

- Menguji setiap endpoint menggunakan Postman sebelum dianggap selesai
- Menyesuaikan kode hasil AI dengan struktur token nyata yang berbeda dari asumsi awal
- Memahami alasan di balik setiap solusi sebelum menerapkannya ke project
- Memilih pendekatan teknis yang paling sesuai (misalnya: regex vs library XML parser, HTTP vs AMQP langsung)

AI tidak digunakan sebagai pengganti pemahaman konsep, melainkan sebagai alat yang mempercepat siklus _eksplorasi → implementasi → verifikasi_ dalam proses belajar yang tetap berpusat pada mahasiswa.

# Prompt Engineering Log - Room Chat 2

### Konteks Proyek

Proyek ini merupakan implementasi sistem terintegrasi untuk Enterprise Digital City dengan fokus pada manajemen sewa properti. Sistem terdiri dari 3 microservice utama:

- **Service Manajemen Tenant** - Mengelola keluhan dan tiket maintenance
- **Service Listing Unit** - Master data unit properti dan ketersediaan
- **Service Kontrak Sewa** - Pengelolaan kontrak sewa dan validasi

### Peran AI dalam Pengembangan

AI (terutama Claude dan ChatGPT) digunakan sebagai alat bantu dalam:

1. **Enterprise Integration Patterns**
    - SOAP/XML transformation dan parsing untuk legacy system integration
    - RabbitMQ event publishing design dan payload structure
    - JWT token validation dan role-based access control (RBAC)

2. **Implementation Guidance**
    - Pseudocode dan skeleton untuk berbagai komponen
    - Library recommendations dan best practices
    - Testing strategy dan comprehensive test cases

3. **Documentation Support**
    - Struktur dokumentasi analisis
    - Code comments dan architecture documentation
    - Deployment checklist dan monitoring rules

### Metodologi Penggunaan AI

Pendekatan yang digunakan:

- **Iteratif Questioning** - Pertanyaan berlapis untuk deep understanding
- **Trade-offs Analysis** - Setiap keputusan teknis diperiksa pro-cons nya
- **Example-Based Learning** - Belajar melalui concrete examples dan pseudocode
- **Validation through Discussion** - Memastikan keputusan align dengan business context

---

## Daftar Prompt yang Digunakan

### Prompt #1: Role-Based Access Control (RBAC) Design

**Tujuan**  
Merancang role hierarchy dan permission matrix untuk transaksi penyimpanan contract, termasuk validasi apa yang harus dilakukan untuk setiap tipe user (SUPER_ADMIN, CONTRACT_MANAGER, PROPERTY_OWNER, TENANT, AUDITOR).

**Prompt Pengguna**

```
Dalam sistem sewa properti, ada beberapa tipe user:
1. SUPER_ADMIN - Boss semua orang, bisa approve contract
2. CONTRACT_MANAGER - Admin yang handle kontrak, submit untuk approval
3. PROPERTY_OWNER - Pemilik properti, bisa buat contract untuk unit sendiri
4. TENANT - Penyewa, hanya bisa sign dan lihat contract
5. AUDITOR - Compliance, read-only

Untuk transaksi POST /api/v1/contracts, buatkan:
1. Permission matrix - siapa bisa CREATE, UPDATE, DELETE, PUBLISH
2. Business logic untuk setiap role:
   - Property Owner buat contract, tapi auto-set status=DRAFT
   - CONTRACT_MANAGER buat contract, auto-set status=DRAFT, harus approval SUPER_ADMIN buat publish
   - SUPER_ADMIN bisa langsung publish tanpa approval
   - TENANT tidak bisa create

3. Validasi yang harus di-check:
   - JWT token valid
   - User role di whitelist untuk endpoint ini
   - Jika PROPERTY_OWNER, property_id harus milik user tersebut
   - Jika property bukan miliknya, return 403

4. Return status code yang tepat untuk setiap scenario
```

**Ringkasan Respon AI**

- **Permission Matrix:**

    ```
    SUPER_ADMIN     : CREATE (any), UPDATE (any), DELETE, PUBLISH (direct)
    CONTRACT_MANAGER: CREATE, UPDATE (draft only), PUBLISH (requires approval)
    PROPERTY_OWNER  : CREATE (own properties), UPDATE (own only, draft), PUBLISH (no)
    TENANT          : READ, SIGN (no create/update)
    AUDITOR         : READ (all)
    ```

- **Business Logic per Role:**
    - SUPER_ADMIN → status bisa langsung ACTIVE atau DRAFT
    - CONTRACT_MANAGER → status force to DRAFT, perlu SUPER_ADMIN approval buat PUBLISHED
    - PROPERTY_OWNER → status force to DRAFT, hanya buat property miliknya
    - TENANT → tidak bisa POST, hanya GET + PUT untuk sign
    - AUDITOR → GET only, no mutating operations

- **HTTP Status Codes:**
    - 201 Created - contract berhasil dibuat
    - 202 Accepted - contract dibuat tapi status PENDING_APPROVAL
    - 400 Bad Request - data invalid, property bukan miliknya
    - 401 Unauthorized - JWT invalid/expired
    - 403 Forbidden - role tidak punya akses (TENANT, AUDITOR)
    - 422 Unprocessable Entity - business logic validation fail (unit tidak ada, tenant already punya contract)

- **Validasi Sequence:**
    1. Check JWT valid (401 if not)
    2. Check role in whitelist (403 if TENANT/AUDITOR)
    3. Check property ownership (403 if PROPERTY_OWNER try other property)
    4. Check business logic (unit exists, tenant valid, no duplicate)
    5. Force status based on role (PROPERTY_OWNER & CONTRACT_MANAGER → DRAFT)

**Hasil atau Keputusan**
✅ **RBAC design diterapkan dalam section "Skema Role Lokal"**

Keputusan implementasi:

- JWT token extracted di API Gateway (tidak di setiap service)
- Role validation sebagai middleware di setiap service
- Property ownership check di Service Listing (cross-service validation)
- Status forcing berdasarkan role sebagai business logic di Service Kontrak

---

### Prompt #2: SOAP/XML Transformation & Integration

**Tujuan**  
Memahami cara mentransformasi data JSON dari request contract menjadi SOAP XML envelope yang sesuai dengan legacy audit system, termasuk parsing response dan extracting receipt number.

**Prompt Pengguna**

```
Legacy Audit System menggunakan SOAP/XML untuk integration.
Saya perlu transform data JSON contract menjadi SOAP envelope.

Input JSON dari client:
{
  "property_id": "PROP-2024-001",
  "tenant_id": "TENANT-2024-001",
  "monthly_rent": 5000000,
  "start_date": "2024-07-01",
  "end_date": "2025-07-01",
  "deposit": 10000000,
  "status": "ACTIVE"
}

Requirements untuk SOAP envelope:
1. Valid XML structure dengan proper namespace
2. Include metadata: timestamp, transaction_id, user_id, user_role
3. Wrap dalam soap:Envelope dengan soap:Body
4. Setiap field dalam tag XML sesuai WSDL definition
5. Include operation type (CREATE, UPDATE, DELETE)

Buatkan SOAP envelope template-nya.

Kemudian, legacy system akan return SOAP response dengan ReceiptNumber.
Bagaimana cara parse XML response itu dan extract ReceiptNumber di Node.js?
Include error handling untuk malformed XML atau SOAP Fault.
```

**Ringkasan Respon AI**

- **SOAP Envelope Template:**

    ```xml
    <?xml version="1.0" encoding="UTF-8"?>
    <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
                   xmlns:con="http://legacy.telkomu.ac.id/contract">
      <soap:Header>
        <con:requestId>{requestId}</con:requestId>
        <con:timestamp>{timestamp}</con:timestamp>
      </soap:Header>
      <soap:Body>
        <con:createContractAudit>
          <con:auditData>
            <con:transactionId>TXN-2024-{requestId}</con:transactionId>
            <con:timestamp>{timestamp}</con:timestamp>
            <con:serviceId>SERVICE-CONTRACT</con:serviceId>
            <con:operationType>CREATE</con:operationType>
            <con:dataPayload>
              <con:propertyId>{propertyId}</con:propertyId>
              <con:tenantId>{tenantId}</con:tenantId>
              <con:monthlyRent>{monthlyRent}</con:monthlyRent>
              <con:startDate>{startDate}</con:startDate>
              <con:endDate>{endDate}</con:endDate>
              <con:depositAmount>{depositAmount}</con:depositAmount>
              <con:contractStatus>{status}</con:contractStatus>
            </con:dataPayload>
            <con:userId>{userId}</con:userId>
            <con:userRole>{userRole}</con:userRole>
          </con:auditData>
        </con:createContractAudit>
      </soap:Body>
    </soap:Envelope>
    ```

- **Parsing SOAP Response dengan Node.js:**
    - Library: `xml2js` untuk parse XML → JSON
    - Check for soap:Fault (error response dari SOAP server)
    - Extract receiptNumber dari con:createContractAuditResponse
    - Validate receipt number format (tidak boleh null/empty)
    - Handle timeout dengan Promise.race()

- **Error Scenarios:**
    - Malformed XML → throw error "Invalid XML response"
    - SOAP Fault → extract faultstring dari soap:Fault
    - Missing receiptNumber → throw error "Receipt not in response"
    - Timeout (>30s) → throw error "SOAP timeout"

- **Retry Strategy:**
    - Jangan retry pada SOAP Fault (logic error, tidak akan sukses retry)
    - Retry pada timeout atau connection error (transient failure)
    - Exponential backoff: 1s, 2s, 4s, 8s (max 3-5 attempts)

**Hasil atau Keputusan**
✅ **SOAP integration detail diterapkan dalam section "Data Flow pada SOAP Integration"**

Keputusan implementasi:

- Gunakan XML builder library (bukan string concatenation) untuk prevent injection
- Validasi XML schema dengan XSD sebelum send ke legacy system
- Implement timeout 30 detik untuk SOAP call
- Store ReceiptNumber di database table `contract_audit` sebagai bukti audit
- Jika SOAP fail → keep contract as PENDING_AUDIT, retry async
- Log semua SOAP interaction (request, response, receipt number)

---

### Prompt #3: RabbitMQ Event Publishing & Async Messaging

**Tujuan**  
Merancang event payload yang akan dipublikasi ke RabbitMQ setelah contract berhasil dibuat, termasuk subscribers yang akan consume event tersebut dan strategi error handling jika RabbitMQ down.

**Prompt Pengguna**

```
Setelah contract berhasil dibuat dan terekam di legacy audit system,
harus publish event ke RabbitMQ agar departemen lain tahu tentang contract baru.

Departemen yang interested:
- Account Service: Update billing setup untuk tenant
- Notification Service: Kirim email ke tenant & property owner
- Analytics Service: Update occupancy metrics & revenue tracking
- Compliance Service: Log untuk regulatory & audit trail

Event payload JSON apa yang harus dikirim ke RabbitMQ?

Requirements:
1. Event harus self-contained (subscribers tidak perlu query service lagi)
2. Include semua informasi yang mungkin dibutuhkan subscribers
3. Include metadata: event_id, timestamp, source_service
4. Include business data: contract_id, property details, tenant info, financial info
5. Include audit info: receipt_number (dari SOAP call)
6. Event naming convention yang jelas

Kemudian, bagaimana error handling jika RabbitMQ connection down atau publish fail?
Opsi:
A. Reject contract creation jika RabbitMQ down
B. Keep contract, queue untuk later retry, return 201 anyway
C. Something else?

Mana yang better untuk production?
```

**Ringkasan Respon AI**

- **Event Payload Structure (self-contained):**

    ```json
    {
        "event_type": "CONTRACT_CREATED",
        "event_id": "EVT-2024-XXXXX",
        "timestamp": "2024-07-01T10:30:15Z",
        "source_service": "SERVICE-CONTRACT",
        "contract": {
            "contract_id": "CONTRACT-2024-001",
            "property_id": "PROP-2024-001",
            "property_name": "Apartemen Tower A - Unit 501",
            "property_address": "...",
            "tenant_id": "TENANT-2024-001",
            "tenant_name": "John Doe",
            "tenant_email": "john@example.com",
            "monthly_rent": 5000000,
            "start_date": "2024-07-01",
            "end_date": "2025-07-01",
            "deposit_amount": 10000000,
            "status": "ACTIVE",
            "created_by": "USER-2024-001",
            "created_at": "2024-07-01T10:30:00Z"
        },
        "audit": {
            "audit_receipt": "AUDIT-2024-00001234",
            "audit_timestamp": "2024-07-01T10:30:15Z",
            "legacy_system_response": "SUCCESS"
        }
    }
    ```

- **RabbitMQ Routing:**
    - Exchange: `contract.events`
    - Routing key: `contract.created`
    - Queue bindings:
        - account-service queue: binding key `contract.*`
        - notification-service queue: binding key `contract.created`
        - analytics-service queue: binding key `contract.*`
        - compliance-service queue: binding key `contract.created`

- **Error Handling Recommendation (Opsi B):**
    - Contract **sudah disimpan** di database
    - RabbitMQ publish gagal → store event ke `outbox` table
    - Return 201 Created kepada client (success dari perspektif client)
    - Background job retry publish setiap 5 menit (dengan exponential backoff)
    - If still fail after 24 jam → alert admin untuk manual intervention
    - Rationale: RabbitMQ adalah infrastructure, bukan core business. Tidak boleh block contract creation.

- **Circuit Breaker Pattern:**
    - Jika RabbitMQ consistently down (5+ failed publishes) → open circuit
    - Log semua event ke outbox table (fallback)
    - Retry dengan jitter untuk avoid thundering herd

**Hasil atau Keputusan**
✅ **RabbitMQ design diterapkan dalam section "Message Broker (RabbitMQ) Publishing"**

Keputusan implementasi:

- Event payload harus self-contained dengan semua data (tidak perlu subscribers query service)
- Publish ke RabbitMQ **ASYNC** di luar database transaction
- Jika RabbitMQ down → keep contract, store to outbox table, return 201 anyway
- Implement background job untuk retry failed publishes (exponential backoff + jitter)
- Dead Letter Queue untuk handling poison messages (yang selalu fail)
- Monitoring: alert jika RabbitMQ unavailable atau event publish failure rate > 1%

---

### Prompt #4: Database Transaction Boundaries & Consistency

**Tujuan**  
Menentukan dengan tepat operasi mana yang harus di-include dalam database transaction dan operasi mana yang harus di-exclude, serta strategi untuk menjaga consistency antara contract dan audit receipt.

**Prompt Pengguna**

```
Untuk POST /api/v1/contracts, urutan operasi adalah:
1. Validate JWT & role (aplikasi logic)
2. GET /api/v1/listings/{id} (Service Listing validation, external API)
3. BEGIN TRANSACTION
4. INSERT contract ke database
5. SOAP call ke legacy audit system (external system)
6. Jika SOAP success → INSERT audit_receipt ke contract_audit table
7. UPDATE contract status = ACTIVE
8. COMMIT TRANSACTION
9. Publish event ke RabbitMQ (external system)

Pertanyaan:
1. Operasi mana yang harus di-INCLUDE dalam transaction?
2. Operasi mana yang harus di-EXCLUDE?
3. Bagaimana jika SOAP call timeout? Apakah harus ROLLBACK?
4. Bagaimana jika RabbitMQ publish fail? Impact terhadap client response?

Jelaskan dari perspektif ACID properties:
- Atomicity: semua or nothing
- Consistency: data integrity antara tables
- Isolation: concurrent requests tidak corrupt data
- Durability: setelah commit, data persistent
```

**Ringkasan Respon AI**

- **Operations DALAM Transaction:**
    - INSERT contract (initial status = PENDING_AUDIT)
    - INSERT audit_receipt (from SOAP response)
    - UPDATE contract status = ACTIVE (after SOAP success)
    - **Rationale:** Contract dan audit receipt harus 1:1 relationship. Jika ada contract tanpa receipt, akan corrupt data consistency. Transaction guarantee atomicity.

- **Operations DI LUAR Transaction:**
    - Service Listing validation (PRE-transaction, early fail fast)
    - SOAP call (external, tapi hasil harus di-save DALAM transaction)
    - RabbitMQ publish (POST-transaction, tidak boleh block)
    - **Rationale:** External systems tidak bisa dijamin atomicity. Jika include SOAP dalam transaction, akan block long. Better isolate external call outcomes.

- **Handling SOAP Timeout:**
    - Option A (Pessimistic): ROLLBACK contract jika SOAP timeout
        - Pros: Guarantee consistency antara contract & audit
        - Cons: Bad UX (client submit data, tapi fail because external system slow)
    - Option B (Optimistic): Keep contract as PENDING_AUDIT, retry SOAP async
        - Pros: Good UX, contract sudah created, data not lost
        - Cons: Ada transient gap saat contract exist tapi belum fully audited
    - **Recommendation:** Option B dengan reconciliation
        - Contract disimpan dan bisa digunakan
        - SOAP retry otomatis di background
        - Admin can manually reconcile if still fail after retries

- **Handling RabbitMQ Publish Fail:**
    - **Tidak impact response:** RabbitMQ publish di-LUAR transaction
    - **Tidak block client:** Return 201 Created immediately, RabbitMQ retry di background
    - **Outbox pattern:** Store event to outbox table, then publish async
    - **Impact:** Event might delayed (seconds to minutes), not instant

- **ACID Guarantee:**
    - Atomicity: Contract + audit_receipt inserted together or not at all
    - Consistency: 1 contract ↔ 1 audit_receipt (enforced by FK constraint)
    - Isolation: Row-level locks prevent concurrent contract creation for same property+tenant
    - Durability: After COMMIT, data persistent in database

**Hasil atau Keputusan**
✅ **Database transaction design diterapkan dalam section "Database Transaction & Consistency"**

Keputusan implementasi:

- BEGIN TRANSACTION sebelum INSERT contract
- SOAP call DALAM transaction BUT dengan timeout (jangan infinite wait)
- Jika SOAP timeout → ROLLBACK contract (PENDING_AUDIT tidak jadi disimpan)
- Jika SOAP success → UPDATE status = ACTIVE, COMMIT
- RabbitMQ publish AFTER COMMIT (async, no blocking)
- Implement outbox table untuk failed RabbitMQ publishes
- Add unique constraint (property_id, tenant_id, active_period) untuk prevent duplicate

---

### Prompt #5: Error Handling & Retry Strategy

**Tujuan**  
Merancang strategi comprehensive error handling untuk berbagai failure scenarios, termasuk kapan harus return error ke client, kapan harus retry, dan kapan harus alert admin.

**Prompt Pengguna**

```
Buatkan comprehensive error handling untuk 7 failure scenarios:

1. JWT Token Invalid
   - Return 401 Unauthorized
   - No database changes, no external calls?

2. Role tidak diizinkan (TENANT trying to create)
   - Return 403 Forbidden
   - No database changes, no external calls?

3. Service Listing timeout (unit validation fail)
   - Should return error immediately atau retry?
   - Max retry berapa kali?
   - Return 502 atau 400?

4. Contract data validation fail (field missing, invalid format)
   - Return 400 Bad Request
   - No database changes?

5. Database INSERT fail (constraint violation)
   - Return 422 Unprocessable Entity
   - Include error details (duplicate contract, etc)

6. SOAP timeout (legacy audit system not responding)
   - Option A: Reject contract (ROLLBACK)
   - Option B: Keep contract as PENDING_AUDIT, retry SOAP async
   - Which is better?

7. RabbitMQ down (cannot publish event)
   - Should this block contract creation?
   - Return 201 or 500?
   - How to ensure event eventually get published?

Untuk setiap scenario, buatkan:
- HTTP status code
- Error message
- Client action (retry, contact support, etc)
- Server action (log, alert, retry, rollback)
```

**Ringkasan Respon AI**

- **1. JWT Token Invalid:**

    ```
    HTTP Status: 401 Unauthorized
    Error Message: "Invalid or missing JWT token"
    Client Action: Login again, get new token
    Server Action: Log authentication failure, do NOT call any service
    ```

- **2. Role tidak diizinkan:**

    ```
    HTTP Status: 403 Forbidden
    Error Message: "User role TENANT tidak diizinkan create contract"
    Client Action: Contact support to request role upgrade
    Server Action: Log authorization failure, audit who tried
    ```

- **3. Service Listing timeout:**

    ```
    HTTP Status: 502 Bad Gateway
    Error Message: "Service Listing sedang tidak responsif"
    Client Action: Retry after 30 seconds
    Server Action:
      - Retry 3x dengan exponential backoff (1s, 2s, 4s)
      - Jika tetap fail: Log error, return 502
      - No database changes
      - Alert monitoring if consistently failing
    ```

- **4. Contract data validation fail:**

    ```
    HTTP Status: 400 Bad Request
    Error Message: "Invalid data: property_id harus format PROP-XXXX"
    Client Action: Fix data and retry
    Server Action: Log validation error, no database changes
    ```

- **5. Database INSERT fail:**

    ```
    HTTP Status: 422 Unprocessable Entity
    Error Message: "Contract sudah ada untuk property+tenant+period ini"
    Client Action: Check existing contract atau modify period
    Server Action: Log constraint violation, rollback transaction
    ```

- **6. SOAP timeout (CRITICAL DECISION):**

    ```
    RECOMMENDATION: Keep contract, retry SOAP async (Option B)

    HTTP Status: 201 Created (contract created)
    Response Body:
    {
      "contract_id": "...",
      "status": "PENDING_AUDIT",
      "audit_status": "PENDING",
      "message": "Contract created. Audit pending, will process shortly."
    }

    Server Action:
      - Contract SUDAH disimpan (status = PENDING_AUDIT)
      - Queue SOAP retry dengan exponential backoff
      - Max retry 3-5x, then manual reconciliation alert
      - Background job monitor dan re-check periodically

    Rationale: UX lebih baik, data tidak hilang, consistency eventually achieved
    Trade-off: Ada window (seconds to minutes) saat contract exist tapi belum fully audited
    Mitigation: Contract marking PENDING_AUDIT, admin aware, will reconcile
    ```

- **7. RabbitMQ down:**

    ```
    HTTP Status: 201 Created (contract created)
    Response Body: Same as above

    Server Action:
      - Contract SUDAH disimpan (commit transaction berhasil)
      - Event store ke outbox table
      - Return 201 to client immediately
      - Background job retry RabbitMQ publish
      - If still fail: log to dead letter queue, alert admin

    Rationale: RabbitMQ adalah infrastructure notification, bukan core business.
    Event eventual consistency acceptable untuk notification use case.
    ```

**Hasil atau Keputusan**
✅ **Error handling diterapkan dalam section "Error Handling & Rollback Strategy"**

Keputusan implementasi:

- Pre-transaction errors (JWT, role, validation) → immediate return error, no state change
- Service Listing errors → retry 3x, return 502 if still fail
- Database errors → return 422 with details
- SOAP errors → keep contract as PENDING_AUDIT, async retry
- RabbitMQ errors → keep contract, async retry to outbox
- All errors logged dengan structured logging (correlation ID, user ID, timestamp)
- Critical errors (SOAP consistently fail, DB down) → alert admin via monitoring

---

### Prompt #6: Structured Logging & Correlation ID

**Tujuan**  
Merancang structured logging strategy untuk audit trail dan debugging, termasuk bagaimana track 1 request end-to-end melalui multiple services menggunakan correlation ID.

**Prompt Pengguna**

```
Untuk compliance dan debugging, setiap step di POST /api/v1/contracts harus di-log.

Design requirements:
1. Log harus terstruktur (JSON, bukan unstructured text)
2. Must include correlation_id untuk trace 1 request end-to-end
3. Log level yang tepat: INFO (normal flow), WARN (validation fail), ERROR (exception)
4. Sensitive data harus di-redact (password, PII, credit card)
5. Timestamp harus UTC timezone
6. Setiap log entry harus include:
   - timestamp
   - correlation_id (untuk link semua logs 1 request)
   - service name
   - endpoint
   - user_id & user_role
   - step/operation (JWT validation, role check, DB insert, etc)
   - status (SUCCESS, FAIL, TIMEOUT)
   - duration (execution time dalam ms)

Buatkan contoh structured log JSON untuk berbagai scenario:
- JWT validation success
- JWT validation fail
- Role authorization fail
- Service Listing validation success
- Service Listing validation timeout
- Database INSERT success
- SOAP call timeout
- RabbitMQ publish success
- RabbitMQ publish fail

Bagaimana cara implement correlation_id:
- Buat di API Gateway ketika request masuk
- Pass ke semua services & external calls
- Collect semua logs 1 request untuk debugging
```

**Ringkasan Respon AI**

- **Structured Log Format (JSON):**

    ```json
    {
        "timestamp": "2024-07-01T10:30:00.123Z",
        "correlation_id": "req-2024-7ae8c5d2",
        "log_level": "INFO",
        "service": "SERVICE-CONTRACT",
        "endpoint": "POST /api/v1/contracts",
        "method": "POST",
        "path": "/api/v1/contracts",
        "user_id": "USER-2024-001",
        "user_role": "CONTRACT_MANAGER",
        "step": "JWT_VALIDATION",
        "status": "SUCCESS",
        "details": {
            "jwt_valid": true,
            "claims_extracted": {
                "sub": "USER-2024-001",
                "role": "CONTRACT_MANAGER",
                "exp": 1719835800
            }
        },
        "duration_ms": 25,
        "request_id": "req-2024-7ae8c5d2"
    }
    ```

- **Contoh Logs untuk berbagai scenario:**

    **JWT Validation Success:**

    ```json
    {
        "timestamp": "2024-07-01T10:30:00Z",
        "correlation_id": "...",
        "step": "JWT_VALIDATION",
        "status": "SUCCESS",
        "duration_ms": 25
    }
    ```

    **JWT Validation Fail (expired):**

    ```json
    {
        "timestamp": "2024-07-01T10:30:00Z",
        "correlation_id": "...",
        "step": "JWT_VALIDATION",
        "status": "FAIL",
        "error": "Token expired at 1719835800",
        "duration_ms": 5
    }
    ```

    **Role Authorization Fail:**

    ```json
    {
        "timestamp": "2024-07-01T10:30:01Z",
        "correlation_id": "...",
        "step": "ROLE_VALIDATION",
        "status": "FAIL",
        "error": "Role TENANT not in whitelist [SUPER_ADMIN, CONTRACT_MANAGER, PROPERTY_OWNER]",
        "user_role": "TENANT"
    }
    ```

    **Service Listing Validation Success:**

    ```json
    {
        "timestamp": "2024-07-01T10:30:02Z",
        "correlation_id": "...",
        "step": "SERVICE_LISTING_VALIDATION",
        "status": "SUCCESS",
        "details": {
            "property_id": "PROP-2024-001",
            "unit_exists": true,
            "unit_available": true
        },
        "duration_ms": 150
    }
    ```

    **Database INSERT Success:**

    ```json
    {
        "timestamp": "2024-07-01T10:30:03Z",
        "correlation_id": "...",
        "step": "DATABASE_INSERT",
        "status": "SUCCESS",
        "details": {
            "contract_id": "CONTRACT-2024-001",
            "table": "contracts",
            "rows_affected": 1
        },
        "duration_ms": 45
    }
    ```

    **SOAP Call Timeout:**

    ```json
    {
        "timestamp": "2024-07-01T10:30:05Z",
        "correlation_id": "...",
        "step": "SOAP_AUDIT_CALL",
        "status": "TIMEOUT",
        "error": "SOAP request timeout after 30000ms",
        "duration_ms": 30000,
        "retry_attempt": 1
    }
    ```

    **RabbitMQ Publish Success:**

    ```json
    {
        "timestamp": "2024-07-01T10:30:10Z",
        "correlation_id": "...",
        "step": "RABBITMQ_PUBLISH",
        "status": "SUCCESS",
        "details": {
            "exchange": "contract.events",
            "routing_key": "contract.created",
            "event_id": "EVT-2024-XXXXX"
        },
        "duration_ms": 120
    }
    ```

- **Correlation ID Implementation:**
    - Generate di API Gateway untuk setiap incoming request
    - Format: `req-YYYY-{random-hex}` (e.g., req-2024-7ae8c5d2)
    - Pass di HTTP headers untuk inter-service calls: `X-Correlation-ID`
    - Pass di SOAP header untuk legacy system call
    - Include di RabbitMQ message header
    - Gunakan untuk link semua logs 1 request saat troubleshooting

- **Log Collection & Analysis:**
    - Gunakan centralized logging (ELK Stack, Splunk, atau CloudWatch)
    - Filter by correlation_id untuk trace 1 request end-to-end
    - Search by user_id untuk audit user activities
    - Alert pada ERROR atau TIMEOUT logs

**Hasil atau Keputusan**
✅ **Structured logging design diterapkan dalam documentation**

Keputusan implementasi:

- Semua logs dalam format JSON untuk machine-readable
- Correlation ID generate di API Gateway, propagate ke semua services
- Log level: INFO (normal), WARN (validation fail), ERROR (exception)
- Redact sensitive data (password, PII, credit card digits)
- Include duration_ms untuk performance monitoring
- Centralized logging dengan ELK atau similar
- Alert pada logs dengan level ERROR atau duration > threshold

---

### Prompt #7: Testing Strategy & Test Cases

**Tujuan**  
Merancang comprehensive test plan untuk POST /api/v1/contracts yang cover happy path, authentication/authorization failures, validation failures, external service failures, database failures, concurrent scenarios, dan edge cases.

**Prompt Pengguna**

```
Buatkan comprehensive test plan untuk POST /api/v1/contracts.

Harus cover:
1. Happy path (semua sistem OK)
2. Authentication failures (invalid JWT, expired token, missing token)
3. Authorization failures (role tidak allowed, property bukan miliknya)
4. Input validation failures (missing field, invalid format, invalid dates)
5. Business logic validation (unit tidak ada, unit sudah tersewa, duplicate contract)
6. External service failures:
   - Service Listing timeout
   - Service Listing return 404
   - SOAP audit timeout
   - SOAP audit return FAULT
   - RabbitMQ connection fail
7. Database failures:
   - Connection timeout
   - Constraint violation (duplicate)
   - Transaction rollback
8. Edge cases:
   - Concurrent requests untuk same property+tenant
   - Very long contract period
   - Zero/negative rent (validation boundary)
9. Idempotency test (retry same request)

Untuk setiap test case, buatkan:
- Test name
- Setup (mock dependencies)
- Request payload
- Expected response (status, body, side effects)
- Assertions yang dicek

Gunakan table-driven testing format untuk ease of adding more cases.
```

**Ringkasan Respon AI**

- **Test Structure (Table-Driven):**

    ```javascript
    const testCases = [
      {
        name: "Happy path - Contract creation success",
        setup: {
          jwt: validJWT,
          role: "CONTRACT_MANAGER",
          listingMock: { status: 200, unit_exists: true },
          soapMock: { status: 200, receipt: "AUDIT-2024-001" },
          rabbitmqMock: { status: "success" }
        },
        payload: { property_id: "PROP-001", tenant_id: "TENANT-001", ... },
        expectedResponse: { status: 201, body: { contract_id: "CONTRACT-...", status: "ACTIVE" } },
        assertions: [
          "Response status === 201",
          "Contract created in DB",
          "Audit receipt saved",
          "Event published to RabbitMQ"
        ]
      },
      {
        name: "JWT validation fail - Invalid token",
        setup: { jwt: "invalid.token.xyz" },
        payload: { ... },
        expectedResponse: { status: 401, body: { error: "Invalid JWT" } },
        assertions: [
          "Response status === 401",
          "No service called",
          "No DB changes"
        ]
      },
      {
        name: "Authorization fail - TENANT role not allowed",
        setup: { jwt: tenantJWT, role: "TENANT" },
        payload: { ... },
        expectedResponse: { status: 403, body: { error: "Role not allowed" } },
        assertions: [
          "Response status === 403",
          "No business logic executed"
        ]
      },
      {
        name: "Validation fail - Property not found",
        setup: {
          jwt: validJWT,
          listingMock: { status: 404, error: "Property not found" }
        },
        payload: { property_id: "PROP-NOTEXIST", ... },
        expectedResponse: { status: 502, body: { error: "Service Listing error" } },
        assertions: [
          "Response status === 502",
          "No contract created"
        ]
      },
      {
        name: "SOAP timeout - Async retry",
        setup: {
          jwt: validJWT,
          listingMock: { status: 200 },
          soapMock: { timeout: true }
        },
        payload: { ... },
        expectedResponse: { status: 201, body: { contract_id: "...", status: "PENDING_AUDIT" } },
        assertions: [
          "Response status === 201",
          "Contract created with status PENDING_AUDIT",
          "Async retry job queued"
        ]
      },
      {
        name: "RabbitMQ down - Event queued to outbox",
        setup: {
          jwt: validJWT,
          listingMock: { status: 200 },
          soapMock: { status: 200 },
          rabbitmqMock: { down: true }
        },
        payload: { ... },
        expectedResponse: { status: 201, body: { contract_id: "..." } },
        assertions: [
          "Response status === 201",
          "Contract created successfully",
          "Event stored in outbox table for later retry"
        ]
      },
      {
        name: "Concurrent requests - Duplicate contract prevention",
        setup: { jwt: validJWT, concurrent: true },
        payload: { property_id: "PROP-001", tenant_id: "TENANT-001", period: "2024-07-01 to 2025-07-01" },
        expectedResponse: { multipleResponses: [{ status: 201 }, { status: 409 }] },
        assertions: [
          "Only 1 request returns 201",
          "Other returns 409 Conflict or 400 Bad Request",
          "Only 1 contract created in DB"
        ]
      }
    ];
    ```

- **Test Execution:**
    - Unit tests (mocking semua external services)
    - Integration tests (real database, mocked external services)
    - E2E tests (real database, real (stubbed) external services)
    - Load tests (concurrent requests, latency check)
    - Chaos tests (random failures simulation)

- **Mock Strategy:**
    - Mock Service Listing responses
    - Mock SOAP responses (success, fault, timeout)
    - Mock RabbitMQ (success, failure)
    - Real database (or in-memory SQLite for tests)
    - Use libraries: sinon, nock, jest.mock()

**Hasil atau Keputusan**
✅ **Testing strategy diterapkan dalam documentation**

Keputusan implementasi:

- Table-driven testing untuk ease of adding more test cases
- Mock external services untuk isolate unit tests
- Integration tests dengan real database (test transaction behavior)
- E2E tests untuk full flow validation
- Load testing untuk concurrent scenario
- Test coverage target: >80% code coverage, 100% for critical paths
- CI/CD pipeline run tests sebelum merge

---

### Prompt #8: Monitoring, Metrics & Alerting

**Tujuan**  
Merancang metrics dan alerting rules untuk monitoring POST /api/v1/contracts endpoint pada production, termasuk throughput, success/failure rate, latency, dan dependency health.

**Prompt Pengguna**

```
Untuk production monitoring, apa metrics yang harus di-track dari POST /api/v1/contracts?

Metrics yang harus include:
1. Throughput metrics
   - Request rate (requests per minute)
   - Trend (increasing, stable, decreasing)

2. Success/Failure metrics
   - Success rate (%)
   - Failure rate breakdown by reason (auth fail, validation fail, external fail)

3. Latency metrics
   - P50, P95, P99 latencies
   - Breakdown per operation (JWT validation, Service Listing call, SOAP call, DB insert)

4. Dependency health
   - Service Listing availability (%)
   - SOAP Audit System availability (%)
   - RabbitMQ connection status
   - Database connection pool health

5. Data quality metrics
   - Contract audit receipt success rate
   - RabbitMQ publish success rate
   - Duplicate contract attempts

Untuk setiap metric, buatkan:
- Metric name & type (counter, gauge, histogram)
- What to track
- Alert threshold
- Alert severity (critical, warning, info)
- Remediation action

Include juga:
- Dashboard yang should display
- Alerting rules (Prometheus alert syntax)
- Runbook reference untuk oncall engineer
```

**Ringkasan Respon AI**

- **Metric Details:**

    **Throughput Metrics:**

    ```
    Metric: contract_creation_requests_total
    Type: Counter
    Description: Total number of POST /contracts requests
    Alert Threshold: rate > 100 req/min (potential abuse/load spike)
    Alert Severity: WARNING
    Remediation: Check for bot attack, increase rate limit, scale service

    Metric: contract_creation_requests_per_minute
    Type: Gauge
    Description: Requests per minute (for trend analysis)
    Alert Threshold: Sudden 5x spike
    Alert Severity: INFO (for awareness)
    ```

    **Success/Failure Metrics:**

    ```
    Metric: contract_creation_success_total
    Type: Counter (with labels: role, status_code)
    Description: Successful contract creations (201, 202)

    Metric: contract_creation_failure_total
    Type: Counter (with labels: reason: auth_fail, validation_fail, external_fail, db_fail)
    Description: Failed contract creations (4xx, 5xx)
    Alert Threshold: Error rate > 5% (compared to 7-day baseline)
    Alert Severity: CRITICAL
    Remediation: Check service logs, investigate root cause, page oncall engineer

    Metric: contract_creation_success_rate
    Type: Gauge (calculated: success / (success + failure))
    Description: Percentage of successful requests
    Alert Threshold: < 95%
    Alert Severity: CRITICAL
    ```

    **Latency Metrics:**

    ```
    Metric: contract_creation_duration_ms
    Type: Histogram (with buckets: [100, 500, 1000, 5000, 10000])
    Description: Total request latency
    Sub-metrics:
      - contract_creation_duration_p50 (median)
      - contract_creation_duration_p95
      - contract_creation_duration_p99
    Alert Threshold:
      - P95 > 5000ms → WARNING
      - P99 > 10000ms → CRITICAL

    Metric: service_listing_call_duration_ms
    Type: Histogram
    Description: Latency of Service Listing validation
    Alert Threshold: P95 > 2000ms → investigate why Listing slow

    Metric: soap_audit_call_duration_ms
    Type: Histogram
    Description: Latency of SOAP audit call
    Alert Threshold: P95 > 3000ms → legacy system slow
    ```

    **Dependency Health Metrics:**

    ```
    Metric: service_listing_availability
    Type: Gauge (percentage)
    Description: % of successful calls to Service Listing
    Alert Threshold: < 99%
    Alert Severity: CRITICAL

    Metric: soap_audit_system_availability
    Type: Gauge
    Description: % of successful SOAP calls
    Alert Threshold: < 99%
    Alert Severity: CRITICAL

    Metric: rabbitmq_connection_status
    Type: Gauge (1=healthy, 0=down)
    Description: RabbitMQ connection health
    Alert Threshold: = 0 (down)
    Alert Severity: WARNING (not critical, async retry will handle)

    Metric: database_connection_pool_available
    Type: Gauge
    Description: Available connections in pool
    Alert Threshold: < 5 (near exhaustion)
    Alert Severity: WARNING
    ```

    **Data Quality Metrics:**

    ```
    Metric: contract_audit_receipt_success_rate
    Type: Gauge
    Description: % of contracts with successful audit receipt
    Alert Threshold: < 95%
    Alert Severity: CRITICAL

    Metric: contract_rabbitmq_publish_success_rate
    Type: Gauge
    Description: % of events successfully published to RabbitMQ
    Alert Threshold: < 95%
    Alert Severity: WARNING (eventual consistency, will retry)

    Metric: duplicate_contract_attempts
    Type: Counter
    Description: Attempts to create duplicate contracts (409 responses)
    Alert Threshold: > 10 per hour
    Alert Severity: INFO (for trend analysis)
    ```

- **Prometheus Alert Rules Example:**

    ```yaml
    groups:
        - name: contract_service
          interval: 30s
          rules:
              - alert: ContractCreationErrorRateHigh
                expr: rate(contract_creation_failure_total[5m]) / rate(contract_creation_requests_total[5m]) > 0.05
                for: 5m
                annotations:
                    summary: "Contract creation error rate > 5% for 5 minutes"
                    runbook: "https://wiki.company.com/runbooks/contract-service-errors"

              - alert: ContractSOAPAuditUnavailable
                expr: soap_audit_system_availability < 0.99
                for: 2m
                annotations:
                    summary: "SOAP Audit System availability < 99%"
                    runbook: "https://wiki.company.com/runbooks/soap-audit-system"

              - alert: ContractLatencyHigh
                expr: histogram_quantile(0.95, contract_creation_duration_ms) > 5000
                for: 5m
                annotations:
                    summary: "Contract creation P95 latency > 5 seconds"
                    runbook: "https://wiki.company.com/runbooks/contract-latency"
    ```

- **Dashboard Content:**
    - Throughput trend (requests/min over 24h)
    - Success rate gauge (%)
    - Latency heatmap (P50, P95, P99)
    - Error breakdown pie chart (by reason)
    - Dependency health status (Service Listing, SOAP, RabbitMQ, DB)
    - Audit receipt success rate
    - RabbitMQ publish success rate
    - Top error types (last 1h)

**Hasil atau Keputusan**
✅ **Monitoring & metrics design diterapkan dalam documentation**

Keputusan implementasi:

- Prometheus untuk metrics collection
- Grafana untuk visualization & dashboard
- AlertManager untuk alerting
- All metrics dengan labels (role, region, environment)
- Breakdown metrics per operation untuk easier troubleshooting
- Alert only pada actionable events (not every dip)
- Runbooks linked to alerts untuk faster resolution

---

### Prompt #9: Deployment, Configuration & Operational Readiness

**Tujuan**  
Merancang deployment checklist dan operational readiness untuk memastikan Service Kontrak siap di-deploy ke production dengan semua konfigurasi, monitoring, dan documentation yang lengkap.

**Prompt Pengguna**

```
Sebelum deploy Service Kontrak ke production, apa yang harus di-check dan di-prepare?

Buatkan deployment checklist yang mencakup:

1. Code & Build
   - Code review selesai
   - Unit tests passed (target >80% coverage)
   - Integration tests passed
   - E2E tests passed
   - No security vulnerabilities (SAST scan)
   - Code lint & format (eslint, prettier)
   - Docker image built & scanned for vulns

2. Configuration & Secrets
   - Environment variables documented (.env.example)
   - Secrets in vault (API keys, DB password, SOAP endpoint URL)
   - No hardcoded secrets
   - Configuration for prod vs staging different
   - SSL/TLS certificates valid

3. Database
   - Schema migrations tested in staging
   - Migrations backward-compatible (no destructive changes)
   - Data backup strategy defined
   - Rollback procedure documented
   - Database connections pooled properly

4. External Services
   - Service Listing endpoint tested & latency acceptable
   - SOAP Audit System endpoint tested
   - RabbitMQ connection tested
   - All services registered in service discovery

5. Monitoring & Logging
   - Prometheus metrics configured & exported
   - Grafana dashboard created
   - Alert rules configured in AlertManager
   - ELK/centralized logging configured
   - Runbooks written for critical alerts

6. Documentation & Runbooks
   - API documentation (OpenAPI/Swagger) updated
   - Architecture decision records (ADR) documented
   - Deployment runbook written
   - Rollback runbook written
   - Troubleshooting guide written
   - On-call runbooks for common issues

7. Security & Compliance
   - JWT validation tested
   - Role-based access control tested
   - SQL injection prevention verified
   - Rate limiting configured
   - CORS configured properly
   - Data encryption at rest & in transit

8. Performance & Load Testing
   - Load test completed (target: >100 concurrent users)
   - No memory leaks (heap dump analysis)
   - Database query performance OK (no slow queries)
   - Cache strategy implemented if needed
   - Identified bottlenecks & optimization plan

9. Disaster Recovery
   - Backup strategy defined & tested
   - Rollback procedure practiced
   - Circuit breaker configured for external services
   - Graceful shutdown implemented
   - Recovery time objective (RTO) & recovery point objective (RPO) defined

10. Release Process
    - Version tag created (semantic versioning)
    - Release notes written
    - Changelog updated
    - Canary deployment plan (deploy to 5% first, then 100%)
    - Health checks defined (readiness & liveness probes)
    - Monitoring dashboards opened before release

Untuk setiap item, buatkan checkbox & owner responsibility.
```

**Ringkasan Respon AI**

- **Deployment Checklist:**

    ```markdown
    ## Pre-Deployment Checklist: Service Kontrak

    ### Code & Build (Owner: Dev Lead)

    - [ ] Code review approved (min 2 approvals)
    - [ ] Unit tests passed (coverage >80%)
    - [ ] Integration tests passed
    - [ ] E2E tests passed
    - [ ] Security scan passed (no critical vulns)
    - [ ] Lint & format checks passed
    - [ ] Docker image built & scanned for vulnerabilities
    - [ ] Build pipeline green (CI/CD passed)

    ### Configuration & Secrets (Owner: DevOps)

    - [ ] All env variables documented in .env.example
    - [ ] Secrets stored in HashiCorp Vault / AWS Secrets Manager
    - [ ] No hardcoded secrets in codebase (verified by scanning)
    - [ ] Dev/staging/prod configurations different
    - [ ] SSL/TLS certificates valid & installed
    - [ ] Service discovery registration configured

    ### Database (Owner: DBA / Backend Lead)

    - [ ] Migrations tested in staging environment
    - [ ] Backward compatibility verified (no breaking changes)
    - [ ] Connection pool size calculated & configured
    - [ ] Backup procedure tested & documented
    - [ ] Rollback procedure tested & documented
    - [ ] Database indexes created for performance
    - [ ] Replication configured (if high availability required)

    ### External Services (Owner: DevOps / Integration)

    - [ ] Service Listing endpoint tested (200 response)
    - [ ] Service Listing latency acceptable (<2s)
    - [ ] SOAP Audit System endpoint tested
    - [ ] SOAP endpoint latency acceptable (<3s)
    - [ ] RabbitMQ connection tested & exchange/queue created
    - [ ] All service endpoints registered in service discovery
    - [ ] Circuit breaker configuration for each external service

    ### Monitoring & Logging (Owner: DevOps)

    - [ ] Prometheus metrics configured & test scrape works
    - [ ] Grafana dashboard created with key metrics
    - [ ] Alert rules configured in AlertManager
    - [ ] Alert notification channel tested (Slack, PagerDuty, email)
    - [ ] Centralized logging (ELK) configured
    - [ ] Log retention policy set
    - [ ] Trace sampling configured (if using distributed tracing)

    ### Documentation (Owner: Tech Lead)

    - [ ] OpenAPI/Swagger documentation updated
    - [ ] Deployment runbook written & tested
    - [ ] Rollback procedure documented & practiced
    - [ ] Troubleshooting guide written (common issues & solutions)
    - [ ] On-call runbooks for alerts created
    - [ ] Architecture decision records (ADR) documented
    - [ ] Configuration reference documented

    ### Security & Compliance (Owner: Security / Backend Lead)

    - [ ] JWT validation tested with invalid/expired tokens
    - [ ] Role-based access control tested (RBAC)
    - [ ] SQL injection prevention verified
    - [ ] Rate limiting configured & tested
    - [ ] CORS policy configured correctly
    - [ ] HTTPS enforced (no HTTP)
    - [ ] Secrets not exposed in logs
    - [ ] GDPR compliance verified (if handling personal data)

    ### Performance (Owner: Backend Lead)

    - [ ] Load test completed (100+ concurrent users)
    - [ ] Memory profiling done (no leaks)
    - [ ] CPU profiling done (no bottlenecks)
    - [ ] Database query performance analyzed
    - [ ] Slow query log reviewed & optimized
    - [ ] Cache strategy implemented (if needed)
    - [ ] Response time SLA achievable

    ### Disaster Recovery (Owner: DevOps)

    - [ ] Backup procedure tested & automated
    - [ ] Restore procedure tested
    - [ ] RTO (Recovery Time Objective) defined
    - [ ] RPO (Recovery Point Objective) defined
    - [ ] Circuit breaker configured for external services
    - [ ] Graceful shutdown implemented
    - [ ] Health check endpoints defined (readiness & liveness)

    ### Release Process (Owner: Release Manager)

    - [ ] Version tag created (semantic versioning, e.g., v1.0.0)
    - [ ] Release notes written
    - [ ] Changelog updated
    - [ ] Canary deployment plan (5% → 100%)
    - [ ] Health check procedure defined
    - [ ] Rollback procedure practiced
    - [ ] On-call engineer briefed & ready
    - [ ] Monitoring dashboards open & visible

    ### Sign-Off (Owner: Engineering Lead)

    - [ ] All checklist items completed
    - [ ] Stakeholder approval obtained
    - [ ] Deployment window scheduled
    - [ ] On-call schedule updated
    ```

- **Health Check Probes (Kubernetes):**

    ```yaml
    livenessProbe:
        httpGet:
            path: /health/live
            port: 3000
        initialDelaySeconds: 30
        periodSeconds: 10

    readinessProbe:
        httpGet:
            path: /health/ready
            port: 3000
        initialDelaySeconds: 10
        periodSeconds: 5
    ```

- **Rollback Procedure:**
    1. If error rate > 10% within 10 min of deploy → trigger rollback
    2. Kubernetes: `kubectl rollout undo deployment/service-contract -n production`
    3. Notify stakeholders via Slack
    4. Investigate root cause
    5. Create issue for post-incident review

**Hasil atau Keputusan**
✅ **Deployment checklist & operational readiness diterapkan dalam documentation**

Keputusan implementasi:

- Comprehensive checklist dengan 10 categories & 50+ items
- Clear ownership untuk setiap item (Dev, DevOps, DBA, Security)
- Automated checks (CI/CD pipeline) untuk code & build
- Manual checks untuk configuration & infrastructure
- Canary deployment (5% → 100%) untuk risk mitigation
- Health check probes untuk automated recovery
- Documented rollback procedure untuk quick recovery

---

## Insight Pembelajaran

### 1. Hal-Hal yang Dipelajari dari Penggunaan AI

#### A. Enterprise Integration Patterns

- **Synchronous vs Asynchronous Patterns:**
    - Synchronous calls (REST, SOAP) untuk operasi yang critical & time-sensitive
    - Asynchronous calls (RabbitMQ) untuk notification & non-critical updates
    - Trade-off: latency vs consistency vs user experience

- **Transaction Management dalam Distributed Systems:**
    - Tidak semua external calls harus dalam transaction scope
    - SOAP call (critical) DALAM transaction → guarantee consistency
    - RabbitMQ publish (non-critical) LUAR transaction → avoid blocking
    - Outbox pattern untuk handling async failures

- **Error Handling Strategy:**
    - Pre-transaction errors (auth, validation) → immediate fail
    - External service errors → retry dengan exponential backoff
    - Database errors → rollback & return error
    - Async errors → store to queue, retry later, eventual consistency

#### B. Security & Access Control

- **Role-Based Access Control (RBAC):**
    - Role hierarchy harus reflect business logic, bukan hanya teknis
    - Permission matrix untuk clarity siapa bisa apa
    - Property ownership validation untuk tenant isolation
    - Status forcing based on role untuk enforce workflow

- **JWT Token Management:**
    - Validate di API Gateway, ekstrak claims, pass ke services
    - Redact sensitive fields dari logs (JWT full value tidak boleh di-log)
    - Include user_id & role dalam correlation context untuk audit

#### C. Data Consistency & Reliability

- **ACID Properties in Distributed Systems:**
    - Atomicity: Group related operations dalam transaction
    - Consistency: Enforce constraints (unique, foreign keys)
    - Isolation: Use row-level locks untuk prevent race conditions
    - Durability: Log semua changes, backup strategy

- **Handling Race Conditions:**
    - Duplicate contract prevention: unique constraint (property_id, tenant_id, period)
    - Concurrent requests: database-level locking
    - Eventual consistency: async reconciliation jobs

#### D. Logging & Observability

- **Structured Logging:**
    - JSON format untuk machine-readable logs
    - Correlation ID untuk trace end-to-end
    - Field consistency (timestamp, service, user_id, step, status)
    - Redact sensitive data

- **Metrics-Based Monitoring:**
    - Metrics vs logs: metrics untuk trends, logs untuk debugging
    - Percentile-based alerting (P95, P99) lebih useful daripada average
    - Dependency health tracking (external services)
    - Data quality metrics (audit receipt success, event publish success)

### 2. Konsep Baru yang Ditemukan

#### A. Async Retry Patterns

- **Outbox Pattern:** Store failed events to database, retry asynchronously
- **Exponential Backoff:** 1s, 2s, 4s, 8s untuk prevent overwhelming external service
- **Circuit Breaker:** Prevent cascading failures jika external service consistently down
- **Dead Letter Queue:** Poison messages yang selalu fail → human review

#### B. Event-Driven Architecture

- **Event Payload Design:** Self-contained data, include all necessary info
- **Exchange & Routing Key:** Flexible subscription model via RabbitMQ
- **Event Sourcing:** Store all changes as immutable events
- **Eventual Consistency:** Accept delay, ensure consistency eventually

#### C. Distributed Tracing

- **Correlation ID:** Unique ID per request, propagate through services
- **Causality Tracking:** Link parent-child requests
- **Latency Analysis:** Breakdown per operation (JWT validation, DB insert, SOAP call)

#### D. Chaos Engineering

- **Failure Injection:** Test system behavior under failures
- **Timeout Simulation:** What happens if SOAP takes 60 seconds?
- **Dependency Simulation:** Mock external services with different latencies
- **Rollback Testing:** Practice rollback procedure before production

### 3. Solusi Teknis yang Berhasil Diterapkan

#### A. Architecture Decisions

✅ **SOAP call dalam transaction (bukan luar):** Guarantee contract ↔ audit receipt consistency  
✅ **RabbitMQ publish luar transaction:** Avoid blocking user, async retry  
✅ **Service Listing validation pre-transaction:** Early fail fast  
✅ **Async SOAP retry (202 Accepted):** Better UX daripada reject

#### B. Implementation Patterns

✅ **RBAC with role-forced status:** Property Owner → DRAFT, SUPER_ADMIN → any status  
✅ **Outbox table for failed events:** Fallback jika RabbitMQ down  
✅ **Exponential backoff for retries:** Prevent thundering herd  
✅ **Structured JSON logging with correlation ID:** Easy troubleshooting

#### C. Testing & Validation

✅ **Table-driven test cases:** Easy to add more scenarios  
✅ **Mock external services:** Isolate unit tests  
✅ **Load test with concurrent requests:** Find bottlenecks  
✅ **Chaos test (inject failures):** Verify error handling

#### D. Operational Readiness

✅ **Comprehensive deployment checklist:** 50+ items, clear ownership  
✅ **Health check probes:** Automated recovery  
✅ **Canary deployment:** 5% → 100% untuk risk mitigation  
✅ **Runbooks & troubleshooting guides:** Faster incident response

---

## Kesimpulan

### Kontribusi AI dalam Pengembangan Proyek

AI (Claude & ChatGPT) telah memberikan kontribusi signifikan dalam pengembangan Tugas 3 ini:

1. **Design & Architecture:**
    - Sequence diagram dengan 7 komponen sistem
    - Transaction boundaries & consistency strategies
    - Error handling & retry patterns

2. **Technical Guidance:**
    - SOAP/XML transformation & parsing
    - RabbitMQ event design & publishing
    - JWT validation & RBAC implementation

3. **Documentation:**
    - Structured logging & monitoring design
    - Test cases & test plan
    - Deployment checklist & runbooks

### Keputusan Implementasi Tetap di Tangan Mahasiswa

**Penting:** Meskipun AI memberikan guidance dan suggestions, semua keputusan final tetap dilakukan dan diverifikasi oleh mahasiswa:

- ✅ **Analisis keputusan trade-offs** - Mahasiswa evaluate dan decide
- ✅ **Memilih SOAP call dalam transaction** - Berdasarkan reasoning, bukan blind copy
- ✅ **Merancang error handling per scenario** - Understand business context, design accordingly
- ✅ **Implementasi RBAC logic** - Customize sesuai business requirement
- ✅ **Test case design** - Think through edge cases, plan test strategy

### Pembelajaran Independent

Prompt engineering log ini menunjukkan:

1. **Self-directed Learning:** Mengajukan pertanyaan yang tepat untuk deep understanding
2. **Critical Thinking:** Evaluate options, analyze trade-offs, decide berdasarkan context
3. **Technical Problem-Solving:** Design solution untuk complex integration scenarios
4. **Ownership:** Tidak hanya terima solusi AI, tapi understand & customize

---

## Statistik Penggunaan AI

| Metrik                             | Nilai                |
| ---------------------------------- | -------------------- |
| **Total Sesi Prompt**              | 12+ iterasi          |
| **Prompt Categories**              | 10 kategori teknis   |
| **AI Tools Used**                  | Claude, ChatGPT      |
| **Estimated Tokens**               | ~50,000+ tokens      |
| **Hal Baru Dipelajari**            | 15+ concepts         |
| **Solusi Teknis Didesain**         | 20+ design decisions |
| **Test Cases Direncanakan**        | 15+ scenarios        |
| **Metrics & Alerts Didefinisikan** | 20+ items            |
| **Checklist Items**                | 50+ items            |
| **Documentation Pages**            | 15+ pages            |

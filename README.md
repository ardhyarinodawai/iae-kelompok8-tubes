# Sistem Pengajuan Keluhan Tiket Tenant

Dokumentasi ini menjelaskan arsitektur microservices untuk fitur **Pengajuan Keluhan Tiket Tenant**, yang melibatkan 3 backend service dan 1 frontend.

## Latar Belakang & Alur Proses

Fokus: Service Manajemen Tenant, Service Listing Unit, dan Service Kontrak Sewa.

1. Saat terjadi kendala atau kerusakan di tengah masa sewa, penghuni (tenant) mengajukan permohonan perbaikan dengan memberikan detail informasi terkait kerusakan di unitnya.
2. Sistem (Service Manajemen Tiket Tenant) menerima keluhan pengajuan tersebut.
3. Sebelum data disimpan, sistem secara otomatis melakukan **cross-check** terhadap dua hal utama:
   - **Validasi Unit**: Memastikan unit properti yang dilaporkan benar-benar ada dan merupakan aset yang dikelola perusahaan, dengan memanggil Service Listing Unit.
   - **Validasi Kontrak**: Memastikan penghuni yang melapor memiliki perjanjian sewa yang sah dan masih aktif, dengan memanggil Service Kontrak Sewa.
4. Jika kedua validasi tersebut mengembalikan respons **Berhasil (Success - 2xx)**, Service Manajemen Tiket Tenant melanjutkan proses penyimpanan tiket keluhan.
5. Jika salah satu validasi **gagal**, Service Manajemen Tiket Tenant langsung **menolak** proses input tiket tersebut.

```
Frontend
   |
   |  POST /api/v1/ticket-service/tickets
   v
Service Manajemen Tiket Tenant (Ticket Service)
   |
   |--- GET /api/v1/listing-service/listings/{id}  --> Service Listing Unit
   |
   |--- GET /api/v1/contract-service/contracts/{id} --> Service Kontrak Sewa
   |
   v
Jika kedua respon Success (2xx) -> Simpan tiket
Jika salah satu gagal           -> Tolak input tiket
```

---

## Daftar Service

### 1. Service Manajemen Tiket Tenant (Ticket Service)

Mengelola data tiket keluhan/maintenance yang diajukan oleh tenant.

**Base URL:** `http://localhost:8080/api/v1/ticket-service`

**Resource Name:** `tickets`

| Tipe       | Method | Endpoint        | Deskripsi                                            |
| ---------- | ------ | --------------- | ---------------------------------------------------- |
| Collection | GET    | `/tickets`      | Mengambil daftar data riwayat tiket maintenance      |
| Resource   | GET    | `/tickets/{id}` | Mengambil data spesifik satu tiket keluhan           |
| Action     | POST   | `/tickets`      | Menambah data baru saat tenant input tiket kerusakan |

**Catatan integrasi:**
Sebelum menyimpan data tiket ke database, backend Ticket Service wajib memanggil:

- `GET http://localhost:8080/api/v1/listing-service/listings/{id}` — untuk memastikan unit properti benar-benar ada.
- `GET http://localhost:8080/api/v1/contract-service/contracts/{id}` — untuk memastikan kontrak tenant masih aktif.

Jika kedua respons di atas **Success (2xx)**, proses simpan tiket dilanjutkan. Jika salah satu **gagal**, proses input tiket langsung ditolak.

---

### 2. Service Listing Unit (Listing Service)

Mengelola data master unit properti (rumah/apartemen) yang dikelola perusahaan.

**Base URL:** `http://localhost:8080/api/v1/listing-service`

**Resource Name:** `listings`

| Tipe       | Method | Endpoint         | Deskripsi                                    |
| ---------- | ------ | ---------------- | -------------------------------------------- |
| Collection | GET    | `/listings`      | Mengambil daftar seluruh unit properti       |
| Resource   | GET    | `/listings/{id}` | Mengambil data spesifik unit rumah/apartemen |
| Action     | POST   | `/listings`      | Menambah data master unit properti baru      |

---

### 3. Service Kontrak Sewa (Contract Service)

Mengelola data kontrak/perjanjian sewa antara tenant dan perusahaan.

**Base URL:** `http://localhost:8080/api/v1/contract-service`

**Resource Name:** `contracts`

| Tipe       | Method | Endpoint          | Deskripsi                                 |
| ---------- | ------ | ----------------- | ----------------------------------------- |
| Collection | GET    | `/contracts`      | Mengambil daftar seluruh kontrak sewa     |
| Resource   | GET    | `/contracts/{id}` | Mengambil data spesifik kontrak sewa      |
| Action     | POST   | `/contracts`      | Menambah data draf atau kontrak sewa baru |

---

### 4. Frontend

Aplikasi antarmuka yang digunakan tenant untuk mengajukan keluhan/tiket kerusakan unit. Frontend akan memanggil endpoint `POST /api/v1/ticket-service/tickets` pada Ticket Service untuk mengirimkan data tiket keluhan baru.

---

## Cara Setup Project

Project ini terdiri dari 3 backend service (Ticket Service, Listing Service, Contract Service) dan 1 frontend, yang masing-masing dijalankan sebagai container terpisah melalui Docker Compose.

### 1. Clone Repository

```bash
git clone <url-repository-ini>
cd <nama-folder-repository>
```

### 2. Konfigurasi Environment

Lakukan untuk **masing-masing** service (Ticket Service, Listing Service, Contract Service, dan Frontend):

```bash
cp .env.example .env
```

Sesuaikan isi file `.env` sesuai kebutuhan masing-masing service (misalnya koneksi database, port, dan base URL antar service).

### 3. Install Dependencies

**Pada masing-masing backend service** (Ticket Service, Listing Service, Contract Service), jalankan:

```bash
composer install
```

**Pada Frontend**, jalankan:

```bash
npm install
```

### 4. Menjalankan Project dengan Docker

Pastikan Docker dan Docker Compose sudah terinstal. Dari **root folder** project, jalankan:

```bash
docker compose up
```

Tambahkan flag `-d` jika ingin menjalankan container di background:

```bash
docker compose up -d
```

Setelah container berjalan, service dapat diakses melalui:

- Ticket Service: `http://localhost:8080/api/v1/ticket-service`
- Listing Service: `http://localhost:8080/api/v1/listing-service`
- Contract Service: `http://localhost:8080/api/v1/contract-service`

### 5. Menghentikan Project

```bash
docker compose down
```

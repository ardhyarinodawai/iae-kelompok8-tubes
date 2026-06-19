# 🎫 Tenant Complaint Ticket Service

Ini adalah repositori untuk **Service Manajemen Tiket Keluhan Tenant**, bagian dari arsitektur *microservices* Sistem Manajemen Rental. Service ini bertanggung jawab untuk mencatat, mengelola, dan melacak status keluhan dari tenant.


## 🛠️ Tech Stack
* **Framework:** Laravel
* **Database:** SQLite
* **Infrastructure:** Docker & Docker Compose
* **API Architecture:** REST API (Swagger) & GraphQL

## 🚀 Cara Menjalankan Aplikasi (Local Development)

Pastikan **Docker Desktop** sudah terinstal dan berjalan (Engine Running) di sistem Anda.

1. Clone repositori ini.
2. Duplikat file `.env.example` menjadi `.env` (pastikan URL service lain sudah sesuai jika dijalankan bersamaan).
3. Buka terminal di root folder proyek ini.
4. Jalankan perintah berikut untuk mem-build dan menyalakan container:
   ```bash
   docker compose up -d

   Tunggu hingga status container menunjukkan Started.

Akses Dokumentasi & API
Setelah mesin Docker menyala, Anda bisa mengakses antarmuka API melalui browser:

REST API (Swagger UI): http://127.0.0.1:8000/api/v1/documentation
GraphQL Playground: http://127.0.0.1:8000/graphql-playground

Cara Mematikan Service
Untuk menghentikan dan menghapus container dengan bersih, jalankan:
docker compose down

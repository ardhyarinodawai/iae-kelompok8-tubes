# Laravel : Rent Contract Services

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-FF2D20.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.3-777BB4.svg?style=flat-square&logo=php)](https://php.net)
[![Swagger](https://img.shields.io/badge/Swagger-Supported-85EA2D.svg?style=flat-square&logo=swagger)](https://swagger.io/)
[![GraphQL](https://img.shields.io/badge/GraphQL-Lighthouse-E10098.svg?style=flat-square&logo=graphql)](https://graphql.org/)

Project ini merupakan implementasi API modern menggunakan _framework_ Laravel. Project ini dilengkapi dengan antarmuka dokumentasi interaktif (Swagger UI) dan _playground_ (GraphiQL) untuk memudahkan proses _testing_ dan integrasi oleh _frontend developer_ atau klien.

## Fitur Utama

- **RESTful API**: Struktur standar REST.
- **GraphQL API**: Fleksibilitas pengambilan data menggunakan Lighthouse.
- **Swagger UI**: Dokumentasi REST API yang interaktif.
- **GraphiQL Playground**: mengeksplorasi dan menguji _query_ GraphQL secara langsung.

## Teknologi & Library

Project ini dibangun menggunakan _stack_ dan _dependency_ berikut:

- **[Laravel](https://laravel.com/)** - Core Framework
- **[Swagger PHP](https://github.com/zircote/swagger-php)** (`zircote/swagger-php`) - Anotasi standar OpenAPI
- **[L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger)** (`darkaonline/l5-swagger`) - Integrasi Swagger ke Laravel
- **[Lighthouse GraphQL](https://lighthouse-php.com/)** (`nuwave/lighthouse`) - Server GraphQL untuk Laravel
- **[Laravel GraphiQL](https://github.com/mll-lab/laravel-graphiql)** (`mll-lab/laravel-graphiql`) - UI Playground untuk GraphQL

## Persyaratan Sistem

Sebelum menjalankan project ini, pastikan sistem Anda memiliki:

- PHP >= 8.3
- Composer
- Database Server SQLite

## Instalasi

Ikuti langkah-langkah di bawah ini untuk menjalankan project secara lokal di mesin Anda:

1. **Clone repositori ini**
    ```bash
    git clone https://github.com/EsGoreng/102022400056_rent-contract.git
    cd 102022400056_rent-contract
    ```
2. **Install dependency Composer**
    ```bash
    composer install
    ```
3. **Salin file environment**
    ```bash
    cp .env.example .env
    ```
4. **Konfigurasi Database**

    Buka file .env dan sesuaikan kredensial database Anda:

    ```bash
    DB_CONNECTION=sqlite
    #DB_HOST=127.0.0.1
    #DB_PORT=3306
    #DB_DATABASE=nama_database
    #DB_USERNAME=root
    #DB_PASSWORD=DB_CONNECTION=mysql
    ```

5. **Generate Application Key**
    ```bash
    php artisan key:generate
    ```
6. **Jalankan Migrasi dan Seeder**
    ```bash
    php artisan migrate --seed
    ```
7. **Jalankan Local Development Server**
    ```bash
    composer run dev
    ```

## Dokumentasi & Penggunaan API

1. **REST API (Swagger UI)**

    Service ini menggunakan L5 Swagger untuk mengelola dokumentasi REST API berdasarkan anotasi di dalam controller.
    - Akses UI Dokumentasi: http://localhost:8000/api/v1/documentation

    - Generate ulang dokumentasi:

        Setiap kali Anda mengubah atau menambahkan anotasi OpenAPI di controller, jalankan perintah ini untuk memperbarui tampilan UI:

        ```bash
        php artisan l5-swagger:generate
        ```

2. **GraphQL (Lighthouse & GraphiQL)**

    Skema GraphQL didefinisikan di dalam folder graphql/schema.graphql.
    - GraphQL Endpoint API Utama: http://localhost:8000/graphql (Gunakan endpoint ini untuk komunikasi dari aplikasi client).

    - Akses GraphiQL Playground: http://localhost:8000/graphiql
      Buka URL di atas melalui browser untuk mulai menulis query dan mengeksplorasi skema (Docs) yang tersedia.

3. **Konfigurasi API Key (Autentikasi)**
   Project ini membutuhkan API Key untuk mengakses beberapa endpoint. Anda dapat menggunakan credential _default_ atau membuat key baru:
    - **Menggunakan Default Key (NIM):**
      Buka Swagger UI di http://localhost:8000/api/v1/documentation lalu masukan NIM berikut:
        ```
        102022400056
        ```
    - **Men-generate Key Baru:**
      Jalankan perintah berikut pada terminal:
        ```bash
        php artisan apikey:generate
        ```
        Salin (_copy_) teks token hasil generate yang muncul di terminal, lalu tempel (_paste_) ke dalam file `.env`:
        ```
        API_KEY=isi_dengan_hasil_generate_tadi
        ```

---

### Menjalankan dengan Docker

Jika Anda lebih memilih menggunakan Docker agar tidak perlu menginstal PHP, Composer, atau database secara lokal di mesin Anda, project ini sudah menyediakan konfigurasi Docker Compose.

Pastikan Docker Desktop/Daemon Anda sudah aktif, lalu jalankan perintah berikut di terminal:

```bash
docker compose up -d
```

Perintah di atas akan mengunduh image, membangun (build) container, dan menjalankan server di latar belakang (detached mode). Setelah proses selesai, aplikasi beserta seluruh layatannya dapat langsung diakses melalui http://localhost:8000.

Untuk menghentikan container, Anda cukup menjalankan perintah:

```bash
docker compose down
```

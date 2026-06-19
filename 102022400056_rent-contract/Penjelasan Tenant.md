- Permintaan HTTP -> route di api.php -> method di `TenantController` -> (jika perlu) `StoreTenantRequest` validasi -> model `Tenant` (Eloquent) operasi -> hasil dibungkus `TenantResource` -> JSON response.

**TenantController.php**

- **Tipe:** Controller API resource.
- **Fungsi utama:** Menangani CRUD untuk tenant.
- **Metode penting:**
    - `index`: ambil semua tenant beserta relasi `contracts` (via `with('contracts')`), urutkan `created_at` desc, kembalikan koleksi sebagai `TenantResource::collection(...)`.
    - `store`: validasi via `StoreTenantRequest`, buat `Tenant::create(...)`, load relasi `contracts`, kembalikan `new TenantResource(...)`.
    - `show`: load relasi `contracts`, kembalikan `TenantResource`.
    - `update`: validasi via `StoreTenantRequest`, update model, load `contracts`, kembalikan resource.
    - `destroy`: hapus model lalu kembalikan `TenantResource` (model sudah dihapus dari DB tetapi instance masih dikembalikan sebagai JSON).
- **Catatan:** Controller mengandalkan Eloquent relation `contracts` pada model `Tenant` untuk menampilkan jumlah dan daftar kontrak.

**StoreTenantRequest.php**

- **Tipe:** Form request untuk validasi dan otorisasi.
- **Fungsi utama:** Menyediakan aturan validasi untuk pembuatan dan pembaruan tenant.
- **Aturan:**
    - `name`: required, string, max 255
    - `email`: required, email, max 255, unique pada tabel `tenants` (mengabaikan ID tenant saat update)
    - `phone`: required, string, max 20
    - `nik`: required, string, max 20, unique pada tabel `tenants`
- **Catatan implementasi:** Unique rule menggunakan `unique:tenants,email,'.($this->tenant->id ?? 'NULL')` untuk mengabaikan record saat update. Pada create `$this->tenant` biasanya null sehingga bagian `NULL` dimasukkan — pola umum yang lebih jelas adalah mengambil id dari route (`$this->route('tenant')`) atau menggunakan `->ignore($id)` builder jika memakai `Rule::unique(...)`.

**TenantResource.php**

- **Tipe:** API Resource (transformer).
- **Fungsi utama:** Mengubah instance `Tenant` menjadi array yang dikembalikan sebagai JSON.
- **Field yang dikembalikan:** `id`, `name`, `email`, `phone`, `nik`, `contracts_count` (hitung koleksi relasi), `contracts` (koleksi relasi mentah), `created_at`, `updated_at` (ISO8601).
- **Catatan:** Karena controller memanggil `load('contracts')`, akses ke `$this->contracts` tidak memicu query tambahan. Untuk output kontrak yang lebih konsisten biasanya tiap kontrak dibungkus `ContractResource` alih-alih mengembalikan model mentah.

**api.php**

- **Fungsi:** Mendaftarkan resource routes API.
- **Routes yang dibuat (contoh):**
    - `GET /api/v1/tenants` -> `TenantController@index`
    - `POST /api/v1/tenants` -> `TenantController@store`
    - `GET /api/v1/tenants/{tenant}` -> `TenantController@show`
    - `PUT/PATCH /api/v1/tenants/{tenant}` -> `TenantController@update`
    - `DELETE /api/v1/tenants/{tenant}` -> `TenantController@destroy`
- **Catatan:** Ada juga `contracts` resource terdaftar serupa untuk controller kontrak.

# Menggunakan image PHP 8.2 CLI yang ringan
FROM php:8.2-cli

# Install dependensi sistem dan SQLite
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Bersihkan cache instalasi
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Salin Composer dari image resminya
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur folder kerja di dalam Docker
WORKDIR /var/www

# Salin semua file proyek Anda ke dalam Docker
COPY . .

# Pastikan folder storage dan bootstrap/cache dibuat terlebih dahulu sebelum diberi izin akses
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Beri hak akses baca-tulis penuh agar Laravel bisa menulis log dan cache dengan aman
RUN chmod -R 777 storage bootstrap/cache

# Install dependensi Laravel (Vendor) di dalam Docker
RUN composer install --optimize-autoloader

# Buka port 8000
EXPOSE 8002

# Perintah otomatis: Migrate, Clear Cache, dan jalankan Server
CMD php artisan migrate --force && \
    php artisan clear-compiled && \
    php artisan optimize:clear && \
    php artisan serve --host=0.0.0.0 --port=8002
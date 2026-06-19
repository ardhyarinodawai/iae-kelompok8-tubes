-- Buat database konga jika belum ada
-- Script ini dijalankan otomatis saat postgres pertama kali start
SELECT 'CREATE DATABASE konga OWNER kong'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'konga')\gexec
# Analisis Tugas 3: Integrasi Sistem Terpusat

## 1. Justifikasi Transaksi Kritis (State-Changing)

Jadi pada layanan **Listing Unit Service**, transaksi yang dinilai sebagai transaksi paling darurat dan memiliki dampak perubahan atau di sebut State Changing adalah Pembuatan Listing Baru (`POST /api/v1/listings`). 

Alasan / Justifikasi:
1.  Dampak Finansial dan Operasional: Pembuatan listing unit baru berarti ada aset properti baru yang didaftarin ke dalam sistem yang nantinya bisa ditawarin kepada tenant atau si penyewa. Ini adalah awal dari siklus hidup sebuah unit apartemen/kamar kos dalam sistem informasi manajemen propertinya.
2.  State-Changing: Operasi ini dilakukan insert data ke database utama (`listings` table). Status awal unit disetel menjadi `available`. Perubahan ini bersifat permanen dan mempengaruhi resource yang tersedia untuk transaksi bisnis selanjutnya.
3.  Kebutuhan Audit (SOAP): Karena nilai dan pentingnya aset ini, setiap penambahan unit baru harus dicatat oleh sistem audit atau biasanya di sebut Legacy SOAP milik perusahaan pusat sebagai bentuk keterbukaan operasional.
4.  Kebutuhan Notifikasi (AMQP/RabbitMQ): Departemen lain (seperti Pemasaran, Keuangan, atau Maintenance) perlu mengetahui kalau semisal ada unit baru yang terdaftar secara langsung biar mereka bisa melakukan tindak lanjutan (misalnya: menyiapkan promosi, membagi rata staf pembersih). Oleh sebab itu, event ini sangat relevan untuk disebarkan secara Message Broker.

## 2. Sequence Diagram: Aliran Interaksi Layanan Terpusat

Diagram di bawah ini menggambarkan alur lengkap ketika User atau sebagai Warga melakukan request pembuatan Listing baru, mulai dari validasi SSO (JWT), eksekusi logika bisnis lokal, pelaporan Audit via SOAP, hingga pengiriman notifikasi via AMQP ke RabbitMQ pusat.

## Kesimpulan
Dengan implementasi arsitektur di atas:
- SSO Terpusat menjamin keamanan otentikasi.
- Audit SOAP memastikan semua *creation* tercatat di sistem lama dengan bukti ReceiptNumber.
- AMQP Broker memastikan sistem lain di lingkungan perusahaan dapat bereaksi secara event-driven terhadap unit baru.

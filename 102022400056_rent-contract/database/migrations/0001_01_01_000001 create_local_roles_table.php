<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();       // 'admin', 'owner', 'tenant'
            $table->string('display_name');         // 'Administrator', 'Pemilik', 'Penyewa'
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed role default untuk aplikasi sewa/rental
        DB::table('local_roles')->insert([
            ['name' => 'admin',  'display_name' => 'Administrator', 'description' => 'Akses penuh ke seluruh sistem',          'created_at' => now(), 'updated_at' => now()],
            ['name' => 'owner',  'display_name' => 'Pemilik',       'description' => 'Pemilik properti/barang yang disewakan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'tenant', 'display_name' => 'Penyewa',       'description' => 'Pengguna yang menyewa properti/barang',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('local_roles');
    }
};
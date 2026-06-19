<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sso_users', function (Blueprint $table) {
            $table->id();

            // --- Data dari JWT payload server Pa Eki ---
            $table->string('sso_subject')->unique();  // JWT 'sub' claim (ID unik dari SSO)
            $table->string('email')->unique();
            $table->string('full_name')->nullable();  // dari profile.full_name
            $table->string('nim')->nullable();        // dari profile.nim (nomor induk mahasiswa)
            $table->string('token_type');             // 'm2m' atau 'user'

            // Simpan raw JWT payload untuk keperluan debugging/audit
            $table->json('sso_payload')->nullable();

            // --- Mapping ke role lokal ---
            $table->foreignId('local_role_id')
                  ->constrained('local_roles')
                  ->restrictOnDelete();

            // --- Token management ---
            $table->text('last_jwt_token')->nullable();  // simpan token terakhir
            $table->timestamp('token_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_users');
    }
};
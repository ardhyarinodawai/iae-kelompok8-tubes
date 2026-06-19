<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();    // nullable karena login via SSO
            $table->string('sso_sub')->nullable();     // subject dari JWT SSO dosen
            $table->string('role')->default('tenant'); // role lokal
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
 
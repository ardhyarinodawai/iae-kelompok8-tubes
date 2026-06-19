<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('listing_id');
            $table->string('contract_id');
            $table->string('tenant_name');
            $table->string('tenant_email');
            $table->text('description');
            $table->string('soap_receipt')->nullable();
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
 
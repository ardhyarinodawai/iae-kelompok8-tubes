<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id('contract_id');

            $table->integer('tenant_id');
            $table->integer('listing_id');

            $table->date('start_date');
            $table->date('end_date');

            $table->boolean('is_active')->default(false);

            $table->string('soap_receipt_number')->nullable();
            $table->timestamp('soap_audited_at')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('tenant_id')
                ->on('tenants')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

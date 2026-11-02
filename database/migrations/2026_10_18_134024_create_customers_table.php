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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->restrictOnDelete(); // restrictOnDelete() Empêche la suppression d'un Tenant lorsqu'un client lui est encore rattaché.
            $table->string('phone', 20);
            $table->string('name');
            $table->string('address');
            $table->string('status')->default('actif');
            $table->softdeletes();
            $table->timestamps();
            $table->index(['reference', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

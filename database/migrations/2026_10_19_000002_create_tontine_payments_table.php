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
        Schema::create('tontine_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tontine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tontine_day_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('payment_date');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->json('history')->nullable();
            $table->timestamps();

            $table->index(['tontine_id', 'voided_at', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('tontine_payments');
    }
};

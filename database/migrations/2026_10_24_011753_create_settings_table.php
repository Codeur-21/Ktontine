<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('contact_phone', 25)->default('');
            $table->string('contact_email')->default('');
            $table->string('address')->default('');
            $table->string('ticket_footer', 300)->default('');
            $table->string('ticket_paper', 2)->default('A4');
            $table->boolean('show_signatures')->default(true);
            $table->boolean('show_commission')->default(true);
            $table->unsignedSmallInteger('priority_days')->default(7);
            $table->string('report_period', 10)->default('month');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id('id_verification_logs');
            $table->foreignId('id_user')
                ->nullable()
                ->constrained('users', 'id_user')
                ->nullOnDelete();
            $table->enum('type', ['nik', 'nop', 'npwpd']);
            $table->string('value_checked');
            $table->boolean('is_found');
            $table->string('source');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['type', 'value_checked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};

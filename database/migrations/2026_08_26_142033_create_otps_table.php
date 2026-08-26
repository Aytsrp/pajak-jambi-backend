<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id('id_otp');
            $table->foreignId('id_user')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->enum('purpose', ['unlock_account', 'reset_password', 'reset_pin']);
            $table->enum('channel', ['email', 'sms']);
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['id_user', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
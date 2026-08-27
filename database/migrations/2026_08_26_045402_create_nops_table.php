<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nops', function (Blueprint $table) {
            $table->id('id_nop');
            $table->foreignId('id_user')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->string('nop_number', 30);
            $table->string('object_name');
            $table->string('owner_name');
            $table->text('object_address');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['id_user', 'nop_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nops');
    }
};
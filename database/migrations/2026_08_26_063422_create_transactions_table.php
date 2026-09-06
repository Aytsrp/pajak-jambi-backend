<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('id_transactions');
            $table->foreignId('id_user')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->foreignId('id_bill')
                ->constrained('bills', 'id_bills')
                ->restrictOnDelete();
            $table->foreignId('id_payment')->nullable();
            $table->string('payment_channel', 20)->default('bank_transfer');
            $table->string('bank_code', 20)->nullable();
            $table->string('va_number', 30)->nullable()->unique();
            $table->timestamp('va_expired_at')->nullable();
            $table->text('qr_string')->nullable();
            $table->string('qr_image_url')->nullable();
            $table->timestamp('qr_expired_at')->nullable();
            $table->string('transaction_ref')->unique();
            $table->string('idempotency_key')->unique();
            $table->enum('tax_type', ['pbb', 'pajak_usaha', 'bphtb']);
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->decimal('amount', 15, 2);
            $table->string('gateway_ref')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            $table->string('proof_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id('id_bills');

            $table->string('billable_type');
            $table->unsignedBigInteger('billable_id');
            $table->string('tax_period', 20);
            $table->decimal('amount_due', 15, 2);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['unpaid', 'paid', 'overdue'])->default('unpaid');
            $table->date('due_date');
            $table->timestamp('fetched_at');
            $table->timestamps();
            $table->index(['billable_type', 'billable_id']);
            $table->unique(['billable_type', 'billable_id', 'tax_period'], 'bills_billable_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
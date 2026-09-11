<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['id_user', 'created_at'], 'transactions_user_created_index');
            $table->index(['id_user', 'status', 'paid_at'], 'transactions_user_status_paid_index');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_user_created_index');
            $table->dropIndex('transactions_user_status_paid_index');
        });
    }
};

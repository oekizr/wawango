<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Payment method is now chosen by the pemesan after the provider
     * confirms the order (not at checkout time), so it lives solely on the
     * `payments` table (created once chosen) instead of being duplicated
     * here as a column that has to be filled in immediately.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'transfer', 'qris'])->after('total');
        });
    }
};

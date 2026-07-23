<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Plain string rather than a DB enum: the allowed values are
            // validated at the request layer (Rule::in) and a string column
            // is trivial to extend across MySQL/SQLite as new reasons are
            // added, unlike an ENUM which requires a driver-specific ALTER.
            $table->string('reason', 30);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_issues');
    }
};

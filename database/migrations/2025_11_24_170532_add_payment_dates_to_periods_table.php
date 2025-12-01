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
    Schema::table('periods', function (Blueprint $table) {
        // Guardaremos un array JSON: ["2025-01-15", "2025-02-20", ...]
        $table->json('payment_dates')->nullable()->after('monthly_payments_count');
    });
}

public function down(): void
{
    Schema::table('periods', function (Blueprint $table) {
        $table->dropColumn('payment_dates');
    });
}
};

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
        // Este archivo solo debe MODIFICAR la tabla 'billings'
        Schema::table('billings', function (Blueprint $table) {
            $table->foreignId('period_id')
                  ->nullable()
                  ->after('user_id') // La coloca después de user_id
                  ->constrained('periods') // Enlaza con tu tabla 'periods'
                  ->onDelete('set null'); // Si se borra un periodo, la factura no se borra
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Este es el código para revertir el cambio
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
};
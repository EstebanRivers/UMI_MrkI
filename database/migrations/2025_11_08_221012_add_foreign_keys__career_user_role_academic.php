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
        Schema::table('academic_profiles', function (Blueprint $table) {
            // 1. Eliminar los campos antiguos de texto/JSON
            $table->dropColumn(['carrera']);

            // 2. Agregar la clave foránea para la Carrera
            // Referencia la tabla 'carrers' que definiste.
            $table->foreignId('carrera_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('carrers')
                  ->onDelete('set null'); // Si se elimina una carrera, el campo se pone a NULL.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_profiles', function (Blueprint $table) {
            // 1. Revertir: Eliminar las claves foráneas
            // Laravel automáticamente genera el nombre del índice, dropConstrainedForeignId lo elimina.
            $table->dropConstrainedForeignId('carrera_id');
            $table->dropConstrainedForeignId('rol_user_id');

            // 2. Revertir: Agregar los campos originales
            // **ADVERTENCIA:** Se perderán los datos originales de estos campos.
            $table->string('carrera')->nullable();
            $table->json('rol')->nullable();
        });
    }
};

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
        Schema::create('horario_franjas', function (Blueprint $table) {
            $table->id();
            // 1. Clave foránea que apunta al Registro Maestro
            // Esto asegura que cada franja sepa a qué clase (Materia/Docente/Aula) pertenece
            $table->foreignId('horario_clase_id')
                ->constrained('horario_clases') // Nombre de la tabla maestra que definimos
                ->onDelete('cascade'); 
                
            // 2. Los campos de tiempo
            // Usamos TINYINT para el día (más eficiente) y TIME para las horas.
            $table->json('dias_semana') 
                ->comment('Array de días de la semana: [1, 2, 5] para Lunes, Martes y Viernes.');
                
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_franjas');
    }
};

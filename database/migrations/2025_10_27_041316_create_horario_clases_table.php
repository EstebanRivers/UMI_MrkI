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
        Schema::create('horario_clases', function (Blueprint $table) {
            $table->id();
            // Llave foránea a id de materia (Materia)
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade'); 
            // Llave foránea a id de carrera (Career)
            // Nota: Aunque la carrera se puede obtener desde la materia,
            // mantenerla aquí puede acelerar ciertos filtros.
            $table->foreignId('carrera_id')->constrained('carrers')->onDelete('cascade');
            
            // Llave foránea a id de usuario (Docente)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // LLAVE FORÁNEA PARA EL AULA
            $table->foreignId('aula_id')->constrained('facilities')->onDelete('cascade'); 
            
            // Los campos dias_disponibles, hora_inicio, y hora_fin se eliminan de aquí.
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_clases');
    }
};

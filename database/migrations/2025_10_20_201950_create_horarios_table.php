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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            // Llave foránea a id de materia (Materia)
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade'); 
            
            // Llave foránea a id de carrera (Career)
            $table->foreignId('career_id')->constrained('careers')->onDelete('cascade');
            
            // Llave foránea a id de usuario (User)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Días disponibles: Se puede usar un string para guardar múltiples días (ej. "Lunes,Miércoles,Viernes") o un campo para un solo día.
            $table->string('dias_disponibles', 100); 
            
            // Hora disponible: Se puede usar un string para guardar la hora de inicio (ej. "08:00") o un rango de horas.
            $table->time('hora_inicio'); 
            $table->time('hora_fin')->nullable(); // Hora de fin opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};

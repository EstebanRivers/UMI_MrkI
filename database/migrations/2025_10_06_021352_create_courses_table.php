<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla 'courses' con soporte Multi-Institucional y campos de targeting.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->integer('credits')->default(0);
            $table->integer('hours')->default(0);
            $table->string('image')->nullable();

            // --- RELACIONES BASE ---
            
            // Instructor (Usuario que creó/imparte el curso)
            $table->foreignId('instructor_id')
                  ->constrained('users') 
                  ->onDelete('restrict'); 

            // --- FILTROS DE ARQUITECTURA Y AUDIENCIA ---
            
            // 1. FILTRO MULTI-INSTITUCIONAL (Multi-Tenancy)
            $table->foreignId('institution_id')
                  ->constrained('institutions') 
                  ->onDelete('cascade'); 
            
            // 2. DESTINO ACADÉMICO/DEPARTAMENTAL (Carrera o Departamento)
            $table->string('target_department_career')
                  ->nullable()
                  ->comment('Carrera (Académico) o Nombre del Departamento (Corporativo)');
            
            // 3. DESTINO CORPORATIVO (Puesto de Trabajo)
            $table->string('target_job_title')
                  ->nullable()
                  ->comment('Puesto de trabajo específico para el filtrado corporativo');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
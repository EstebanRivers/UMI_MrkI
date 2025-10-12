<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Create Course Career Table (Pivot)
     *
     * Crea la tabla pivote para la relación muchos-a-muchos entre cursos y carreras.
     *
     * ## Propósito
     *
     * Permite que:
     * - **Un curso** pueda estar dirigido a **múltiples carreras**
     * - **Una carrera** pueda tener **múltiples cursos** disponibles
     *
     * ## Casos de Uso
     *
     * 1. **Filtrado de cursos para usuarios académicos**
     *    - Usuario con carrera "Ingeniería en Sistemas"
     *    - Mostrar solo cursos asignados a esa carrera
     *
     * 2. **Creación de cursos multi-carrera**
     *    - Curso "Matemáticas Básicas" para:
     *      - Ingeniería en Sistemas
     *      - Ingeniería Industrial
     *      - Administración
     *
     * 3. **Reportes institucionales**
     *    - ¿Cuántos cursos hay disponibles por carrera?
     *    - ¿Qué carreras tienen más cursos asignados?
     *
     * ## Ejemplo de Datos
     *
     * | id | course_id | career_id | Significado                                      |
     * |----|-----------|-----------|--------------------------------------------------|
     * | 1  | 10        | 1         | Curso 10 está disponible para Ing. Sistemas     |
     * | 2  | 10        | 2         | Curso 10 TAMBIÉN para Administración             |
     * | 3  | 15        | 1         | Curso 15 solo para Ing. Sistemas                 |
     *
     * ## Integridad Referencial
     *
     * - Si se elimina un curso → se eliminan sus relaciones (cascade)
     * - Si se elimina una carrera → se eliminan sus relaciones (cascade)
     * - No permite duplicados (mismo curso + misma carrera)
     */
    public function up(): void
    {
        Schema::create('course_career', function (Blueprint $table) {
            $table->id();

            // Relación con cursos
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->onDelete('cascade');

            // Relación con carreras
            $table->foreignId('career_id')
                  ->constrained('careers')
                  ->onDelete('cascade');

            $table->timestamps();

            // Evitar duplicados: un curso no puede estar asignado
            // a la misma carrera más de una vez
            $table->unique(['course_id', 'career_id']);

            // Índices para búsquedas eficientes
            $table->index('course_id');
            $table->index('career_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_career');
    }
};

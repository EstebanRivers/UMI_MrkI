<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Create Course Job Position Table (Pivot)
     *
     * Crea la tabla pivote para la relación muchos-a-muchos entre cursos y puestos de trabajo.
     *
     * ## Propósito
     *
     * Permite que:
     * - **Un curso** pueda estar dirigido a **múltiples puestos**
     * - **Un puesto** pueda tener **múltiples cursos** disponibles
     *
     * ## Casos de Uso
     *
     * 1. **Filtrado de cursos para usuarios corporativos**
     *    - Usuario con puesto "Gerente de Ventas"
     *    - Mostrar solo cursos asignados a ese puesto
     *
     * 2. **Creación de cursos multi-puesto**
     *    - Curso "Liderazgo Efectivo" para:
     *      - Gerente de Ventas
     *      - Gerente de Marketing
     *      - Coordinador General
     *
     * 3. **Reportes organizacionales**
     *    - ¿Cuántos cursos hay disponibles por puesto?
     *    - ¿Qué puestos tienen más capacitación asignada?
     *
     * ## Ejemplo de Datos
     *
     * | id | course_id | job_position_id | Significado                                  |
     * |----|-----------|-----------------|----------------------------------------------|
     * | 1  | 20        | 1               | Curso 20 para Gerente de Ventas              |
     * | 2  | 20        | 2               | Curso 20 TAMBIÉN para Coordinador            |
     * | 3  | 25        | 3               | Curso 25 solo para Analista Jr               |
     *
     * ## Integridad Referencial
     *
     * - Si se elimina un curso → se eliminan sus relaciones (cascade)
     * - Si se elimina un puesto → se eliminan sus relaciones (cascade)
     * - No permite duplicados (mismo curso + mismo puesto)
     */
    public function up(): void
    {
        Schema::create('course_job_position', function (Blueprint $table) {
            $table->id();

            // Relación con cursos
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->onDelete('cascade');

            // Relación con puestos de trabajo
            $table->foreignId('job_position_id')
                  ->constrained('job_positions')
                  ->onDelete('cascade');

            $table->timestamps();

            // Evitar duplicados: un curso no puede estar asignado
            // al mismo puesto más de una vez
            $table->unique(['course_id', 'job_position_id']);

            // Índices para búsquedas eficientes
            $table->index('course_id');
            $table->index('job_position_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_job_position');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Create Careers Table
     *
     * Crea el catálogo centralizado de carreras académicas.
     *
     * ## Propósito
     *
     * Esta tabla sirve como único catálogo de carreras para:
     * 1. **Asignar carrera a usuarios** en `academic_profiles`
     * 2. **Filtrar cursos** disponibles por carrera
     * 3. **Reportes institucionales** de alumnos por carrera
     *
     * ## Ventajas del Catálogo Centralizado
     *
     * - Nombres consistentes (sin errores tipográficos)
     * - Integridad referencial
     * - Queries eficientes
     * - Gestión centralizada (activar/desactivar carreras)
     *
     * ## Campos
     *
     * - `id`: Identificador único
     * - `name`: Nombre de la carrera (ej: "Ingeniería en Sistemas")
     * - `institution_id`: Institución a la que pertenece la carrera
     * - `department_id`: Departamento académico al que pertenece (opcional)
     * - `active`: Estado de la carrera (permite desactivar sin eliminar)
     *
     * ## Relaciones
     *
     * - Pertenece a una institución (multi-tenancy)
     * - Puede pertenecer a un departamento (opcional)
     * - Será referenciado por `academic_profiles.career_id`
     * - Será referenciado por `course_career` (muchos a muchos con cursos)
     *
     * ## Ejemplo de Datos
     *
     * | id | name                      | institution_id | department_id | active |
     * |----|---------------------------|----------------|---------------|--------|
     * | 1  | Ingeniería en Sistemas    | 1              | 5             | true   |
     * | 2  | Administración            | 1              | 3             | true   |
     * | 3  | Derecho                   | 1              | 2             | true   |
     */
    public function up(): void
    {
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Multi-tenancy: cada carrera pertenece a una institución
            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->onDelete('cascade');

            // Departamento académico al que pertenece (opcional)
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->onDelete('set null');

            // Estado: permite desactivar sin eliminar datos históricos
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Índices para búsquedas eficientes
            $table->index(['institution_id', 'active']);
            $table->index('department_id');

            // Evitar carreras duplicadas en la misma institución
            $table->unique(['name', 'institution_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};

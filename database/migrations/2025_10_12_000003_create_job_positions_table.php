<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Create Job Positions Table
     *
     * Crea el catálogo centralizado de puestos de trabajo corporativos.
     *
     * ## Propósito
     *
     * Esta tabla sirve como único catálogo de puestos para:
     * 1. **Asignar puesto a usuarios** en `corporate_profiles`
     * 2. **Filtrar cursos** disponibles por puesto
     * 3. **Reportes organizacionales** de empleados por puesto
     * 4. **Estructura organizacional** de la empresa
     *
     * ## Ventajas del Catálogo Centralizado
     *
     * - Nombres consistentes de puestos
     * - Integridad referencial
     * - Queries eficientes
     * - Gestión centralizada (activar/desactivar puestos)
     * - Jerarquía organizacional clara
     *
     * ## Campos
     *
     * - `id`: Identificador único
     * - `name`: Nombre del puesto (ej: "Gerente de Ventas")
     * - `institution_id`: Institución/empresa a la que pertenece
     * - `department_id`: Departamento corporativo al que pertenece (opcional)
     * - `active`: Estado del puesto (permite desactivar sin eliminar)
     *
     * ## Relaciones
     *
     * - Pertenece a una institución (multi-tenancy)
     * - Puede pertenecer a un departamento corporativo (opcional)
     * - Será referenciado por `corporate_profiles.job_position_id`
     * - Será referenciado por `course_job_position` (muchos a muchos con cursos)
     *
     * ## Ejemplo de Datos
     *
     * | id | name              | institution_id | department_id | active |
     * |----|-------------------|----------------|---------------|--------|
     * | 1  | Gerente de Ventas | 2              | 1             | true   |
     * | 2  | Coordinador       | 2              | 2             | true   |
     * | 3  | Analista Jr       | 2              | 3             | true   |
     */
    public function up(): void
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Multi-tenancy: cada puesto pertenece a una institución
            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->onDelete('cascade');

            // Departamento corporativo al que pertenece (opcional)
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

            // Evitar puestos duplicados en la misma institución
            $table->unique(['name', 'institution_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_positions');
    }
};

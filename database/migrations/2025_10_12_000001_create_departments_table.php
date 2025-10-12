<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Create Departments Table
     *
     * Crea la tabla de departamentos que sirve como catálogo centralizado
     * para departamentos académicos y corporativos.
     *
     * ## Propósito
     *
     * Esta tabla unifica la gestión de departamentos para ambos contextos:
     * - **Académico**: Facultades, departamentos académicos
     * - **Corporativo**: Áreas, departamentos empresariales
     *
     * ## Campos
     *
     * - `id`: Identificador único
     * - `name`: Nombre del departamento
     * - `institution_id`: Institución a la que pertenece
     * - `type`: Tipo de departamento ('academic' o 'corporate')
     * - `active`: Estado del departamento (permite desactivar sin eliminar)
     *
     * ## Relaciones
     *
     * - Pertenece a una institución (multi-tenancy)
     * - Será referenciado por `careers` y `job_positions`
     * - Será referenciado por `academic_profiles` y `corporate_profiles`
     *
     * ## Seguridad
     *
     * - Eliminación en cascada si se elimina la institución
     * - Índices para búsquedas eficientes por institución y tipo
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // Multi-tenancy: cada departamento pertenece a una institución
            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->onDelete('cascade');

            // Tipo: academic (escolar) o corporate (empresarial)
            $table->enum('type', ['academic', 'corporate']);

            // Estado: permite desactivar sin eliminar datos históricos
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Índices para búsquedas eficientes
            $table->index(['institution_id', 'type']);
            $table->index(['institution_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

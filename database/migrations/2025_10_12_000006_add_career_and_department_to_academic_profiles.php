<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Add Career and Department Foreign Keys to Academic Profiles
     *
     * Moderniza la tabla `academic_profiles` para usar relaciones con catálogos
     * centralizados en lugar de strings libres.
     *
     * ## Cambios
     *
     * ### Se AGREGAN (nuevas columnas con Foreign Keys):
     * - `career_id` → FK a tabla `careers`
     * - `department_id` → FK a tabla `departments`
     *
     * ### Se MANTIENEN TEMPORALMENTE (para migración de datos):
     * - `carrera` (string) - DEPRECADO, se eliminará en migración futura
     * - `departamento` (string) - DEPRECADO, se eliminará en migración futura
     *
     * ## Beneficios
     *
     * 1. **Integridad Referencial**
     *    - Solo se pueden asignar carreras/departamentos que existen
     *    - Previene errores tipográficos
     *
     * 2. **Queries Eficientes**
     *    - Búsquedas indexadas
     *    - Joins directos con tablas relacionadas
     *
     * 3. **Consistencia con Cursos**
     *    - Mismos catálogos para perfiles Y filtrado de cursos
     *    - Un solo lugar para gestionar carreras/departamentos
     *
     * ## Plan de Migración de Datos
     *
     * 1. Ejecutar esta migración (agrega columnas nuevas)
     * 2. Script manual para convertir strings → IDs
     * 3. Verificar que todos los registros tengan career_id
     * 4. Migración futura eliminará columnas `carrera` y `departamento`
     *
     * ## Ejemplo de Conversión
     *
     * ANTES:
     * | user_id | carrera                | departamento |
     * |---------|------------------------|--------------|
     * | 1       | Ingeniería en Sistemas | Ingeniería   |
     *
     * DESPUÉS:
     * | user_id | carrera                | career_id | departamento | department_id |
     * |---------|------------------------|-----------|--------------|---------------|
     * | 1       | Ingeniería en Sistemas | 5         | Ingeniería   | 3             |
     */
    public function up(): void
    {
        Schema::table('academic_profiles', function (Blueprint $table) {
            // Nueva columna: career_id (FK a careers)
            // Nullable durante la transición
            $table->foreignId('career_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('careers')
                  ->onDelete('restrict'); // No permitir eliminar carrera si hay usuarios asignados

            // Nueva columna: department_id (FK a departments)
            // Nullable ya que es opcional
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('career_id')
                  ->constrained('departments')
                  ->onDelete('set null'); // Si se elimina el departamento, set null

            // Índices para búsquedas eficientes
            $table->index('career_id');
            $table->index('department_id');
        });

        // NOTA: Las columnas 'carrera' y 'departamento' (string) se mantienen
        // temporalmente para la migración de datos. Se eliminarán en una migración futura.
    }

    public function down(): void
    {
        Schema::table('academic_profiles', function (Blueprint $table) {
            // Eliminar foreign keys primero
            $table->dropForeign(['career_id']);
            $table->dropForeign(['department_id']);

            // Eliminar columnas
            $table->dropColumn(['career_id', 'department_id']);
        });
    }
};

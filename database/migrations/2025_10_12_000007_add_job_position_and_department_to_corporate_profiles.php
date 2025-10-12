<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Add Job Position and Department Foreign Keys to Corporate Profiles
     *
     * Moderniza la tabla `corporate_profiles` para usar relaciones con catálogos
     * centralizados en lugar de strings libres.
     *
     * ## Cambios
     *
     * ### Se AGREGAN (nuevas columnas con Foreign Keys):
     * - `job_position_id` → FK a tabla `job_positions`
     * - `department_id` → FK a tabla `departments`
     *
     * ### Se MANTIENEN TEMPORALMENTE (para migración de datos):
     * - `puesto` (string) - DEPRECADO, se eliminará en migración futura
     * - `departamento` (string) - DEPRECADO, se eliminará en migración futura
     * - `unidad_negocio` (string) - se mantiene tal cual
     * - `rol` (string) - se mantiene tal cual
     *
     * ## Beneficios
     *
     * 1. **Integridad Referencial**
     *    - Solo se pueden asignar puestos/departamentos que existen
     *    - Previene errores tipográficos
     *
     * 2. **Queries Eficientes**
     *    - Búsquedas indexadas
     *    - Joins directos con tablas relacionadas
     *
     * 3. **Consistencia con Cursos**
     *    - Mismos catálogos para perfiles Y filtrado de cursos
     *    - Un solo lugar para gestionar puestos/departamentos
     *
     * 4. **Estructura Organizacional Clara**
     *    - Departamento → Puestos → Usuarios
     *    - Facilita organigramas y reportes
     *
     * ## Plan de Migración de Datos
     *
     * 1. Ejecutar esta migración (agrega columnas nuevas)
     * 2. Script manual para convertir strings → IDs
     * 3. Verificar que todos los registros tengan job_position_id
     * 4. Migración futura eliminará columnas `puesto` y `departamento`
     *
     * ## Ejemplo de Conversión
     *
     * ANTES:
     * | user_id | puesto    | departamento | unidad_negocio |
     * |---------|-----------|--------------|----------------|
     * | 1       | Gerente   | Ventas       | Principal      |
     *
     * DESPUÉS:
     * | user_id | puesto  | job_position_id | departamento | department_id | unidad_negocio |
     * |---------|---------|-----------------|--------------|---------------|----------------|
     * | 1       | Gerente | 5               | Ventas       | 3             | Principal      |
     */
    public function up(): void
    {
        Schema::table('corporate_profiles', function (Blueprint $table) {
            // Nueva columna: job_position_id (FK a job_positions)
            // Nullable durante la transición
            $table->foreignId('job_position_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('job_positions')
                  ->onDelete('restrict'); // No permitir eliminar puesto si hay usuarios asignados

            // Nueva columna: department_id (FK a departments)
            // Nullable ya que es opcional
            $table->foreignId('department_id')
                  ->nullable()
                  ->after('job_position_id')
                  ->constrained('departments')
                  ->onDelete('set null'); // Si se elimina el departamento, set null

            // Índices para búsquedas eficientes
            $table->index('job_position_id');
            $table->index('department_id');
        });

        // NOTA: Las columnas 'puesto' y 'departamento' (string) se mantienen
        // temporalmente para la migración de datos. Se eliminarán en una migración futura.
    }

    public function down(): void
    {
        Schema::table('corporate_profiles', function (Blueprint $table) {
            // Eliminar foreign keys primero
            $table->dropForeign(['job_position_id']);
            $table->dropForeign(['department_id']);

            // Eliminar columnas
            $table->dropColumn(['job_position_id', 'department_id']);
        });
    }
};

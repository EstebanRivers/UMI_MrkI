<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * # Remove Old Target Columns from Courses
     *
     * Elimina las columnas antiguas de targeting que han sido reemplazadas
     * por las tablas pivote `course_career` y `course_job_position`.
     *
     * ## Columnas a Eliminar
     *
     * - `target_department_career` (string) → OBSOLETA
     * - `target_job_title` (string) → OBSOLETA
     *
     * ## Por Qué se Eliminan
     *
     * Estas columnas ya no son necesarias porque:
     * 1. Solo permitían asignar UN curso a UNA carrera/puesto
     * 2. Eran texto libre (sin validación, propenso a errores)
     * 3. No había integridad referencial
     *
     * ## Reemplazo
     *
     * Han sido reemplazadas por:
     * - Tabla `course_career` → muchos-a-muchos (curso ↔ carreras)
     * - Tabla `course_job_position` → muchos-a-muchos (curso ↔ puestos)
     *
     * ## IMPORTANTE: Ejecutar Esta Migración SOLO Después de:
     *
     * 1. ✅ Haber ejecutado migraciones anteriores (000001 a 000007)
     * 2. ✅ Haber migrado los datos de las columnas antiguas a las tablas pivote
     * 3. ✅ Haber actualizado el código (models, controllers, vistas)
     * 4. ✅ Haber probado que el filtrado de cursos funciona con las nuevas tablas
     *
     * ## Ejemplo de Migración de Datos (MANUAL)
     *
     * Antes de ejecutar esta migración, ejecutar script:
     *
     * ```php
     * // Para cada curso que tenga target_department_career:
     * $courses = Course::whereNotNull('target_department_career')->get();
     * foreach ($courses as $course) {
     *     $career = Career::where('name', $course->target_department_career)
     *                     ->where('institution_id', $course->institution_id)
     *                     ->first();
     *     if ($career) {
     *         $course->careers()->attach($career->id);
     *     }
     * }
     *
     * // Similar para target_job_title → job_positions
     * ```
     *
     * ## Rollback
     *
     * En caso de necesitar rollback, las columnas se restauran vacías.
     * Los datos originales NO se recuperan (asegurar backup antes de ejecutar).
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Eliminar columnas obsoletas
            $table->dropColumn([
                'target_department_career',
                'target_job_title',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Restaurar columnas (pero vacías, datos no recuperables)
            $table->string('target_department_career')
                  ->nullable()
                  ->after('institution_id')
                  ->comment('OBSOLETO - usar tabla course_career');

            $table->string('target_job_title')
                  ->nullable()
                  ->after('target_department_career')
                  ->comment('OBSOLETO - usar tabla course_job_position');
        });
    }
};

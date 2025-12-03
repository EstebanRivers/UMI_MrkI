<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Verificamos si la tabla existe (por seguridad)
        if (Schema::hasTable('academic_profiles')) {
            
            Schema::table('academic_profiles', function (Blueprint $table) {
                
                // 2. SOLUCIÓN: Verificamos si la columna 'matricula' NO existe antes de agregarla
                if (!Schema::hasColumn('academic_profiles', 'matricula')) {
                    $table->string('matricula', 20)->nullable()->unique()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('academic_profiles')) {
            Schema::table('academic_profiles', function (Blueprint $table) {
                // Verificamos si existe antes de borrarla para evitar errores al hacer rollback
                if (Schema::hasColumn('academic_profiles', 'matricula')) {
                    $table->dropColumn('matricula');
                }
            });
        }
    }
};
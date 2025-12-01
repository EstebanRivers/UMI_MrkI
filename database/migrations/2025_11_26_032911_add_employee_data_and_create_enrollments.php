<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Inscripciones (Historial) - Solo si no existe
        if (!Schema::hasTable('enrollments')) {
            Schema::create('enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('career_id')->constrained('careers'); // Carrera en ese momento
                $table->integer('semestre'); 
                $table->string('periodo')->nullable(); 
                
                // Rutas de Archivos
                $table->string('doc_acta_nacimiento')->nullable();
                $table->string('doc_certificado_prepa')->nullable();
                $table->string('doc_curp')->nullable();
                $table->string('doc_ine')->nullable();
                $table->string('doc_comprobante_domicilio')->nullable();
                
                $table->enum('status', ['Inscrito', 'Pendiente', 'Baja'])->default('Pendiente');
                $table->timestamps();
            });
        }

        // 2. Datos laborales en Usuarios - Solo si faltan las columnas
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments');
            }
            if (!Schema::hasColumn('users', 'workstation_id')) {
                $table->foreignId('workstation_id')->nullable()->constrained('workstations');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
        
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            if (Schema::hasColumn('users', 'workstation_id')) {
                $table->dropForeign(['workstation_id']);
                $table->dropColumn('workstation_id');
            }
        });
    }
};
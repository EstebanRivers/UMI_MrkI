<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('clave', 10)->unique()->nullable();
            $table->integer('creditos');
            $table->foreignId('career_id') // Laravel asume que la columna es UNSIGNED BIGINT
                  ->constrained('careers') 
                  ->onDelete('cascade'); // Opcional: si se borra la carrera, se borran las materias
            $table->string('descripcion');
            $table->string('type');
            $table->string('semestre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};

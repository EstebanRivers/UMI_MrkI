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
        //Tabla de carreras
        Schema::create('carrers', function (Blueprint $table) {
            $table->id();
            $table->string('official_id');
            $table->string('name');
            $table->string('description1');
            $table->string('description2');
            $table->string('description3');
            $table->integer('credits')->default(0);
            $table->timestamps();

            $table->unique(['official_id']);
        });
        //Tabla de Carrera - Cursos(Tabla Pivote)
        Schema::create('carrers-courses', function (Blueprint $table){
            $table->id();
            $table->foreignId('carrer_id')->constrained('carrers')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['course_id', 'carrer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrers');
    }
};

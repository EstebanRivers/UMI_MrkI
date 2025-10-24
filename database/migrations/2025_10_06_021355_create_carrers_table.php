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
            $table->string('official_id');//RVOE
            $table->string('name');//Nombre de Carrera
            $table->string('description1');//
            $table->string('description2');//Descripciones
            $table->string('description3');//
            $table->string('type');//Radiobox: Tipo de carrera (Escolar, sabatino)
            $table->integer('semesters')->default(1);//Select:numero de Semestre
            $table->integer('credits')->default(0);//Esta opcion, solo es para uso en otro formulario no se usara
            $table->timestamps();

            $table->unique(['official_id']);
        });
        //Tabla de Carrera - Cursos(Tabla Pivote)
        Schema::create('carrers-courses', function (Blueprint $table){
            $table->id();
            $table->foreignId('carrer_id')->constrained('carrers')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamps();


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

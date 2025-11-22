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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('official_id')->nullable();//RVOE
            $table->string('name');//Nombre de Carrera
            $table->string('description1')->nullable();//;//
            $table->string('description2')->nullable();//Descripciones
            $table->string('description3')->nullable();//
            $table->string('type')->nullable();//Radiobox: Tipo de carrera (Escolar, sabatino)
            $table->integer('semesters')->default(1);//Select:numero de Semestre
            $table->integer('credits')->default(0);//Esta opcion, solo es para uso en otro formulario no se usara
            $table->timestamps();
            $table->foreignId('institution_id')->constrained('institutions')->onDelete('cascade');


            $table->unique(['official_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};

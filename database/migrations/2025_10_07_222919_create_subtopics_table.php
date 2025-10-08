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
        Schema::create('subtopics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('topics')->onDelete('cascade'); 
            $table->string('title', 150); // El título del Subtema
            $table->text('description')->nullable(); // Una descripción
            $table->string('file_path')->nullable(); // Para el contenido (PDF/Video)
            $table->integer('order')->default(0); // Para ordenar los subtemas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtopics');
    }
};

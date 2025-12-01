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
        // Esto crea la tabla 'periods' con todas las columnas correctas
        Schema::create('periods', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->date('start_date');
        $table->date('end_date');
        $table->date('re_enrollment_deadline')->nullable(); // 
        $table->boolean('is_active')->default(false);
        $table->foreignId('institution_id')->constrained('institutions')->onDelete('cascade');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
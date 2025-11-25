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
        // Este archivo solo debe crear la tabla 'billings'
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->string('factura_uid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('concepto');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento');
            $table->string('archivo_path')->nullable();
            $table->enum('status', ['Pendiente', 'Pagada'])->default('Pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
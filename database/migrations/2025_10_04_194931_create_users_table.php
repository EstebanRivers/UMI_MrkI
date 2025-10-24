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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');

            $table->string('email')->unique();
            $table->string('password');
            $table->string('RFC')->unique();
            $table->string('telefono')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('edad')->nullable();

            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');;
            $table->foreignId('workstation_id')->nullable()->constrained('workstations')->onDelete('set null');;

            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

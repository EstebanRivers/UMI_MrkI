<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Si existe, la borramos para evitar errores al refrescar
        Schema::dropIfExists('billing_concepts');

        Schema::create('billing_concepts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institution_id'); 
            $table->string('concept');                   
            $table->decimal('amount', 10, 2);            
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_concepts');
    }
};
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
    Schema::table('academic_profiles', function (Blueprint $table) {
        $table->string('documentoSEP_path')->nullable()->after('matricula');
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('academic_profiles', function (Blueprint $table) {
        $table->dropColumn('documento_path');
    });
}

};

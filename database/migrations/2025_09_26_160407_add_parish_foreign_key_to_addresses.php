<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migración se ejecuta DESPUÉS de crear la tabla parishes
     * para agregar la FK de parish_id en addresses
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreign('parish_id')->references('id')->on('parishes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['parish_id']);
        });
    }
};


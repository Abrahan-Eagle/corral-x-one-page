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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('ranch_id')->constrained('states')->onDelete('set null');
            $table->index('state_id'); // Índice para optimizar búsquedas por estado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropIndex(['state_id']);
            $table->dropColumn('state_id');
        });
    }
};

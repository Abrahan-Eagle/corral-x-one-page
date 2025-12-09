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
            $table->enum('feeding_type', [
                'pastura_natural',
                'pasto_corte',
                'concentrado',
                'mixto',
                'otro'
            ])->nullable()->after('feeding_info')->comment('Tipo de alimento del ganado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('feeding_type');
        });
    }
};

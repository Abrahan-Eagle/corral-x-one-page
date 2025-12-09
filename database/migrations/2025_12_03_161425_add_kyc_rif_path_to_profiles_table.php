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
        Schema::table('profiles', function (Blueprint $table) {
            // Agregar campo para RIF
            $table->string('kyc_rif_path')
                ->nullable()
                ->after('kyc_doc_front_path');
            
            // Eliminar campo de dorso (ya no se usa)
            $table->dropColumn('kyc_doc_back_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Restaurar campo de dorso
            $table->string('kyc_doc_back_path')
                ->nullable()
                ->after('kyc_doc_front_path');
            
            // Eliminar campo RIF
            $table->dropColumn('kyc_rif_path');
        });
    }
};

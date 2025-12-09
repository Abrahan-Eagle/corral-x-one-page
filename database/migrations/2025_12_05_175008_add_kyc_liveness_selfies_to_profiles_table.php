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
            // Campo JSON para guardar las rutas de las 5 selfies del liveness detection
            // Estructura: ["kyc/{user_id}/liveness_1.jpg", "kyc/{user_id}/liveness_2.jpg", ...]
            $table->json('kyc_liveness_selfies_paths')
                ->nullable()
                ->after('kyc_selfie_with_doc_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('kyc_liveness_selfies_paths');
        });
    }
};

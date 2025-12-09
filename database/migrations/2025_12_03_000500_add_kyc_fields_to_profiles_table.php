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
            // Estado general de KYC
            $table->string('kyc_status')
                ->default('no_verified')
                ->after('is_verified');

            $table->text('kyc_rejection_reason')
                ->nullable()
                ->after('kyc_status');

            // Metadatos mínimos del documento
            $table->string('kyc_document_type')
                ->nullable()
                ->after('kyc_rejection_reason');

            $table->string('kyc_document_number', 50)
                ->nullable()
                ->after('kyc_document_type');

            $table->string('kyc_country_code', 5)
                ->nullable()
                ->after('kyc_document_number');

            // Paths de imágenes KYC
            $table->string('kyc_doc_front_path')
                ->nullable()
                ->after('kyc_country_code');

            $table->string('kyc_doc_back_path')
                ->nullable()
                ->after('kyc_doc_front_path');

            $table->string('kyc_selfie_path')
                ->nullable()
                ->after('kyc_doc_back_path');

            $table->string('kyc_selfie_with_doc_path')
                ->nullable()
                ->after('kyc_selfie_path');

            // Marca de tiempo de verificación automática
            $table->timestamp('kyc_verified_at')
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
            $table->dropColumn([
                'kyc_status',
                'kyc_rejection_reason',
                'kyc_document_type',
                'kyc_document_number',
                'kyc_country_code',
                'kyc_doc_front_path',
                'kyc_doc_back_path',
                'kyc_selfie_path',
                'kyc_selfie_with_doc_path',
                'kyc_verified_at',
            ]);
        });
    }
};



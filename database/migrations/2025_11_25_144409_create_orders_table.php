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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Relaciones principales
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('seller_profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ranch_id')->constrained()->cascadeOnDelete();

            // Información del pedido
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->string('currency', 3)->default('USD');

            // Estado del pedido
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'delivered',
                'completed',
                'cancelled',
            ])->default('pending');

            // Información de delivery / transporte
            $table->enum('delivery_method', [
                'buyer_transport',
                'seller_transport',
                'external_delivery',
                'corralx_delivery',
            ])->default('buyer_transport');
            $table->enum('pickup_location', ['ranch', 'other'])->default('ranch');
            $table->text('pickup_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->decimal('delivery_cost', 12, 2)->default(0);
            $table->string('delivery_cost_currency', 3)->default('USD');
            $table->string('delivery_provider')->nullable();
            $table->string('delivery_tracking_number')->nullable();

            // Fechas de logística
            $table->date('expected_pickup_date')->nullable();
            $table->date('actual_pickup_date')->nullable();

            // Notas
            $table->text('buyer_notes')->nullable();
            $table->text('seller_notes')->nullable();

            // Comprobante
            $table->string('receipt_number')->nullable()->unique();
            $table->json('receipt_data')->nullable();

            // Timestamps de estados
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Índices auxiliares
            $table->index(['buyer_profile_id', 'status']);
            $table->index(['seller_profile_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

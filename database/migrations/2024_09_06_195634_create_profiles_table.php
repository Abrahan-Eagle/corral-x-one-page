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
        Schema::create('profiles', function (Blueprint $table) {
           $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');


            $table->string('firstName');
            $table->string('middleName')->nullable();
            $table->string('lastName');
            $table->string('secondLastName')->nullable();
            $table->string('photo_users')->nullable();
            $table->text('bio')->nullable()->comment('Biografía del usuario'); // CONSOLIDADO: add_bio_to_profiles
            $table->date('date_of_birth')->nullable();
            $table->enum('maritalStatus', ['married', 'divorced', 'single', 'widowed'])->nullable(); // CONSOLIDADO: make_nullable
            $table->enum('sex', ['F', 'M', 'O'])->nullable(); // CONSOLIDADO: make_nullable
            $table->enum('status', ['completeData', 'incompleteData', 'notverified'])->default('notverified');
            $table->string('ranch')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            
            // Campos marketplace trasladados desde users
            $table->boolean('is_verified')->default(false);
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->boolean('has_unread_messages')->default(false);
            
            // Campos específicos del marketplace
            $table->enum('user_type', ['buyer', 'seller', 'both'])->default('both')->comment('Tipo de usuario en el marketplace');
            $table->boolean('is_both_verified')->default(false)->comment('Indica si el perfil es un both verificado');
            // $table->integer('years_of_experience')->nullable()->comment('Años de experiencia en el sector ganadero');
            $table->boolean('accepts_calls')->default(true)->comment('Acepta llamadas telefónicas'); // TODO: Verificar si es necesario
            $table->boolean('accepts_whatsapp')->default(true)->comment('Acepta contacto por WhatsApp'); // TODO: Verificar si es necesario
            $table->boolean('accepts_emails')->default(true)->comment('Acepta contacto por correo electrónico'); // TODO: Verificar si es necesario
            // $table->json('notification_settings')->nullable()->comment('Configuración de notificaciones del usuario (JSON)');
            // $table->text('bio')->nullable()->comment('Biografía del usuario');
            

            $table->string('whatsapp_number')->nullable();
            // $table->json('preferred_communication_hours')->nullable(); // REMOVIDO: Ahora se maneja en ranches.contact_hours
            $table->boolean('is_premium_seller')->default(false);
            $table->date('premium_expires_at')->nullable();

            $table->string('ci_number', 20)->unique();
            
            // Firebase FCM device token para notificaciones push
            $table->string('fcm_device_token')->nullable()->comment('Token de dispositivo para Firebase Cloud Messaging');
            
            $table->timestamps();
            
            // Índices adicionales para marketplace
            $table->index('user_type');
            $table->index('is_both_verified');
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};


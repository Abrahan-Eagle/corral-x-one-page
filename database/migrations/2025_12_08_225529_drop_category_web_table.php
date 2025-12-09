<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina la tabla category_web que era usada para categorías del blog/proyectos (ya no se usa)
     */
    public function up(): void
    {
        Schema::dropIfExists('category_web');
    }

    /**
     * Reverse the migrations.
     * Recrea la tabla category_web (solo para rollback, no se usará)
     */
    public function down(): void
    {
        Schema::create('category_web', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 128);
            $table->string('slug', 128)->unique();
            $table->mediumText('content')->nullable();
            $table->enum('level', ['blog', 'project', 'event'])->default('blog');
            $table->timestamps();
        });
    }
};

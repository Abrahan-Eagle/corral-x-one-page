<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('id')
                ->constrained('orders')
                ->nullOnDelete();

            $table->index(['order_id', 'profile_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('reviews')) {
            return;
        }

        if (! Schema::hasColumn('reviews', 'order_id')) {
            return;
        }

        $connection = Schema::getConnection()->getName();
        $database = DB::connection($connection)->getDatabaseName();

        $constraint = DB::connection($connection)->selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1',
            [$database, 'reviews', 'order_id']
        );

        if ($constraint) {
            DB::statement("ALTER TABLE `reviews` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        $indexExists = DB::connection($connection)->selectOne(
            "SHOW INDEX FROM `reviews` WHERE Key_name = 'reviews_order_id_profile_id_index'"
        );

        if ($indexExists) {
            DB::statement('DROP INDEX `reviews_order_id_profile_id_index` ON `reviews`');
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};

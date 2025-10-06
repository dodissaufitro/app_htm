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
        Schema::table('users', function (Blueprint $table) {
            $table->json('lokasi_hunian')->nullable()->after('urutan')->comment('Lokasi hunian yang ditangani developer (JSON array of DataHunian IDs)');

            // Add simple index for urutan field to help with developer queries
            $table->index('urutan', 'idx_users_urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_urutan');
            $table->dropColumn('lokasi_hunian');
        });
    }
};

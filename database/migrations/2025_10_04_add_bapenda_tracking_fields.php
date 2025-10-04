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
        Schema::table('data_pemohon', function (Blueprint $table) {
            // Cek apakah kolom sudah ada sebelum menambahkan
            if (!Schema::hasColumn('data_pemohon', 'bapenda_updated_at')) {
                $table->timestamp('bapenda_updated_at')->nullable()->after('aset_hunian');
            }

            // Index for better performance
            if (!Schema::hasIndex('data_pemohon', 'idx_nik_bapenda_updated')) {
                $table->index(['nik', 'bapenda_updated_at'], 'idx_nik_bapenda_updated');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_pemohon', function (Blueprint $table) {
            if (Schema::hasIndex('data_pemohon', 'idx_nik_bapenda_updated')) {
                $table->dropIndex('idx_nik_bapenda_updated');
            }
            if (Schema::hasColumn('data_pemohon', 'bapenda_updated_at')) {
                $table->dropColumn('bapenda_updated_at');
            }
        });
    }
};

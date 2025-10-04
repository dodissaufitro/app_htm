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
            // Make bapenda fields nullable
            $table->text('bapenda_pasangan')->nullable()->change();
            $table->text('bapenda_pasangan_pbb')->nullable()->change();

            // Make status_permohonan nullable with default
            $table->string('status_permohonan', 255)->nullable()->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_pemohon', function (Blueprint $table) {
            // Revert changes
            $table->text('bapenda_pasangan')->nullable(false)->change();
            $table->text('bapenda_pasangan_pbb')->nullable(false)->change();
            $table->string('status_permohonan', 255)->nullable(false)->change();
        });
    }
};

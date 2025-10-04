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
        Schema::table('daftar_bank', function (Blueprint $table) {
            $table->string('nama_bank', 100)->change(); // Extend from 32 to 100 characters
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daftar_bank', function (Blueprint $table) {
            $table->string('nama_bank', 32)->change(); // Revert back to 32 characters
        });
    }
};

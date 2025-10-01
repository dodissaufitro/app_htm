<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_bank', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('nama_bank', 32);
            $table->string('kode_bank', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daftar_bank');
    }
};

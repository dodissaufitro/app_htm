<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status', function (Blueprint $table) {
            $table->string('kode', 255)->primary();
            $table->integer('urut');
            $table->integer('kode_urut');
            $table->string('nama_status', 32);
            $table->text('keterangan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status');
    }
};

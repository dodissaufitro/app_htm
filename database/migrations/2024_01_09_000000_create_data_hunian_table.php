<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_hunian', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pemukiman', 255)->nullable();
            $table->text('alamat_pemukiman')->nullable();
            $table->char('kode_lokasi', 50)->default('0');
            $table->char('kode_hunian', 50)->nullable();
            $table->char('tipe_hunian', 50)->nullable();
            $table->char('ukuran', 50)->nullable();
            $table->integer('harga')->nullable();
            $table->integer('tahun5')->nullable();
            $table->integer('tahun10')->nullable();
            $table->integer('tahun15')->nullable();
            $table->integer('tahun20')->nullable();
            $table->char('deleted', 2)->nullable();
            $table->datetime('create_date')->useCurrent();
            $table->datetime('update_date')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_hunian');
    }
};

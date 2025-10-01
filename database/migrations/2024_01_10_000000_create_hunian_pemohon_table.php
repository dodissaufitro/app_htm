<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hunian_pemohon', function (Blueprint $table) {
            $table->id();
            $table->integer('pemohon_id');
            $table->string('username', 100)->unique();
            $table->char('tipe_program', 50)->nullable();
            $table->char('pernah_ikut', 50)->nullable();
            $table->char('tipe_rumah', 50)->nullable();
            $table->integer('harga_rumah')->nullable();
            $table->char('lokasi_rumah', 50)->nullable();
            $table->char('alasan1', 50)->nullable();
            $table->char('alasan2', 50)->nullable();
            $table->char('alasan3', 50)->nullable();
            $table->char('alasan4', 50)->nullable();
            $table->char('alasan5', 50)->nullable();
            $table->char('alasan6', 50)->nullable();
            $table->datetime('created_at')->nullable()->useCurrent();
            $table->integer('created_by')->nullable();
            $table->datetime('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
            $table->integer('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hunian_pemohon');
    }
};

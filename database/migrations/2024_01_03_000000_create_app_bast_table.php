<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_bast', function (Blueprint $table) {
            $table->id();
            $table->integer('pemohon_id');
            $table->string('no_bast', 255);
            $table->date('tgl_bast');
            $table->string('file_bast', 255)->nullable();
            $table->string('foto_bast', 255)->nullable();
            $table->string('foto_serah_kunci', 255)->nullable();
            $table->enum('menerima_hasil_kerja', ['Y', 'N']);
            $table->text('komplain')->nullable();
            $table->char('sesuai', 34)->nullable();
            $table->enum('keputusan', ['disetujui', 'ditolak', 'ditunda'])->nullable();
            $table->text('catatan')->nullable();
            $table->enum('verifikasi_pemohon', ['sudah', 'belum'])->nullable()->default('belum');
            $table->enum('dihuni', ['sudah', 'belum'])->nullable()->default('belum');
            $table->datetime('created_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_bast');
    }
};

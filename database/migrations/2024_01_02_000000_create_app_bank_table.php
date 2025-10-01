<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_bank', function (Blueprint $table) {
            $table->id();
            $table->integer('pemohon_id');
            $table->tinyInteger('data_lengkap')->nullable();
            $table->tinyInteger('data_pendukung_valid')->nullable();
            $table->tinyInteger('bi_checking')->nullable();
            $table->tinyInteger('info_biaya')->nullable();
            $table->enum('masih_minat', ['Y', 'N'])->nullable()->default('Y');
            $table->enum('keputusan', ['disetujui', 'ditolak', 'ditunda']);
            $table->string('alasan_tolak', 255)->nullable();
            $table->text('catatan')->nullable();
            $table->string('dok_pm1', 255)->nullable();
            $table->string('dok_slip_gaji', 255)->nullable();
            $table->datetime('created_at')->nullable();
            $table->integer('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_bank');
    }
};

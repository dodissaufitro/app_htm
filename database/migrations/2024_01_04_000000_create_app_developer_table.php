<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_developer', function (Blueprint $table) {
            $table->id();
            $table->integer('pemohon_id');
            $table->enum('hadir', ['Y', 'N']);
            $table->enum('idle', ['Y', 'N'])->nullable();
            $table->enum('masih_minat', ['Y', 'N']);
            $table->enum('perubahan_unit', ['Y', 'N'])->nullable();
            $table->string('history_visit', 255)->nullable();
            $table->string('foto_kehadiran', 255)->nullable();
            $table->enum('keputusan', ['disetujui', 'ditolak', 'ditunda'])->default('ditunda');
            $table->text('catatan')->nullable();
            $table->datetime('created_at')->nullable();
            $table->integer('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_developer');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_akad', function (Blueprint $table) {
            $table->id();
            $table->integer('pemohon_id');
            $table->enum('masih_minat', ['Y', 'N'])->nullable();
            $table->date('tanggal_akad');
            $table->string('saksi', 255)->nullable();
            $table->string('notaris', 255);
            $table->decimal('dana_akad', 28, 2);
            $table->string('no_spk', 128);
            $table->string('foto_spk_hal_depan', 255)->nullable();
            $table->string('foto_spk_hal_belakang', 255)->nullable();
            $table->string('foto_akad', 255)->nullable();
            $table->enum('keputusan', ['disetujui', 'ditolak', 'ditunda'])->nullable();
            $table->text('catatan')->nullable();
            $table->datetime('created_at')->nullable();
            $table->integer('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_akad');
    }
};

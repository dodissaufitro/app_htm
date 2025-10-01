<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_penetapan', function (Blueprint $table) {
            $table->id();
            $table->integer('pemohon_id');
            $table->enum('masih_minat', ['Y', 'N']);
            $table->enum('perubahan_unit', ['Y', 'N'])->nullable()->default('N');
            $table->enum('keputusan', ['disetujui', 'ditolak', 'ditunda']);
            $table->text('catatan')->nullable();
            $table->datetime('created_at')->nullable();
            $table->integer('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_penetapan');
    }
};

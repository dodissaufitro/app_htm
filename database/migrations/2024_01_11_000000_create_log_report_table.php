<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_report', function (Blueprint $table) {
            $table->id();
            $table->integer('pemohon_id');
            $table->string('status_id', 255);
            $table->enum('keputusan', ['disetujui', 'ditolak', 'ditunda']);
            $table->text('catatan')->nullable();
            $table->boolean('api_sent')->default(0);
            $table->datetime('created_at')->nullable();
            $table->integer('created_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_report');
    }
};

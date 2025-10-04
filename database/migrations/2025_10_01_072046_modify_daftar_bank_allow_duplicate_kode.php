<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, backup existing data
        $existingData = DB::table('daftar_bank')->get();

        // Drop existing table
        Schema::dropIfExists('daftar_bank');

        // Recreate table with new structure
        Schema::create('daftar_bank', function (Blueprint $table) {
            $table->id(); // auto-increment primary key
            $table->string('nama_bank', 32);
            $table->string('kode_bank', 10)->nullable();
            $table->string('kode_bank_legacy', 32)->nullable(); // for old data migration
            $table->string('status', 50)->default('active');
            $table->timestamps();

            // Index for better performance
            $table->index(['kode_bank', 'status']);
        });

        // Restore data with new structure
        foreach ($existingData as $data) {
            DB::table('daftar_bank')->insert([
                'nama_bank' => $data->nama_bank,
                'kode_bank' => $data->kode_bank ?? substr($data->id, 0, 10),
                'kode_bank_legacy' => $data->id,
                'status' => 'active',
                'created_at' => $data->created_at ?? now(),
                'updated_at' => $data->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup current data
        $currentData = DB::table('daftar_bank')->get();

        // Drop new table
        Schema::dropIfExists('daftar_bank');

        // Recreate original table structure
        Schema::create('daftar_bank', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('nama_bank', 32);
            $table->string('kode_bank', 10)->nullable();
            $table->timestamps();
        });

        // Restore original data format
        foreach ($currentData as $data) {
            DB::table('daftar_bank')->insert([
                'id' => $data->kode_bank_legacy ?? $data->kode_bank,
                'nama_bank' => $data->nama_bank,
                'kode_bank' => $data->kode_bank,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
            ]);
        }
    }
};

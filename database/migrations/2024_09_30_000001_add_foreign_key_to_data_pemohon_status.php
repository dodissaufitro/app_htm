<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DataPemohon;
use App\Models\Status;

return new class extends Migration
{
    public function up(): void
    {
        // First, update existing data to use valid status codes
        $this->updateExistingData();

        // Then add foreign key constraint
        Schema::table('data_pemohon', function (Blueprint $table) {
            // Make sure the column allows null temporarily
            $table->string('status_permohonan', 255)->nullable()->change();

            // Add foreign key constraint
            $table->foreign('status_permohonan')
                ->references('kode')
                ->on('status')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('data_pemohon', function (Blueprint $table) {
            $table->dropForeign(['status_permohonan']);
        });
    }

    private function updateExistingData(): void
    {
        // Get all DataPemohon records
        $dataPemohon = DataPemohon::all();
        $updatedCount = 0;

        // Update each record with appropriate status
        foreach ($dataPemohon as $record) {
            // Set default status based on business logic
            if (empty($record->status_permohonan) || !Status::where('kode', $record->status_permohonan)->exists()) {
                // Set to DRAFT as default for existing records
                $record->update(['status_permohonan' => 'DRAFT']);
                $updatedCount++;
            }
        }

        echo "Updated {$updatedCount} DataPemohon records with valid status codes.\n";
    }
};

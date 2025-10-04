<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->boot();

use App\Models\DataPemohon;
use App\Models\AppVerifikator;

echo "=== Testing Duplicate Fix ===\n";

// Get current counts
$beforeCount = AppVerifikator::count();
echo "App Verifikator count before: {$beforeCount}\n";

// Get first data pemohon
$dp = DataPemohon::first();
echo "Current status: {$dp->status_permohonan}\n";

// Update status (this should trigger Observer)
$dp->update([
    'status_permohonan' => '3',
    'keterangan' => 'Test catatan ditolak via script'
]);

// Check counts after
$afterCount = AppVerifikator::count();
echo "App Verifikator count after: {$afterCount}\n";

// Check the difference
$difference = $afterCount - $beforeCount;
echo "Records created: {$difference}\n";

if ($difference === 0) {
    echo "✅ SUCCESS: No duplicate created (updated existing record)\n";
} elseif ($difference === 1) {
    echo "✅ SUCCESS: Only 1 new record created\n";
} else {
    echo "❌ PROBLEM: {$difference} records created - this indicates duplication!\n";
}

// Show final status
echo "Final status: {$dp->fresh()->status_permohonan}\n";

echo "=== Test Complete ===\n";

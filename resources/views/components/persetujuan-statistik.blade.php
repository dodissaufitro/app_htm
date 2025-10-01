<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h4 class="font-medium text-green-800 mb-2">Total Persetujuan</h4>
            <p class="text-3xl font-bold text-green-900">{{ $total }}</p>
            <p class="text-sm text-green-600 mt-1">Total keseluruhan</p>
        </div>

        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h4 class="font-medium text-blue-800 mb-2">Hari Ini</h4>
            <p class="text-3xl font-bold text-blue-900">{{ $today }}</p>
            <p class="text-sm text-blue-600 mt-1">Persetujuan hari ini</p>
        </div>

        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <h4 class="font-medium text-purple-800 mb-2">Bulan Ini</h4>
            <p class="text-3xl font-bold text-purple-900">{{ $thisMonth }}</p>
            <p class="text-sm text-purple-600 mt-1">Persetujuan bulan ini</p>
        </div>
    </div>

    <div class="text-xs text-gray-500 text-center mt-4">
        * Data persetujuan dengan status khusus
    </div>
</div>

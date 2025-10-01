<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h4 class="font-medium text-blue-800 mb-2">Total Verifikasi</h4>
            <p class="text-3xl font-bold text-blue-900">{{ $total }}</p>
            <p class="text-sm text-blue-600 mt-1">Semua data verifikasi</p>
        </div>

        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h4 class="font-medium text-green-800 mb-2">Disetujui</h4>
            <p class="text-3xl font-bold text-green-900">{{ $approved }}</p>
            <p class="text-sm text-green-600 mt-1">Verifikasi disetujui</p>
        </div>

        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <h4 class="font-medium text-red-800 mb-2">Ditolak</h4>
            <p class="text-3xl font-bold text-red-900">{{ $rejected }}</p>
            <p class="text-sm text-red-600 mt-1">Verifikasi ditolak</p>
        </div>

        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <h4 class="font-medium text-yellow-800 mb-2">Menunggu</h4>
            <p class="text-3xl font-bold text-yellow-900">{{ $pending }}</p>
            <p class="text-sm text-yellow-600 mt-1">Menunggu verifikasi</p>
        </div>
    </div>

    <div class="text-xs text-gray-500 text-center mt-4">
        * Statistik verifikasi diperbarui secara real-time
    </div>
</div>

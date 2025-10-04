<div class="space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h4 class="font-medium text-blue-800 mb-2">Total Pemohon</h4>
            <p class="text-2xl font-bold text-blue-900">{{ $total }}</p>
            <p class="text-sm text-blue-600 mt-1">Keseluruhan</p>
        </div>

        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <h4 class="font-medium text-yellow-800 mb-2">Ditunda</h4>
            <p class="text-2xl font-bold text-yellow-900">{{ $ditunda }}</p>
            <p class="text-sm text-yellow-600 mt-1">Menunggu keputusan</p>
        </div>

        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h4 class="font-medium text-green-800 mb-2">Disetujui</h4>
            <p class="text-2xl font-bold text-green-900">{{ $disetujui }}</p>
            <p class="text-sm text-green-600 mt-1">Telah disetujui</p>
        </div>

        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
            <h4 class="font-medium text-red-800 mb-2">Ditolak</h4>
            <p class="text-2xl font-bold text-red-900">{{ $ditolak }}</p>
            <p class="text-sm text-red-600 mt-1">Permohonan ditolak</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-200">
            <h4 class="font-medium text-indigo-800 mb-2">Pendaftar Hari Ini</h4>
            <p class="text-2xl font-bold text-indigo-900">{{ $today }}</p>
            <p class="text-sm text-indigo-600 mt-1">Registrasi hari ini</p>
        </div>

        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
            <h4 class="font-medium text-purple-800 mb-2">Pendaftar Bulan Ini</h4>
            <p class="text-2xl font-bold text-purple-900">{{ $thisMonth }}</p>
            <p class="text-sm text-purple-600 mt-1">Registrasi bulan ini</p>
        </div>
    </div>

    <div class="text-xs text-gray-500 text-center mt-4">
        * Statistik persetujuan berdasarkan status: 1=Ditunda, 2=Disetujui, 3=Ditolak
    </div>
</div>

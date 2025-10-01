<div class="space-y-4">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-semibold text-lg mb-2">{{ $nama_pemukiman }}</h3>
        <p class="text-sm text-gray-600">Tipe: {{ $tipe_hunian }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h4 class="font-medium text-blue-800 mb-2">Harga Awal</h4>
            <p class="text-2xl font-bold text-blue-900">
                {{ $harga_awal ? 'Rp ' . number_format($harga_awal, 0, ',', '.') : 'Tidak tersedia' }}
            </p>
        </div>

        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h4 class="font-medium text-green-800 mb-2">Proyeksi 5 Tahun</h4>
            <p class="text-2xl font-bold text-green-900">
                {{ $tahun5 ? 'Rp ' . number_format($tahun5, 0, ',', '.') : 'Tidak tersedia' }}
            </p>
            @if ($harga_awal && $tahun5)
                <p class="text-sm text-green-600 mt-1">
                    Kenaikan: {{ number_format((($tahun5 - $harga_awal) / $harga_awal) * 100, 1) }}%
                </p>
            @endif
        </div>

        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <h4 class="font-medium text-yellow-800 mb-2">Proyeksi 10 Tahun</h4>
            <p class="text-2xl font-bold text-yellow-900">
                {{ $tahun10 ? 'Rp ' . number_format($tahun10, 0, ',', '.') : 'Tidak tersedia' }}
            </p>
            @if ($harga_awal && $tahun10)
                <p class="text-sm text-yellow-600 mt-1">
                    Kenaikan: {{ number_format((($tahun10 - $harga_awal) / $harga_awal) * 100, 1) }}%
                </p>
            @endif
        </div>

        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
            <h4 class="font-medium text-orange-800 mb-2">Proyeksi 15 Tahun</h4>
            <p class="text-2xl font-bold text-orange-900">
                {{ $tahun15 ? 'Rp ' . number_format($tahun15, 0, ',', '.') : 'Tidak tersedia' }}
            </p>
            @if ($harga_awal && $tahun15)
                <p class="text-sm text-orange-600 mt-1">
                    Kenaikan: {{ number_format((($tahun15 - $harga_awal) / $harga_awal) * 100, 1) }}%
                </p>
            @endif
        </div>

        <div class="bg-red-50 p-4 rounded-lg border border-red-200 md:col-span-2">
            <h4 class="font-medium text-red-800 mb-2">Proyeksi 20 Tahun</h4>
            <p class="text-3xl font-bold text-red-900">
                {{ $tahun20 ? 'Rp ' . number_format($tahun20, 0, ',', '.') : 'Tidak tersedia' }}
            </p>
            @if ($harga_awal && $tahun20)
                <p class="text-sm text-red-600 mt-1">
                    Kenaikan: {{ number_format((($tahun20 - $harga_awal) / $harga_awal) * 100, 1) }}%
                </p>
            @endif
        </div>
    </div>

    <div class="text-xs text-gray-500 text-center mt-4">
        * Proyeksi harga berdasarkan perhitungan internal dan dapat berubah sewaktu-waktu
    </div>
</div>

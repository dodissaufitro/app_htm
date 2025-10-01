<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($stats as $status_name => $count)
            @php
                // Find status by name to get urut for color mapping
                $status = \App\Models\Status::where('nama_status', $status_name)->first();
                $urut = $status?->urut ?? 0;

                $colors = [
                    0 => 'yellow',
                    1 => 'cyan',
                    2 => 'amber',
                    3 => 'fuchsia',
                    4 => 'sky',
                    5 => 'green',
                    6 => 'rose',
                    7 => 'orange',
                    8 => 'pink',
                    9 => 'lime',
                    10 => 'teal',
                    11 => 'violet',
                    12 => 'blue',
                    13 => 'green',
                    14 => 'red',
                    15 => 'purple',
                    16 => 'indigo',
                    17 => 'yellow',
                    18 => 'cyan',
                    19 => 'amber',
                    20 => 'fuchsia',
                ];

                $color = $colors[$urut] ?? 'gray';
            @endphp
            <div class="bg-{{ $color }}-50 p-4 rounded-lg border border-{{ $color }}-200">
                <h4 class="font-medium text-{{ $color }}-800 mb-2">
                    {{ $status_name ?? 'Tidak ada status' }}
                </h4>
                <p class="text-3xl font-bold text-{{ $color }}-900">
                    {{ $count }}
                </p>
                <p class="text-sm text-{{ $color }}-600 mt-1">
                    {{ $count == 1 ? 'Pemohon' : 'Pemohon' }}
                </p>
                @if ($status?->keterangan)
                    <p class="text-xs text-{{ $color }}-600 mt-2">
                        {{ Str::limit($status->keterangan, 50) }}
                    </p>
                @endif
            </div>
        @endforeach

        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
            <h4 class="font-medium text-slate-800 mb-2">Total</h4>
            <p class="text-3xl font-bold text-slate-900">
                {{ $stats->sum() }}
            </p>
            <p class="text-sm text-slate-600 mt-1">Total semua data</p>
        </div>
    </div>

    <div class="text-xs text-gray-500 text-center mt-4">
        * Setiap status memiliki warna yang berbeda berdasarkan urutan dalam workflow
    </div>
</div>
@endphp

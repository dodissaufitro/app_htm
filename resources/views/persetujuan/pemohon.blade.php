@extends('layouts.app')

@section('title', 'Persetujuan Pemohon')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Detail Pemohon - {{ $dataPemohon->nama }}</h4>
                        <div class="btn-group float-right">
                            <button type="button" class="btn btn-primary" onclick="updateBapenda()">
                                <i class="fas fa-sync"></i> Update Data Bapenda
                            </button>
                            <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="showUpdateByNikModal()">
                                    <i class="fas fa-id-card"></i> Update berdasarkan NIK
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="updateBapenda()">
                                    <i class="fas fa-sync"></i> Update data pemohon ini
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Data Pemohon -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informasi Pemohon</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>ID Pendaftaran</strong></td>
                                        <td>{{ $dataPemohon->id_pendaftaran }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>NIK</strong></td>
                                        <td>{{ $dataPemohon->nik }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nama</strong></td>
                                        <td>{{ $dataPemohon->nama }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>No. HP</strong></td>
                                        <td>{{ $dataPemohon->no_hp }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status Permohonan</strong></td>
                                        <td>
                                            <span class="badge badge-info">{{ $dataPemohon->status_permohonan }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Status Persetujuan</h5>

                                @if (Auth::user() && Auth::user()->urutan === 1)
                                    <form method="POST" action="{{ route('persetujuan.update-status') }}">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $dataPemohon->id }}">

                                        <div class="form-group">
                                            <label for="status_permohonan">Status Permohonan</label>
                                            <select name="status_permohonan" id="status_permohonan" class="form-control"
                                                required>
                                                <option value="">Pilih Status</option>
                                                <option value="1"
                                                    {{ $dataPemohon->status_permohonan == '1' ? 'selected' : '' }}>Ditunda
                                                </option>
                                                <option value="2"
                                                    {{ $dataPemohon->status_permohonan == '2' ? 'selected' : '' }}>
                                                    Disetujui
                                                </option>
                                                <option value="3"
                                                    {{ $dataPemohon->status_permohonan == '3' ? 'selected' : '' }}>Ditolak
                                                </option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="keterangan">Keterangan</label>
                                            <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Catatan persetujuan...">{{ old('keterangan') }}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Update Status
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Akses Terbatas:</strong> Hanya user dengan urutan 1 (Verifikator Awal) yang
                                        dapat mengubah status permohonan.
                                        <br>
                                        <small class="text-muted">
                                            Urutan Anda: {{ Auth::user()->urutan ?? 'Tidak dalam workflow' }}
                                            @if (Auth::user() && Auth::user()->urutan > 1)
                                                ({{ ['', 'Verifikator Awal', 'Developer', 'Bank Analisis', 'Supervisor', 'Manager'][Auth::user()->urutan] ?? 'Custom' }})
                                            @endif
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label>Status Permohonan Saat Ini</label>
                                        <div class="form-control-plaintext">
                                            @switch($dataPemohon->status_permohonan)
                                                @case('1')
                                                    <span class="badge badge-warning">Ditunda</span>
                                                @break

                                                @case('2')
                                                    <span class="badge badge-success">Disetujui</span>
                                                @break

                                                @case('3')
                                                    <span class="badge badge-danger">Ditolak</span>
                                                @break

                                                @default
                                                    <span
                                                        class="badge badge-secondary">{{ $dataPemohon->status_permohonan }}</span>
                                            @endswitch
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Data Bapenda -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Data Bapenda</h5>

                                @if ($bapendaData)
                                    <div class="card">
                                        <div class="card-header">
                                            <h6>Data Kendaraan (PKB)</h6>
                                        </div>
                                        <div class="card-body">
                                            @if (count($bapendaData) > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Jenis Kendaraan</th>
                                                                <th>Merk</th>
                                                                <th>Tahun</th>
                                                                <th>No. Polisi</th>
                                                                <th>Pajak</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($bapendaData as $index => $kendaraan)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $kendaraan['jenis'] ?? '-' }}</td>
                                                                    <td>{{ $kendaraan['merk'] ?? '-' }}</td>
                                                                    <td>{{ $kendaraan['tahun'] ?? '-' }}</td>
                                                                    <td>{{ $kendaraan['no_polisi'] ?? '-' }}</td>
                                                                    <td>{{ isset($kendaraan['pajak']) ? 'Rp ' . number_format($kendaraan['pajak'], 0, ',', '.') : '-' }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted">Tidak ada data kendaraan.</p>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Data Bapenda belum tersedia. Klik tombol "Update Data Bapenda" untuk mengambil data
                                        dari API.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Data Aset Hunian -->
                        @if ($asetHunianData)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6>Data Aset Hunian (PBB)</h6>
                                        </div>
                                        <div class="card-body">
                                            @if (count($asetHunianData) > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Alamat</th>
                                                                <th>Luas Tanah</th>
                                                                <th>Luas Bangunan</th>
                                                                <th>NJOP</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($asetHunianData as $index => $hunian)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $hunian['alamat'] ?? '-' }}</td>
                                                                    <td>{{ $hunian['luas_tanah'] ?? '-' }} m²</td>
                                                                    <td>{{ $hunian['luas_bangunan'] ?? '-' }} m²</td>
                                                                    <td>{{ isset($hunian['njop']) ? 'Rp ' . number_format($hunian['njop'], 0, ',', '.') : '-' }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted">Tidak ada data aset hunian.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Data Bapenda Pasangan -->
                        @if ($dataPemohon->nik2 && ($bapendaPasanganData || $bapendaPasanganPbbData))
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h5>Data Bapenda Pasangan (NIK: {{ $dataPemohon->nik2 }})</h5>

                                    @if ($bapendaPasanganData)
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                <h6>Data Kendaraan Pasangan (PKB)</h6>
                                            </div>
                                            <div class="card-body">
                                                @if (count($bapendaPasanganData) > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Jenis Kendaraan</th>
                                                                    <th>Merk</th>
                                                                    <th>Tahun</th>
                                                                    <th>No. Polisi</th>
                                                                    <th>Pajak</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($bapendaPasanganData as $index => $kendaraan)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $kendaraan['jenis'] ?? '-' }}</td>
                                                                        <td>{{ $kendaraan['merk'] ?? '-' }}</td>
                                                                        <td>{{ $kendaraan['tahun'] ?? '-' }}</td>
                                                                        <td>{{ $kendaraan['no_polisi'] ?? '-' }}</td>
                                                                        <td>{{ isset($kendaraan['pajak']) ? 'Rp ' . number_format($kendaraan['pajak'], 0, ',', '.') : '-' }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <p class="text-muted">Tidak ada data kendaraan pasangan.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if ($bapendaPasanganPbbData)
                                        <div class="card">
                                            <div class="card-header">
                                                <h6>Data Aset Hunian Pasangan (PBB)</h6>
                                            </div>
                                            <div class="card-body">
                                                @if (count($bapendaPasanganPbbData) > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Alamat</th>
                                                                    <th>Luas Tanah</th>
                                                                    <th>Luas Bangunan</th>
                                                                    <th>NJOP</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($bapendaPasanganPbbData as $index => $hunian)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $hunian['alamat'] ?? '-' }}</td>
                                                                        <td>{{ $hunian['luas_tanah'] ?? '-' }} m²</td>
                                                                        <td>{{ $hunian['luas_bangunan'] ?? '-' }} m²</td>
                                                                        <td>{{ isset($hunian['njop']) ? 'Rp ' . number_format($hunian['njop'], 0, ',', '.') : '-' }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <p class="text-muted">Tidak ada data aset hunian pasangan.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($dataPemohon->nik2)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Data Bapenda pasangan (NIK: {{ $dataPemohon->nik2 }}) belum tersedia. Data akan
                                        diambil otomatis saat update data Bapenda.
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Raw Data (untuk debugging) -->
                        @if (config('app.debug') && ($bapendaData || $asetHunianData || $bapendaPasanganData || $bapendaPasanganPbbData))
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6>Raw Data (Debug)</h6>
                                        </div>
                                        <div class="card-body">
                                            @if ($bapendaData)
                                                <h6>Bapenda Data (Pemohon):</h6>
                                                <pre>{{ json_encode($bapendaData, JSON_PRETTY_PRINT) }}</pre>
                                            @endif

                                            @if ($asetHunianData)
                                                <h6>Aset Hunian Data (Pemohon):</h6>
                                                <pre>{{ json_encode($asetHunianData, JSON_PRETTY_PRINT) }}</pre>
                                            @endif

                                            @if ($bapendaPasanganData)
                                                <h6>Bapenda Data (Pasangan):</h6>
                                                <pre>{{ json_encode($bapendaPasanganData, JSON_PRETTY_PRINT) }}</pre>
                                            @endif

                                            @if ($bapendaPasanganPbbData)
                                                <h6>Bapenda PBB Data (Pasangan):</h6>
                                                <pre>{{ json_encode($bapendaPasanganPbbData, JSON_PRETTY_PRINT) }}</pre>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update Bapenda by NIK -->
    <div class="modal fade" id="updateByNikModal" tabindex="-1" role="dialog" aria-labelledby="updateByNikModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateByNikModalLabel">Update Data Bapenda berdasarkan NIK</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updateByNikForm" method="POST" action="{{ route('persetujuan.update-bapenda-by-nik') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nikInput">NIK (16 digit)</label>
                            <input type="text" class="form-control" id="nikInput" name="nik"
                                placeholder="Masukkan NIK 16 digit" maxlength="16" value="{{ $dataPemohon->nik }}"
                                required>
                            <small class="form-text text-muted">
                                Masukkan NIK 16 digit untuk mengambil data Bapenda dari API.
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong><br>
                            - Sistem akan mencari data pemohon berdasarkan NIK yang dimasukkan<br>
                            - Data Bapenda akan diambil dari API dan disimpan ke database<br>
                            - Jika ada pasangan (NIK2), data pasangan juga akan diambil
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="updateByNikBtn">
                            <i class="fas fa-sync"></i> Update Data Bapenda
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form tersembunyi untuk update Bapenda -->
    <form id="updateBapendaForm" method="POST" action="{{ route('persetujuan.update-bapenda') }}"
        style="display: none;">
        @csrf
        <input type="hidden" name="id" value="{{ $dataPemohon->id }}">
    </form>

@endsection

@push('scripts')
    <script>
        function updateBapenda() {
            if (confirm(
                    'Apakah Anda yakin ingin mengupdate data Bapenda? Proses ini akan mengambil data terbaru dari API Bapenda.'
                )) {
                // Show loading
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                btn.disabled = true;

                // Submit form
                document.getElementById('updateBapendaForm').submit();
            }
        }

        function showUpdateByNikModal() {
            $('#updateByNikModal').modal('show');
        }

        // Handle update by NIK form submission
        document.getElementById('updateByNikForm').addEventListener('submit', function(e) {
            const nikInput = document.getElementById('nikInput');
            const nik = nikInput.value.trim();

            // Validate NIK format
            if (nik.length !== 16 || !/^\d{16}$/.test(nik)) {
                e.preventDefault();
                alert('NIK harus berupa 16 digit angka!');
                nikInput.focus();
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('updateByNikBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;

            // Allow form to submit
            return true;
        });

        // Format NIK input to only accept numbers
        document.getElementById('nikInput').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 16) {
                this.value = this.value.slice(0, 16);
            }
        });

        // Auto dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@endpush

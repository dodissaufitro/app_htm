@extends('layouts.app')

@section('title', 'Update Data Bapenda berdasarkan NIK')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Update Data Bapenda berdasarkan NIK</h4>
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

                        <div class="row">
                            <div class="col-md-8 mx-auto">
                                <div class="card">
                                    <div class="card-header">
                                        <h6><i class="fas fa-search"></i> Pencarian dan Update Data Bapenda</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('persetujuan.update-bapenda-by-nik') }}">
                                            @csrf
                                            <div class="form-group">
                                                <label for="nik">NIK (16 digit)</label>
                                                <input type="text"
                                                    class="form-control @error('nik') is-invalid @enderror" id="nik"
                                                    name="nik" placeholder="Masukkan NIK 16 digit" maxlength="16"
                                                    value="{{ old('nik') }}" required>
                                                @error('nik')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                                <small class="form-text text-muted">
                                                    Masukkan NIK 16 digit untuk mencari dan mengupdate data Bapenda pemohon.
                                                </small>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                                                <i class="fas fa-sync"></i> Cari dan Update Data Bapenda
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6><i class="fas fa-info-circle"></i> Informasi</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-success"></i> Sistem akan mencari data pemohon
                                                berdasarkan NIK yang dimasukkan</li>
                                            <li><i class="fas fa-check text-success"></i> Data Bapenda akan diambil dari API
                                                dan disimpan ke database</li>
                                            <li><i class="fas fa-check text-success"></i> Jika pemohon memiliki pasangan
                                                (NIK2), data pasangan juga akan diambil</li>
                                            <li><i class="fas fa-check text-success"></i> Data yang diambil meliputi:
                                                <ul class="mt-2">
                                                    <li>- Data kendaraan (PKB)</li>
                                                    <li>- Data properti/hunian (PBB)</li>
                                                    <li>- Perhitungan jumlah kendaraan</li>
                                                </ul>
                                            </li>
                                            <li><i class="fas fa-check text-success"></i> Setelah berhasil update, Anda akan
                                                diarahkan ke halaman detail pemohon</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h6><i class="fas fa-history"></i> Riwayat Update Terbaru</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">
                                            <small>Fitur riwayat update akan ditambahkan pada versi selanjutnya.</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Format NIK input to only accept numbers
        document.getElementById('nik').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length > 16) {
                this.value = this.value.slice(0, 16);
            }
        });

        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const nikInput = document.getElementById('nik');
            const nik = nikInput.value.trim();

            // Validate NIK format
            if (nik.length !== 16 || !/^\d{16}$/.test(nik)) {
                e.preventDefault();
                alert('NIK harus berupa 16 digit angka!');
                nikInput.focus();
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitBtn.disabled = true;

            // Allow form to submit
            return true;
        });

        // Auto dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
@endpush

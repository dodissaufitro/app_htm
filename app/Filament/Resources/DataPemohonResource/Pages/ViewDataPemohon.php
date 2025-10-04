<?php

namespace App\Filament\Resources\DataPemohonResource\Pages;

use App\Filament\Resources\DataPemohonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Support\Enums\FontWeight;

class ViewDataPemohon extends ViewRecord
{
    protected static string $resource = DataPemohonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Data Verifikasi Section
                Section::make('📋 Data Verifikasi')
                    ->description('Informasi verifikasi dan status pemohon')
                    ->schema([
                        TextEntry::make('id_pendaftaran')
                            ->label('Nomor Pendaftaran')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->copyable()
                            ->columnSpan(1),
                        TextEntry::make('created_at')
                            ->label('Waktu Pendaftaran')
                            ->dateTime('d M Y, H:i:s')
                            ->color('gray')
                            ->columnSpan(1),
                        TextEntry::make('username')
                            ->label('Email')
                            ->copyable()
                            ->icon('heroicon-o-envelope')
                            ->columnSpan(1),
                        TextEntry::make('nama')
                            ->label('Nama Pemohon')
                            ->weight(FontWeight::Bold)
                            ->size('lg')
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('no_hp')
                            ->label('No. Telepon')
                            ->copyable()
                            ->icon('heroicon-o-phone')
                            ->columnSpan(1),
                        TextEntry::make('nik')
                            ->label('NIK')
                            ->copyable()
                            ->formatStateUsing(fn(string $state): string => chunk_split($state, 4, ' '))
                            ->columnSpan(1),
                        TextEntry::make('status.nama_status')
                            ->label('Status Permohonan')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Draft' => 'gray',
                                'Pemohon Baru' => 'warning',
                                'Disetujui' => 'success',
                                'Ditolak' => 'danger',
                                'Selesai' => 'success',
                                default => 'primary',
                            })
                            ->columnSpan(1),
                        TextEntry::make('bank.nama_bank')
                            ->label('Bank Pilihan')
                            ->badge()
                            ->color('info')
                            ->columnSpan(1),
                        TextEntry::make('gaji')
                            ->label('Penghasilan')
                            ->money('IDR')
                            ->color('success')
                            ->weight(FontWeight::Bold)
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Lampiran Dokumen Section
                Section::make('📄 Lampiran Dokumen')
                    ->description('Dokumen yang diperlukan untuk verifikasi')
                    ->schema([
                        TextEntry::make('E-KTP')
                            ->label('E-KTP')
                            ->default('Tersedia')
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                        TextEntry::make('NPWP')
                            ->label('NPWP')
                            ->formatStateUsing(function ($record) {
                                if (!empty($record->npwp)) {
                                    return $record->validasi_npwp ? 'Valid' : 'Tidak Valid';
                                }
                                return 'Tidak Ada';
                            })
                            ->badge()
                            ->color(function ($record) {
                                if (!empty($record->npwp)) {
                                    return $record->validasi_npwp ? 'success' : 'warning';
                                }
                                return 'danger';
                            })
                            ->columnSpan(1),
                        TextEntry::make('Kartu Keluarga')
                            ->label('Kartu Keluarga')
                            ->default('Tersedia')
                            ->badge()
                            ->color('success')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // NPWP Detail Section
                Section::make('🆔 Detail NPWP')
                    ->description('Informasi lengkap NPWP pemohon')
                    ->schema([
                        TextEntry::make('npwp')
                            ->label('Nomor NPWP')
                            ->copyable()
                            ->formatStateUsing(fn(?string $state): string => $state ? chunk_split($state, 2, '.') : 'Tidak Ada')
                            ->columnSpan(1),
                        TextEntry::make('nama_npwp')
                            ->label('Nama NPWP')
                            ->columnSpan(1),
                        IconEntry::make('validasi_npwp')
                            ->label('Status Validasi')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),
                        IconEntry::make('status_npwp')
                            ->label('Status Aktif')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->hidden(fn($record) => empty($record->npwp)),

                // Domisili dan Korespondensi Section
                Section::make('🏠 Domisili dan Korespondensi')
                    ->description('Alamat tempat tinggal dan korespondensi')
                    ->schema([
                        TextEntry::make('provinsi_dom')
                            ->label('Provinsi')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('kabupaten_dom')
                            ->label('Kabupaten/Kota')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('kecamatan_dom')
                            ->label('Kecamatan')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('kelurahan_dom')
                            ->label('Desa/Kelurahan')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('alamat_dom')
                            ->label('Alamat Lengkap')
                            ->placeholder('Tidak diisi')
                            ->columnSpanFull(),
                        TextEntry::make('sts_rumah')
                            ->label('Status Rumah')
                            ->formatStateUsing(fn(?string $state): string => match ($state) {
                                'milik_sendiri' => 'Milik Sendiri',
                                'sewa' => 'Sewa',
                                'kontrak' => 'Kontrak',
                                'tinggal_keluarga' => 'Tinggal dengan Keluarga',
                                default => $state ?? 'Orang Tua'
                            })
                            ->badge()
                            ->color(fn(?string $state): string => match ($state) {
                                'milik_sendiri' => 'success',
                                'sewa' => 'warning',
                                'kontrak' => 'info',
                                'tinggal_keluarga' => 'gray',
                                default => 'primary'
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Data Pasangan Section  
                Section::make('💑 Data Pasangan')
                    ->description('Informasi pasangan pemohon')
                    ->schema([
                        TextEntry::make('nik2')
                            ->label('NIK Pasangan')
                            ->copyable()
                            ->formatStateUsing(fn(?string $state): string => $state ? chunk_split($state, 4, ' ') : 'Tidak Ada')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('nama2')
                            ->label('Nama Pasangan')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('no_hp2')
                            ->label('No. HP Pasangan')
                            ->copyable()
                            ->icon('heroicon-o-phone')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('pendidikan2')
                            ->label('Pendidikan Pasangan')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('pekerjaan2')
                            ->label('Pekerjaan Pasangan')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('gaji2')
                            ->label('Gaji Pasangan')
                            ->money('IDR')
                            ->placeholder('IDR 0')
                            ->columnSpan(1),
                        IconEntry::make('is_couple_dki')
                            ->label('Pasangan Warga DKI')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Data Keuangan Section
                Section::make('💰 Data Keuangan')
                    ->description('Informasi keuangan dan kemampuan pembayaran')
                    ->schema([
                        TextEntry::make('count_of_vehicle1')
                            ->label('Jumlah Kendaraan Roda 2')
                            ->numeric()
                            ->placeholder('0')
                            ->columnSpan(1),
                        TextEntry::make('count_of_vehicle2')
                            ->label('Jumlah Kendaraan Roda 4')
                            ->numeric()
                            ->placeholder('0')
                            ->columnSpan(1),
                        TextEntry::make('kemampuan_bayar_cicilan')
                            ->label('Kemampuan Bayar Cicilan')
                            ->formatStateUsing(fn(?string $state): string => $state ?? 'Rp. 2.500.000 - Rp. 3.000.000')
                            ->color('success')
                            ->weight(FontWeight::Bold)
                            ->columnSpan(1),
                        IconEntry::make('kepemilikan_tabungan_bank')
                            ->label('Kepemilikan Tabungan Bank')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),
                        IconEntry::make('kepemilikan_tabungan_rumah')
                            ->label('Kepemilikan Tabungan Rumah')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),
                        TextEntry::make('pengeluaran_hunian')
                            ->label('Pengeluaran Hunian/Sewa/Kontrak')
                            ->money('IDR')
                            ->placeholder('IDR 1.000.000')
                            ->columnSpan(1),
                        TextEntry::make('pengeluaran_makanan')
                            ->label('Pengeluaran Makan/Minum')
                            ->money('IDR')
                            ->placeholder('IDR 1.000.000')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                // Data Hunian Section
                Section::make('🏢 Data Hunian yang Dipilih')
                    ->description('Informasi hunian yang akan dibeli')
                    ->schema([
                        TextEntry::make('lokasi_rumah')
                            ->label('Lokasi Pemilihan')
                            ->placeholder('Tower Swasana Nuansa Pondok Kelapa')
                            ->columnSpan(1),
                        TextEntry::make('nama_blok')
                            ->label('Nama Blok')
                            ->placeholder('Tidak diisi')
                            ->columnSpan(1),
                        TextEntry::make('tipe_rumah')
                            ->label('Tipe Rumah')
                            ->placeholder('32,9')
                            ->columnSpan(1),
                        TextEntry::make('harga_unit')
                            ->label('Harga Unit')
                            ->money('IDR')
                            ->weight(FontWeight::Bold)
                            ->color('success')
                            ->columnSpan(1),
                        IconEntry::make('is_have_booking_kpr_dpnol')
                            ->label('Punya Booking KPR DP Nol')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}

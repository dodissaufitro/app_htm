<?php

namespace App\Filament\Resources\PersetujuanResource\Pages;

use App\Filament\Resources\PersetujuanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Support\Enums\FontWeight;

class ViewPersetujuan extends ViewRecord
{
    protected static string $resource = PersetujuanResource::class;

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    protected function getViewData(): array
    {
        return [
            'record' => $this->getRecord(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Persetujuan')
                ->icon('heroicon-o-pencil-square')
                ->color('warning'),
            Actions\Action::make('approve')
                ->label('Setujui')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Persetujuan')
                ->modalDescription('Apakah Anda yakin ingin menyetujui permohonan ini?')
                ->form([
                    \Filament\Forms\Components\Textarea::make('catatan_verifikator')
                        ->label('Catatan Verifikator')
                        ->placeholder('Masukkan catatan untuk persetujuan ini...')
                        ->rows(3)
                        ->helperText('Catatan ini akan disimpan dalam sistem verifikasi.')
                ])
                ->action(fn($record, array $data) => $this->approveApplication($record, $data)),
            Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Penolakan')
                ->modalDescription('Apakah Anda yakin ingin menolak permohonan ini?')
                ->form([
                    \Filament\Forms\Components\Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->rows(3),
                    \Filament\Forms\Components\Textarea::make('catatan_verifikator')
                        ->label('Catatan Verifikator')
                        ->placeholder('Masukkan catatan tambahan...')
                        ->rows(3)
                        ->helperText('Catatan ini akan disimpan dalam sistem verifikasi.')
                ])
                ->action(fn($record, array $data) => $this->rejectApplication($record, $data)),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Wizard Steps')
                    ->contained(false)
                    ->persistTabInQueryString()
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'w-full'
                    ])
                    ->tabs([
                        Tabs\Tab::make('Data Pemohon')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                Section::make('Data Verifikasi')
                                    ->description('Informasi verifikasi dan status pemohon')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->schema([
                                        KeyValueEntry::make('data_verifikasi')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Nomor Pendaftaran' => $record->id_pendaftaran ?? '-',
                                                    'Waktu' => $record->created_at->format('M j, Y g:i:s A'),
                                                    'Lokasi Pemilihan' => $record->lokasi_rumah ?? 'Tower Samawa Nuansa Pondok Kelapa',
                                                    'Nama Pemohon' => $record->nama ?? '-',
                                                    'Tipe Rumah' => $record->tipe_rumah ?? '-',
                                                    'NIK' => $record->nik ?? '-',
                                                    'Nama Blok' => $record->nama_blok ?? '-',
                                                    'No. Telepon' => $record->no_hp ?? '-',
                                                    'Email' => $record->username ?? '-',
                                                    'Status NPWP' => $record->validasi_npwp ? 'Valid' : 'Tidak Valid',
                                                    'Status Kawin' => $this->getStatusKawin($record->status_kawin ?? 0),
                                                    'NPWP' => $record->npwp ?? '-',
                                                    'Nama Pasangan' => $record->nama2 ?? '-',
                                                    'Nama NPWP' => $record->nama_npwp ?? '-',
                                                    'Pemilihan Bank' => $record->bank->nama_bank ?? '-',
                                                    'Penghasilan' => 'IDR ' . number_format($record->gaji ?? 0, 2),
                                                ];
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                // Lampiran Dokumen Section



                                Section::make('Lampiran Dokumen')
                                    ->description('Dokumen yang diperlukan untuk verifikasi')
                                    ->icon('heroicon-o-folder-open')

                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Group::make([
                                                    TextEntry::make('ektp_title')
                                                        ->label('E-KTP')
                                                        ->getStateUsing(fn() => '')
                                                        ->weight(FontWeight::Bold)
                                                        ->size('lg'),
                                                    // Placeholder untuk gambar E-KTP
                                                    TextEntry::make('ektp_placeholder')
                                                        ->label('')
                                                        ->getStateUsing(fn() => '📄 E-KTP Image Preview')
                                                        ->extraAttributes([
                                                            'class' => 'border rounded-lg p-8 bg-gray-100 text-center h-48 flex items-center justify-center'
                                                        ]),
                                                    TextEntry::make('ektp_update')
                                                        ->label('')
                                                        ->getStateUsing(fn() => 'Last Update 19 hari yang lalu')
                                                        ->color('gray')
                                                        ->size('sm'),
                                                ]),

                                                Group::make([
                                                    TextEntry::make('npwp_title')
                                                        ->label('NPWP')
                                                        ->getStateUsing(fn() => '')
                                                        ->weight(FontWeight::Bold)
                                                        ->size('lg'),
                                                    // Placeholder untuk gambar NPWP
                                                    TextEntry::make('npwp_placeholder')
                                                        ->label('')
                                                        ->getStateUsing(fn() => '📄 NPWP Image Preview')
                                                        ->extraAttributes([
                                                            'class' => 'border rounded-lg p-8 bg-yellow-100 text-center h-48 flex items-center justify-center'
                                                        ]),
                                                    TextEntry::make('npwp_update')
                                                        ->label('')
                                                        ->getStateUsing(fn() => 'Last Update 19 hari yang lalu')
                                                        ->color('gray')
                                                        ->size('sm'),
                                                ]),

                                                Group::make([
                                                    TextEntry::make('kk_title')
                                                        ->label('Kartu Keluarga')
                                                        ->getStateUsing(fn() => '')
                                                        ->weight(FontWeight::Bold)
                                                        ->size('lg'),
                                                    // Placeholder untuk gambar KK
                                                    TextEntry::make('kk_placeholder')
                                                        ->label('')
                                                        ->getStateUsing(fn() => '📄 KK Image Preview')
                                                        ->extraAttributes([
                                                            'class' => 'border rounded-lg p-8 bg-blue-100 text-center h-48 flex items-center justify-center'
                                                        ]),
                                                    TextEntry::make('kk_update')
                                                        ->label('')
                                                        ->getStateUsing(fn() => 'Last Update 19 hari yang lalu')
                                                        ->color('gray')
                                                        ->size('sm'),
                                                ]),
                                            ])
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                // Domisili dan Korespondensi Section
                                Section::make('Domisili dan Korespondensi')
                                    ->description('Data alamat dan korespondensi pemohon')
                                    ->icon('heroicon-o-map-pin')

                                    ->schema([
                                        KeyValueEntry::make('domisili_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Provinsi' => $record->provinsi_dom ?? '-',
                                                    'Kabupaten' => $record->kabupaten_dom ?? '-',
                                                    'Kecamatan' => $record->kecamatan_dom ?? '-',
                                                    'Desa/Kelurahan' => $record->kelurahan_dom ?? '-',
                                                    'Alamat Domisili' => $record->alamat_dom ?? '-',
                                                    'Status Rumah' => $this->getStatusRumah($record->sts_rumah ?? ''),
                                                    'Korespondensi' => '-',
                                                ];
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                // Collapsible Sections (Pekerjaan, Pasangan, dll)
                                Section::make('Pekerjaan')
                                    ->description('Informasi pekerjaan pemohon')
                                    ->icon('heroicon-o-briefcase')

                                    ->schema([
                                        KeyValueEntry::make('pekerjaan_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Pekerjaan' => $record->pekerjaan ?? '-',
                                                    'Penghasilan' => 'IDR ' . number_format($record->gaji ?? 0, 2),
                                                    'Perusahaan' => '-',
                                                ];
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),

                                Section::make('Pasangan')
                                    ->description('Informasi pasangan pemohon')
                                    ->icon('heroicon-o-heart')

                                    ->schema([
                                        KeyValueEntry::make('pasangan_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Nama Pasangan' => $record->nama2 ?? '-',
                                                    'NIK Pasangan' => $record->nik2 ?? '-',
                                                    'Pekerjaan Pasangan' => $record->pekerjaan2 ?? '-',
                                                    'Penghasilan Pasangan' => 'IDR ' . number_format($record->gaji2 ?? 0, 2),
                                                ];
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),

                                Section::make('Pekerjaan Pasangan')
                                    ->description('Informasi pekerjaan pasangan')
                                    ->icon('heroicon-o-briefcase')

                                    ->schema([
                                        KeyValueEntry::make('pekerjaan_pasangan_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Pekerjaan' => $record->pekerjaan2 ?? '-',
                                                    'Penghasilan' => 'IDR ' . number_format($record->gaji2 ?? 0, 2),
                                                    'Perusahaan' => '-',
                                                ];
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),

                                Section::make('Daftar Kepemilikan Kendaraan Pemohon')
                                    ->description('Informasi kendaraan yang dimiliki pemohon')
                                    ->icon('heroicon-o-truck')

                                    ->schema([
                                        KeyValueEntry::make('kendaraan_pemohon_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Kendaraan Roda 2' => ($record->count_of_vehicle1 ?? 0) . ' unit',
                                                    'Kendaraan Roda 4' => ($record->count_of_vehicle2 ?? 0) . ' unit',
                                                ];
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),

                                Section::make('Daftar Kepemilikan Rumah/Bangunan Pemohon')
                                    ->description('Informasi kepemilikan rumah/bangunan pemohon')
                                    ->icon('heroicon-o-home')

                                    ->schema([
                                        KeyValueEntry::make('rumah_pemohon_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Status Kepemilikan' => $this->getStatusRumah($record->sts_rumah ?? ''),
                                                    'Alamat Rumah' => $record->alamat_dom ?? '-',
                                                ];
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),

                                Section::make('Daftar Kepemilikan Kendaraan Pasangan')
                                    ->description('Informasi kendaraan yang dimiliki pasangan')
                                    ->icon('heroicon-o-truck')

                                    ->schema([
                                        TextEntry::make('kendaraan_pasangan_info')
                                            ->label('')
                                            ->getStateUsing(fn() => 'Data kendaraan pasangan tidak tersedia')
                                            ->color('gray')
                                    ])
                                    ->collapsible()
                                    ->collapsed(true),

                                Section::make('Daftar Kepemilikan Rumah/Bangunan Pasangan')
                                    ->description('Informasi kepemilikan rumah/bangunan pasangan')
                                    ->icon('heroicon-o-home')

                                    ->schema([
                                        // Tabel seperti di gambar
                                        Grid::make(1)
                                            ->schema([
                                                Group::make([
                                                    // Header tabel
                                                    Grid::make(5)
                                                        ->schema([
                                                            TextEntry::make('header_no')
                                                                ->label('#')
                                                                ->getStateUsing(fn() => '#')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_jenis_pajak')
                                                                ->label('Jenis Pajak')
                                                                ->getStateUsing(fn() => 'Jenis Pajak')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_nik')
                                                                ->label('NIK')
                                                                ->getStateUsing(fn() => 'NIK')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_nop')
                                                                ->label('NOP')
                                                                ->getStateUsing(fn() => 'NOP')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_luas')
                                                                ->label('L. BUMI / L. BNG')
                                                                ->getStateUsing(fn() => 'L. BUMI / L. BNG')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                        ]),
                                                    // Baris kosong sebagai contoh
                                                    Grid::make(5)
                                                        ->schema([
                                                            TextEntry::make('row1_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_jenis')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_nik')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_nop')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_luas')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),
                                                ])
                                            ])
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                            ]),

                        Tabs\Tab::make('Data Keuangan')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Informasi Keuangan Pemohon')
                                    ->description('Detail penghasilan dan data keuangan pemohon')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->columnSpanFull()
                                    ->schema([
                                        KeyValueEntry::make('keuangan_pemohon')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->columnSpanFull()
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Pekerjaan' => $record->pekerjaan ?? '-',
                                                    'Penghasilan' => 'IDR ' . number_format($record->gaji ?? 0, 2),
                                                    'Perusahaan' => '-',
                                                    'Pemilihan Bank' => $record->bank->nama_bank ?? '-',
                                                ];
                                            })
                                    ],),

                                Section::make('Informasi Keuangan Pasangan')
                                    ->description('Detail penghasilan dan data keuangan pasangan')
                                    ->icon('heroicon-o-heart')
                                    ->columnSpanFull()
                                    ->schema([
                                        KeyValueEntry::make('keuangan_pasangan')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->columnSpanFull()
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Nama Pasangan' => $record->nama2 ?? '-',
                                                    'NIK Pasangan' => $record->nik2 ?? '-',
                                                    'Pekerjaan Pasangan' => $record->pekerjaan2 ?? '-',
                                                    'Penghasilan Pasangan' => 'IDR ' . number_format($record->gaji2 ?? 0, 2),
                                                ];
                                            })
                                    ]),
                            ]),

                        Tabs\Tab::make('Data Hunian')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Section::make('Daftar Kepemilikan Kendaraan Pemohon')
                                    ->description('Informasi kendaraan yang dimiliki pemohon')
                                    ->icon('heroicon-o-truck')
                                    ->columnSpanFull()
                                    ->schema([
                                        KeyValueEntry::make('kendaraan_pemohon_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->columnSpanFull()
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Kendaraan Roda 2' => ($record->count_of_vehicle1 ?? 0) . ' unit',
                                                    'Kendaraan Roda 4' => ($record->count_of_vehicle2 ?? 0) . ' unit',
                                                ];
                                            })
                                    ],),

                                Section::make('Daftar Kepemilikan Rumah/Bangunan Pemohon')
                                    ->description('Informasi kepemilikan rumah/bangunan pemohon')
                                    ->icon('heroicon-o-home')
                                    ->columnSpanFull()
                                    ->schema([
                                        KeyValueEntry::make('rumah_pemohon_data')
                                            ->label('')
                                            ->keyLabel('Field')
                                            ->valueLabel('Value')
                                            ->columnSpanFull()
                                            ->getStateUsing(function ($record) {
                                                return [
                                                    'Status Kepemilikan' => $this->getStatusRumah($record->sts_rumah ?? ''),
                                                    'Alamat Rumah' => $record->alamat_dom ?? '-',
                                                ];
                                            })
                                    ],),

                                Section::make('Daftar Kepemilikan Rumah/Bangunan Pasangan')
                                    ->description('Informasi kepemilikan rumah/bangunan pasangan')
                                    ->icon('heroicon-o-home')
                                    ->columnSpanFull()
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                Group::make([
                                                    Grid::make(5)
                                                        ->schema([
                                                            TextEntry::make('header_no')
                                                                ->label('#')
                                                                ->getStateUsing(fn() => '#')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_jenis_pajak')
                                                                ->label('Jenis Pajak')
                                                                ->getStateUsing(fn() => 'Jenis Pajak')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_nik')
                                                                ->label('NIK')
                                                                ->getStateUsing(fn() => 'NIK')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_nop')
                                                                ->label('NOP')
                                                                ->getStateUsing(fn() => 'NOP')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                            TextEntry::make('header_luas')
                                                                ->label('L. BUMI / L. BNG')
                                                                ->getStateUsing(fn() => 'L. BUMI / L. BNG')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100']),
                                                        ]),
                                                    Grid::make(5)
                                                        ->schema([
                                                            TextEntry::make('row1_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_jenis')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_nik')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_nop')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_luas')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),
                                                ])
                                            ])
                                    ]),
                            ]),

                        Tabs\Tab::make('Persetujuan')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Section::make('Resume (Pemohon + Pasangan dalam hal sudah menikah)')
                                    ->description('')
                                    ->icon('heroicon-o-document-text')
                                    ->columnSpanFull()
                                    ->schema([
                                        Grid::make(1)
                                            ->schema([
                                                Group::make([
                                                    // Header tabel resume
                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('resume_header_no')
                                                                ->label('#')
                                                                ->getStateUsing(fn() => '#')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100 text-center']),
                                                            TextEntry::make('resume_header_desc')
                                                                ->label('Description')
                                                                ->getStateUsing(fn() => 'Description')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100 text-center']),
                                                            TextEntry::make('resume_header_value')
                                                                ->label('Value')
                                                                ->getStateUsing(fn() => 'Value')
                                                                ->weight(FontWeight::Bold)
                                                                ->extraAttributes(['class' => 'border p-2 bg-gray-100 text-center']),
                                                        ]),

                                                    // Data rows
                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row1_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '01')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row1_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Nama Pemohon')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row1_value')
                                                                ->label('')
                                                                ->getStateUsing(fn($record) => $record->nama ?? '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row2_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '02')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row2_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Nama Pasangan Pemohon')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row2_value')
                                                                ->label('')
                                                                ->getStateUsing(fn($record) => $record->nama2 ?? '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row3_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '03')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row3_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'No HP')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row3_value')
                                                                ->label('')
                                                                ->getStateUsing(fn($record) => $record->no_hp ?? '-')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row4_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '04')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row4_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Lokasi Hunian')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row4_value')
                                                                ->label('')
                                                                ->getStateUsing(fn($record) => $record->lokasi_rumah ?? 'Tower Samawa Nuansa Pondok Kelapa')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row5_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '05')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row5_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Total Penghasilan')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row5_value')
                                                                ->label('')
                                                                ->getStateUsing(fn($record) => 'IDR ' . number_format(($record->gaji ?? 0) + ($record->gaji2 ?? 0), 2))
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row6_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '06')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row6_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Total Nilai Jual Kendaraan')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row6_value')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'IDR 8.600.000,00 + IDR 0.00 = IDR 8.600.000,00')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row7_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '07')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row7_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Jumlah Kendaraan Roda 2 dan Roda 3')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row7_value')
                                                                ->label('')
                                                                ->getStateUsing(fn($record) => ($record->count_of_vehicle1 ?? 0))
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row8_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '08')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row8_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Jumlah Kendaraan Roda 4 keatas')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row8_value')
                                                                ->label('')
                                                                ->getStateUsing(fn($record) => ($record->count_of_vehicle2 ?? 0))
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),

                                                    Grid::make(3)
                                                        ->schema([
                                                            TextEntry::make('row9_no')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '09')
                                                                ->extraAttributes(['class' => 'border p-2 text-center']),
                                                            TextEntry::make('row9_desc')
                                                                ->label('')
                                                                ->getStateUsing(fn() => 'Jumlah Kepemilikan Bangunan')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                            TextEntry::make('row9_value')
                                                                ->label('')
                                                                ->getStateUsing(fn() => '0')
                                                                ->extraAttributes(['class' => 'border p-2']),
                                                        ]),
                                                ])
                                            ])
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),

                                Section::make('Persetujuan Verifikator Dokumen')
                                    ->description('')
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->columnSpanFull()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('keputusan_label')
                                                    ->label('Keputusan')
                                                    ->getStateUsing(fn() => '')
                                                    ->weight(FontWeight::Bold),

                                                TextEntry::make('keputusan_value')
                                                    ->label('')
                                                    ->getStateUsing(function ($record) {
                                                        $verifikator = \App\Models\AppVerifikator::where('pemohon_id', $record->id)->first();
                                                        if ($verifikator) {
                                                            return $verifikator->keputusan === 'disetujui' ? 'Disetujui' : ($verifikator->keputusan === 'ditolak' ? 'Ditolak' : '- Pilih -');
                                                        }
                                                        return '- Pilih -';
                                                    })
                                                    ->color(fn($state) => match ($state) {
                                                        'Disetujui' => 'success',
                                                        'Ditolak' => 'danger',
                                                        default => 'gray'
                                                    }),
                                            ]),

                                        Grid::make(1)
                                            ->schema([
                                                TextEntry::make('catatan_label')
                                                    ->label('Catatan')
                                                    ->getStateUsing(fn() => '')
                                                    ->weight(FontWeight::Bold),

                                                TextEntry::make('catatan_value')
                                                    ->label('')
                                                    ->getStateUsing(function ($record) {
                                                        $verifikator = \App\Models\AppVerifikator::where('pemohon_id', $record->id)->first();
                                                        return $verifikator?->catatan ?? 'Belum ada catatan verifikator';
                                                    })
                                                    ->placeholder('Belum ada catatan')
                                                    ->extraAttributes([
                                                        'class' => 'min-h-32 border p-3 bg-gray-50 rounded'
                                                    ]),
                                            ]),

                                        TextEntry::make('print_info')
                                            ->label('')
                                            ->getStateUsing(fn() => '🖨️ PRINT RESUME - Gunakan tombol "Setujui" atau "Tolak" untuk menyimpan dan mencetak resume')
                                            ->color('danger')
                                            ->weight(FontWeight::Bold)
                                            ->extraAttributes([
                                                'class' => 'bg-red-50 border border-red-200 p-3 rounded text-center'
                                            ])
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->collapsed(false),
                            ]),
                    ])
            ]);
    }

    private function saveCatatan($record, array $data)
    {
        // Update keterangan di data_pemohon - Observer akan handle AppVerifikator jika ada perubahan status
        $record->update([
            'keterangan' => $data['catatan_baru']
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Catatan Disimpan')
            ->body('Catatan verifikator telah disimpan.')
            ->success()
            ->send();
    }
    private function getStatusKawin(int $status): string
    {
        return match ($status) {
            0 => 'Belum Kawin',
            1 => 'Menikah',
            2 => 'Cerai',
            default => 'Tidak Kawin'
        };
    }

    private function getStatusRumah(string $status): string
    {
        return match ($status) {
            'milik_sendiri' => 'Milik Sendiri',
            'sewa' => 'Sewa',
            'kontrak' => 'Kontrak',
            'tinggal_keluarga' => 'Tinggal dengan Keluarga',
            default => 'Rumah Orang Tua'
        };
    }

    private function approveApplication($record, array $data = [])
    {
        // Logic untuk approve - Observer akan handle pembuatan AppVerifikator
        $record->update([
            'status_permohonan' => '2',
            'keterangan' => $data['catatan_verifikator'] ?? ''
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Permohonan Disetujui')
            ->body('Permohonan telah disetujui dan catatan telah disimpan.')
            ->success()
            ->send();
    }

    private function rejectApplication($record, array $data)
    {
        // Logic untuk reject - Observer akan handle pembuatan AppVerifikator
        $record->update([
            'status_permohonan' => '3',
            'keterangan' => $data['catatan_verifikator'] ?? ''
        ]);

        \Filament\Notifications\Notification::make()
            ->title('Permohonan Ditolak')
            ->body('Permohonan telah ditolak dan catatan telah disimpan.')
            ->danger()
            ->send();
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataPemohonResource\Pages;
use App\Filament\Resources\DataPemohonResource\RelationManagers;
use App\Models\DataPemohon;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DataPemohonResource extends Resource
{
    protected static ?string $model = DataPemohon::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Data Pemohon';

    protected static ?string $modelLabel = 'Pemohon';

    protected static ?string $pluralModelLabel = 'Data Pemohon';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user || !($user instanceof \App\Models\User)) {
            return '0';
        }

        // Start with base query
        $query = static::getModel()::query();

        // Apply user status access control (same as getEloquentQuery)
        if (!empty($user->allowed_status)) {
            // Ensure allowed_status is an array
            $allowedStatus = $user->allowed_status;
            if (is_string($allowedStatus)) {
                $allowedStatus = json_decode($allowedStatus, true);
            }

            if (is_array($allowedStatus) && !empty($allowedStatus)) {
                $query->whereIn('status_permohonan', $allowedStatus);
            }
        }

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pendaftaran')
                    ->schema([
                        Forms\Components\TextInput::make('id_pendaftaran')
                            ->label('ID Pendaftaran')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\Select::make('status_permohonan')
                            ->label('Status Permohonan')
                            ->options(Status::orderBy('urut')->pluck('nama_status', 'kode'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('id_bank')
                            ->label('Bank')
                            ->relationship('bank', 'nama_bank')
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-identification'),

                Forms\Components\Section::make('Data Pemohon Utama')
                    ->schema([
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->maxLength(16)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('kk')
                            ->label('No. KK')
                            ->maxLength(16)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Lengkap')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('no_hp')
                            ->label('No. HP')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\Select::make('pendidikan')
                            ->label('Pendidikan')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'D3' => 'Diploma 3',
                                'S1' => 'Sarjana',
                                'S2' => 'Magister',
                                'S3' => 'Doktor',
                            ])
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('pekerjaan')
                            ->label('Pekerjaan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('gaji')
                            ->label('Gaji')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan(1),
                        Forms\Components\Select::make('status_kawin')
                            ->label('Status Kawin')
                            ->options([
                                0 => 'Belum Kawin',
                                1 => 'Kawin',
                                2 => 'Cerai',
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-user'),

                Forms\Components\Section::make('Data NPWP')
                    ->schema([
                        Forms\Components\TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('nama_npwp')
                            ->label('Nama NPWP')
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('validasi_npwp')
                            ->label('NPWP Valid')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('status_npwp')
                            ->label('Status NPWP Aktif')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-document-text'),

                Forms\Components\Section::make('Alamat KTP')
                    ->schema([
                        Forms\Components\TextInput::make('provinsi2_ktp')
                            ->label('Provinsi')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('kabupaten_ktp')
                            ->label('Kabupaten/Kota')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('kecamatan_ktp')
                            ->label('Kecamatan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('kelurahan_ktp')
                            ->label('Kelurahan')
                            ->maxLength(100)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-map-pin'),

                Forms\Components\Section::make('Alamat Domisili')
                    ->schema([
                        Forms\Components\Checkbox::make('chkDomisili')
                            ->label('Sama dengan KTP')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('provinsi_dom')
                            ->label('Provinsi')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('kabupaten_dom')
                            ->label('Kabupaten/Kota')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('kecamatan_dom')
                            ->label('Kecamatan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('kelurahan_dom')
                            ->label('Kelurahan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('alamat_dom')
                            ->label('Alamat Lengkap')
                            ->maxLength(100)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('sts_rumah')
                            ->label('Status Rumah')
                            ->options([
                                'milik_sendiri' => 'Milik Sendiri',
                                'sewa' => 'Sewa',
                                'kontrak' => 'Kontrak',
                                'tinggal_keluarga' => 'Tinggal dengan Keluarga',
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-home'),

                Forms\Components\Section::make('Data Pasangan')
                    ->schema([
                        Forms\Components\TextInput::make('nik2')
                            ->label('NIK Pasangan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('nama2')
                            ->label('Nama Pasangan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('no_hp2')
                            ->label('No. HP Pasangan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\Select::make('pendidikan2')
                            ->label('Pendidikan Pasangan')
                            ->options([
                                'SD' => 'SD',
                                'SMP' => 'SMP',
                                'SMA' => 'SMA',
                                'D3' => 'Diploma 3',
                                'S1' => 'Sarjana',
                                'S2' => 'Magister',
                                'S3' => 'Doktor',
                            ])
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('pekerjaan2')
                            ->label('Pekerjaan Pasangan')
                            ->maxLength(100)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('gaji2')
                            ->label('Gaji Pasangan')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_couple_dki')
                            ->label('Pasangan Warga DKI')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-heart'),

                Forms\Components\Section::make('Data Hunian')
                    ->schema([
                        Forms\Components\TextInput::make('lokasi_rumah')
                            ->label('Lokasi Rumah')
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('tipe_rumah')
                            ->label('Tipe Rumah')
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('nama_blok')
                            ->label('Nama Blok')
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('tipe_unit')
                            ->label('Tipe Unit')
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('harga_unit')
                            ->label('Harga Unit')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_have_booking_kpr_dpnol')
                            ->label('Punya Booking KPR DP Nol')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-building-office'),

                Forms\Components\Section::make('Keuangan & Aset')
                    ->schema([
                        Forms\Components\TextInput::make('count_of_vehicle1')
                            ->label('Jumlah Kendaraan Roda 2')
                            ->numeric()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('count_of_vehicle2')
                            ->label('Jumlah Kendaraan Roda 4')
                            ->numeric()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('mounthly_expense1')
                            ->label('Pengeluaran Bulanan 1')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('mounthly_expense2')
                            ->label('Pengeluaran Bulanan 2')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_have_saving_bank')
                            ->label('Punya Tabungan Bank')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_have_home_credit')
                            ->label('Punya Kredit Rumah')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-banknotes'),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_ektp')
                            ->label('Foto e-KTP')
                            ->image()
                            ->directory('dokumen/ktp')
                            ->columnSpan(1),
                        Forms\Components\FileUpload::make('foto_npwp')
                            ->label('Foto NPWP')
                            ->image()
                            ->directory('dokumen/npwp')
                            ->columnSpan(1),
                        Forms\Components\FileUpload::make('foto_kk')
                            ->label('Foto KK')
                            ->image()
                            ->directory('dokumen/kk')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->icon('heroicon-o-camera'),

                // ...existing code...
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_pendaftaran')
                    ->label('ID Pendaftaran')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone'),

                Tables\Columns\TextColumn::make('gaji')
                    ->label('Gaji')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status.nama_status')
                    ->label('Status')

                    ->formatStateUsing(fn($record) => $record->status?->nama_status ?? 'Tidak ada status')
                    ->tooltip(fn($record) => $record->status?->keterangan ?? 'Tidak ada keterangan'),

                Tables\Columns\TextColumn::make('bank.nama_bank')
                    ->label('Bank')
                    ->badge()
                    ->color(fn($record) => match ($record->bank?->id) {
                        'BCA' => 'cyan',
                        'BNI' => 'orange',
                        'BRI' => 'emerald',
                        'MANDIRI' => 'yellow',
                        'BTN' => 'purple',
                        'DKI' => 'pink',
                        'CIMB' => 'violet',
                        'DANAMON' => 'lime',
                        'OCBC' => 'amber',
                        'PERMATA' => 'rose',
                        default => 'info'
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lokasi_rumah')
                    ->label('Lokasi Rumah')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('harga_unit')
                    ->label('Harga Unit')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_couple_dki')
                    ->label('Warga DKI')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_permohonan')
                    ->label('Status Permohonan')
                    ->relationship('status', 'nama_status')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('id_bank')
                    ->label('Bank')
                    ->relationship('bank', 'nama_bank'),

                Tables\Filters\Filter::make('gaji')
                    ->form([
                        Forms\Components\TextInput::make('gaji_min')
                            ->label('Gaji Minimum')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('gaji_max')
                            ->label('Gaji Maksimum')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['gaji_min'],
                                fn(Builder $query, $gaji): Builder => $query->where('gaji', '>=', $gaji),
                            )
                            ->when(
                                $data['gaji_max'],
                                fn(Builder $query, $gaji): Builder => $query->where('gaji', '<=', $gaji),
                            );
                    }),

                Tables\Filters\TernaryFilter::make('is_couple_dki')
                    ->label('Warga DKI')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('ubah_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status_baru')
                                ->label('Status Baru')
                                ->options(Status::orderBy('urut')->pluck('nama_status', 'kode'))
                                ->required(),
                            Forms\Components\Textarea::make('catatan')
                                ->label('Catatan Perubahan'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status_permohonan' => $data['status_baru'],
                            ]);
                        }),
                    Tables\Actions\Action::make('lihat_dokumen')
                        ->label('Lihat Dokumen')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->visible(fn($record) => $record->foto_ektp || $record->foto_npwp || $record->foto_kk),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('ubah_status_bulk')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('status_baru')
                                ->label('Status Baru')
                                ->options(Status::orderBy('urut')->pluck('nama_status', 'kode'))
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status_permohonan' => $data['status_baru']]);
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Pemohon')
            ->emptyStateDescription('Mulai dengan menambahkan pemohon pertama.')
            ->emptyStateIcon('heroicon-o-users')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Get current user
        $user = Auth::user();

        // If user has allowed_status configured, filter the data
        if ($user && !empty($user->allowed_status)) {
            // Ensure allowed_status is an array
            $allowedStatus = $user->allowed_status;
            if (is_string($allowedStatus)) {
                $allowedStatus = json_decode($allowedStatus, true);
            }

            if (is_array($allowedStatus) && !empty($allowedStatus)) {
                $query->whereIn('status_permohonan', $allowedStatus);
            }
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataPemohons::route('/'),
            'create' => Pages\CreateDataPemohon::route('/create'),
            'view' => Pages\ViewDataPemohon::route('/{record}'),
            'edit' => Pages\EditDataPemohon::route('/{record}/edit'),
        ];
    }
}

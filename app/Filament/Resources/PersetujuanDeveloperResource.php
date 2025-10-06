<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersetujuanDeveloperResource\Pages;
use App\Models\DataPemohon;
use App\Models\Status;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PersetujuanDeveloperResource extends Resource
{
    protected static ?string $model = DataPemohon::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationLabel = 'Developer';

    protected static ?string $modelLabel = 'Persetujuan Developer';

    protected static ?string $pluralModelLabel = 'Persetujuan Developer';

    protected static ?string $navigationGroup = 'Menunggu Persetujuan';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            return false;
        }

        // Tampilkan untuk Super Admin dan user dengan urutan = 3 (Developer)
        return $user->hasRole('Super Admin') || $user->urutan === 3;
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            return false;
        }

        // Bisa diakses oleh Super Admin dan user dengan urutan = 3 (Developer)
        return $user->hasRole('Super Admin') || $user->urutan === 3;
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            return '0';
        }

        // Tampilkan badge untuk Super Admin dan Developer (urutan = 3)
        if (!($user->hasRole('Super Admin') || $user->urutan === 3)) {
            return '0';
        }

        // Hitung data dengan status_permohonan = 2 (Approval Pengembang/Developer)
        $count = static::getEloquentQuery()->count();
        return $count > 0 ? (string) $count : '0';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status_permohonan', '2') // Hanya status = 2 (Approval Pengembang/Developer)
            ->with(['bank', 'status', 'appVerifikator'])
            ->orderBy('updated_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemohon')
                    ->schema([
                        Forms\Components\TextInput::make('id_pendaftaran')
                            ->label('ID Pendaftaran')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Pemohon')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('no_hp')
                            ->label('No. HP')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-user'),

                Forms\Components\Section::make('Detail Permohonan')
                    ->schema([
                        Forms\Components\TextInput::make('gaji')
                            ->label('Gaji')
                            ->disabled()
                            ->prefix('Rp')
                            ->numeric()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('bank.nama_bank')
                            ->label('Bank')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('harga_unit')
                            ->label('Harga Unit')
                            ->disabled()
                            ->prefix('Rp')
                            ->numeric()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('lokasi_rumah')
                            ->label('Lokasi Rumah')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-home'),

                Forms\Components\Section::make('Status Permohonan')
                    ->schema([
                        Forms\Components\Select::make('status_permohonan')
                            ->label('Status Permohonan')
                            ->options([
                                '2' => 'Approval Pengembang/Developer (Saat ini)',
                                '9' => 'Lanjut ke Bank',
                                '6' => 'Ditunda Developer',
                                '3' => 'Ditolak',
                            ])
                            ->default('2')
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Catatan Developer')
                            ->placeholder('Masukkan catatan untuk keputusan ini...')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-clipboard-document-check'),
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
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('gaji')
                    ->label('Gaji')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bank.nama_bank')
                    ->label('Bank')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('harga_unit')
                    ->label('Harga Unit')
                    ->money('IDR')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status.nama')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Approval Pengembang/Developer' => 'warning',
                        'Bank' => 'success',
                        'Ditunda Developer' => 'gray',
                        'Ditolak' => 'danger',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->keterangan ?: 'Belum ada catatan';
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_bank')
                    ->label('Bank')
                    ->relationship('bank', 'nama_bank'),

                Tables\Filters\Filter::make('gaji_range')
                    ->form([
                        Forms\Components\TextInput::make('gaji_min')
                            ->label('Gaji Minimum')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('gaji_max')
                            ->label('Gaji Maximum')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['gaji_min'],
                                fn(Builder $query, $value): Builder => $query->where('gaji', '>=', $value),
                            )
                            ->when(
                                $data['gaji_max'],
                                fn(Builder $query, $value): Builder => $query->where('gaji', '<=', $value),
                            );
                    }),

                Tables\Filters\Filter::make('tanggal_update')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('updated_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('updated_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),
                Tables\Actions\EditAction::make()
                    ->label('Proses Persetujuan')
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square'),
                Tables\Actions\Action::make('approve_to_bank')
                    ->label('Lanjut ke Bank')
                    ->icon('heroicon-o-building-library')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Lanjut ke Bank')
                    ->modalDescription('Apakah Anda yakin ingin melanjutkan permohonan ini ke tahap Bank?')
                    ->form([
                        Forms\Components\Textarea::make('catatan_developer')
                            ->label('Catatan Developer')
                            ->placeholder('Masukkan catatan untuk Bank...')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status_permohonan' => '9', // Bank
                            'keterangan' => $data['catatan_developer'],
                        ]);

                        Notification::make()
                            ->title('Berhasil Diteruskan ke Bank')
                            ->body('Permohonan telah diteruskan ke tahap Bank.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('hold')
                    ->label('Tunda')
                    ->icon('heroicon-o-pause-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penundaan')
                    ->modalDescription('Apakah Anda yakin ingin menunda permohonan ini?')
                    ->form([
                        Forms\Components\Textarea::make('alasan_tunda')
                            ->label('Alasan Penundaan')
                            ->placeholder('Masukkan alasan penundaan...')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status_permohonan' => '6', // Ditunda Developer
                            'keterangan' => $data['alasan_tunda'],
                        ]);

                        Notification::make()
                            ->title('Permohonan Ditunda')
                            ->body('Permohonan telah ditunda.')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Penolakan')
                    ->modalDescription('Apakah Anda yakin ingin menolak permohonan ini?')
                    ->form([
                        Forms\Components\Textarea::make('alasan_tolak')
                            ->label('Alasan Penolakan')
                            ->placeholder('Masukkan alasan penolakan...')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status_permohonan' => '3', // Ditolak
                            'keterangan' => $data['alasan_tolak'],
                        ]);

                        Notification::make()
                            ->title('Permohonan Ditolak')
                            ->body('Permohonan telah ditolak.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_approve_to_bank')
                        ->label('Lanjut ke Bank (Bulk)')
                        ->icon('heroicon-o-building-library')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Bulk Lanjut ke Bank')
                        ->modalDescription('Apakah Anda yakin ingin melanjutkan semua permohonan terpilih ke tahap Bank?')
                        ->form([
                            Forms\Components\Textarea::make('catatan_bulk')
                                ->label('Catatan untuk semua')
                                ->placeholder('Masukkan catatan yang sama untuk semua permohonan...')
                                ->rows(3)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status_permohonan' => '9', // Bank
                                    'keterangan' => $data['catatan_bulk'],
                                ]);
                            }

                            Notification::make()
                                ->title('Berhasil Diteruskan ke Bank')
                                ->body(count($records) . ' permohonan telah diteruskan ke tahap Bank.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Permohonan Developer')
            ->emptyStateDescription('Belum ada permohonan yang perlu diproses oleh Developer.')
            ->emptyStateIcon('heroicon-o-code-bracket')
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
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
            'index' => Pages\ListPersetujuanDevelopers::route('/'),
            'create' => Pages\CreatePersetujuanDeveloper::route('/create'),
            'view' => Pages\ViewPersetujuanDeveloper::route('/{record}'),
            'edit' => Pages\EditPersetujuanDeveloper::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Tidak bisa create manual, data diambil dari status_permohonan = 2
        return false;
    }

    public static function canDelete($record): bool
    {
        // Tidak bisa delete, hanya bisa update status
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}

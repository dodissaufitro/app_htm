<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersetujuanResource\Pages;
use App\Models\DataPemohon;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PersetujuanResource extends Resource
{
    protected static ?string $model = DataPemohon::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Persetujuan';

    protected static ?string $modelLabel = 'Persetujuan';

    protected static ?string $pluralModelLabel = 'Data Persetujuan';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user || !($user instanceof \App\Models\User)) {
            return false;
        }

        // Only show for users with appropriate roles
        return $user->hasRole(['Super Admin', 'Admin', 'Approver', 'Verifikator']);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user || !($user instanceof \App\Models\User)) {
            return '0';
        }

        // Start with base persetujuan query
        $query = static::getModel()::forPersetujuan();

        // Apply user status access control (same as getEloquentQuery)
        if (!empty($user->allowed_status)) {
            $query->whereIn('status_permohonan', $user->allowed_status);
        }

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->forPersetujuan()
            ->with(['status', 'bank']);

        // Apply user status access control
        $user = Auth::user();
        if ($user && !empty($user->allowed_status)) {
            $query->whereIn('status_permohonan', $user->allowed_status);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemohon')
                    ->schema([
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

                        Forms\Components\Select::make('status_permohonan')
                            ->label('Status Persetujuan')
                            ->options(Status::orderBy('urut')->pluck('nama_status', 'kode'))
                            ->searchable()
                            ->required()
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
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('id_bank')
                            ->label('Bank')
                            ->disabled()
                            ->formatStateUsing(fn($record) => $record->bank?->nama_bank ?? '-')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('harga_unit')
                            ->label('Harga Unit')
                            ->disabled()
                            ->prefix('Rp')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('lokasi_rumah')
                            ->label('Lokasi Rumah')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-home'),

                Forms\Components\Section::make('Catatan Persetujuan')
                    ->schema([
                        Forms\Components\Textarea::make('reason_of_choose_location')
                            ->label('Alasan Memilih Lokasi')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Catatan Tambahan')
                            ->rows(4)
                            ->placeholder('Tambahkan catatan untuk persetujuan ini...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
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
                    ->copyable()
                    ->toggleable(),

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
                    ->color('success')
                    ->formatStateUsing(fn($record) => $record->status?->nama_status ?? 'Persetujuan'),

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
                        default => 'info'
                    }),

                Tables\Columns\TextColumn::make('harga_unit')
                    ->label('Harga Unit')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lokasi_rumah')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
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

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('statistik_persetujuan')
                    ->label('Statistik Persetujuan')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->modalHeading('Statistik Data Persetujuan')
                    ->modalContent(function () {
                        $total = DataPemohon::where('status_permohonan', 'lSnjFGXa68gYemjMG9H5IWHtrdWfh9G1')->count();
                        $today = DataPemohon::where('status_permohonan', 'lSnjFGXa68gYemjMG9H5IWHtrdWfh9G1')
                            ->whereDate('created_at', today())->count();
                        $thisMonth = DataPemohon::where('status_permohonan', 'lSnjFGXa68gYemjMG9H5IWHtrdWfh9G1')
                            ->whereMonth('created_at', now()->month)->count();

                        return view('components.persetujuan-statistik', compact('total', 'today', 'thisMonth'));
                    })
                    ->modalWidth('lg'),
            ])
            ->actions([
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
                            ->label('Catatan Perubahan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status_permohonan' => $data['status_baru'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            ->emptyStateHeading('Belum Ada Data Persetujuan')
            ->emptyStateDescription('Belum ada pemohon dengan status persetujuan.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s');
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
            'index' => Pages\ListPersetujuans::route('/'),
            'view' => Pages\ViewPersetujuan::route('/{record}'),
            'edit' => Pages\EditPersetujuan::route('/{record}/edit'),
        ];
    }
}

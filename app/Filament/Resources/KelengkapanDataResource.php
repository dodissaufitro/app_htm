<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KelengkapanDataResource\Pages;
use App\Models\DataPemohon;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class KelengkapanDataResource extends Resource
{
    protected static ?string $model = DataPemohon::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Kelengkapan Data';

    protected static ?string $modelLabel = 'Kelengkapan Data';

    protected static ?string $pluralModelLabel = 'Kelengkapan Data';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 10;

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
        return 'info';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Apply user access control
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
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->maxLength(16)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('no_hp')
                            ->label('No. HP')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\Select::make('status_permohonan')
                            ->label('Status Kelengkapan Data')
                            ->options(Status::orderBy('urut')->pluck('nama_status', 'kode'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('gaji')
                            ->label('Gaji')
                            ->numeric()
                            ->prefix('Rp')
                            ->columnSpan(1),

                        Forms\Components\Select::make('id_bank')
                            ->label('Bank')
                            ->relationship('bank', 'nama_bank')
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-users'),

                Forms\Components\Section::make('Detail Alamat')
                    ->schema([
                        Forms\Components\TextInput::make('provinsi_dom')
                            ->label('Provinsi')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kabupaten_dom')
                            ->label('Kabupaten/Kota')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('alamat_dom')
                            ->label('Alamat Lengkap')
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-map-pin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status.nama_status')
                    ->label('Status Kelengkapan')

                    ->formatStateUsing(fn($record) => $record->status?->nama_status ?? 'Tidak ada status')
                    ->tooltip(fn($record) => $record->status?->keterangan ?? 'Tidak ada keterangan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone'),

                Tables\Columns\TextColumn::make('gaji')
                    ->label('Gaji')
                    ->money('IDR')
                    ->sortable(),

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

                Tables\Columns\TextColumn::make('provinsi_dom')
                    ->label('Provinsi')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('kabupaten_dom')
                    ->label('Kab/Kota')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_permohonan')
                    ->label('Status Kelengkapan')
                    ->options(Status::orderBy('urut')->pluck('nama_status', 'kode')),

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
                    ->label('Pasangan DKI')
                    ->placeholder('Semua')
                    ->trueLabel('Memiliki Pasangan DKI')
                    ->falseLabel('Tidak Memiliki Pasangan DKI'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('statistik')
                    ->label('Lihat Statistik')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('Statistik Kelengkapan Data')
                    ->modalContent(function () {
                        $stats = DataPemohon::with('status')
                            ->get()
                            ->groupBy('status.nama_status')
                            ->map(function ($group) {
                                return is_countable($group) ? $group->count() : 0;
                            })
                            ->reject(fn($count, $key) => is_null($key) || empty($key));

                        return view('components.kelengkapan-statistik', compact('stats'));
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
            'index' => Pages\ListKelengkapanData::route('/'),
            'create' => Pages\CreateKelengkapanData::route('/create'),
            'view' => Pages\ViewKelengkapanData::route('/{record}'),
            'edit' => Pages\EditKelengkapanData::route('/{record}/edit'),
        ];
    }
}

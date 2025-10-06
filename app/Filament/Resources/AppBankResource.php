<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppBankResource\Pages;
use App\Filament\Resources\AppBankResource\RelationManagers;
use App\Models\AppBank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppBankResource extends Resource
{
    protected static ?string $model = AppBank::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Approval Bank';

    protected static ?string $modelLabel = 'Approval Bank';

    protected static ?string $pluralModelLabel = 'Approval Bank';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 3;

    // Menambahkan policy untuk mengatur akses berdasarkan roles
    protected static ?string $policy = \App\Policies\AppBankPolicy::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemohon')
                    ->schema([
                        Forms\Components\Select::make('pemohon_id')
                            ->label('Pemohon')
                            ->relationship('pemohon', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Validasi Data')
                    ->schema([
                        Forms\Components\ToggleButtons::make('data_lengkap')
                            ->label('Data Lengkap')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'danger',
                            ]),

                        Forms\Components\ToggleButtons::make('data_pendukung_valid')
                            ->label('Data Pendukung Valid')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'danger',
                            ]),

                        Forms\Components\ToggleButtons::make('bi_checking')
                            ->label('BI Checking')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'danger',
                            ]),

                        Forms\Components\ToggleButtons::make('info_biaya')
                            ->label('Info Biaya')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'danger',
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Aplikasi')
                    ->schema([
                        Forms\Components\ToggleButtons::make('masih_minat')
                            ->label('Masih Berminat')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-heart',
                                false => 'heroicon-o-heart-slash',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'gray',
                            ]),

                        Forms\Components\Select::make('keputusan')
                            ->label('Keputusan')
                            ->options([
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'pending' => 'Menunggu',
                                'review' => 'Review',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('alasan_tolak')
                            ->label('Alasan Penolakan')
                            ->maxLength(255)
                            ->visible(fn(Forms\Get $get) => $get('keputusan') === 'rejected'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Catatan & Dokumen')
                    ->schema([
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('dok_pm1')
                            ->label('Dokumen PM1')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120),

                        Forms\Components\FileUpload::make('dok_slip_gaji')
                            ->label('Slip Gaji')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pemohon.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                Tables\Columns\IconColumn::make('data_lengkap')
                    ->label('Data Lengkap')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('data_pendukung_valid')
                    ->label('Data Valid')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('bi_checking')
                    ->label('BI Check')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('masih_minat')
                    ->label('Minat')
                    ->boolean()
                    ->trueIcon('heroicon-o-heart')
                    ->falseIcon('heroicon-o-heart-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('keputusan')
                    ->label('Keputusan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        'review' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'pending' => 'Menunggu',
                        'review' => 'Review',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('alasan_tolak')
                    ->label('Alasan Tolak')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('keputusan')
                    ->label('Keputusan')
                    ->options([
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'pending' => 'Menunggu',
                        'review' => 'Review',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('data_lengkap')
                    ->label('Data Lengkap'),

                Tables\Filters\TernaryFilter::make('masih_minat')
                    ->label('Masih Berminat'),

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
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListAppBanks::route('/'),
            'create' => Pages\CreateAppBank::route('/create'),
            'view' => Pages\ViewAppBank::route('/{record}'),
            'edit' => Pages\EditAppBank::route('/{record}/edit'),
        ];
    }

    /**
     * Menentukan apakah resource dapat diakses berdasarkan permission
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Cek permission menggunakan Laravel Gate
        return Gate::allows('view_any_app::bank');
    }

    /**
     * Mengecek apakah resource ditampilkan di navigasi
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}

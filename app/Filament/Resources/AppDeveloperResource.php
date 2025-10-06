<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppDeveloperResource\Pages;
use App\Filament\Resources\AppDeveloperResource\RelationManagers;
use App\Models\AppDeveloper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppDeveloperResource extends Resource
{
    protected static ?string $model = AppDeveloper::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Approval Developer';

    protected static ?string $modelLabel = 'Approval Developer';

    protected static ?string $pluralModelLabel = 'Approval Developer';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 2;

    // Menambahkan policy untuk mengatur akses berdasarkan roles
    protected static ?string $policy = \App\Policies\AppDeveloperPolicy::class;

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

                Forms\Components\Section::make('Status Kehadiran')
                    ->schema([
                        Forms\Components\ToggleButtons::make('hadir')
                            ->label('Status Kehadiran')
                            ->boolean()
                            ->grouped()
                            ->required()
                            ->icons([
                                true => 'heroicon-o-check-circle',
                                false => 'heroicon-o-x-circle',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'danger',
                            ]),

                        Forms\Components\ToggleButtons::make('idle')
                            ->label('Status Idle')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-pause-circle',
                                false => 'heroicon-o-play-circle',
                            ])
                            ->colors([
                                true => 'warning',
                                false => 'success',
                            ]),

                        Forms\Components\ToggleButtons::make('masih_minat')
                            ->label('Masih Berminat')
                            ->boolean()
                            ->grouped()
                            ->required()
                            ->icons([
                                true => 'heroicon-o-heart',
                                false => 'heroicon-o-heart-slash',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'gray',
                            ]),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detail Kunjungan')
                    ->schema([
                        Forms\Components\ToggleButtons::make('perubahan_unit')
                            ->label('Perubahan Unit')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-arrow-path',
                                false => 'heroicon-o-check',
                            ])
                            ->colors([
                                true => 'info',
                                false => 'success',
                            ]),

                        Forms\Components\Textarea::make('history_visit')
                            ->label('Riwayat Kunjungan')
                            ->rows(3)
                            ->placeholder('Masukkan riwayat kunjungan...')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dokumentasi')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_kehadiran')
                            ->label('Foto Kehadiran')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->downloadable()
                            ->previewable()
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Keputusan & Catatan')
                    ->schema([
                        Forms\Components\Select::make('keputusan')
                            ->label('Keputusan')
                            ->options([
                                'lanjut' => 'Lanjut Proses',
                                'pending' => 'Pending',
                                'batal' => 'Batal',
                                'review' => 'Perlu Review',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(4)
                            ->placeholder('Catatan tambahan mengenai kunjungan developer...')
                            ->columnSpanFull(),
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

                Tables\Columns\IconColumn::make('hadir')
                    ->label('Hadir')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('idle')
                    ->label('Idle')
                    ->boolean()
                    ->trueIcon('heroicon-o-pause-circle')
                    ->falseIcon('heroicon-o-play-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                Tables\Columns\IconColumn::make('masih_minat')
                    ->label('Minat')
                    ->boolean()
                    ->trueIcon('heroicon-o-heart')
                    ->falseIcon('heroicon-o-heart-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('perubahan_unit')
                    ->label('Ubah Unit')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-check')
                    ->trueColor('info')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('history_visit')
                    ->label('Riwayat')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('foto_kehadiran')
                    ->label('Foto')
                    ->formatStateUsing(fn($state) => $state ? 'Ada' : 'Tidak Ada')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('keputusan')
                    ->label('Keputusan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'lanjut' => 'success',
                        'pending' => 'warning',
                        'batal' => 'danger',
                        'review' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'lanjut' => 'Lanjut Proses',
                        'pending' => 'Pending',
                        'batal' => 'Batal',
                        'review' => 'Perlu Review',
                        default => $state,
                    }),

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
                        'lanjut' => 'Lanjut Proses',
                        'pending' => 'Pending',
                        'batal' => 'Batal',
                        'review' => 'Perlu Review',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('hadir')
                    ->label('Status Kehadiran'),

                Tables\Filters\TernaryFilter::make('masih_minat')
                    ->label('Masih Berminat'),

                Tables\Filters\TernaryFilter::make('idle')
                    ->label('Status Idle'),

                Tables\Filters\TernaryFilter::make('perubahan_unit')
                    ->label('Perubahan Unit'),

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
            'index' => Pages\ListAppDevelopers::route('/'),
            'create' => Pages\CreateAppDeveloper::route('/create'),
            'view' => Pages\ViewAppDeveloper::route('/{record}'),
            'edit' => Pages\EditAppDeveloper::route('/{record}/edit'),
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
        return Gate::allows('view_any_app::developer');
    }

    /**
     * Mengecek apakah resource ditampilkan di navigasi
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}

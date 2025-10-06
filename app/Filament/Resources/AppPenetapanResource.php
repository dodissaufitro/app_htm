<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppPenetapanResource\Pages;
use App\Filament\Resources\AppPenetapanResource\RelationManagers;
use App\Models\AppPenetapan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppPenetapanResource extends Resource
{
    protected static ?string $model = AppPenetapan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Approval Penetapan';

    protected static ?string $modelLabel = 'Aplikasi Penetapan';

    protected static ?string $pluralModelLabel = 'Aplikasi Penetapan';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 4;

    // Menambahkan policy untuk mengatur akses berdasarkan roles
    protected static ?string $policy = \App\Policies\AppPenetapanPolicy::class;

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

                Forms\Components\Section::make('Status Penetapan')
                    ->schema([
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Keputusan Penetapan')
                    ->schema([
                        Forms\Components\Select::make('keputusan')
                            ->label('Keputusan')
                            ->options([
                                'ditetapkan' => 'Ditetapkan',
                                'ditunda' => 'Ditunda',
                                'ditolak' => 'Ditolak',
                                'revisi' => 'Perlu Revisi',
                                'pending' => 'Menunggu',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Penetapan')
                            ->rows(4)
                            ->placeholder('Catatan mengenai penetapan unit...')
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

                Tables\Columns\TextColumn::make('keputusan')
                    ->label('Keputusan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ditetapkan' => 'success',
                        'ditunda' => 'warning',
                        'ditolak' => 'danger',
                        'revisi' => 'info',
                        'pending' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ditetapkan' => 'Ditetapkan',
                        'ditunda' => 'Ditunda',
                        'ditolak' => 'Ditolak',
                        'revisi' => 'Perlu Revisi',
                        'pending' => 'Menunggu',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
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
                        'ditetapkan' => 'Ditetapkan',
                        'ditunda' => 'Ditunda',
                        'ditolak' => 'Ditolak',
                        'revisi' => 'Perlu Revisi',
                        'pending' => 'Menunggu',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('masih_minat')
                    ->label('Masih Berminat'),

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
            'index' => Pages\ListAppPenetapans::route('/'),
            'create' => Pages\CreateAppPenetapan::route('/create'),
            'view' => Pages\ViewAppPenetapan::route('/{record}'),
            'edit' => Pages\EditAppPenetapan::route('/{record}/edit'),
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
        return Gate::allows('view_any_app::penetapan');
    }

    /**
     * Mengecek apakah resource ditampilkan di navigasi
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}

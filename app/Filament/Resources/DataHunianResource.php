<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataHunianResource\Pages;
use App\Models\DataHunian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DataHunianResource extends Resource
{
    protected static ?string $model = DataHunian::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Data Hunian';

    protected static ?string $modelLabel = 'Data Hunian';

    protected static ?string $pluralModelLabel = 'Data Hunian';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemukiman')
                    ->schema([
                        Forms\Components\TextInput::make('nama_pemukiman')
                            ->label('Nama Pemukiman')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('alamat_pemukiman')
                            ->label('Alamat Pemukiman')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('kode_lokasi')
                            ->label('Kode Lokasi')
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kode_hunian')
                            ->label('Kode Hunian')
                            ->maxLength(50)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Hunian')
                    ->schema([
                        Forms\Components\TextInput::make('tipe_hunian')
                            ->label('Tipe Hunian')
                            ->maxLength(50)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('ukuran')
                            ->label('Ukuran (m²)')
                            ->maxLength(50)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('harga')
                            ->label('Harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Proyeksi Harga (Tahun)')
                    ->schema([
                        Forms\Components\TextInput::make('tahun5')
                            ->label('5 Tahun')
                            ->numeric()
                            ->prefix('Rp')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : null)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('tahun10')
                            ->label('10 Tahun')
                            ->numeric()
                            ->prefix('Rp')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : null)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('tahun15')
                            ->label('15 Tahun')
                            ->numeric()
                            ->prefix('Rp')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : null)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('tahun20')
                            ->label('20 Tahun')
                            ->numeric()
                            ->prefix('Rp')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn($state) => $state ? (int) str_replace(['.', ','], '', $state) : null)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('deleted')
                            ->label('Status')
                            ->options([
                                null => 'Aktif',
                                'Y' => 'Dihapus',
                                'N' => 'Non-Aktif',
                            ])
                            ->default(null)
                            ->columnSpan(1),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_pemukiman')
                    ->label('Nama Pemukiman')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),

                Tables\Columns\TextColumn::make('kode_lokasi')
                    ->label('Kode Lokasi')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('kode_hunian')
                    ->label('Kode Hunian')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('tipe_hunian')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ukuran')
                    ->label('Ukuran')
                    ->suffix(' m²')
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('deleted')
                    ->label('Status')
                    ->colors([
                        'success' => fn($state) => $state === null,
                        'danger' => 'Y',
                        'warning' => 'N',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        null => 'Aktif',
                        'Y' => 'Dihapus',
                        'N' => 'Non-Aktif',
                        default => 'Aktif'
                    }),

                Tables\Columns\TextColumn::make('create_date')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('update_date')
                    ->label('Diupdate')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_hunian')
                    ->label('Tipe Hunian'),

                Tables\Filters\SelectFilter::make('deleted')
                    ->label('Status')
                    ->options([
                        null => 'Aktif',
                        'Y' => 'Dihapus',
                        'N' => 'Non-Aktif',
                    ]),

                Tables\Filters\Filter::make('harga')
                    ->form([
                        Forms\Components\TextInput::make('harga_min')
                            ->label('Harga Minimum')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('harga_max')
                            ->label('Harga Maksimum')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['harga_min'],
                                fn(Builder $query, $price): Builder => $query->where('harga', '>=', $price),
                            )
                            ->when(
                                $data['harga_max'],
                                fn(Builder $query, $price): Builder => $query->where('harga', '<=', $price),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('lihat_proyeksi')
                    ->label('Lihat Proyeksi')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('Proyeksi Harga')
                    ->modalDescription('Proyeksi harga hunian untuk berbagai periode tahun')
                    ->modalContent(function ($record) {
                        return view('components.proyeksi-modal', [
                            'harga_awal' => $record->harga,
                            'tahun5' => $record->tahun5,
                            'tahun10' => $record->tahun10,
                            'tahun15' => $record->tahun15,
                            'tahun20' => $record->tahun20,
                            'nama_pemukiman' => $record->nama_pemukiman,
                            'tipe_hunian' => $record->tipe_hunian,
                        ]);
                    })
                    ->modalWidth('2xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('create_date', 'desc')
            ->striped();
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
            'index' => Pages\ListDataHunians::route('/'),
            'create' => Pages\CreateDataHunian::route('/create'),
            'view' => Pages\ViewDataHunian::route('/{record}'),
            'edit' => Pages\EditDataHunian::route('/{record}/edit'),
        ];
    }
}

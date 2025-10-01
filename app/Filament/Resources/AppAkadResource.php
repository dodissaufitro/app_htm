<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppAkadResource\Pages;
use App\Models\AppAkad;
use App\Models\DataPemohon;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppAkadResource extends Resource
{
    protected static ?string $model = AppAkad::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Akad Kredit';

    protected static ?string $modelLabel = 'Akad Kredit';

    protected static ?string $pluralModelLabel = 'Akad Kredit';

    protected static ?string $navigationGroup = 'Aplikasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemohon')
                    ->schema([
                        Forms\Components\Select::make('pemohon_id')
                            ->label('Pemohon')
                            ->relationship('pemohon', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Radio::make('masih_minat')
                            ->label('Masih Berminat')
                            ->options([
                                'Y' => 'Ya',
                                'N' => 'Tidak',
                            ])
                            ->required()
                            ->default('Y')
                            ->inline()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Akad')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_akad')
                            ->label('Tanggal Akad')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('dana_akad')
                            ->label('Dana Akad')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                            ->dehydrateStateUsing(fn($state) => (int) str_replace(['.', ','], '', $state))
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('notaris')
                            ->label('Notaris')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('saksi')
                            ->label('Saksi')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('no_spk')
                            ->label('Nomor SPK')
                            ->required()
                            ->maxLength(128)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dokumen')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_spk_hal_depan')
                            ->label('Foto SPK Halaman Depan')
                            ->image()
                            ->imageEditor()
                            ->directory('akad/spk-depan')
                            ->visibility('private')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('foto_spk_hal_belakang')
                            ->label('Foto SPK Halaman Belakang')
                            ->image()
                            ->imageEditor()
                            ->directory('akad/spk-belakang')
                            ->visibility('private')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('foto_akad')
                            ->label('Foto Akad')
                            ->image()
                            ->imageEditor()
                            ->directory('akad/foto-akad')
                            ->visibility('private')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Keputusan')
                    ->schema([
                        Forms\Components\Select::make('keputusan')
                            ->label('Keputusan')
                            ->options([
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                                'ditunda' => 'Ditunda',
                            ])
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Audit Trail')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Dibuat Pada')
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('creator', 'name')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->visibleOn(['edit', 'view']),
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

                Tables\Columns\TextColumn::make('pemohon.nama')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('masih_minat')
                    ->label('Minat')
                    ->colors([
                        'success' => 'Y',
                        'danger' => 'N',
                    ])
                    ->formatStateUsing(fn(string $state): string => $state === 'Y' ? 'Ya' : 'Tidak'),

                Tables\Columns\TextColumn::make('tanggal_akad')
                    ->label('Tgl Akad')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dana_akad')
                    ->label('Dana Akad')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notaris')
                    ->label('Notaris')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('no_spk')
                    ->label('No SPK')
                    ->searchable()
                    ->copyable()
                    ->limit(15),

                Tables\Columns\BadgeColumn::make('keputusan')
                    ->label('Keputusan')
                    ->colors([
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                        'warning' => 'ditunda',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('masih_minat')
                    ->label('Masih Berminat')
                    ->options([
                        'Y' => 'Ya',
                        'N' => 'Tidak',
                    ]),

                Tables\Filters\SelectFilter::make('keputusan')
                    ->label('Keputusan')
                    ->options([
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        'ditunda' => 'Ditunda',
                    ]),

                Tables\Filters\Filter::make('tanggal_akad')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_akad', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_akad', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download_documents')
                    ->label('Unduh Dokumen')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn($record) => $record->foto_spk_hal_depan || $record->foto_spk_hal_belakang || $record->foto_akad),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListAppAkads::route('/'),
            'create' => Pages\CreateAppAkad::route('/create'),
            'view' => Pages\ViewAppAkad::route('/{record}'),
            'edit' => Pages\EditAppAkad::route('/{record}/edit'),
        ];
    }
}

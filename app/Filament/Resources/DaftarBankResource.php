<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DaftarBankResource\Pages;
use App\Filament\Resources\DaftarBankResource\RelationManagers;
use App\Models\DaftarBank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DaftarBankResource extends Resource
{
    protected static ?string $model = DaftarBank::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Daftar Bank';

    protected static ?string $modelLabel = 'Bank';

    protected static ?string $pluralModelLabel = 'Daftar Bank';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bank')
                    ->description('Masukkan informasi detail bank')
                    ->schema([
                        Forms\Components\TextInput::make('kode_bank')
                            ->label('Kode Bank')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('Contoh: BCA, BNI, BRI')
                            ->helperText('Kode bank (dapat duplikat dengan status berbeda)')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nama_bank')
                            ->label('Nama Bank')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Bank Central Asia - Jakarta Pusat')
                            ->helperText('Nama lengkap bank dengan lokasi/cabang')
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Tidak Aktif',
                                'pending' => 'Pending',
                                'maintenance' => 'Maintenance',
                            ])
                            ->default('active')
                            ->helperText('Status operasional bank')
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-building-library'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('kode_bank')
                    ->label('Kode Bank')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Kode bank berhasil disalin')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nama_bank')
                    ->label('Nama Bank')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-building-library')
                    ->description(fn($record) => 'Kode: ' . $record->kode_bank),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'pending' => 'warning',
                        'maintenance' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        'pending' => 'Pending',
                        'maintenance' => 'Maintenance',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\Filter::make('nama_bank')
                    ->form([
                        Forms\Components\TextInput::make('nama')
                            ->label('Cari Nama Bank')
                            ->placeholder('Masukkan nama bank...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['nama'],
                                fn(Builder $query, $nama): Builder => $query->where('nama_bank', 'like', "%{$nama}%"),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Bank')
                        ->modalDescription('Apakah Anda yakin ingin menghapus bank ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Bank Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus bank yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Bank')
            ->emptyStateDescription('Mulai dengan menambahkan bank pertama.')
            ->emptyStateIcon('heroicon-o-building-library')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Bank')
                    ->color('primary'),
            ])
            ->striped()
            ->defaultSort('nama_bank', 'asc')
            ->poll('30s')
            ->searchPlaceholder('Cari bank...')
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
            'index' => Pages\ListDaftarBanks::route('/'),
            'create' => Pages\CreateDaftarBank::route('/create'),
            'view' => Pages\ViewDaftarBank::route('/{record}'),
            'edit' => Pages\EditDaftarBank::route('/{record}/edit'),
        ];
    }
}

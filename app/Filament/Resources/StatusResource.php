<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Status Workflow';

    protected static ?string $modelLabel = 'Status';

    protected static ?string $pluralModelLabel = 'Status Workflow';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Status')
                    ->description('Konfigurasi status workflow aplikasi')
                    ->schema([
                        Forms\Components\TextInput::make('kode')
                            ->label('Kode Status')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: PENDING, APPROVED')
                            ->helperText('Kode unik untuk identifikasi status')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nama_status')
                            ->label('Nama Status')
                            ->required()
                            ->maxLength(32)
                            ->placeholder('Contoh: Menunggu Persetujuan')
                            ->helperText('Nama status yang akan ditampilkan')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('urut')
                            ->label('Urutan')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->helperText('Urutan tampilan status')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('kode_urut')
                            ->label('Kode Urut')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->helperText('Kode urutan untuk sorting')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->required()
                            ->rows(4)
                            ->placeholder('Deskripsi lengkap tentang status ini...')
                            ->helperText('Penjelasan detail tentang status dan kondisinya')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-cog-6-tooth'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode')
                    ->label('Kode Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Kode status berhasil disalin')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nama_status')
                    ->label('Nama Status')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-flag')
                    ->description(fn($record) => 'Kode: ' . $record->kode),

                Tables\Columns\TextColumn::make('urut')
                    ->label('Urutan')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('kode_urut')
                    ->label('Kode Urut')
                    ->sortable()
                    ->badge()
                    ->color('secondary')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->tooltip(fn($record) => $record->keterangan),

                Tables\Columns\BadgeColumn::make('status_aktif')
                    ->label('Status')
                    ->getStateUsing(fn($record) => 'aktif')
                    ->colors([
                        'success' => 'aktif',
                    ])
                    ->formatStateUsing(fn(string $state): string => 'Aktif'),
            ])
            ->filters([
                Tables\Filters\Filter::make('urut')
                    ->form([
                        Forms\Components\TextInput::make('urut_min')
                            ->label('Urutan Minimum')
                            ->numeric(),
                        Forms\Components\TextInput::make('urut_max')
                            ->label('Urutan Maksimum')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['urut_min'],
                                fn(Builder $query, $urut): Builder => $query->where('urut', '>=', $urut),
                            )
                            ->when(
                                $data['urut_max'],
                                fn(Builder $query, $urut): Builder => $query->where('urut', '<=', $urut),
                            );
                    }),

                Tables\Filters\Filter::make('nama_status')
                    ->form([
                        Forms\Components\TextInput::make('nama')
                            ->label('Cari Nama Status')
                            ->placeholder('Masukkan nama status...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['nama'],
                                fn(Builder $query, $nama): Builder => $query->where('nama_status', 'like', "%{$nama}%"),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('workflow_guide')
                    ->label('Panduan Workflow')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('info')
                    ->modalHeading('Panduan Status Workflow')
                    ->modalContent(view('components.workflow-guide'))
                    ->modalWidth('2xl'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\EditAction::make()
                        ->color('warning'),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Status')
                        ->modalDescription('Apakah Anda yakin ingin menghapus status ini? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                    Tables\Actions\Action::make('duplicate')
                        ->label('Duplikasi')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('secondary')
                        ->form([
                            Forms\Components\TextInput::make('kode_baru')
                                ->label('Kode Status Baru')
                                ->required(),
                            Forms\Components\TextInput::make('nama_baru')
                                ->label('Nama Status Baru')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            Status::create([
                                'kode' => $data['kode_baru'],
                                'nama_status' => $data['nama_baru'],
                                'urut' => $record->urut + 1,
                                'kode_urut' => $record->kode_urut + 1,
                                'keterangan' => $record->keterangan,
                            ]);
                        }),
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
                        ->modalHeading('Hapus Status Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus status yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                    Tables\Actions\BulkAction::make('reorder')
                        ->label('Atur Ulang Urutan')
                        ->icon('heroicon-o-arrows-up-down')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('start_order')
                                ->label('Mulai dari Urutan')
                                ->numeric()
                                ->default(1)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $order = $data['start_order'];
                            $records->each(function ($record) use (&$order) {
                                $record->update([
                                    'urut' => $order,
                                    'kode_urut' => $order,
                                ]);
                                $order++;
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Status')
            ->emptyStateDescription('Mulai dengan menambahkan status workflow pertama.')
            ->emptyStateIcon('heroicon-o-flag')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Status')
                    ->color('primary'),
            ])
            ->striped()
            ->defaultSort('urut', 'asc')
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
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'view' => Pages\ViewStatus::route('/{record}'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}

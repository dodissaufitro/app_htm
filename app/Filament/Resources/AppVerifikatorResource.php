<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppVerifikatorResource\Pages;
use App\Filament\Resources\AppVerifikatorResource\RelationManagers;
use App\Models\AppVerifikator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AppVerifikatorResource extends Resource
{
    protected static ?string $model = AppVerifikator::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Approval UPDP';

    protected static ?string $modelLabel = 'Verifikator';

    protected static ?string $pluralModelLabel = 'Verifikator';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    // Menambahkan policy untuk mengatur akses berdasarkan roles
    protected static ?string $policy = \App\Policies\AppVerifikatorPolicy::class;

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
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Hasil Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('keputusan')
                            ->label('Keputusan Verifikasi')
                            ->options([
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'pending' => 'Menunggu',
                                'revision' => 'Perlu Revisi',
                                'review' => 'Dalam Review',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Verifikasi')
                            ->required()
                            ->rows(4)
                            ->placeholder('Tambahkan catatan detail mengenai hasil verifikasi...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pemohon.nama')
                    ->label('Pemohon')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('pemohon.id_pendaftaran')
                    ->label('Id Pendaftaran')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('keputusan')
                    ->label('Keputusan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        'revision' => 'info',
                        'review' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'pending' => 'Menunggu',
                        'revision' => 'Perlu Revisi',
                        'review' => 'Dalam Review',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.nama')
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
                        'revision' => 'Perlu Revisi',
                        'review' => 'Dalam Review',
                    ])
                    ->multiple(),

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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->tooltip('Lihat detail verifikasi'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit Verifikasi')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->tooltip('Edit hasil verifikasi'),

                    Tables\Actions\Action::make('ubah_status')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->tooltip('Ubah status verifikasi')
                        ->form([
                            Forms\Components\Select::make('keputusan_baru')
                                ->label('Keputusan Baru')
                                ->options([
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'pending' => 'Menunggu',
                                    'revision' => 'Perlu Revisi',
                                    'review' => 'Dalam Review',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\Textarea::make('catatan_baru')
                                ->label('Catatan Tambahan')
                                ->placeholder('Alasan perubahan status...')
                                ->rows(3),
                        ])
                        ->modalHeading('Ubah Status Verifikasi')
                        ->modalDescription('Ubah status verifikasi untuk pemohon ini')
                        ->modalIcon('heroicon-o-arrow-path')
                        ->modalIconColor('success')
                        ->action(function ($record, array $data) {
                            $record->update([
                                'keputusan' => $data['keputusan_baru'],
                                'catatan' => $data['catatan_baru'] ?
                                    $record->catatan . "\n\n[" . now()->format('d/m/Y H:i') . "] " . $data['catatan_baru'] :
                                    $record->catatan,
                            ]);
                        }),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button()
                    ->tooltip('Pilih aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('ubah_status_bulk')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('keputusan_baru')
                                ->label('Keputusan Baru')
                                ->options([
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'pending' => 'Menunggu',
                                    'revision' => 'Perlu Revisi',
                                    'review' => 'Dalam Review',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\Textarea::make('catatan_bulk')
                                ->label('Catatan')
                                ->placeholder('Alasan perubahan status...')
                                ->rows(3),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'keputusan' => $data['keputusan_baru'],
                                    'catatan' => $data['catatan_bulk'] ?
                                        $record->catatan . "\n\n[" . now()->format('d/m/Y H:i') . "] " . $data['catatan_bulk'] :
                                        $record->catatan,
                                ]);
                            });
                        }),

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
            'index' => Pages\ListAppVerifikators::route('/'),
            'create' => Pages\CreateAppVerifikator::route('/create'),
            'view' => Pages\ViewAppVerifikator::route('/{record}'),
            'edit' => Pages\EditAppVerifikator::route('/{record}/edit'),
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
        return \Illuminate\Support\Facades\Gate::allows('view_any_app::verifikator');
    }

    /**
     * Mengecek apakah resource ditampilkan di navigasi
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}

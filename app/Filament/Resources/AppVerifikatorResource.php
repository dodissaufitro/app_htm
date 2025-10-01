<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppVerifikatorResource\Pages;
use App\Models\DataPemohon;
use App\Models\AppVerifikator;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppVerifikatorResource extends Resource
{
    protected static ?string $model = DataPemohon::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Verifikasi';

    protected static ?string $modelLabel = 'Verifikasi';

    protected static ?string $pluralModelLabel = 'Data Verifikasi';

    protected static ?string $navigationGroup = 'Proses';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('appVerifikator')
            ->with(['status', 'bank', 'appVerifikator']);
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

                        Forms\Components\TextInput::make('id_pendaftaran')
                            ->label('ID Pendaftaran')
                            ->disabled()
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

                        Forms\Components\TextInput::make('bank.nama_bank')
                            ->label('Bank')
                            ->disabled()
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

                Forms\Components\Section::make('Hasil Verifikasi')
                    ->schema([
                        Forms\Components\Select::make('appVerifikator.keputusan')
                            ->label('Keputusan Verifikasi')
                            ->options([
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'pending' => 'Menunggu',
                                'revision' => 'Perlu Revisi',
                            ])
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('status_permohonan')
                            ->label('Status Permohonan')
                            ->options(Status::orderBy('urut')->pluck('nama_status', 'kode'))
                            ->searchable()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('appVerifikator.catatan')
                            ->label('Catatan Verifikasi')
                            ->rows(4)
                            ->placeholder('Tambahkan catatan verifikasi...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
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

                Tables\Columns\BadgeColumn::make('appVerifikator.keputusan')
                    ->label('Keputusan')
                    ->colors([
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'warning' => 'pending',
                        'info' => 'revision',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'pending' => 'Menunggu',
                        'revision' => 'Perlu Revisi',
                        default => ucfirst($state)
                    }),



                Tables\Columns\TextColumn::make('gaji')
                    ->label('Gaji')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bank.nama_bank')
                    ->label('Bank')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('appVerifikator.catatan')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->appVerifikator?->catatan)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('appVerifikator.created_at')
                    ->label('Tanggal Verifikasi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('appVerifikator.keputusan')
                    ->label('Keputusan Verifikasi')
                    ->options([
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'pending' => 'Menunggu',
                        'revision' => 'Perlu Revisi',
                    ]),

                Tables\Filters\SelectFilter::make('status_permohonan')
                    ->label('Status Permohonan')
                    ->options(Status::orderBy('urut')->pluck('nama_status', 'kode')),

                Tables\Filters\SelectFilter::make('id_bank')
                    ->label('Bank')
                    ->relationship('bank', 'nama_bank'),

                Tables\Filters\Filter::make('tanggal_verifikasi')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereHas(
                                    'appVerifikator',
                                    fn($q) => $q->whereDate('created_at', '>=', $date)
                                ),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereHas(
                                    'appVerifikator',
                                    fn($q) => $q->whereDate('created_at', '<=', $date)
                                ),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('statistik_verifikasi')
                    ->label('Statistik Verifikasi')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('Statistik Data Verifikasi')
                    ->modalContent(function () {
                        $total = DataPemohon::whereHas('appVerifikator')->count();
                        $approved = AppVerifikator::where('keputusan', 'approved')->count();
                        $rejected = AppVerifikator::where('keputusan', 'rejected')->count();
                        $pending = AppVerifikator::where('keputusan', 'pending')->count();

                        return view('components.verifikasi-statistik', compact('total', 'approved', 'rejected', 'pending'));
                    })
                    ->modalWidth('lg'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('ubah_keputusan')
                    ->label('Ubah Keputusan')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('keputusan_baru')
                            ->label('Keputusan Baru')
                            ->options([
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'pending' => 'Menunggu',
                                'revision' => 'Perlu Revisi',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('catatan_baru')
                            ->label('Catatan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->appVerifikator->update([
                            'keputusan' => $data['keputusan_baru'],
                            'catatan' => $data['catatan_baru'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('ubah_keputusan_bulk')
                        ->label('Ubah Keputusan')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('keputusan_baru')
                                ->label('Keputusan Baru')
                                ->options([
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    'pending' => 'Menunggu',
                                    'revision' => 'Perlu Revisi',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->appVerifikator->update([
                                    'keputusan' => $data['keputusan_baru'],
                                ]);
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Verifikasi')
            ->emptyStateDescription('Belum ada data pemohon yang memiliki status verifikasi.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->defaultSort('appVerifikator.created_at', 'desc')
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
            'index' => Pages\ListAppVerifikators::route('/'),
            'view' => Pages\ViewAppVerifikator::route('/{record}'),
            'edit' => Pages\EditAppVerifikator::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return AppVerifikator::where('keputusan', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}

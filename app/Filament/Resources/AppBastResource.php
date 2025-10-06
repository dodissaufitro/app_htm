<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppBastResource\Pages;
use App\Filament\Resources\AppBastResource\RelationManagers;
use App\Models\AppBast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppBastResource extends Resource
{
    protected static ?string $model = AppBast::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Approval BAST';

    protected static ?string $modelLabel = 'Berita Acara Serah Terima';

    protected static ?string $pluralModelLabel = 'Berita Acara Serah Terima';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 6;

    // Menambahkan policy untuk mengatur akses berdasarkan roles
    protected static ?string $policy = \App\Policies\AppBastPolicy::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi BAST')
                    ->schema([
                        Forms\Components\Select::make('pemohon_id')
                            ->label('Pemohon')
                            ->relationship('pemohon', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('no_bast')
                            ->label('Nomor BAST')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: BAST/2024/001'),

                        Forms\Components\DatePicker::make('tgl_bast')
                            ->label('Tanggal BAST')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dokumen & Foto')
                    ->schema([
                        Forms\Components\FileUpload::make('file_bast')
                            ->label('File BAST')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->previewable()
                            ->columnSpan(2),

                        Forms\Components\FileUpload::make('foto_bast')
                            ->label('Foto BAST')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->downloadable()
                            ->previewable(),

                        Forms\Components\FileUpload::make('foto_serah_kunci')
                            ->label('Foto Serah Terima Kunci')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->downloadable()
                            ->previewable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Penerimaan')
                    ->schema([
                        Forms\Components\ToggleButtons::make('menerima_hasil_kerja')
                            ->label('Menerima Hasil Kerja')
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

                        Forms\Components\Select::make('sesuai')
                            ->label('Kesesuaian')
                            ->options([
                                'sesuai' => 'Sesuai',
                                'tidak_sesuai' => 'Tidak Sesuai',
                                'perlu_perbaikan' => 'Perlu Perbaikan',
                            ])
                            ->native(false),

                        Forms\Components\Select::make('keputusan')
                            ->label('Keputusan')
                            ->options([
                                'diterima' => 'Diterima',
                                'ditolak' => 'Ditolak',
                                'revisi' => 'Perlu Revisi',
                                'pending' => 'Menunggu',
                            ])
                            ->native(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Verifikasi & Status')
                    ->schema([
                        Forms\Components\ToggleButtons::make('verifikasi_pemohon')
                            ->label('Verifikasi Pemohon')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-shield-check',
                                false => 'heroicon-o-shield-exclamation',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'warning',
                            ]),

                        Forms\Components\ToggleButtons::make('dihuni')
                            ->label('Status Hunian')
                            ->boolean()
                            ->grouped()
                            ->icons([
                                true => 'heroicon-o-home',
                                false => 'heroicon-o-home-modern',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'gray',
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Catatan & Komplain')
                    ->schema([
                        Forms\Components\Textarea::make('komplain')
                            ->label('Komplain/Keluhan')
                            ->rows(3)
                            ->placeholder('Masukkan komplain atau keluhan jika ada...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan Tambahan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan mengenai BAST...')
                            ->columnSpanFull(),
                    ]),
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

                Tables\Columns\TextColumn::make('no_bast')
                    ->label('No. BAST')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('tgl_bast')
                    ->label('Tanggal BAST')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                Tables\Columns\IconColumn::make('menerima_hasil_kerja')
                    ->label('Diterima')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('sesuai')
                    ->label('Kesesuaian')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sesuai' => 'success',
                        'tidak_sesuai' => 'danger',
                        'perlu_perbaikan' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sesuai' => 'Sesuai',
                        'tidak_sesuai' => 'Tidak Sesuai',
                        'perlu_perbaikan' => 'Perlu Perbaikan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('keputusan')
                    ->label('Keputusan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'diterima' => 'success',
                        'ditolak' => 'danger',
                        'revisi' => 'warning',
                        'pending' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                        'revisi' => 'Perlu Revisi',
                        'pending' => 'Menunggu',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('verifikasi_pemohon')
                    ->label('Verifikasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\IconColumn::make('dihuni')
                    ->label('Dihuni')
                    ->boolean()
                    ->trueIcon('heroicon-o-home')
                    ->falseIcon('heroicon-o-home-modern')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('file_bast')
                    ->label('File')
                    ->formatStateUsing(fn($state) => $state ? 'Ada' : 'Tidak Ada')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray')
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
                        'diterima' => 'Diterima',
                        'ditolak' => 'Ditolak',
                        'revisi' => 'Perlu Revisi',
                        'pending' => 'Menunggu',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('sesuai')
                    ->label('Kesesuaian')
                    ->options([
                        'sesuai' => 'Sesuai',
                        'tidak_sesuai' => 'Tidak Sesuai',
                        'perlu_perbaikan' => 'Perlu Perbaikan',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('menerima_hasil_kerja')
                    ->label('Menerima Hasil Kerja'),

                Tables\Filters\TernaryFilter::make('verifikasi_pemohon')
                    ->label('Verifikasi Pemohon'),

                Tables\Filters\TernaryFilter::make('dihuni')
                    ->label('Status Hunian'),

                Tables\Filters\Filter::make('tgl_bast')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_bast', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_bast', '<=', $date),
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
            ->defaultSort('tgl_bast', 'desc');
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
            'index' => Pages\ListAppBasts::route('/'),
            'create' => Pages\CreateAppBast::route('/create'),
            'view' => Pages\ViewAppBast::route('/{record}'),
            'edit' => Pages\EditAppBast::route('/{record}/edit'),
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
        return Gate::allows('view_any_app::bast');
    }

    /**
     * Mengecek apakah resource ditampilkan di navigasi
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}

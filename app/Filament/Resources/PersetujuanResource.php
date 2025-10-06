<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersetujuanResource\Pages;
use App\Models\DataPemohon;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PersetujuanResource extends Resource
{
    protected static ?string $model = DataPemohon::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'UPDP';

    protected static ?string $modelLabel = 'Persetujuan';

    protected static ?string $pluralModelLabel = 'Data Persetujuan';

    protected static ?string $navigationGroup = 'Menunggu Persetujuan';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user || !($user instanceof \App\Models\User)) {
            return false;
        }

        // Only show for users with appropriate roles
        return $user->hasRole(['Super Admin', 'Admin', 'Approver', 'Verifikator']);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if (!$user || !($user instanceof \App\Models\User)) {
            return '0';
        }

        // Start with base persetujuan query
        $query = static::getModel()::forPersetujuan();

        // Apply user status access control (same as getEloquentQuery)
        if (!empty($user->allowed_status)) {
            // Ensure allowed_status is an array
            $allowedStatus = $user->allowed_status;
            if (is_string($allowedStatus)) {
                $allowedStatus = json_decode($allowedStatus, true);
            }

            if (is_array($allowedStatus) && !empty($allowedStatus)) {
                $query->whereIn('status_permohonan', $allowedStatus);
            }
        }

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->forPersetujuan()
            ->with(['status', 'bank']);

        // Apply user status access control
        $user = Auth::user();
        if ($user && !empty($user->allowed_status)) {
            // Ensure allowed_status is an array
            $allowedStatus = $user->allowed_status;
            if (is_string($allowedStatus)) {
                $allowedStatus = json_decode($allowedStatus, true);
            }

            if (is_array($allowedStatus) && !empty($allowedStatus)) {
                $query->whereIn('status_permohonan', $allowedStatus);
            }
        }

        return $query;
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

                        Forms\Components\Select::make('status_permohonan')
                            ->label('Status Persetujuan')
                            ->options(Status::orderBy('urut')->pluck('nama_status', 'kode'))
                            ->searchable()
                            ->required()
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

                        Forms\Components\TextInput::make('id_bank')
                            ->label('Bank')
                            ->disabled()
                            ->formatStateUsing(fn($record) => $record->bank?->nama_bank ?? '-')
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

                Forms\Components\Section::make('Catatan Persetujuan')
                    ->schema([
                        Forms\Components\Textarea::make('reason_of_choose_location')
                            ->label('Alasan Memilih Lokasi')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Catatan Tambahan')
                            ->rows(4)
                            ->placeholder('Tambahkan catatan untuk persetujuan ini...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
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

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone'),

                Tables\Columns\TextColumn::make('gaji')
                    ->label('Gaji')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status.nama_status')
                    ->label('Status')
                    ->color('success')
                    ->formatStateUsing(fn($record) => $record->status?->nama_status ?? 'Persetujuan'),

                Tables\Columns\TextColumn::make('bank.nama_bank')
                    ->label('Bank')
                    ->badge()
                    ->color(fn($record) => match ($record->bank?->id) {
                        'BCA' => 'cyan',
                        'BNI' => 'orange',
                        'BRI' => 'emerald',
                        'MANDIRI' => 'yellow',
                        'BTN' => 'purple',
                        'DKI' => 'pink',
                        default => 'info'
                    }),

                Tables\Columns\TextColumn::make('harga_unit')
                    ->label('Harga Unit')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lokasi_rumah')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('count_of_vehicle1')
                    ->label('Kendaraan R2')
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('count_of_vehicle2')
                    ->label('Kendaraan R4')
                    ->color('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bapenda_updated_at')
                    ->label('Update Bapenda')
                    ->dateTime('d/m/Y H:i')
                    ->color(fn($record) => $record->bapenda_updated_at ? 'success' : 'danger')
                    ->placeholder('Belum diupdate')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_bank')
                    ->label('Bank')
                    ->relationship('bank', 'nama_bank'),

                Tables\Filters\Filter::make('gaji')
                    ->form([
                        Forms\Components\TextInput::make('gaji_min')
                            ->label('Gaji Minimum')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('gaji_max')
                            ->label('Gaji Maksimum')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['gaji_min'],
                                fn(Builder $query, $gaji): Builder => $query->where('gaji', '>=', $gaji),
                            )
                            ->when(
                                $data['gaji_max'],
                                fn(Builder $query, $gaji): Builder => $query->where('gaji', '<=', $gaji),
                            );
                    }),

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
            ->headerActions([
                Tables\Actions\Action::make('statistik_persetujuan')
                    ->label('Statistik Persetujuan')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->modalHeading('Statistik Data Persetujuan')
                    ->modalContent(function () {
                        $total = DataPemohon::where('status_permohonan', '1')->count();
                        $today = DataPemohon::where('status_permohonan', '1')
                            ->whereDate('created_at', today())->count();
                        $thisMonth = DataPemohon::where('status_permohonan', '1')
                            ->whereMonth('created_at', now()->month)->count();

                        return view('components.persetujuan-statistik', compact('total', 'today', 'thisMonth'));
                    })
                    ->modalWidth('lg'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Primary Actions
                    Tables\Actions\ViewAction::make()
                        ->label('👁️ Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->tooltip('Lihat detail lengkap permohonan'),

                    Tables\Actions\EditAction::make()
                        ->label('✏️ Edit Persetujuan')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->tooltip('Edit data persetujuan dan status'),

                    // Separator
                    Tables\Actions\Action::make('separator1')
                        ->label('────────────────')
                        ->disabled()
                        ->color('gray'),

                    // Data Management Actions
                    Tables\Actions\Action::make('refresh_bapenda')
                        ->label('🔄 Refresh Bapenda')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->tooltip('Perbarui data kendaraan dari server Bapenda')
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => '🔄 Refresh Data Bapenda - ' . $record->nama)
                        ->modalDescription('Sistem akan mengambil data terbaru dari server Bapenda untuk memperbarui informasi kendaraan dan aset pemohon ini. Proses ini mungkin membutuhkan beberapa detik.')
                        ->modalIcon('heroicon-o-arrow-path')
                        ->modalWidth('lg')
                        ->modalSubmitActionLabel('✅ Ya, Refresh Data')
                        ->modalCancelActionLabel('❌ Batal')
                        ->action(function ($record) {
                            try {
                                // Show loading notification
                                \Filament\Notifications\Notification::make()
                                    ->title('⏳ Sedang Memproses...')
                                    ->body('Mengambil data terbaru dari server Bapenda')
                                    ->info()
                                    ->duration(3000)
                                    ->send();

                                $success = \App\Observers\BapendaObserver::refreshBapendaData($record);

                                if ($success) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('✅ Data Bapenda Berhasil Diperbarui')
                                        ->body("Data kendaraan untuk {$record->nama} telah diperbarui dari server Bapenda")
                                        ->success()
                                        ->duration(5000)
                                        ->actions([
                                            \Filament\Notifications\Actions\Action::make('view')
                                                ->button()
                                                ->url(static::getUrl('view', ['record' => $record]))
                                                ->label('👁️ Lihat Detail'),
                                        ])
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('❌ Gagal Memperbarui Data')
                                        ->body('Tidak dapat terhubung ke server Bapenda atau data tidak ditemukan')
                                        ->danger()
                                        ->duration(7000)
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('⚠️ Terjadi Kesalahan')
                                    ->body('Error: ' . $e->getMessage())
                                    ->danger()
                                    ->duration(7000)
                                    ->send();
                            }
                        }),

                    // Separator
                    Tables\Actions\Action::make('separator2')
                        ->label('────────────────')
                        ->disabled()
                        ->color('gray'),

                    // Status Management Action
                    Tables\Actions\Action::make('ubah_status')
                        ->label('📝 Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('purple')
                        ->tooltip('Ubah status permohonan dengan catatan')
                        ->modalHeading(fn($record) => '📝 Ubah Status Permohonan - ' . $record->nama)
                        ->modalDescription('Pilih status baru untuk permohonan ini dan berikan catatan perubahan yang akan tercatat dalam sistem.')
                        ->modalIcon('heroicon-o-arrow-path')
                        ->modalWidth('2xl')
                        ->modalSubmitActionLabel('✅ Simpan Perubahan')
                        ->modalCancelActionLabel('❌ Batal')
                        ->form([
                            Forms\Components\Section::make('📋 Informasi Permohonan')
                                ->description('Detail pemohon dan status saat ini')
                                ->schema([
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Placeholder::make('pemohon_info')
                                                ->label('👤 Pemohon')
                                                ->content(function ($record) {
                                                    return "**{$record->nama}**\nNIK: {$record->nik}\nHP: {$record->no_hp}";
                                                })
                                                ->extraAttributes(['style' => 'white-space: pre-line; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;']),

                                            Forms\Components\Placeholder::make('current_status')
                                                ->label('📊 Status Saat Ini')
                                                ->content(fn($record) => $record->status?->nama_status ?? 'Tidak Diketahui')
                                                ->extraAttributes(['style' => 'background: #dcfce7; padding: 12px; border-radius: 8px; font-weight: bold; color: #059669; border: 1px solid #16a34a;']),

                                            Forms\Components\Placeholder::make('registration_info')
                                                ->label('📅 Info Pendaftaran')
                                                ->content(function ($record) {
                                                    return "ID: {$record->id_pendaftaran}\nTanggal: " . $record->created_at?->format('d/m/Y H:i');
                                                })
                                                ->extraAttributes(['style' => 'white-space: pre-line; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;']),
                                        ]),
                                ])
                                ->collapsible()
                                ->collapsed(),

                            Forms\Components\Section::make('🔄 Perubahan Status')
                                ->description('Pilih status baru dan berikan alasan perubahan')
                                ->icon('heroicon-o-cog-6-tooth')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('status_baru')
                                                ->label('📌 Status Baru')
                                                ->options(function () {
                                                    return Status::orderBy('urut')
                                                        ->get()
                                                        ->mapWithKeys(function ($status) {
                                                            return [$status->kode => "🏷️ {$status->nama_status} - {$status->keterangan}"];
                                                        })
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->required()
                                                ->native(false)
                                                ->placeholder('Pilih status baru untuk permohonan ini')
                                                ->live()
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if ($state) {
                                                        $status = Status::where('kode', $state)->first();
                                                        if ($status) {
                                                            $set('status_preview', "✅ {$status->nama_status}");
                                                            $set('status_description', "💡 {$status->keterangan}");
                                                        }
                                                    }
                                                }),

                                            Forms\Components\Placeholder::make('status_info')
                                                ->label('ℹ️ Informasi Status')
                                                ->content(function ($get) {
                                                    $preview = $get('status_preview');
                                                    $description = $get('status_description');

                                                    if ($preview && $description) {
                                                        return "{$preview}\n{$description}";
                                                    }
                                                    return '🔍 Pilih status untuk melihat informasi';
                                                })
                                                ->extraAttributes(['style' => 'background: #eff6ff; padding: 12px; border-radius: 8px; border: 1px solid #3b82f6; white-space: pre-line; font-size: 14px;']),
                                        ]),

                                    Forms\Components\Textarea::make('catatan')
                                        ->label('📝 Catatan Perubahan')
                                        ->required()
                                        ->rows(4)
                                        ->placeholder('Jelaskan alasan perubahan status ini...\nContoh: Dokumen sudah lengkap dan sesuai syarat')
                                        ->helperText('💡 Catatan ini akan tercatat dalam log perubahan status dan dapat dilihat oleh user lain')
                                        ->columnSpanFull(),

                                    Forms\Components\Placeholder::make('warning_info')
                                        ->label('⚠️ Peringatan Penting')
                                        ->content('🔒 Perubahan status ini akan tercatat dalam sistem dan tidak dapat dibatalkan. Pastikan Anda telah memverifikasi data dengan benar sebelum melanjutkan.')
                                        ->extraAttributes(['style' => 'background: #fef3c7; padding: 12px; border-radius: 8px; border: 1px solid #f59e0b; color: #92400e; font-size: 14px;'])
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->action(function ($record, array $data) {
                            $oldStatus = $record->status?->nama_status ?? 'Tidak Diketahui';
                            $newStatus = Status::where('kode', $data['status_baru'])->first();

                            $record->update([
                                'status_permohonan' => $data['status_baru'],
                                'keterangan' => $data['catatan'],
                            ]);

                            // Log the status change if activity log is available
                            

                            \Filament\Notifications\Notification::make()
                                ->title('✅ Status Berhasil Diubah')
                                ->body("Status permohonan **{$record->nama}** telah diubah dari '**{$oldStatus}**' menjadi '**{$newStatus?->nama_status}**'")
                                ->success()
                                ->duration(6000)
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('view')
                                        ->button()
                                        ->url(static::getUrl('view', ['record' => $record]))
                                        ->label('👁️ Lihat Detail'),
                                ])
                                ->send();
                        }),
                ])
                    ->label('⚙️ Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button()
                    ->outlined(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('ubah_status_bulk')
                        ->label('📝 Ubah Status Massal')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->modalHeading('📝 Ubah Status Massal')
                        ->modalDescription('Ubah status untuk semua permohonan yang dipilih sekaligus dengan catatan yang sama.')
                        ->modalIcon('heroicon-o-arrow-path')
                        ->modalWidth('xl')
                        ->modalSubmitActionLabel('✅ Ubah Status Semua')
                        ->modalCancelActionLabel('❌ Batal')
                        ->form([
                            Forms\Components\Section::make('📊 Informasi Perubahan')
                                ->description('Ringkasan data yang akan diubah')
                                ->schema([
                                    Forms\Components\Placeholder::make('selected_count')
                                        ->label('🎯 Jumlah Data Terpilih')
                                        ->content(fn($records) => "**" . count($records) . " permohonan** akan diubah statusnya")
                                        ->extraAttributes(['style' => 'background: #f3f4f6; padding: 12px; border-radius: 8px; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db;']),
                                ])
                                ->collapsible(),

                            Forms\Components\Section::make('⚙️ Pengaturan Status')
                                ->description('Pilih status baru yang akan diterapkan ke semua data terpilih')
                                ->schema([
                                    Forms\Components\Select::make('status_baru')
                                        ->label('📌 Status Baru')
                                        ->options(function () {
                                            return Status::orderBy('urut')
                                                ->get()
                                                ->mapWithKeys(function ($status) {
                                                    return [$status->kode => "🏷️ {$status->nama_status} - {$status->keterangan}"];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->required()
                                        ->native(false)
                                        ->placeholder('Pilih status baru untuk semua data terpilih')
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            if ($state) {
                                                $status = Status::where('kode', $state)->first();
                                                if ($status) {
                                                    $set('bulk_preview', "✅ Semua data akan diubah ke: **{$status->nama_status}**\n💡 {$status->keterangan}");
                                                }
                                            }
                                        }),

                                    Forms\Components\Placeholder::make('bulk_preview')
                                        ->label('🔍 Preview Perubahan')
                                        ->content(fn($get) => $get('bulk_preview') ?? '🔍 Pilih status untuk melihat preview')
                                        ->visible(fn($get) => !empty($get('status_baru')))
                                        ->extraAttributes(['style' => 'background: #ecfdf5; padding: 12px; border-radius: 8px; border: 1px solid #10b981; color: #065f46; white-space: pre-line;']),

                                    Forms\Components\Textarea::make('catatan')
                                        ->label('📝 Catatan Perubahan')
                                        ->rows(3)
                                        ->placeholder('Jelaskan alasan perubahan status massal ini...\nContoh: Batch verifikasi dokumen bulan ini')
                                        ->helperText('💡 Catatan yang sama akan diterapkan untuk semua permohonan yang dipilih'),

                                    Forms\Components\Placeholder::make('bulk_warning')
                                        ->label('⚠️ Peringatan')
                                        ->content('🔒 Operasi ini akan mengubah status semua data terpilih secara bersamaan dan tidak dapat dibatalkan. Pastikan pilihan Anda sudah benar.')
                                        ->extraAttributes(['style' => 'background: #fef2f2; padding: 12px; border-radius: 8px; border: 1px solid #ef4444; color: #dc2626; font-size: 14px;']),
                                ]),
                        ])
                        ->action(function ($records, array $data) {
                            $newStatus = Status::where('kode', $data['status_baru'])->first();
                            $count = 0;

                            $records->each(function ($record) use ($data, &$count) {
                                $record->update([
                                    'status_permohonan' => $data['status_baru'],
                                    'keterangan' => $data['catatan'] ?? null,
                                ]);

                                // Log each change if activity log is available
                                

                                $count++;
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('✅ Status Massal Berhasil Diubah')
                                ->body("**{$count} permohonan** telah berhasil diubah statusnya menjadi '**{$newStatus?->nama_status}**'")
                                ->success()
                                ->duration(6000)
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Persetujuan')
            ->emptyStateDescription('Belum ada pemohon dengan status persetujuan.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListPersetujuans::route('/'),
            'view' => Pages\ViewPersetujuan::route('/{record}'),
            'edit' => Pages\EditPersetujuan::route('/{record}/edit'),
        ];
    }
}

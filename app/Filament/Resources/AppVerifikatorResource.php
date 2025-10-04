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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class AppVerifikatorResource extends Resource
{
    protected static ?string $model = DataPemohon::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Verifikasi';

    protected static ?string $modelLabel = 'Verifikasi';

    protected static ?string $pluralModelLabel = 'Data Verifikasi';

    protected static ?string $navigationGroup = 'Aplikasi';



    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('appVerifikator')
            ->with(['bank', 'appVerifikator'])
            ->select(['id', 'id_pendaftaran', 'nama', 'nik', 'no_hp', 'gaji', 'status_permohonan', 'id_bank'])
            ->limit(1000); // Batasi hasil untuk mencegah timeout
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
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                // Status akan diupdate otomatis berdasarkan keputusan
                                // Tidak perlu manual select status_permohonan
                            })
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('status_display')
                            ->label('Status Permohonan Akan Berubah')
                            ->content(function ($get) {
                                $keputusan = $get('appVerifikator.keputusan');
                                $statusMapping = [
                                    'pending' => 'Ditunda (1)',
                                    'approved' => 'Disetujui (2)',
                                    'rejected' => 'Ditolak (3)',
                                    'revision' => 'Ditunda (1)',
                                ];
                                return $statusMapping[$keputusan] ?? 'Pilih keputusan terlebih dahulu';
                            })
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
                    ->getStateUsing(function ($record) {
                        $verifikator = $record->latestAppVerifikator ?? $record->appVerifikator()->latest()->first();
                        return $verifikator?->catatan ?? '-';
                    })
                    ->tooltip(function ($record) {
                        $verifikator = $record->latestAppVerifikator ?? $record->appVerifikator()->latest()->first();
                        return $verifikator?->catatan ?? 'Belum ada catatan';
                    })
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
                    ->options([
                        '1' => 'Ditunda',
                        '2' => 'Disetujui',
                        '3' => 'Ditolak',
                    ]),

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
                Tables\Actions\Action::make('total_data')
                    ->label('Total Data')
                    ->icon('heroicon-o-chart-bar')
                    ->color('success')
                    ->modalHeading('Total Data Verifikasi')
                    ->modalContent(function () {
                        $total = DataPemohon::whereHas('appVerifikator')->count();
                        $approved = AppVerifikator::where('keputusan', 'approved')->count();
                        $rejected = AppVerifikator::where('keputusan', 'rejected')->count();
                        $pending = AppVerifikator::where('keputusan', 'pending')->count();
                        $revision = AppVerifikator::where('keputusan', 'revision')->count();

                        return new HtmlString(
                            '<div class="space-y-4">' .
                                '<div class="text-center">' . self::generateTotalDataSpan($total) . '</div>' .
                                '<div class="grid grid-cols-2 gap-4">' .
                                '<div class="text-center">' . self::generateKeputusanCountSpan('approved', $approved) . '</div>' .
                                '<div class="text-center">' . self::generateKeputusanCountSpan('rejected', $rejected) . '</div>' .
                                '<div class="text-center">' . self::generateKeputusanCountSpan('pending', $pending) . '</div>' .
                                '<div class="text-center">' . self::generateKeputusanCountSpan('revision', $revision) . '</div>' .
                                '</div>' .
                                '</div>'
                        );
                    })
                    ->modalWidth('md'),

                Tables\Actions\Action::make('statistik_verifikasi')
                    ->label('Statistik Verifikasi')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('Statistik Data Verifikasi')
                    ->modalContent(function () {
                        // Optimalkan query statistik
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
                        $verifikator = $record->latestAppVerifikator ?? $record->appVerifikator()->latest()->first();
                        if ($verifikator) {
                            $verifikator->update([
                                'keputusan' => $data['keputusan_baru'],
                                'catatan' => $data['catatan_baru'],
                            ]);
                        }
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
                                $verifikator = $record->latestAppVerifikator ?? $record->appVerifikator()->latest()->first();
                                if ($verifikator) {
                                    $verifikator->update([
                                        'keputusan' => $data['keputusan_baru'],
                                    ]);
                                }
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data Verifikasi')
            ->emptyStateDescription('Belum ada data pemohon yang memiliki status verifikasi.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->defaultSort('id', 'desc')  // Sort by data_pemohon id instead of subquery
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
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
            'index' => Pages\ListAppVerifikators::route('/'),
            'view' => Pages\ViewAppVerifikator::route('/{record}'),
            'edit' => Pages\EditAppVerifikator::route('/{record}/edit'),
        ];
    }

    /**
     * Generate total data count span
     */
    public static function generateTotalDataSpan(int $count): string
    {
        return "<div class=\"text-center p-4\">" .
            "<span class=\"inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200\">" .
            "<svg class=\"w-5 h-5 mr-2\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">" .
            "<path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\"></path>" .
            "</svg>" .
            "Total Data: {$count}" .
            "</span>" .
            "</div>";
    }

    /**
     * Generate keputusan count span
     */
    public static function generateKeputusanCountSpan(string $keputusan, int $count): string
    {
        $config = match ($keputusan) {
            'approved' => [
                'text' => 'Disetujui',
                'class' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                'icon' => '✓'
            ],
            'rejected' => [
                'text' => 'Ditolak',
                'class' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                'icon' => '✗'
            ],
            'pending' => [
                'text' => 'Menunggu',
                'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                'icon' => '⏳'
            ],
            'revision' => [
                'text' => 'Perlu Revisi',
                'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                'icon' => '📝'
            ],
            default => [
                'text' => ucfirst($keputusan),
                'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                'icon' => '❓'
            ]
        };

        return "<div class=\"p-3\">" .
            "<span class=\"inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium {$config['class']}\">" .
            "{$config['icon']} {$config['text']}: {$count}" .
            "</span>" .
            "</div>";
    }

    /**
     * Generate summary statistics span
     */
    public static function generateSummarySpan(): string
    {
        $total = DataPemohon::whereHas('appVerifikator')->count();
        $approved = AppVerifikator::where('keputusan', 'approved')->count();
        $rejected = AppVerifikator::where('keputusan', 'rejected')->count();
        $pending = AppVerifikator::where('keputusan', 'pending')->count();

        $approvedPercent = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
        $rejectedPercent = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;
        $pendingPercent = $total > 0 ? round(($pending / $total) * 100, 1) : 0;

        return "<div class=\"grid grid-cols-4 gap-4 p-4\">" .
            "<div class=\"text-center\">" .
            "<span class=\"inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-blue-100 text-blue-800\">" .
            "📊 Total: {$total}" .
            "</span>" .
            "</div>" .
            "<div class=\"text-center\">" .
            "<span class=\"inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-green-100 text-green-800\">" .
            "✓ {$approved} ({$approvedPercent}%)" .
            "</span>" .
            "</div>" .
            "<div class=\"text-center\">" .
            "<span class=\"inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-red-100 text-red-800\">" .
            "✗ {$rejected} ({$rejectedPercent}%)" .
            "</span>" .
            "</div>" .
            "<div class=\"text-center\">" .
            "<span class=\"inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-yellow-100 text-yellow-800\">" .
            "⏳ {$pending} ({$pendingPercent}%)" .
            "</span>" .
            "</div>" .
            "</div>";
    }

    public static function getNavigationBadge(): ?string
    {
        $total = DataPemohon::whereHas('appVerifikator')->count();
        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    /**
     * Mutate form data before saving to handle relationship data properly
     */
    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Debug: Log data yang masuk
        Log::info('AppVerifikatorResource mutateFormDataBeforeSave - Incoming data: ', $data);

        // AGRESIF: Hapus field status dengan semua variasi
        $statusVariations = ['status', 'Status', 'STATUS', 'keputusan'];
        foreach ($statusVariations as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
                Log::info("AppVerifikatorResource - Removed status variation field: {$field}");
            }
        }

        // Remove any invalid field if present dan hanya pertahankan yang valid
        $allowedFields = ['status_permohonan', 'appVerifikator', 'nama', 'nik', 'no_hp', 'gaji', 'id_bank'];

        // Filter hanya field yang diizinkan untuk mencegah mass assignment
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields) || strpos($key, 'appVerifikator.') === 0) {
                $filteredData[$key] = $value;
            } else {
                Log::info("AppVerifikatorResource - Filtering out field: {$key}");
            }
        }

        // Hapus field status yang bermasalah secara eksplisit lagi
        $forbiddenFields = ['status', 'id', 'created_at', 'updated_at'];
        foreach ($forbiddenFields as $field) {
            if (isset($filteredData[$field])) {
                unset($filteredData[$field]);
                Log::info("AppVerifikatorResource - Removed forbidden field: {$field}");
            }
        }

        // Convert 'keputusan' to proper status_permohonan if present
        if (isset($filteredData['appVerifikator']['keputusan'])) {
            $statusMapping = [
                'pending' => '1',    // Ditunda
                'approved' => '2',   // Disetujui
                'rejected' => '3',   // Ditolak
                'revision' => '1',   // Perlu Revisi = Ditunda
            ];

            $keputusan = $filteredData['appVerifikator']['keputusan'];
            if (isset($statusMapping[$keputusan])) {
                $filteredData['status_permohonan'] = $statusMapping[$keputusan];
                Log::info("AppVerifikatorResource - Mapped keputusan {$keputusan} to status_permohonan {$statusMapping[$keputusan]}");
            }
        }

        Log::info('AppVerifikatorResource mutateFormDataBeforeSave - Final data: ', $filteredData);

        return $filteredData;
    }
}

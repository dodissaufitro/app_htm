<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Status;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Kelola User';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $navigationGroup = 'Manajemen Akses';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user instanceof User && $user->hasRole('Super Admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi User')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('urutan')
                            ->label('Urutan Developer')
                            ->numeric()
                            ->default(0)
                            ->helperText('0 = Tidak dalam workflow, 1+ = Urutan dalam workflow developer')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('view_workflow')
                                    ->icon('heroicon-o-eye')
                                    ->tooltip('Lihat Workflow')
                                    ->action(function () {
                                        // This will show workflow info in a notification
                                        $workflowUsers = User::getDeveloperWorkflowUsers();
                                        $workflow = $workflowUsers->map(fn($u) => "{$u->urutan}. {$u->name}")->join(' → ');
                                        \Filament\Notifications\Notification::make()
                                            ->title('Developer Workflow')
                                            ->body($workflow ?: 'Belum ada user dalam workflow')
                                            ->send();
                                    })
                            ),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Roles & Permissions')
                    ->description('Pilih roles yang akan diberikan kepada user ini.')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->helperText('Pilih satu atau lebih roles untuk user ini. Roles: Super Admin (full access), Admin (manage data), Verifikator (verify), Approver (approve), Operator (input), Viewer (read-only)'),

                        Forms\Components\Select::make('lokasi_hunian')
                            ->label('Lokasi Hunian Developer')
                            ->options(function () {
                                return \App\Models\DataHunian::select('nama_pemukiman', 'id')
                                    ->distinct()
                                    ->orderBy('nama_pemukiman')
                                    ->pluck('nama_pemukiman', 'id')
                                    ->toArray();
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih lokasi hunian yang akan ditangani developer ini')
                            ->helperText('Lokasi hunian yang akan menjadi tanggung jawab developer ini')
                            ->visible(
                                fn(Forms\Get $get): bool =>
                                collect($get('roles'))->contains(
                                    fn($roleId) =>
                                    Role::find($roleId)?->name === 'Developer'
                                )
                            )
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Akses Kontrol Status')
                    ->description('Pilih status yang dapat diakses oleh user ini. Jika tidak ada yang dipilih, user dapat mengakses semua status.')
                    ->schema([
                        Forms\Components\CheckboxList::make('allowed_status')
                            ->label('Status yang Diizinkan')
                            ->options(function () {
                                return Status::orderBy('urut')->pluck('nama_status', 'kode')->toArray();
                            })
                            ->descriptions(function () {
                                return Status::orderBy('urut')->pluck('keterangan', 'kode')->toArray();
                            })
                            ->columns(3)
                            ->helperText('Kosongkan untuk memberikan akses ke semua status'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('urutan')
                    ->label('Urutan')
                    ->getStateUsing(function (User $record) {
                        return $record->urutan > 0 ? $record->urutan : 'Tidak dalam workflow';
                    })
                    ->color(function (User $record) {
                        return $record->urutan > 0 ? 'success' : 'gray';
                    })
                    ->sortable(),
                Tables\Columns\TagsColumn::make('lokasi_hunian_names')
                    ->label('Lokasi Developer')
                    ->getStateUsing(function (User $record) {
                        if (!$record->hasRole('Developer') || empty($record->lokasi_hunian)) {
                            return [];
                        }
                        return $record->lokasi_hunian_names;
                    })
                    ->color('info')
                    ->separator(', ')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->placeholder('Semua Lokasi')
                    ->toggleable(),
                Tables\Columns\TagsColumn::make('roles.name')
                    ->label('Roles')
                    ->color('info')
                    ->separator(', ')
                    ->limitList(3)
                    ->expandableLimitedList(),
                // Tables\Columns\TagsColumn::make('allowed_status_names')
                //     ->label('Status Diizinkan')
                //     ->getStateUsing(function (User $record) {
                //         if (empty($record->allowed_status)) {
                //             return ['Semua Status'];
                //         }

                //         // Ensure allowed_status is an array
                //         $allowedStatus = $record->allowed_status;
                //         if (is_string($allowedStatus)) {
                //             $allowedStatus = json_decode($allowedStatus, true);
                //         }

                //         if (is_array($allowedStatus) && !empty($allowedStatus)) {
                //             return Status::whereIn('kode', $allowedStatus)
                //                 ->orderBy('urut')
                //                 ->pluck('nama_status')
                //                 ->toArray();
                //         }

                //         return ['Semua Status'];
                //     })
                //     ->color(function (User $record) {
                //         return empty($record->allowed_status) ? 'success' : 'primary';
                //     }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Filter by Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('urutan')
                    ->label('Filter by Urutan')
                    ->options([
                        0 => 'Tidak dalam workflow',
                        1 => 'Urutan 1 (Verifikator Awal)',
                        2 => 'Urutan 2 (Developer)',
                        3 => 'Urutan 3 (Bank Analisis)',
                        4 => 'Urutan 4 (Supervisor)',
                        5 => 'Urutan 5 (Manager)',
                    ]),
                Tables\Filters\Filter::make('in_workflow')
                    ->label('Dalam Workflow')
                    ->query(fn(Builder $query): Builder => $query->where('urutan', '>', 0)),
                Tables\Filters\Filter::make('has_limited_access')
                    ->label('Akses Terbatas')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('allowed_status')),
                Tables\Filters\Filter::make('full_access')
                    ->label('Akses Penuh')
                    ->query(fn(Builder $query): Builder => $query->whereNull('allowed_status')),
                Tables\Filters\Filter::make('developer_with_locations')
                    ->label('Developer dengan Lokasi')
                    ->query(
                        fn(Builder $query): Builder =>
                        $query->whereHas('roles', fn($q) => $q->where('name', 'Developer'))
                            ->whereNotNull('lokasi_hunian')
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Detail')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->tooltip('Lihat detail lengkap user'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit User')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->tooltip('Edit informasi user'),

                    Tables\Actions\Action::make('manage_roles')
                        ->label('Kelola Roles')
                        ->icon('heroicon-o-user-group')
                        ->color('success')
                        ->tooltip('Kelola roles dan permissions user')
                        ->modalHeading(fn(User $record) => 'Kelola Roles - ' . $record->name)
                        ->modalDescription('Atur roles yang dimiliki oleh user ini. Roles menentukan hak akses dan permissions user dalam sistem.')
                        ->modalIcon('heroicon-o-user-group')
                        ->modalWidth('lg')
                        ->form([
                            Forms\Components\Section::make('Informasi User')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Placeholder::make('name')
                                                ->label('Nama User')
                                                ->content(fn(User $record) => $record->name),
                                            Forms\Components\Placeholder::make('email')
                                                ->label('Email')
                                                ->content(fn(User $record) => $record->email),
                                        ]),
                                    Forms\Components\Placeholder::make('current_roles')
                                        ->label('Roles Saat Ini')
                                        ->content(fn(User $record) => $record->roles->count() > 0 ?
                                            $record->roles->pluck('name')->map(fn($role) => "• {$role}")->join("\n") :
                                            'Belum memiliki roles')
                                        ->extraAttributes(['style' => 'white-space: pre-line; background: #f3f4f6; padding: 8px; border-radius: 6px;']),
                                ])
                                ->collapsible()
                                ->collapsed(),

                            Forms\Components\Section::make('Pengaturan Roles')
                                ->description('Pilih roles yang akan diberikan kepada user ini.')
                                ->icon('heroicon-o-shield-check')
                                ->schema([
                                    Forms\Components\Select::make('roles')
                                        ->label('Roles')
                                        ->relationship('roles', 'name')
                                        ->options(Role::all()->pluck('name', 'id'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Pilih roles untuk user ini')
                                        ->helperText('Roles tersedia: Super Admin (akses penuh), Admin (kelola data), Verifikator (verifikasi), Approver (approve), Operator (input), Viewer (baca saja)')
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            if ($state) {
                                                $roleNames = Role::whereIn('id', $state)->pluck('name')->join(', ');
                                                $set('role_preview', $roleNames);
                                            }
                                        }),

                                    Forms\Components\Placeholder::make('role_preview')
                                        ->label('Preview Roles Terpilih')
                                        ->content(fn($get) => $get('role_preview') ?: 'Belum ada roles yang dipilih')
                                        ->visible(fn($get) => !empty($get('roles')))
                                        ->extraAttributes(['style' => 'background: #dbeafe; padding: 8px; border-radius: 6px; color: #1e40af;']),
                                ]),
                        ])
                        ->fillForm(fn(User $record): array => [
                            'roles' => $record->roles()->pluck('id')->toArray(),
                            'role_preview' => $record->roles->pluck('name')->join(', '),
                        ])
                        ->action(function (array $data, User $record): void {
                            $oldRoles = $record->roles->pluck('name')->toArray();
                            $newRoles = Role::whereIn('id', $data['roles'] ?? [])->pluck('name')->toArray();

                            $record->syncRoles($newRoles);

                            \Filament\Notifications\Notification::make()
                                ->title('Roles Berhasil Diperbarui')
                                ->body("Roles user {$record->name} telah diperbarui")
                                ->success()
                                ->duration(5000)
                                ->send();
                        }),

                    Tables\Actions\Action::make('manage_status')
                        ->label('Kelola Status')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('purple')
                        ->tooltip('Atur status yang dapat diakses user')
                        ->visible(function () {
                            $currentUser = Auth::user();
                            return $currentUser && $currentUser instanceof User && $currentUser->urutan === 1;
                        })
                        ->modalHeading(fn(User $record) => 'Kelola Akses Status - ' . $record->name)
                        ->modalDescription('Atur status mana saja yang dapat diakses oleh user ini. Jika tidak ada yang dipilih, user dapat mengakses semua status.')
                        ->modalIcon('heroicon-o-adjustments-horizontal')
                        ->modalWidth('2xl')
                        ->form([
                            Forms\Components\Section::make('Status Akses Saat Ini')
                                ->schema([
                                    Forms\Components\Placeholder::make('current_access')
                                        ->label('Akses Saat Ini')
                                        ->content(function (User $record) {
                                            if (empty($record->allowed_status)) {
                                                return 'User memiliki akses ke SEMUA STATUS';
                                            }

                                            $allowedStatus = is_string($record->allowed_status) ?
                                                json_decode($record->allowed_status, true) :
                                                $record->allowed_status;

                                            $statusNames = Status::whereIn('kode', $allowedStatus)
                                                ->orderBy('urut')
                                                ->pluck('nama_status')
                                                ->toArray();

                                            return 'User hanya dapat mengakses: ' . implode(', ', $statusNames);
                                        })
                                        ->extraAttributes(['style' => 'background: #f9fafb; padding: 12px; border-radius: 8px; border: 1px solid #6366f1;']),
                                ])
                                ->collapsible()
                                ->collapsed(),

                            Forms\Components\Section::make('Pengaturan Akses Status')
                                ->description('Pilih status yang dapat diakses oleh user ini.')
                                ->icon('heroicon-o-lock-closed')
                                ->schema([
                                    Forms\Components\CheckboxList::make('allowed_status')
                                        ->label('Status yang Diizinkan')
                                        ->options(function () {
                                            return Status::orderBy('urut')->pluck('nama_status', 'kode')->toArray();
                                        })
                                        ->descriptions(function () {
                                            return Status::orderBy('urut')->pluck('keterangan', 'kode')->toArray();
                                        })
                                        ->columns(2)
                                        ->gridDirection('row')
                                        ->helperText('Kosongkan semua pilihan untuk memberikan akses ke SEMUA STATUS')
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            if (empty($state)) {
                                                $set('access_preview', 'Akses penuh ke semua status');
                                            } else {
                                                $statusNames = Status::whereIn('kode', $state)
                                                    ->orderBy('urut')
                                                    ->pluck('nama_status')
                                                    ->toArray();
                                                $set('access_preview', 'Akses terbatas ke: ' . implode(', ', $statusNames));
                                            }
                                        }),

                                    Forms\Components\Placeholder::make('access_preview')
                                        ->label('Preview Akses')
                                        ->content(fn($get) => $get('access_preview') ?? 'Akses penuh ke semua status')
                                        ->extraAttributes(['style' => 'background: #ecfdf5; padding: 10px; border-radius: 6px; border: 1px solid #10b981; color: #065f46;']),
                                ])
                        ])
                        ->fillForm(fn(User $record): array => [
                            'allowed_status' => $record->allowed_status ?? [],
                        ])
                        ->action(function (array $data, User $record): void {
                            $record->update([
                                'allowed_status' => empty($data['allowed_status']) ? null : $data['allowed_status'],
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Akses Status Berhasil Diperbarui')
                                ->body("Akses status user {$record->name} telah diperbarui")
                                ->success()
                                ->duration(5000)
                                ->send();
                        }),

                    
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('assign_roles')
                        ->label('Assign Roles')
                        ->icon('heroicon-o-user-group')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('roles')
                                ->label('Roles to Assign')
                                ->options(Role::all()->pluck('name', 'id'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->helperText('Roles yang dipilih akan ditambahkan ke semua user yang dipilih'),
                            Forms\Components\Toggle::make('sync_roles')
                                ->label('Sync Roles (hapus roles lain)')
                                ->helperText('Jika diaktifkan, akan menghapus roles lain dan hanya menyimpan yang dipilih')
                                ->default(false),
                        ])
                        ->action(function (array $data, $records): void {
                            $roleNames = Role::whereIn('id', $data['roles'] ?? [])->pluck('name');
                            $records->each(function ($record) use ($data, $roleNames) {
                                if ($data['sync_roles']) {
                                    $record->syncRoles($roleNames);
                                } else {
                                    $record->assignRole($roleNames->toArray());
                                }
                            });
                        }),
                    Tables\Actions\BulkAction::make('set_status_access')
                        ->label('Atur Akses Status')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('warning')
                        ->visible(function () {
                            $currentUser = Auth::user();
                            return $currentUser && $currentUser instanceof User && $currentUser->urutan === 1;
                        })
                        ->form([
                            Forms\Components\CheckboxList::make('allowed_status')
                                ->label('Status yang Diizinkan')
                                ->options(function () {
                                    return Status::orderBy('urut')->pluck('nama_status', 'kode')->toArray();
                                })
                                ->columns(2)
                                ->helperText('Kosongkan untuk memberikan akses ke semua status'),
                        ])
                        ->action(function (array $data, $records): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'allowed_status' => empty($data['allowed_status']) ? null : $data['allowed_status'],
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('set_urutan')
                        ->label('Atur Urutan Developer')
                        ->icon('heroicon-o-queue-list')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('urutan')
                                ->label('Urutan')
                                ->numeric()
                                ->default(0)
                                ->helperText('0 = Tidak dalam workflow, 1+ = Urutan dalam workflow')
                                ->required(),
                            Forms\Components\Toggle::make('auto_increment')
                                ->label('Auto Increment')
                                ->helperText('Mulai dari urutan yang dimasukkan dan increment otomatis untuk setiap user')
                                ->default(false),
                        ])
                        ->action(function (array $data, $records): void {
                            $startUrutan = $data['urutan'];
                            $autoIncrement = $data['auto_increment'];

                            $records->each(function ($record, $index) use ($startUrutan, $autoIncrement) {
                                $urutan = $autoIncrement ? $startUrutan + $index : $startUrutan;
                                $record->update(['urutan' => $urutan]);
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada User')
            ->emptyStateDescription('Mulai dengan menambahkan user pertama.')
            ->emptyStateIcon('heroicon-o-users')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

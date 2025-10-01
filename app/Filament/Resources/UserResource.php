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
                            ->helperText('Pilih satu atau lebih roles untuk user ini. Roles: Super Admin (full access), Admin (manage data), Verifikator (verify), Approver (approve), Operator (input), Viewer (read-only)'),
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
                            ->columns(2)
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
                Tables\Columns\TagsColumn::make('roles.name')
                    ->label('Roles')
                    ->color('info')
                    ->separator(', ')
                    ->limitList(3)
                    ->expandableLimitedList(),
                Tables\Columns\TagsColumn::make('allowed_status_names')
                    ->label('Status Diizinkan')
                    ->getStateUsing(function (User $record) {
                        if (empty($record->allowed_status)) {
                            return ['Semua Status'];
                        }
                        return Status::whereIn('kode', $record->allowed_status)
                            ->orderBy('urut')
                            ->pluck('nama_status')
                            ->toArray();
                    })
                    ->color(function (User $record) {
                        return empty($record->allowed_status) ? 'success' : 'primary';
                    }),
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
                Tables\Filters\Filter::make('has_limited_access')
                    ->label('Akses Terbatas')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('allowed_status')),
                Tables\Filters\Filter::make('full_access')
                    ->label('Akses Penuh')
                    ->query(fn(Builder $query): Builder => $query->whereNull('allowed_status')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('manage_roles')
                    ->label('Kelola Roles')
                    ->icon('heroicon-o-user-group')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->fillForm(fn(User $record): array => [
                        'roles' => $record->roles()->pluck('id')->toArray(),
                    ])
                    ->action(function (array $data, User $record): void {
                        $record->syncRoles(Role::whereIn('id', $data['roles'] ?? [])->pluck('name'));
                    }),
                Tables\Actions\Action::make('manage_status')
                    ->label('Kelola Status')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Forms\Components\CheckboxList::make('allowed_status')
                            ->label('Status yang Diizinkan')
                            ->options(function () {
                                return Status::orderBy('urut')->pluck('nama_status', 'kode')->toArray();
                            })
                            ->descriptions(function () {
                                return Status::orderBy('urut')->pluck('keterangan', 'kode')->toArray();
                            })
                            ->columns(2)
                            ->helperText('Kosongkan untuk memberikan akses ke semua status'),
                    ])
                    ->fillForm(fn(User $record): array => [
                        'allowed_status' => $record->allowed_status ?? [],
                    ])
                    ->action(function (array $data, User $record): void {
                        $record->update([
                            'allowed_status' => empty($data['allowed_status']) ? null : $data['allowed_status'],
                        ]);
                    }),
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

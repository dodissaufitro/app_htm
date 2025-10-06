# AppVerifikatorResource - Navigation & Access Configuration

## Overview

AppVerifikatorResource telah dikonfigurasi untuk muncul di menu "Settings" bersama dengan Roles management dari Filament Shield. Resource ini memiliki access control yang ketat berdasarkan roles dan permissions.

## Navigation Configuration

### Group & Position

-   **Navigation Group**: `Settings`
-   **Navigation Label**: `Verifikasi`
-   **Navigation Sort**: `2`
-   **Navigation Icon**: `heroicon-o-shield-check`

### Access Control

Resource ini menggunakan 2 level access control:

#### 1. Navigation Visibility (`shouldRegisterNavigation()`)

```php
public static function shouldRegisterNavigation(): bool
{
    $user = Auth::user();
    if (!$user || !($user instanceof \App\Models\User)) {
        return false;
    }

    // Show for Super Admin and users with app::verifikator permissions
    return $user->hasRole('Super Admin') || $user->can('view_any_app::verifikator');
}
```

#### 2. Resource Access (`canAccess()`)

```php
public static function canAccess(): bool
{
    $user = Auth::user();
    if (!$user || !($user instanceof \App\Models\User)) {
        return false;
    }

    // Allow access for Super Admin and users with app::verifikator permissions
    return $user->hasRole('Super Admin') || $user->can('view_any_app::verifikator');
}
```

## Permissions & Roles

### Available Permissions

Dari output `php artisan permission:show`, permissions untuk `app::verifikator`:

| Permission                          | Super Admin | Verifikator | Other Roles |
| ----------------------------------- | ----------- | ----------- | ----------- |
| `view_any_app::verifikator`         | ✅          | ✅          | ❌          |
| `view_app::verifikator`             | ✅          | ❌          | ❌          |
| `create_app::verifikator`           | ✅          | ❌          | ❌          |
| `update_app::verifikator`           | ✅          | ❌          | ❌          |
| `delete_app::verifikator`           | ✅          | ❌          | ❌          |
| `delete_any_app::verifikator`       | ✅          | ❌          | ❌          |
| `force_delete_app::verifikator`     | ✅          | ❌          | ❌          |
| `force_delete_any_app::verifikator` | ✅          | ❌          | ❌          |
| `restore_app::verifikator`          | ✅          | ❌          | ❌          |
| `restore_any_app::verifikator`      | ✅          | ❌          | ❌          |
| `replicate_app::verifikator`        | ✅          | ❌          | ❌          |
| `reorder_app::verifikator`          | ✅          | ❌          | ❌          |

### Role Configuration

#### Super Admin

-   **Full Access**: Semua permissions untuk app::verifikator
-   **Navigation**: Visible in Settings group
-   **Functionality**: Create, Read, Update, Delete all verifikator data

#### Verifikator Role

-   **Limited Access**: Hanya `view_any_app::verifikator`
-   **Navigation**: Visible in Settings group
-   **Functionality**: Read-only access to verifikator data

#### Other Roles

-   **No Access**: Tidak ada permissions untuk app::verifikator
-   **Navigation**: Hidden from navigation
-   **Functionality**: Cannot access resource

## Testing

### Test Command

```bash
php artisan test:app-verifikator-navigation {user_id}
```

### Test Results

#### Super Admin (User ID: 1)

```
✅ Should show in navigation: Yes
✅ Can access resource: Yes
✅ Has Super Admin role: Yes
✅ Has view_any_app::verifikator permission: Yes
✅ Super Admin access working correctly
```

#### Verifikator (User ID: 3)

```
✅ Should show in navigation: Yes
✅ Can access resource: Yes
❌ Has Super Admin role: No
✅ Has view_any_app::verifikator permission: Yes
✅ Permission-based access working correctly
```

#### Other Users (e.g., Developer)

```
❌ Should show in navigation: No
❌ Can access resource: No
❌ Has Super Admin role: No
❌ Has view_any_app::verifikator permission: No
✅ Access control working correctly (blocked)
```

## Menu Location

AppVerifikatorResource sekarang akan muncul di:

```
Settings (Navigation Group)
├── Roles (from Filament Shield)
├── Permissions (from Filament Shield)
└── Verifikasi (AppVerifikatorResource)
```

## Integration dengan Filament Shield

### Automatic Permission Generation

Filament Shield sudah generate permissions untuk AppVerifikatorResource:

-   Pattern: `{action}_app::verifikator`
-   Actions: view, view_any, create, update, delete, etc.

### Role Management

-   Super Admin: Otomatis dapat semua permissions
-   Other Roles: Perlu diberikan permission manual

### Command untuk Management

```bash
# Generate permissions untuk semua resources
php artisan shield:generate

# Berikan permission ke role tertentu
php artisan permission:give-verifikator-access

# Lihat permission saat ini
php artisan permission:show
```

## Customization

### Menambah Role Lain

Untuk memberikan akses ke role lain, tambahkan permission:

```php
// In access control methods
return $user->hasRole(['Super Admin', 'Admin']) ||
       $user->can('view_any_app::verifikator');
```

### Mengubah Navigation Group

```php
protected static ?string $navigationGroup = 'Management'; // atau group lain
```

### Fine-grained Permissions

Bisa menambahkan check untuk specific actions:

```php
public static function canCreate(): bool
{
    return Auth::user()?->can('create_app::verifikator') ?? false;
}

public static function canEdit($record): bool
{
    return Auth::user()?->can('update_app::verifikator') ?? false;
}
```

## Security Notes

1. **Principle of Least Privilege**: Setiap role hanya mendapat minimum permissions yang diperlukan
2. **Double Protection**: Navigation visibility DAN resource access keduanya di-check
3. **Fallback Safe**: Jika user tidak terautentikasi, akses ditolak
4. **Permission Inheritance**: Super Admin otomatis dapat akses penuh

## Troubleshooting

### Permission Cache

Jika perubahan permission tidak terlihat:

```bash
php artisan permission:cache-reset
php artisan config:clear
php artisan route:clear
```

### User Not Seeing Menu

1. Check user role: `php artisan test:app-verifikator-navigation {user_id}`
2. Verify permissions: `php artisan permission:show`
3. Clear cache: `php artisan optimize:clear`

### Shield Integration Issues

1. Regenerate permissions: `php artisan shield:generate`
2. Check Shield config: `config/filament-shield.php`
3. Verify Shield installation: `php artisan shield:doctor`

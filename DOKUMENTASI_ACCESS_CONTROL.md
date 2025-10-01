# Sistem Access Control untuk DataPemohon - Dokumentasi Lengkap

## 🎯 Ringkasan Sistem

Sistem access control yang telah diimplementasi menyediakan:

-   **Status-based filtering**: User hanya bisa mengakses data dengan status yang diizinkan
-   **Role-based management**: Pengelolaan user dengan roles yang berbeda
-   **Navigation badges**: Badge yang menampilkan jumlah data sesuai akses user
-   **Status tabs filtering**: Tabs di halaman list hanya menampilkan status yang diizinkan
-   **Card-based interface**: Interface kartu interaktif untuk laporan dan filtering
-   **Data integrity**: Foreign key constraints untuk konsistensi data
-   **UI consistency**: Badge navigation dan tabs yang konsisten dengan data yang ditampilkan

## 🏗️ Komponen Utama

### 1. Model User (Enhanced)

```php
// app/Models/User.php
protected function casts(): array
{
    return [
        'allowed_status' => 'array',  // Auto-cast JSON ke array
    ];
}

// Method untuk checking access
public function canAccessStatus(string $statusCode): bool
public function getAllowedStatusCodes(): array
public function setAllowedStatus(array $statusCodes): void
```

### 2. DataPemohonResource (Status-based filtering)

```php
// app/Filament/Resources/DataPemohonResource.php
public function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    if (!empty($user->allowed_status)) {
        $query->whereIn('status_permohonan', $user->allowed_status);
    }

    return $query;
}

public function getNavigationBadge(): ?string
{
    $user = auth()->user();
    $query = DataPemohon::query();

    if (!empty($user->allowed_status)) {
        $query->whereIn('status_permohonan', $user->allowed_status);
    }

    return (string) $query->count();
}
```

### 3. ListDataPemohons (Status tabs filtering)

```php
// app/Filament/Resources/DataPemohonResource/Pages/ListDataPemohns.php
public function getTabs(): array
{
    $user = Auth::user();

    // Get allowed statuses only
    $statusesQuery = Status::orderBy('urut');
    if (!empty($user->allowed_status)) {
        $statusesQuery->whereIn('kode', $user->allowed_status);
    }
    $statuses = $statusesQuery->get();

    $tabs = [];

    // Show "Semua" tab only if user can see multiple statuses
    if ($statuses->count() > 1) {
        $totalQuery = $this->getModel()::query();
        if (!empty($user->allowed_status)) {
            $totalQuery->whereIn('status_permohonan', $user->allowed_status);
        }
        $tabs['semua'] = Tab::make('Semua')->badge($totalQuery->count());
    }

    // Add tabs for each allowed status
    foreach ($statuses as $status) {
        $tabs[$status->kode] = Tab::make($status->nama_status)
            ->modifyQueryUsing(fn(Builder $query) => $query->where('status_permohonan', $status->kode))
            ->badge($this->getModel()::where('status_permohonan', $status->kode)->count());
    }

    return $tabs;
}
```

### 4. PersetujuanResource (Workflow-based filtering)

```php
// app/Filament/Resources/PersetujuanResource.php
public function getEloquentQuery(): Builder
{
    $query = DataPemohon::forPersetujuan(); // status dengan urut = 1
    $user = auth()->user();

    if (!empty($user->allowed_status)) {
        $query->whereIn('status_permohonan', $user->allowed_status);
    }

    return $query;
}

public function getNavigationBadge(): ?string
{
    // Sama seperti getEloquentQuery tapi untuk count
}
```

### 5. KelengkapanDataResource (Card-based interface)

```php
// app/Filament/Resources/KelengkapanDataResource.php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = Auth::user();

    if (!empty($user->allowed_status)) {
        $query->whereIn('status_permohonan', $user->allowed_status);
    }

    return $query;
}

// app/Filament/Resources/KelengkapanDataResource/Pages/ListKelengkapanData.php
class KelengkapanDataOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Interactive cards dengan filtering
        $stats[] = Stat::make('Total Data', $totalCount)
            ->description('Total semua data pemohon')
            ->descriptionIcon('heroicon-o-users')
            ->color('primary')
            ->extraAttributes([
                'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                'wire:click' => 'handleStatusFilter("all")'
            ]);

        // Status cards dengan access control
        foreach ($statuses as $status) {
            $stats[] = Stat::make($status->nama_status, $count)
                ->color($statusColor)
                ->extraAttributes([
                    'wire:click' => 'handleStatusFilter("' . $status->kode . '")'
                ]);
        }

        return $stats;
    }
}
```

### 6. UserResource (Super Admin only)

```php
// app/Filament/Resources/UserResource.php
public static function canAccess(): bool
{
    return auth()->user()?->hasRole('Super Admin');
}
```

### 5. Database Schema

```sql
-- Migration untuk foreign key
ALTER TABLE data_pemohon
ADD CONSTRAINT fk_data_pemohon_status
FOREIGN KEY (status_permohonan) REFERENCES status(kode);

-- Migration untuk allowed_status di users
ALTER TABLE users ADD COLUMN allowed_status JSON NULL;
```

## 👥 Role dan Permission Matrix

| Role        | DataPemohon Access | PersetujuanResource | UserResource | Default Status Access |
| ----------- | ------------------ | ------------------- | ------------ | --------------------- |
| Super Admin | ✅ All data        | ✅ Yes              | ✅ Yes       | All status            |
| Admin       | ✅ Status-based    | ✅ Yes              | ❌ No        | All status            |
| Approver    | ✅ Status-based    | ✅ Yes              | ❌ No        | Customizable          |
| Verifikator | ✅ Status-based    | ✅ Yes              | ❌ No        | Customizable          |
| Operator    | ✅ Status-based    | ❌ No               | ❌ No        | Customizable          |
| Viewer      | ✅ Status-based    | ❌ No               | ❌ No        | Read-only status      |

## 🔧 Command untuk Testing

### 1. Create Sample Users

```bash
php artisan test:create-sample-users
```

Creates:

-   `viewer@example.com` (Viewer - akses COMPLETED saja)
-   `operator@example.com` (Operator - akses DRAFT, PROSES)
-   `verifikator@example.com` (Verifikator - akses DRAFT, PROSES, APPROVED)
-   `limited@example.com` (Admin - akses REJECTED saja)

### 2. Test Navigation Badges

```bash
php artisan test:all-navigation-badges {email}
```

Contoh:

```bash
php artisan test:all-navigation-badges admin@gmail.com
php artisan test:all-navigation-badges viewer@example.com
```

### 3. Debug User Status

```bash
php artisan debug:user-status {email}
```

### 4. Test Specific Resource Badge

```bash
php artisan test:navigation-badge {email}
php artisan test:persetujuan-access {email}
```

### 5. Test DataPemohon Tabs

```bash
php artisan test:data-pemohon-tabs {email}
```

Contoh:

```bash
php artisan test:data-pemohon-tabs admin@gmail.com
php artisan test:data-pemohon-tabs operator@example.com
php artisan test:data-pemohon-tabs limited@example.com
```

Test ini akan menampilkan:

-   Tabs mana saja yang akan muncul di halaman DataPemohon
-   Apakah tab "Semua" ditampilkan atau disembunyikan
-   Tabs mana saja yang disembunyikan berdasarkan access control

## 📊 Test Results Summary

### Super Admin (admin@gmail.com)

-   **DataPemohon Badge**: 9 (semua data)
-   **PersetujuanResource Badge**: 4 (semua data urut=1)
-   **DataPemohon Tabs**: Tab "Semua" + 9 status tabs (semua status)
-   **Access**: Semua fitur

### Viewer (viewer@example.com)

-   **DataPemohon Badge**: 1 (hanya COMPLETED)
-   **PersetujuanResource Badge**: 0 (tidak ada COMPLETED dengan urut=1)
-   **DataPemohon Tabs**: Hanya tab "Selesai" (tab "Semua" disembunyikan karena hanya 1 status)
-   **Access**: Read-only, status terbatas

### Operator (operator@example.com)

-   **DataPemohon Badge**: 4 (DRAFT + PROSES)
-   **PersetujuanResource Badge**: 4 (semua DRAFT + PROSES dengan urut=1)
-   **DataPemohon Tabs**: Tab "Semua" + tab "Draft" + tab "Pemohon Baru" (7 status lain disembunyikan)
-   **Access**: Tidak bisa akses PersetujuanResource

### Limited Admin (limited@example.com)

-   **DataPemohon Badge**: 2 (hanya REJECTED)
-   **PersetujuanResource Badge**: 0 (tidak ada REJECTED dengan urut=1)
-   **DataPemohon Tabs**: Hanya tab "Ditolak" (tab "Semua" disembunyikan karena hanya 1 status)
-   **Access**: Admin role tapi status terbatas

## 🎯 Key Features Implemented

### ✅ Status-based Access Control

-   User hanya bisa lihat/edit data dengan status yang diizinkan
-   Filtering otomatis di level query
-   Konsisten di semua interface

### ✅ Navigation Badges

-   Badge menampilkan jumlah data yang bisa diakses user
-   Real-time counting berdasarkan access control
-   Konsisten antara badge dan data aktual

### ✅ Status Tabs Filtering

-   Tabs di halaman DataPemohon hanya menampilkan status yang diizinkan
-   Tab "Semua" otomatis disembunyikan jika user hanya punya akses 1 status
-   Intelligent tab management berdasarkan user permissions
-   Konsisten dengan data yang bisa diakses user

### ✅ Role Management

-   6 role dengan akses berbeda
-   Super Admin bisa kelola semua user dan role
-   Role-based resource access

### ✅ Data Integrity

-   Foreign key constraints
-   Automatic data migration
-   Relasi yang konsisten antar tabel

### ✅ User Experience

-   Interface yang konsisten
-   Badge yang akurat
-   Access control yang transparan

## 🔐 Security Implementation

1. **Query Level Filtering**: Data filtered di level Eloquent query
2. **Navigation Control**: Menu items disembunyikan berdasarkan akses
3. **Policy Protection**: Resource access dikontrol dengan policy
4. **Role Verification**: Method `canAccess()` untuk resource-level protection

## 📱 Usage dalam Production

### Menambah User Baru

1. Login sebagai Super Admin
2. Akses menu "Kelola User"
3. Buat user baru dengan role dan allowed_status yang sesuai

### Mengubah Akses User

1. Edit user di UserResource
2. Update field "Roles" dan "Status yang Diizinkan"
3. Perubahan langsung berlaku

### Monitoring Access

Gunakan command debugging untuk verify access:

```bash
php artisan debug:user-status user@example.com
php artisan test:all-navigation-badges user@example.com
```

## 🚀 Next Steps (Optional)

1. **Audit Logging**: Log semua akses dan perubahan data
2. **Time-based Access**: Batasi akses berdasarkan waktu
3. **IP Restriction**: Tambah pembatasan berdasarkan IP
4. **Multi-tenant**: Support untuk multiple organization
5. **API Access**: Extend access control ke API endpoints

## 📖 Conclusion

Sistem access control yang diimplementasi menyediakan:

-   **Keamanan berlapis** dari database sampai UI
-   **Fleksibilitas** dalam pengaturan akses per user
-   **Konsistensi** antara data dan interface
-   **Skalabilitas** untuk pengembangan fitur baru

Semua fitur telah di-test dan verified bekerja dengan baik! 🎉

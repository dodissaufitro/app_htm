# Dokumentasi Akses Kontrol Resource AppXXX

Sistem ini mengatur akses ke semua Resource App (AppVerifikator, AppBank, AppDeveloper, AppPenetapan, AppBast, AppAkad) berdasarkan roles dan permissions.

## 🔐 Konsep Akses Kontrol

Semua Resource AppXXX sekarang **HANYA** dapat diakses jika permission yang sesuai telah diceklis/diberikan kepada role user.

### Resource yang Dikontrol:

-   `AppVerifikatorResource` - Approval UPDP
-   `AppBankResource` - Approval Bank
-   `AppDeveloperResource` - Approval Developer
-   `AppPenetapanResource` - Approval Penetapan
-   `AppBastResource` - Approval BAST
-   `AppAkadResource` - Approval Akad

## 📋 Permissions yang Dibuat

Untuk setiap resource App, dibuat permissions berikut:

-   `view_any_app::{resource}` - Melihat daftar
-   `view_app::{resource}` - Melihat detail
-   `create_app::{resource}` - Membuat baru
-   `update_app::{resource}` - Mengubah
-   `delete_app::{resource}` - Menghapus
-   `delete_any_app::{resource}` - Menghapus batch
-   `restore_app::{resource}` - Memulihkan
-   `restore_any_app::{resource}` - Memulihkan batch
-   `replicate_app::{resource}` - Duplikasi
-   `reorder_app::{resource}` - Mengurutkan
-   `force_delete_app::{resource}` - Hapus permanen
-   `force_delete_any_app::{resource}` - Hapus permanen batch

Contoh untuk AppVerifikator:

-   `view_any_app::verifikator`
-   `view_app::verifikator`
-   `create_app::verifikator`
-   dst...

## 🚀 Setup Awal

### 1. Generate Permissions

```bash
php artisan shield:generate-app-permissions
```

### 2. Seed Roles dan Permissions (Opsional)

```bash
php artisan db:seed --class=AppResourceRoleSeeder
```

### 3. Assign Permission ke Role

```bash
# Assign semua permission resource tertentu ke role
php artisan shield:assign-app-permissions "Verifikator UPDP" --resource=app::verifikator

# Assign permission tertentu saja
php artisan shield:assign-app-permissions "Manager" --permissions=view_any,view,create,update
```

## 👥 Role yang Disediakan

Seeder akan membuat role-role berikut:

### Role Admin

-   **Super Admin**: Akses penuh ke semua resource
-   **Admin**: Akses penuh ke semua resource

### Role Spesialis

-   **Verifikator UPDP**: Hanya akses ke AppVerifikator
-   **Admin Bank**: Hanya akses ke AppBank
-   **Admin Developer**: Hanya akses ke AppDeveloper
-   **Admin Penetapan**: Hanya akses ke AppPenetapan
-   **Admin BAST**: Hanya akses ke AppBast
-   **Admin Akad**: Hanya akses ke AppAkad

### Role Manajerial

-   **Manager**: Akses view/create/update ke semua resource (tidak bisa delete)
-   **Supervisor**: Akses terbatas ke beberapa resource

## ⚙️ Cara Mengatur Akses Manual

### 1. Via Interface Shield

1. Login sebagai Super Admin
2. Buka menu **Shield** → **Roles**
3. Pilih role yang ingin diatur
4. Centang permission yang diinginkan untuk resource App

### 2. Via Command Line

```bash
# Melihat semua permission yang tersedia
php artisan permission:show

# Assign permission spesifik
php artisan shield:assign-app-permissions "Role Name" --resource=app::verifikator

# Assign multiple permissions
php artisan shield:assign-app-permissions "Role Name" --permissions=view_any,view,create
```

## 🔍 Cara Kerja Sistem

### 1. Policy Based Access Control

Setiap Resource App memiliki Policy yang mengecek permission user:

-   `AppVerifikatorPolicy` untuk `AppVerifikatorResource`
-   `AppBankPolicy` untuk `AppBankResource`
-   dst.

### 2. Navigation Control

Resource hanya muncul di menu navigasi jika user memiliki permission `view_any_app::{resource}`

### 3. Action Control

Setiap aksi (create, update, delete) dikontrol oleh permission yang sesuai.

## 🛠️ Troubleshooting

### Resource Tidak Muncul di Menu

1. Pastikan user memiliki role yang tepat
2. Cek apakah role memiliki permission `view_any_app::{resource}`
3. Clear cache permission: `php artisan permission:cache-reset`

### Access Denied Error

1. Cek permission user untuk aksi yang dilakukan
2. Pastikan policy terdaftar di `AuthServiceProvider`
3. Cek apakah user login

### Reset Permissions

```bash
# Clear cache permission
php artisan permission:cache-reset

# Re-generate permissions
php artisan shield:generate-app-permissions

# Re-seed roles (hati-hati, akan reset existing assignments)
php artisan db:seed --class=AppResourceRoleSeeder --force
```

## 📚 Customization

### Menambah Resource App Baru

1. Buat Policy baru dengan pattern `App{Name}Policy`
2. Daftarkan di `AuthServiceProvider`
3. Tambah ke resource list di command dan seeder
4. Generate permission: `php artisan shield:generate-app-permissions`

### Mengubah Permission Pattern

Edit file `config/filament-shield.php` untuk mengubah prefix permission yang digunakan.

## 🔐 Keamanan

-   Semua resource App memerlukan autentikasi
-   Permission dicek di level policy dan resource
-   Navigation otomatis disembunyikan jika tidak ada akses
-   Super Admin selalu memiliki akses penuh (bypass permission)

## 📝 Log Changes

-   **Initial Setup**: Semua Resource App dikontrol dengan policy
-   **Navigation Control**: Menu hanya muncul jika ada permission
-   **Role-based Access**: Akses berdasarkan role dan permission yang diceklis

Untuk pertanyaan lebih lanjut, hubungi developer atau admin sistem.

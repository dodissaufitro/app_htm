# Panduan Akses Kontrol Status dan Roles untuk DataPemohon

## Deskripsi

Fitur ini memungkinkan administrator untuk:

1. Membatasi akses user terhadap data pemohon berdasarkan status tertentu
2. Mengelola roles dan permissions untuk user
3. User hanya dapat melihat dan mengelola data pemohon yang memiliki status yang telah diizinkan dan sesuai dengan roles mereka

## Fitur yang Ditambahkan

### 1. Field `allowed_status` pada User

-   Field JSON yang menyimpan array kode status yang diizinkan untuk user
-   Jika kosong/null, user dapat mengakses semua status
-   Jika terisi, user hanya dapat mengakses status yang tercantum

### 2. Roles & Permissions Management

-   Integrasi dengan Spatie Permission untuk manajemen roles
-   6 roles default: Super Admin, Admin, Verifikator, Approver, Operator, Viewer
-   Permissions granular untuk setiap fitur

### 3. Method pada User Model

-   `canAccessStatus($statusCode)`: Mengecek apakah user dapat mengakses status tertentu
-   `getAllowedStatusCodes()`: Mendapatkan daftar kode status yang diizinkan
-   `setAllowedStatus($statusCodes)`: Mengatur status yang diizinkan
-   Roles management melalui Spatie Permission traits

### 4. Filtering pada DataPemohonResource

-   Data otomatis difilter berdasarkan status yang diizinkan
-   Query dilakukan di level Eloquent untuk efisiensi
-   Respect roles dan permissions

### 5. Policy Updates

-   Policy untuk view, update, dan delete sudah diperbarui
-   Mengecek akses berdasarkan status selain permission yang sudah ada

### 6. UserResource untuk Manajemen

-   **RESTRICTED ACCESS**: Hanya Super Admin yang dapat mengakses UserResource
-   Interface admin untuk mengatur status yang diizinkan untuk setiap user
-   **NEW**: Form untuk assign/remove roles
-   **NEW**: Table dengan kolom roles
-   **NEW**: Filter berdasarkan roles
-   **NEW**: Bulk actions untuk roles dan status
-   Action "Kelola Roles" dan "Kelola Status" terpisah
-   **SECURITY**: Navigation menu hidden untuk non-Super Admin
-   **SECURITY**: Policy protection untuk semua CRUD operations

## Roles Default yang Tersedia

### 1. Super Admin

-   **Akses**: Semua permission
-   **Deskripsi**: Akses penuh ke semua fitur sistem

### 2. Admin

-   **Akses**: Hampir semua permission kecuali manage roles
-   **Deskripsi**: Administrator umum untuk pengelolaan data

### 3. Verifikator

-   **Akses**: View dan update data pemohon
-   **Deskripsi**: Staff yang melakukan verifikasi data

### 4. Approver

-   **Akses**: View dan update data pemohon
-   **Deskripsi**: Staff yang melakukan approval

### 5. Operator

-   **Akses**: View, create, dan update data pemohon
-   **Deskripsi**: Operator input data

### 6. Viewer

-   **Akses**: Hanya view data pemohon
-   **Deskripsi**: User yang hanya bisa melihat data

## Cara Menggunakan

### 1. Jalankan Migration & Seeder

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=StatusSeeder
```

### 2. Akses Manajemen User

-   Buka Filament Admin Panel
-   Navigasi ke "Manajemen Akses" > "Kelola User"
-   Edit user yang ingin diatur aksesnya

### 3. Pengaturan Roles

-   **Section "Roles & Permissions"**: Pilih roles yang akan diberikan
-   Roles memiliki permissions yang sudah terdefinisi
-   Satu user bisa memiliki multiple roles

### 4. Pengaturan Status Access

-   **Section "Akses Kontrol Status"**: Pilih status yang diizinkan
-   **Kosongkan semua checkbox**: User dapat mengakses semua status
-   **Pilih beberapa status**: User hanya dapat mengakses status yang dipilih

## Contoh Skenario

### Skenario 1: Super Admin

-   **Roles**: Super Admin
-   **Status Access**: Kosong (akses semua)
-   **Hasil**: Dapat mengelola semua data dan semua status

### Skenario 2: Staff Verifikator Khusus

-   **Roles**: Verifikator
-   **Status Access**: "Diajukan", "Sedang Ditinjau"
-   **Hasil**: Hanya dapat view/update data dengan status tertentu

### Szenario 3: Operator Input dengan Batasan

-   **Roles**: Operator
-   **Status Access**: "Draft", "Diajukan"
-   **Hasil**: Bisa input/edit data hanya untuk status awal

### Skenario 4: Manager Approval

-   **Roles**: Admin, Approver
-   **Status Access**: "Sedang Ditinjau", "Disetujui", "Ditolak"
-   **Hasil**: Full admin access + khusus status approval

## Command Line Interface

### Command yang Diperluas

```bash
# Lihat akses saat ini (roles + status)
php artisan user:set-status-access user@example.com

# Set roles dan status
php artisan user:set-status-access user@example.com --roles="Admin" --roles="Verifikator" --status="SUBMITTED" --status="UNDER_REVIEW"

# Clear semua roles
php artisan user:set-status-access user@example.com --clear-roles

# Clear semua status restrictions
php artisan user:set-status-access user@example.com --clear

# Kombinasi: set role baru dan clear status restrictions
php artisan user:set-status-access user@example.com --roles="Super Admin" --clear
```

## Interface Improvements

### UserResource Enhancements

1. **Form Sections**:

    - Informasi User (nama, email, password)
    - **NEW**: Roles & Permissions (checkbox roles dengan descriptions)
    - Akses Kontrol Status (checkbox status)

2. **Table Columns**:

    - Nama, Email
    - **NEW**: Kolom Roles (TagsColumn dengan colors)
    - Status Diizinkan (TagsColumn)
    - Created/Updated timestamps

3. **Filters**:

    - **NEW**: Filter by Role (multiple select)
    - Akses Terbatas/Penuh

4. **Actions**:

    - **NEW**: Kelola Roles (quick role assignment)
    - Kelola Status (quick status assignment)
    - View/Edit standard

5. **Bulk Actions**:
    - **NEW**: Assign Roles (dengan opsi sync/add)
    - Set Status Access
    - Delete

## Status Default yang Tersedia

1. **DRAFT** - Draft (Data masih dalam tahap draft)
2. **SUBMITTED** - Diajukan (Data telah diajukan untuk review)
3. **UNDER_REVIEW** - Sedang Ditinjau (Data sedang dalam proses peninjauan)
4. **APPROVED** - Disetujui (Data telah disetujui)
5. **REJECTED** - Ditolak (Data ditolak dan perlu perbaikan)
6. **COMPLETED** - Selesai (Proses telah selesai)

## Keamanan

-   **CRITICAL**: UserResource hanya dapat diakses oleh Super Admin
-   **Navigation Security**: Menu "Kelola User" hanya muncul untuk Super Admin
-   **Policy Protection**: Semua operations di UserResource dilindungi UserPolicy
-   **Double layer security**: Roles (permissions) + Status access untuk DataPemohon
-   **Database level filtering**: Mencegah akses tidak sah melalui query
-   **Authorization chain**: Navigation → Policy → Business Logic
-   **Principle of least privilege**: User hanya mendapat akses minimal yang dibutuhkan

## Access Control Matrix

| Role        | UserResource   | DataPemohon              | Status Control  |
| ----------- | -------------- | ------------------------ | --------------- |
| Super Admin | ✅ Full Access | ✅ Full Access           | ✅ All Status   |
| Admin       | ❌ No Access   | ✅ Limited by Permission | ⚙️ Configurable |
| Verifikator | ❌ No Access   | ✅ View/Update Only      | ⚙️ Configurable |
| Approver    | ❌ No Access   | ✅ View/Update Only      | ⚙️ Configurable |
| Operator    | ❌ No Access   | ✅ Create/Update Only    | ⚙️ Configurable |
| Viewer      | ❌ No Access   | ✅ View Only             | ⚙️ Configurable |

## Maintenance

-   **User Management**: Hanya Super Admin yang dapat create/edit/delete users
-   **Role Assignment**: Super Admin dapat assign roles melalui UserResource atau CLI
-   **Status Configuration**: Super Admin dapat mengatur allowed_status per user
-   Status baru dapat ditambahkan melalui seeder atau langsung ke database
-   Roles baru dapat ditambahkan melalui RolePermissionSeeder
-   Permissions dapat dikustomisasi sesuai kebutuhan
-   Log akses dapat ditambahkan jika diperlukan untuk audit trail

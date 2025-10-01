# Panduan Akses Kontrol Status untuk DataPemohon

## Deskripsi

Fitur ini memungkinkan administrator untuk membatasi akses user terhadap data pemohon berdasarkan status tertentu. User hanya dapat melihat dan mengelola data pemohon yang memiliki status yang telah diizinkan untuk mereka.

## Fitur yang Ditambahkan

### 1. Field `allowed_status` pada User

-   Field JSON yang menyimpan array kode status yang diizinkan untuk user
-   Jika kosong/null, user dapat mengakses semua status
-   Jika terisi, user hanya dapat mengakses status yang tercantum

### 2. Method pada User Model

-   `canAccessStatus($statusCode)`: Mengecek apakah user dapat mengakses status tertentu
-   `getAllowedStatusCodes()`: Mendapatkan daftar kode status yang diizinkan
-   `setAllowedStatus($statusCodes)`: Mengatur status yang diizinkan

### 3. Filtering pada DataPemohonResource

-   Data otomatis difilter berdasarkan status yang diizinkan
-   Query dilakukan di level Eloquent untuk efisiensi

### 4. Policy Updates

-   Policy untuk view, update, dan delete sudah diperbarui
-   Mengecek akses berdasarkan status selain permission yang sudah ada

### 5. UserResource untuk Manajemen

-   Interface admin untuk mengatur status yang diizinkan untuk setiap user
-   Bulk action untuk mengatur akses status untuk multiple user sekaligus

## Cara Menggunakan

### 1. Jalankan Migration

```bash
php artisan migrate
```

### 2. Jalankan Seeder (Opsional)

```bash
php artisan db:seed --class=StatusSeeder
```

### 3. Akses Manajemen User

-   Buka Filament Admin Panel
-   Navigasi ke "Manajemen Akses" > "Kelola User"
-   Edit user yang ingin diatur aksesnya
-   Pilih status yang diizinkan di bagian "Akses Kontrol Status"

### 4. Pengaturan Status untuk User

-   **Kosongkan semua checkbox**: User dapat mengakses semua status
-   **Pilih beberapa status**: User hanya dapat mengakses status yang dipilih

## Contoh Skenario

### Skenario 1: Admin Umum

-   Tidak ada pembatasan status (semua checkbox kosong)
-   Dapat melihat dan mengelola semua data pemohon

### Skenario 2: Staff Verifikator

-   Hanya diizinkan akses status: "Diajukan", "Sedang Ditinjau"
-   Hanya dapat melihat data pemohon dengan status tersebut

### Skenario 3: Staff Approval

-   Hanya diizinkan akses status: "Sedang Ditinjau", "Disetujui", "Ditolak"
-   Dapat mengelola data yang sudah melalui tahap verifikasi

## Status Default yang Tersedia

1. **DRAFT** - Draft (Data masih dalam tahap draft)
2. **SUBMITTED** - Diajukan (Data telah diajukan untuk review)
3. **UNDER_REVIEW** - Sedang Ditinjau (Data sedang dalam proses peninjauan)
4. **APPROVED** - Disetujui (Data telah disetujui)
5. **REJECTED** - Ditolak (Data ditolak dan perlu perbaikan)
6. **COMPLETED** - Selesai (Proses telah selesai)

## Keamanan

-   Filtering dilakukan di level database untuk mencegah akses tidak sah
-   Policy mengecek ganda: permission dasar + akses status
-   Implementasi mengikuti prinsip least privilege

## Maintenance

-   Status baru dapat ditambahkan melalui seeder atau langsung ke database
-   User dapat diatur aksesnya melalui interface admin atau programmatically
-   Log akses dapat ditambahkan jika diperlukan untuk audit trail

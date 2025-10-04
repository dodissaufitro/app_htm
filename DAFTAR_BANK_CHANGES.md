# Daftar Bank - Duplicate Code Implementation

## Problem Solved

Sebelumnya, tabel `daftar_bank` menggunakan `id` sebagai primary key string yang harus unik, sehingga tidak bisa memasukkan bank dengan kode yang sama. Sekarang sudah diperbaiki agar bisa menginput beberapa bank dengan kode yang sama tapi status berbeda.

## Changes Made

### 1. Database Structure Changes

-   **Primary Key**: Diubah dari `string id` menjadi `auto-increment integer id`
-   **New Fields**:
    -   `kode_bank` (varchar 10) - untuk kode bank yang bisa duplikat
    -   `status` (varchar 50) - untuk membedakan bank dengan kode sama
    -   `kode_bank_legacy` (varchar 32) - untuk migrasi data lama
-   **Extended Fields**:
    -   `nama_bank` - diperpanjang dari 32 ke 100 karakter

### 2. Model Updates (DaftarBank.php)

```php
- protected $keyType = 'string';
- public $incrementing = false;
+ protected $keyType = 'int';
+ public $incrementing = true;

protected $fillable = [
+    'nama_bank',
+    'kode_bank',
+    'kode_bank_legacy',
+    'status',
];
```

### 3. Resource Updates (DaftarBankResource.php)

-   **Form Changes**:

    -   Removed `id` field input
    -   Added `kode_bank` field (can be duplicate)
    -   Added `status` select field with options: active, inactive, pending, maintenance
    -   Extended `nama_bank` max length to 100

-   **Table Changes**:
    -   Added `kode_bank` column with badge styling
    -   Added `status` column with colored badges
    -   Updated description to show kode_bank instead of id
    -   Added ID column (hidden by default)

### 4. Status Options Available

-   **active** - Bank aktif dan beroperasi
-   **inactive** - Bank tidak aktif
-   **pending** - Bank dalam review/pending
-   **maintenance** - Bank dalam maintenance

## Migration Files Created

1. `2025_10_01_072046_modify_daftar_bank_allow_duplicate_kode.php`

    - Backup existing data
    - Recreate table with new structure
    - Restore data with migration mapping

2. `2025_10_01_072507_extend_nama_bank_column_length.php`
    - Extend nama_bank from 32 to 100 characters

## Testing Commands Created

1. `test:daftar-bank-duplicate` - Test duplicate code functionality
2. `cleanup:daftar-bank-test` - Clean and create sample data
3. `test:daftar-bank-interface` - Test interface functionality

## Sample Data Structure

After implementation, you can have multiple banks like:

```
BCA - Bank Central Asia - Jakarta Pusat (active)
BCA - Bank Central Asia - Jakarta Timur (active)
BCA - Bank Central Asia - Maintenance Branch (maintenance)
BNI - Bank Negara Indonesia - Kantor Pusat (active)
BNI - Bank Negara Indonesia - Cabang Surabaya (active)
```

## Benefits

1. ✅ **Flexible Bank Management** - Same bank code with different branches/status
2. ✅ **Better Data Organization** - Status-based filtering and display
3. ✅ **Backward Compatibility** - Legacy data preserved during migration
4. ✅ **Enhanced UI** - Colored status badges and better form validation
5. ✅ **Scalable Design** - Can accommodate future requirements

## URL Access

-   List: `/admin/daftar-banks`
-   Create: `/admin/daftar-banks/create`
-   View: `/admin/daftar-banks/{id}`
-   Edit: `/admin/daftar-banks/{id}/edit`

## Testing Results

-   ✅ Duplicate codes working correctly
-   ✅ Status differentiation functional
-   ✅ Form validation working
-   ✅ Table display with proper badges
-   ✅ All CRUD operations functional
-   ✅ Data migration successful

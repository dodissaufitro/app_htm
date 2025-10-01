# Update: Foreign Key Constraint untuk Status Permohonan

## ✅ **Perubahan yang Dilakukan**

### 1. **Database Schema**

-   ✅ Menambahkan foreign key constraint dari `data_pemohon.status_permohonan` ke `status.kode`
-   ✅ Update semua data existing ke status valid (default: DRAFT)
-   ✅ On Update: CASCADE (jika kode status berubah, data pemohon ikut update)
-   ✅ On Delete: SET NULL (jika status dihapus, field jadi null)

### 2. **Model Relationship**

-   ✅ Relasi `belongsTo` sudah ada di DataPemohon model
-   ✅ Foreign key: `status_permohonan` → `status.kode`
-   ✅ Eager loading berfungsi dengan baik

### 3. **DataPemohonResource Updates**

-   ✅ Form Select menggunakan Status::pluck('nama_status', 'kode')
-   ✅ Table BadgeColumn menggunakan relasi: `status.nama_status`
-   ✅ Filter menggunakan relationship: `->relationship('status', 'nama_status')`
-   ✅ Bulk Action untuk ubah status tetap menggunakan kode yang benar

### 4. **Data Migration Results**

```
Before: 9 records with invalid status codes
After:  9 records updated to 'DRAFT'
Random: Distributed across all available status
```

### 5. **New Management Command**

-   ✅ Command: `php artisan data-pemohon:update-status`
-   ✅ Options: `--random`, `--status=KODE_STATUS`
-   ✅ Shows current distribution
-   ✅ Validates status codes

## 🎯 **Current Status Distribution**

| Status Code | Nama Status  | Count     |
| ----------- | ------------ | --------- |
| APPROVED    | Disetujui    | 2 records |
| COMPLETED   | Selesai      | 2 records |
| PROSES      | Pemohon Baru | 2 records |
| REJECTED    | Ditolak      | 2 records |
| SUBMITTED   | Diajukan     | 1 records |

**Total**: 9 DataPemohon records

## 🔧 **Command Usage**

```bash
# View current distribution
php artisan data-pemohon:update-status

# Assign random status to all records
php artisan data-pemohon:update-status --random

# Set specific status to all records
php artisan data-pemohon:update-status --status=UNDER_REVIEW

# Available status codes will be shown if invalid status provided
php artisan data-pemohon:update-status --status=INVALID
```

## 🛡️ **Data Integrity**

### **Foreign Key Benefits:**

1. ✅ **Referential Integrity**: Tidak bisa input status yang tidak ada
2. ✅ **Cascade Updates**: Jika kode status berubah, data otomatis update
3. ✅ **Consistent Data**: Semua status_permohonan guaranteed valid
4. ✅ **Relationship Queries**: Efficient joins dan eager loading

### **Filter & Display Benefits:**

1. ✅ **Proper Filter**: Filter by relationship lebih efficient
2. ✅ **Display Names**: Menampilkan nama status, bukan kode
3. ✅ **Searchable**: Status bisa dicari di filter
4. ✅ **Preloaded**: Options di-preload untuk performance

## 🔄 **Status Access Control Integration**

Foreign key ini mendukung fitur access control yang sudah ada:

-   ✅ User dengan `allowed_status` hanya melihat data dengan status yang diizinkan
-   ✅ Query filtering tetap efisien dengan proper indexing
-   ✅ Status validation di level database dan aplikasi

## 📊 **Performance Improvement**

-   ✅ **Database Level**: Foreign key dengan proper indexing
-   ✅ **Query Level**: Efficient relationship queries
-   ✅ **UI Level**: Preloaded options dan searchable filters
-   ✅ **Data Level**: Consistent status codes, no orphaned data

Status management sekarang memiliki **data integrity** yang kuat! 🔒

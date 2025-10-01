# Persetujuan Resource: Status Urut = 1

## 📋 **Overview**

PersetujuanResource telah dikonfigurasi untuk menampilkan **semua data_pemohon yang memiliki status dengan urut = 1** di table status. Ini memungkinkan admin untuk melihat dan mengelola data pemohon yang berada di tahap persetujuan awal.

## 🎯 **Konfigurasi Status**

### **Status dengan Urut = 1:**

-   **Kode**: DRAFT
-   **Nama**: Draft
-   **Deskripsi**: Data masih dalam tahap draft
-   **Urutan**: 1

## 🔧 **Implementasi Teknis**

### 1. **Model Scope (DataPemohon)**

```php
// Scope untuk filter berdasarkan urutan status
public function scopeWithStatusUrut($query, int $urut)
{
    return $query->whereHas('status', function ($q) use ($urut) {
        $q->where('urut', $urut);
    });
}

// Scope khusus untuk persetujuan
public function scopeForPersetujuan($query)
{
    return $query->withStatusUrut(1);
}
```

### 2. **PersetujuanResource Query**

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery()
        ->forPersetujuan()  // Filter status urut = 1
        ->with(['status', 'bank']);

    // Apply user status access control
    $user = Auth::user();
    if ($user && !empty($user->allowed_status)) {
        $query->whereIn('status_permohonan', $user->allowed_status);
    }

    return $query;
}
```

### 3. **Navigation Badge**

```php
public static function getNavigationBadge(): ?string
{
    $user = Auth::user();
    if (!$user || !($user instanceof \App\Models\User)) {
        return '0';
    }

    // Start with base persetujuan query
    $query = static::getModel()::forPersetujuan();

    // Apply user status access control (same as getEloquentQuery)
    if (!empty($user->allowed_status)) {
        $query->whereIn('status_permohonan', $user->allowed_status);
    }

    return (string) $query->count();
}
```

**✅ IMPROVED: User-Aware Badge Count**

-   Badge count sesuai dengan access control user
-   Menghormati `allowed_status` configuration
-   Consistent dengan data yang ditampilkan di table

## 🛡️ **Access Control**

### **Role-Based Access:**

Hanya user dengan roles berikut yang dapat mengakses PersetujuanResource:

-   ✅ **Super Admin** - Full access
-   ✅ **Admin** - Full access (jika allowed_status mengizinkan)
-   ✅ **Approver** - Access sesuai allowed_status
-   ✅ **Verifikator** - Access sesuai allowed_status
-   ❌ **Operator** - No access
-   ❌ **Viewer** - No access

### **Status-Based Access:**

User juga dibatasi oleh `allowed_status` mereka:

-   Jika `allowed_status` kosong: Akses semua data persetujuan
-   Jika `allowed_status` terisi: Hanya data dengan status yang diizinkan

## 📊 **Current Data Status**

### **Total Data Persetujuan:** 4 records

-   Status: DRAFT (urut = 1)
-   All records are available for persetujuan workflow

### **Status Distribution:**

| Status       | Count | Urut | Available in Persetujuan |
| ------------ | ----- | ---- | ------------------------ |
| DRAFT        | 3     | 1    | ✅ Yes                   |
| SUBMITTED    | 0     | 2    | ❌ No                    |
| UNDER_REVIEW | 0     | 3    | ❌ No                    |
| APPROVED     | 2     | 4    | ❌ No                    |
| REJECTED     | 2     | 5    | ❌ No                    |
| COMPLETED    | 1     | 6    | ❌ No                    |
| PROSES       | 1     | -    | ❌ No                    |

## 🧪 **Testing Commands**

### **Test General Info:**

```bash
php artisan test:persetujuan-access
```

### **Test User Access:**

```bash
php artisan test:persetujuan-access admin@gmail.com
```

### **Update Data Status:**

```bash
# Set specific records to DRAFT for testing
php artisan data-pemohon:update-status --status=DRAFT

# View current distribution
php artisan data-pemohon:update-status
```

## 🎭 **Access Scenarios**

### **Scenario 1: Super Admin**

-   **Role**: Super Admin
-   **Status Access**: ALL
-   **Result**: Can see all 4 persetujuan records ✅

### **Scenario 2: Approver with DRAFT Access**

-   **Role**: Approver
-   **Status Access**: DRAFT, SUBMITTED
-   **Result**: Can see all 4 persetujuan records ✅

### **Scenario 3: Approver without DRAFT Access**

-   **Role**: Approver
-   **Status Access**: SUBMITTED, UNDER_REVIEW
-   **Result**: Cannot see any persetujuan records ❌

### **Scenario 4: Operator**

-   **Role**: Operator
-   **Status Access**: ANY
-   **Result**: Cannot access Persetujuan menu ❌

## 🔄 **Workflow Integration**

PersetujuanResource terintegrasi dengan:

1. **Status Management**: Automatic filtering by urut = 1
2. **User Access Control**: Respect allowed_status configuration
3. **Role-Based Security**: Proper authorization layers
4. **Navigation Control**: Menu visibility based on roles

## 🚀 **Benefits**

1. **Focused View**: Hanya data yang perlu persetujuan yang ditampilkan
2. **Automatic Filtering**: Tidak perlu manual filter, otomatis urut = 1
3. **Access Control**: Respect user permissions dan status restrictions
4. **Real-time Count**: Navigation badge menampilkan jumlah yang akurat
5. **Scalable**: Mudah diubah jika perlu status urut yang berbeda

Sistem persetujuan sekarang memiliki **filtering otomatis** dan **access control** yang terintegrasi! 🎯

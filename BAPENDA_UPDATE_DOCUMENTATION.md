# Dokumentasi Update Data Bapenda

## Overview

Fungsi ini mengambil data dari API Bapenda dan menyimpannya ke field `bapenda`, `aset_hunian`, `bapenda_pasangan`, dan `bapenda_pasangan_pbb` dalam tabel `data_pemohon` dalam format JSON.

## Implementasi

### 1. Service Layer

-   **File**: `app/Services/BapendaService.php`
-   **Fungsi utama**: `updateBapendaDataById(int $id)`
-   **Deskripsi**: Mengambil data dari API Bapenda dan menyimpan ke database
-   **Fitur tambahan**:
    -   Otomatis mengambil data pasangan jika NIK2 tersedia
    -   Generate summary hasil update
    -   Menghitung jumlah kendaraan berdasarkan jenis
    -   Update timestamp bapenda_updated_at

### 2. Controller

-   **File**: `app/Http/Controllers/PersetujuanController.php`
-   **Method**: `updateBapenda(Request $request)`
-   **Route**: `POST /persetujuan/update-bapenda`

### 3. Command Artisan

-   **File**: `app/Console/Commands/UpdateBapendaCommand.php`
-   **Command**: `php artisan bapenda:update`

## Cara Penggunaan

### 1. Via Web Interface (Halaman Persetujuan)

```
URL: /persetujuan/pemohon?id={id}
```

-   Akses halaman persetujuan pemohon
-   Klik tombol "Update Data Bapenda"
-   Data akan diupdate dan halaman akan refresh dengan data terbaru

### 2. Via API

```bash
# Update by ID
POST /api/bapenda/update/{id}

# Update by NIK
POST /api/bapenda/update-by-nik
Content-Type: application/json
{
    "nik": "1234567890123456"
}
```

### 3. Via Command Line

```bash
# Update pemohon tertentu berdasarkan ID
php artisan bapenda:update --id=123

# Update pemohon tertentu berdasarkan NIK
php artisan bapenda:update --nik=1234567890123456

# Update semua pemohon yang belum punya data Bapenda
php artisan bapenda:update --missing

# Update semua pemohon (maksimal 10 records)
php artisan bapenda:update --all --limit=10
```

## Konfigurasi API Bapenda

File konfigurasi: `config/bapenda.php`

```php
return [
    'api_url' => env('BAPENDA_API_URL', 'http://10.15.36.91:7071/dpnol_data_assets'),
    'client_id' => env('BAPENDA_CLIENT_ID', '1001'),
    'username' => env('BAPENDA_USERNAME', 'samawa'),
    'timeout' => env('BAPENDA_TIMEOUT', 30),
];
```

Environment variables yang perlu diset:

```env
BAPENDA_API_URL=http://10.15.36.91:7071/dpnol_data_assets
BAPENDA_CLIENT_ID=1001
BAPENDA_USERNAME=samawa
BAPENDA_TIMEOUT=30
```

## Format Data yang Disimpan

### Field `bapenda` (JSON)

Menyimpan data kendaraan (PKB) pemohon utama dari API:

```json
[
    {
        "jenis": "Motor",
        "merk": "Honda",
        "tahun": "2020",
        "no_polisi": "B1234ABC",
        "pajak": 150000
    }
]
```

### Field `aset_hunian` (JSON)

Menyimpan data properti (PBB) pemohon utama dari API:

```json
[
    {
        "alamat": "Jl. Contoh No. 123",
        "luas_tanah": "100",
        "luas_bangunan": "80",
        "njop": 500000000
    }
]
```

### Field `bapenda_pasangan` (JSON)

Menyimpan data kendaraan (PKB) pasangan (NIK2) dari API:

```json
[
    {
        "jenis": "Motor",
        "merk": "Yamaha",
        "tahun": "2019",
        "no_polisi": "B5678DEF",
        "pajak": 130000
    }
]
```

### Field `bapenda_pasangan_pbb` (JSON)

Menyimpan data properti (PBB) pasangan (NIK2) dari API:

```json
[
    {
        "alamat": "Jl. Pasangan No. 456",
        "luas_tanah": "120",
        "luas_bangunan": "90",
        "njop": 600000000
    }
]
```

### Field tambahan yang diupdate:

-   `count_of_vehicle1`: Jumlah kendaraan roda 2 (motor)
-   `count_of_vehicle2`: Jumlah kendaraan roda 4 (mobil)
-   `bapenda_updated_at`: Timestamp terakhir update data Bapenda

## Error Handling

Service akan mencatat semua error ke log Laravel:

-   Log info untuk proses yang berhasil
-   Log error untuk kegagalan API atau database
-   Log warning untuk data yang tidak valid

## Testing

### 1. Test via Postman

Import collection: `data_pemohon_api.postman_collection.json`

### 2. Test via Browser

1. Login ke aplikasi
2. Akses: `/persetujuan/pemohon?id={id_pemohon}`
3. Klik tombol "Update Data Bapenda"

### 3. Test via Command Line

```bash
php artisan bapenda:update --id=1
```

## Troubleshooting

### API Timeout

Jika terjadi timeout, tingkatkan nilai `BAPENDA_TIMEOUT` di .env

## Troubleshooting

### API Timeout atau Connection Error

Jika terjadi timeout atau connection error:

1. **Check API URL Accessibility**

    ```bash
    # Test koneksi ke API
    php artisan bapenda:test-connection
    ```

2. **Enable Mock Mode untuk Development**

    ```bash
    # Enable mock mode
    php artisan bapenda:mock enable

    # Test dengan mock data
    php artisan bapenda:update --nik=1304081010940006

    # Disable mock mode (untuk production)
    php artisan bapenda:mock disable
    ```

3. **Debug API Issues**

    ```bash
    # Debug lengkap
    php artisan bapenda:debug

    # Check konfigurasi saja
    php artisan bapenda:debug --check-config

    # Test koneksi saja
    php artisan bapenda:debug --test-connection
    ```

### API Configuration Issues

1. Cek log aplikasi: `storage/logs/laravel.log`
2. Pastikan konfigurasi API benar di `config/bapenda.php`
3. Pastikan NIK pemohon valid dan ada di database

### Mock Mode untuk Development

Mock mode berguna untuk development ketika API real tidak accessible:

```env
# Enable mock mode di .env
BAPENDA_MOCK_MODE=true
```

Mock mode akan mengembalikan sample data:

-   2 kendaraan (1 motor Honda, 1 mobil Toyota)
-   1 properti di Jakarta
-   Data akan disimpan normal ke database

### Data Tidak Tersimpan

1. Cek log aplikasi: `storage/logs/laravel.log`
2. Pastikan konfigurasi API benar
3. Pastikan NIK pemohon valid

### Signature Authentication Error

Pastikan `client_id`, `username`, dan algoritma signature sesuai dengan API Bapenda

## Migration yang Diperlukan

Field yang harus ada di tabel `data_pemohon`:

```sql
ALTER TABLE data_pemohon ADD COLUMN bapenda TEXT NULL;
ALTER TABLE data_pemohon ADD COLUMN aset_hunian TEXT NULL;
```

Field ini sudah ada di migration existing: `2024_01_05_000000_create_data_pemohon_table.php`

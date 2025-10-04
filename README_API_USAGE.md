# API Data Pemohon - Cara Penggunaan

## ✅ API Berhasil Dibuat!

API untuk Data Pemohon telah berhasil dibuat dengan body JSON sesuai permintaan Anda. API ini dapat menerima dan memproses data pemohon dengan format yang Anda berikan.

## 📁 File yang Dibuat:

1. **Controller**: `app/Http/Controllers/Api/DataPemohonController.php`
2. **FormRequest**: `app/Http/Requests/Api/StoreDataPemohonRequest.php`
3. **Routes**: Sudah ditambahkan di `routes/api.php`
4. **Model Update**: `app/Models/DataPemohon.php` (ditambahkan cast untuk JSON fields)
5. **Dokumentasi**: `API_DOCUMENTATION.md`
6. **Test Data**: `test_data_pemohon_payload.json`
7. **Postman Collection**: `postman_collection.json`

## 🚀 Endpoints API:

| Method    | Endpoint                 | Deskripsi                                              |
| --------- | ------------------------ | ------------------------------------------------------ |
| POST      | `/api/data-pemohon`      | Membuat data pemohon baru                              |
| GET       | `/api/data-pemohon`      | Mengambil daftar data pemohon (dengan pagination)      |
| GET       | `/api/data-pemohon/{id}` | Mengambil data pemohon berdasarkan ID atau book_number |
| PUT/PATCH | `/api/data-pemohon/{id}` | Update data pemohon                                    |
| DELETE    | `/api/data-pemohon/{id}` | Hapus data pemohon                                     |

## 📋 Field Required Minimal:

```json
{
    "nik": "3173072208930002",
    "name": "BAGUS RIFAI",
    "mobile_phone_number": "081806563006"
}
```

## 🔄 Mapping Field API ke Database:

| Field API                   | Field Database              | Keterangan                |
| --------------------------- | --------------------------- | ------------------------- |
| `book_number`               | `id_pendaftaran`            | Auto-generate jika kosong |
| `name`                      | `nama`                      | Nama lengkap              |
| `nik`                       | `nik`                       | NIK (wajib)               |
| `mobile_phone_number`       | `no_hp`                     | No HP (wajib)             |
| `job`                       | `pekerjaan`                 | Pekerjaan                 |
| `salary`                    | `gaji`                      | Gaji bulanan              |
| `settlement_name`           | `lokasi_rumah`              | Lokasi perumahan          |
| `province_name`             | `provinsi_dom`              | Provinsi domisili         |
| `city_name`                 | `kabupaten_dom`             | Kota domisili             |
| `district_name`             | `kecamatan_dom`             | Kecamatan domisili        |
| `village_name`              | `kelurahan_dom`             | Kelurahan domisili        |
| `address`                   | `alamat_dom`                | Alamat lengkap            |
| `aset_hunian`               | `aset_hunian`               | JSON array                |
| `reason_of_choose_location` | `reason_of_choose_location` | JSON array                |
| `booking_files`             | `booking_files`             | JSON array                |

## 🧪 Cara Testing:

### 1. Menggunakan cURL:

```bash
curl -X POST http://your-domain.com/api/data-pemohon \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d @test_data_pemohon_payload.json
```

### 2. Menggunakan Postman:

-   Import file `postman_collection.json`
-   Set variable `base_url` ke domain Anda
-   Jalankan request yang tersedia

### 3. Menggunakan PHP/JavaScript:

```php
// PHP Example
$data = json_decode(file_get_contents('test_data_pemohon_payload.json'), true);
$response = Http::post('http://your-domain.com/api/data-pemohon', $data);
```

```javascript
// JavaScript Example
const response = await fetch("http://your-domain.com/api/data-pemohon", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify(testData),
});
```

## ✨ Fitur API:

1. **Validasi Komprehensif**: Field wajib, format data, dan validasi business rules
2. **Error Handling**: Response error yang jelas dan informatif
3. **Pagination**: Support pagination untuk list data
4. **Search**: Pencarian berdasarkan nama, NIK, atau ID pendaftaran
5. **Filter**: Filter berdasarkan status permohonan
6. **Flexible Identifier**: Bisa menggunakan ID atau book_number untuk GET/PUT/DELETE
7. **JSON Support**: Otomatis handle field JSON seperti aset_hunian, reason_of_choose_location, booking_files
8. **Auto Generation**: Auto-generate ID pendaftaran dan username jika tidak disediakan
9. **Status Mapping**: Status default ke "15" (Verifikasi Dokumen Pendaftaran)

## 📝 Response Format:

### Success Response:

```json
{
    "success": true,
    "message": "Data pemohon berhasil dibuat",
    "data": {
        "id": 1,
        "id_pendaftaran": "2025100200001",
        "nama": "BAGUS RIFAI",
        "nik": "3173072208930002",
        "status_permohonan": "15",
        "created_at": "2025-10-02T10:30:00.000000Z"
    }
}
```

### Error Response:

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "nik": ["NIK wajib diisi"],
        "name": ["Nama wajib diisi"]
    }
}
```

## 🔒 Security Notes:

-   Saat ini API terbuka (tidak ada authentication)
-   Untuk production, tambahkan:
    -   API Authentication (Laravel Sanctum)
    -   Rate Limiting
    -   CORS configuration
    -   Input sanitization

## 🛠️ Untuk Development:

1. **Jalankan Laravel Development Server**:

    ```bash
    php artisan serve
    ```

2. **Test API**:

    ```bash
    # Test dengan file JSON yang sudah disediakan
    curl -X POST http://localhost:8000/api/data-pemohon \
      -H "Content-Type: application/json" \
      -d @test_data_pemohon_payload.json
    ```

3. **Lihat Routes**:

    ```bash
    php artisan route:list --path=api
    ```

4. **Clear Cache jika ada perubahan**:
    ```bash
    php artisan optimize:clear
    ```

## 📊 Status Codes:

-   `200`: Success (GET, PUT, DELETE)
-   `201`: Created (POST)
-   `404`: Not Found
-   `422`: Validation Error
-   `500`: Internal Server Error

API siap digunakan! 🎉

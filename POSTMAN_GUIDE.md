# Panduan Penggunaan API Data Pemohon di Postman

## 📋 **Persiapan Awal**

### 1. **Import Collection**

1. Buka Postman
2. Klik **Import** di pojok kiri atas
3. Pilih **File** tab
4. Upload file `data_pemohon_api.postman_collection.json`
5. Klik **Import**

### 2. **Setup Environment (Opsional)**

1. Klik **Environment** di sidebar kiri
2. Klik **Create Environment**
3. Nama: `Laravel API Local`
4. Tambahkan variable:
    - Variable: `base_url`
    - Initial Value: `http://localhost:8000`
    - Current Value: `http://localhost:8000`
5. Klik **Save**
6. Pilih environment di dropdown kanan atas

### 3. **Start Laravel Server**

```bash
# Di terminal
cd c:\laragon\www\app_htm
php artisan serve
```

---

## 🔍 **1. GET All Data Pemohon**

### **Setup Request:**

-   **Method**: `GET`
-   **URL**: `http://localhost:8000/api/data-pemohon`
-   **Headers**: Tidak diperlukan

### **Query Parameters (Opsional):**

| Parameter  | Value     | Description                             |
| ---------- | --------- | --------------------------------------- |
| `search`   | `BAGUS`   | Search by name, NIK, or registration ID |
| `status`   | `pending` | Filter by status                        |
| `per_page` | `10`      | Records per page                        |
| `page`     | `1`       | Page number                             |

### **Langkah-langkah:**

1. Pilih request **"Get All Data Pemohon"**
2. Klik **Params** tab
3. Tambahkan parameter sesuai kebutuhan:
    ```
    Key: search     Value: BAGUS
    Key: per_page   Value: 10
    Key: status     Value: pending
    ```
4. Klik **Send**

### **Expected Response:**

```json
{
    "success": true,
    "message": "Data pemohon retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "id_pendaftaran": "2025100100001",
                "nama": "BAGUS RIFAI",
                "nik": "3173072208930002",
                "status": {
                    "kode": "pending",
                    "nama": "Pending"
                }
            }
        ],
        "total": 1,
        "per_page": 10
    }
}
```

---

## ➕ **2. POST Create Data Pemohon**

### **Setup Request:**

-   **Method**: `POST`
-   **URL**: `http://localhost:8000/api/data-pemohon`
-   **Headers**:
    ```
    Content-Type: application/json
    ```

### **Body (JSON):**

```json
{
    "username": "bagus.rifai.2025",
    "nik": "3173072208930002",
    "nama": "BAGUS RIFAI",
    "email_address": "rifai.bagus@gmail.com",
    "mobile_phone_number": "081806563006",
    "job": "Karyawan swasta",
    "salary": 7880000,
    "marital_status": "1",
    "is_couple_dki": 0,
    "couple_id_card_number": null,
    "couple_name": null,
    "is_have_booking_kpr_dpnol": 1,
    "unit_type": null,
    "price": null,
    "education_name": "SLTA",
    "residence_status_name": "Orang Tua",
    "correspondence_address": "Alamat KTP",
    "is_domicile_same_with_ektp": "1",
    "count_of_vehicle1": 0,
    "count_of_vehicle2": 0,
    "is_have_saving_bank": 1,
    "is_have_home_credit": 0,
    "atp_name": "Rp 1.500.000 - Rp 2.000.000",
    "mounthly_expense1": 500000,
    "mounthly_expense2": 4000000,
    "settlement_name": "TOWER SAMAWA NUANSA PONDOK KELAPA",
    "reason_of_choose_location": [
        {
            "id": 4,
            "name": "Dekat Transportasi Publik"
        }
    ],
    "aset_hunian": [],
    "government_assistance_aid": [],
    "booking_files": [
        {
            "fname": "481af878536ed58ee7dadbe18f933767c3dbefac.jpg",
            "file_type": "KTP"
        },
        {
            "fname": "26945e6cbee887cb937560a00ae299303a3b97b6.jpg",
            "file_type": "KK"
        }
    ]
}
```

### **Langkah-langkah:**

1. Pilih request **"Create Data Pemohon"**
2. Klik **Headers** tab
3. Pastikan `Content-Type: application/json` sudah ada
4. Klik **Body** tab → **raw** → pilih **JSON**
5. Copy-paste JSON di atas
6. Klik **Send**

### **Expected Response:**

```json
{
    "success": true,
    "message": "Data pemohon created successfully",
    "data": {
        "id": 2,
        "id_pendaftaran": "2025100100002",
        "username": "bagus.rifai.2025",
        "nama": "BAGUS RIFAI",
        "nik": "3173072208930002",
        "created_at": "2025-10-01T08:00:00.000000Z"
    }
}
```

---

## 👁️ **3. GET Single Data Pemohon**

### **Setup Request:**

-   **Method**: `GET`
-   **URL**: `http://localhost:8000/api/data-pemohon/{id}`
-   **Headers**: Tidak diperlukan

### **Langkah-langkah:**

1. Pilih request **"Get Single Data Pemohon"**
2. Ganti `{id}` di URL dengan ID yang valid (contoh: `1`)
    ```
    http://localhost:8000/api/data-pemohon/1
    ```
3. Klik **Send**

### **Expected Response:**

```json
{
    "success": true,
    "message": "Data pemohon retrieved successfully",
    "data": {
        "id": 1,
        "id_pendaftaran": "2025100100001",
        "nama": "BAGUS RIFAI",
        "reason_of_choose_location": [
            {
                "id": 4,
                "name": "Dekat Transportasi Publik"
            }
        ],
        "booking_files": [
            {
                "fname": "481af878536ed58ee7dadbe18f933767c3dbefac.jpg",
                "file_type": "KTP"
            }
        ],
        "status": {
            "kode": "pending",
            "nama": "Pending"
        }
    }
}
```

---

## ✏️ **4. PUT Update Data Pemohon**

### **Setup Request:**

-   **Method**: `PUT`
-   **URL**: `http://localhost:8000/api/data-pemohon/{id}`
-   **Headers**:
    ```
    Content-Type: application/json
    ```

### **Body (JSON) - Full Update:**

```json
{
    "nama": "BAGUS RIFAI UPDATED",
    "salary": 8500000,
    "job": "Senior Software Developer",
    "email_address": "bagus.updated@gmail.com",
    "mobile_phone_number": "081806563007"
}
```

### **Langkah-langkah:**

1. Pilih request **"Update Data Pemohon"**
2. Ganti `{id}` dengan ID yang valid
3. Klik **Body** tab → **raw** → **JSON**
4. Input JSON untuk update
5. Klik **Send**

---

## 🔧 **5. PATCH Partial Update**

### **Setup Request:**

-   **Method**: `PATCH`
-   **URL**: `http://localhost:8000/api/data-pemohon/{id}`

### **Body (JSON) - Partial Update:**

```json
{
    "salary": 9000000
}
```

### **Langkah-langkah:**

1. Pilih request **"Partial Update Data Pemohon"**
2. Hanya kirim field yang ingin diupdate
3. Klik **Send**

---

## 📖 **6. GET By Book Number**

### **Setup Request:**

-   **Method**: `GET`
-   **URL**: `http://localhost:8000/api/data-pemohon/book-number/{bookNumber}`

### **Langkah-langkah:**

1. Pilih request **"Get Data Pemohon by Book Number"**
2. Ganti `{bookNumber}` dengan book number yang valid
    ```
    http://localhost:8000/api/data-pemohon/book-number/2025100100001
    ```
3. Klik **Send**

---

## 🗑️ **7. DELETE Data Pemohon**

### **Setup Request:**

-   **Method**: `DELETE`
-   **URL**: `http://localhost:8000/api/data-pemohon/{id}`

### **Langkah-langkah:**

1. Pilih request **"Delete Data Pemohon"**
2. Ganti `{id}` dengan ID yang ingin dihapus
3. Klik **Send**

### **Expected Response:**

```json
{
    "success": true,
    "message": "Data pemohon deleted successfully"
}
```

---

## 📁 **8. Upload File dengan Base64**

### **Setup Request:**

-   **Method**: `POST`
-   **URL**: `http://localhost:8000/api/data-pemohon`

### **Body (JSON) dengan Base64:**

```json
{
    "username": "test.base64",
    "nama": "Test Base64 Upload",
    "nik": "1234567890123456",
    "email_address": "test@example.com",
    "booking_files": [
        {
            "base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/...",
            "file_type": "KTP"
        }
    ]
}
```

### **Cara Convert File ke Base64:**

1. Buka website: https://base64.guru/converter/encode/image
2. Upload gambar
3. Copy hasil base64
4. Paste ke field `base64`

---

## 🔧 **Testing Scenarios**

### **Scenario 1: Basic CRUD**

1. **Create**: POST data baru
2. **Read**: GET data yang baru dibuat
3. **Update**: PUT update beberapa field
4. **Delete**: DELETE data

### **Scenario 2: Search & Filter**

1. GET `/api/data-pemohon?search=BAGUS`
2. GET `/api/data-pemohon?status=pending`
3. GET `/api/data-pemohon?search=BAGUS&status=pending&per_page=5`

### **Scenario 3: Error Handling**

1. **Invalid Data**: POST dengan data tidak valid
2. **Not Found**: GET dengan ID yang tidak ada
3. **Validation Error**: POST dengan NIK yang tidak 16 digit

---

## ⚠️ **Troubleshooting**

### **1. Connection Refused**

```
Error: connect ECONNREFUSED 127.0.0.1:8000
```

**Solusi**: Pastikan Laravel server running dengan `php artisan serve`

### **2. Validation Error**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "nik": ["The nik must be exactly 16 characters."]
    }
}
```

**Solusi**: Periksa format data sesuai validasi

### **3. 404 Not Found**

```json
{
    "success": false,
    "message": "Data pemohon not found"
}
```

**Solusi**: Pastikan ID/book number yang digunakan ada di database

### **4. 500 Server Error**

**Solusi**:

1. Cek log Laravel: `storage/logs/laravel.log`
2. Pastikan database connection OK
3. Cek apakah semua migration sudah running

---

## 📝 **Tips & Best Practices**

### **1. Environment Variables**

Setup environment variables di Postman:

-   `{{base_url}}` instead of `http://localhost:8000`
-   `{{auth_token}}` untuk authentication (jika ditambahkan nanti)

### **2. Tests di Postman**

Tambahkan test script di **Tests** tab:

```javascript
// Test status code
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Test response structure
pm.test("Response has success field", function () {
    pm.expect(pm.response.json()).to.have.property("success");
});

// Save ID for next request
if (pm.response.json().data && pm.response.json().data.id) {
    pm.environment.set("last_created_id", pm.response.json().data.id);
}
```

### **3. Pre-request Scripts**

Di **Pre-request Script** tab:

```javascript
// Generate random data
pm.environment.set(
    "random_username",
    "user_" + Math.floor(Math.random() * 10000)
);
pm.environment.set(
    "random_nik",
    "31730722089" + Math.floor(Math.random() * 100000)
);
```

### **4. Collection Variables**

Gunakan `{{last_created_id}}` untuk chaining requests.

---

## 🎯 **Summary Endpoints**

| Request | Method | URL                                    | Purpose                  |
| ------- | ------ | -------------------------------------- | ------------------------ |
| Get All | GET    | `/api/data-pemohon`                    | List all with pagination |
| Search  | GET    | `/api/data-pemohon?search=BAGUS`       | Search functionality     |
| Filter  | GET    | `/api/data-pemohon?status=pending`     | Filter by status         |
| Create  | POST   | `/api/data-pemohon`                    | Create new record        |
| Get One | GET    | `/api/data-pemohon/{id}`               | Get specific record      |
| Update  | PUT    | `/api/data-pemohon/{id}`               | Full update              |
| Partial | PATCH  | `/api/data-pemohon/{id}`               | Partial update           |
| By Book | GET    | `/api/data-pemohon/book-number/{book}` | Get by book number       |
| Delete  | DELETE | `/api/data-pemohon/{id}`               | Delete record            |

**Happy Testing! 🚀**

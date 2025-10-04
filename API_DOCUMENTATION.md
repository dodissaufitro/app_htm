# Data Pemohon API Documentation

## Base URL

```
http://your-domain.com/api
```

## Authentication

Currently, the API is open (no authentication required). You can add authentication later by:

1. Adding middleware to routes
2. Using Laravel Sanctum tokens
3. Implementing API key authentication

## Endpoints

### 1. Create Data Pemohon

**POST** `/data-pemohon`

Creates a new data pemohon record.

#### Request Body Example:

```json
{
    "book_number": "2025071900001",
    "settlement_id": "63",
    "settlement_name": "TOWER SAMAWA NUANSA PONDOK KELAPA",
    "bdtime": "2025-07-19 00:07:00",
    "npwp": "090844465031000",
    "nik": "3173072208930002",
    "email_address": "rifai.bagus@gmail.com",
    "name": "BAGUS RIFAI",
    "mobile_phone_number": "081806563006",
    "job": "Karyawan swasta",
    "salary": "7880000",
    "marital_status": "1",
    "is_couple_dki": 0,
    "couple_id_card_number": null,
    "couple_name": null,
    "is_have_booking_kpr_dpnol": 1,
    "unit_type": null,
    "price": null,
    "is_valid_npwp": 0,
    "checked_npwp_number": null,
    "checked_npwp_name": null,
    "checked_npwp_message": null,
    "education_id": "3",
    "education_name": "SLTA",
    "residence_status_id": "2",
    "residence_status_name": "Orang Tua",
    "correspondence_address": "Alamat KTP",
    "is_domicile_same_with_ektp": "1",
    "province_id": null,
    "province_name": null,
    "city_id": null,
    "city_name": null,
    "district_id": null,
    "district_name": null,
    "village_id": null,
    "village_name": null,
    "address": null,
    "is_domicile_same_with_couple": "1",
    "couple_province_id": null,
    "couple_province_name": null,
    "couple_city_id": null,
    "couple_city_name": null,
    "couple_district_id": null,
    "couple_district_name": null,
    "couple_village_id": null,
    "couple_village_name": null,
    "couple_address": "",
    "couple_job": "",
    "couple_income": "0",
    "count_of_vehicle1": "0",
    "count_of_vehicle2": "0",
    "is_have_saving_bank": 1,
    "is_have_home_credit": 0,
    "atpid": "2",
    "atp_name": "Rp 1.500.000 - Rp 2.000.000",
    "mounthly_expense1": "500000",
    "mounthly_expense2": "4000000",
    "bapenda": null,
    "aset_hunian": [],
    "reason_of_choose_location": [
        {
            "id": 4,
            "name": "Dekat Transportasi Publik"
        }
    ],
    "government_assistance_aid": [],
    "booking_files": [
        {
            "fname": "481af878536ed58ee7dadbe18f933767c3dbefac.jpg",
            "base64": null,
            "file_type": "KTP"
        },
        {
            "fname": "26945e6cbee887cb937560a00ae299303a3b97b6.jpg",
            "base64": null,
            "file_type": "KK"
        },
        {
            "fname": "42fd17c6870d78e1fe3365523cfb76a136cd3f97.jpg",
            "base64": null,
            "file_type": "Kartu NPWP"
        },
        {
            "fname": null,
            "base64": null,
            "file_type": "Surat Nikah/Akta Cerai/Akta Kematian"
        },
        {
            "fname": null,
            "base64": null,
            "file_type": "Surat Belum Mempunyai Rumah"
        },
        {
            "fname": "54b8588debbae50cec418cb532716a31c0b5e913.jpg",
            "base64": null,
            "file_type": "Slip Gaji"
        }
    ]
}
```

#### Required Fields:

-   `nik` (string, max 16 characters)
-   `name` (string, max 100 characters)
-   `mobile_phone_number` (string, max 100 characters)

#### Response Success (201):

```json
{
    "success": true,
    "message": "Data pemohon berhasil dibuat",
    "data": {
        "id": 1,
        "id_pendaftaran": "2025071900001",
        "nama": "BAGUS RIFAI",
        "nik": "3173072208930002",
        "status_permohonan": "15",
        "created_at": "2025-10-02T10:30:00.000000Z"
    }
}
```

#### Response Error (422):

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

### 2. Get All Data Pemohon

**GET** `/data-pemohon`

Retrieves paginated list of data pemohon.

#### Query Parameters:

-   `page` (optional): Page number (default: 1)
-   `per_page` (optional): Items per page (default: 15)
-   `search` (optional): Search by name, NIK, or id_pendaftaran
-   `status_permohonan` (optional): Filter by status

#### Example:

```
GET /data-pemohon?page=1&per_page=10&search=BAGUS&status_permohonan=15
```

#### Response Success (200):

```json
{
    "success": true,
    "message": "Data pemohon berhasil diambil",
    "data": [
        {
            "id": 1,
            "book_number": "2025071900001",
            "name": "BAGUS RIFAI",
            "nik": "3173072208930002",
            "mobile_phone_number": "081806563006",
            "status_permohonan": "15",
            "status_name": "Verifikasi Dokumen Pendaftaran",
            "created_at": "2025-10-02 10:30:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 15,
        "total": 1,
        "from": 1,
        "to": 1
    }
}
```

### 3. Get Data Pemohon by ID or Book Number

**GET** `/data-pemohon/{identifier}`

Retrieves a specific data pemohon by ID or id_pendaftaran (book_number).

#### Examples:

```
GET /data-pemohon/1                    (by ID)
GET /data-pemohon/2025071900001        (by book_number)
```

#### Response Success (200):

```json
{
    "success": true,
    "message": "Data pemohon berhasil ditemukan",
    "data": {
        "id": 1,
        "book_number": "2025071900001",
        "settlement_name": "TOWER SAMAWA NUANSA PONDOK KELAPA",
        "nik": "3173072208930002",
        "name": "BAGUS RIFAI",
        "mobile_phone_number": "081806563006",
        "job": "Karyawan swasta",
        "salary": "7880000.00",
        "status_permohonan": "15",
        "status_name": "Verifikasi Dokumen Pendaftaran",
        "created_at": "2025-10-02 10:30:00",
        "updated_at": "2025-10-02 10:30:00"
    }
}
```

#### Response Error (404):

```json
{
    "success": false,
    "message": "Data pemohon tidak ditemukan"
}
```

### 4. Update Data Pemohon

**PUT/PATCH** `/data-pemohon/{identifier}`

Updates an existing data pemohon record.

#### Request Body Example:

```json
{
    "name": "BAGUS RIFAI UPDATED",
    "mobile_phone_number": "081806563007",
    "salary": "8000000",
    "status_permohonan": "16"
}
```

#### Response Success (200):

```json
{
    "success": true,
    "message": "Data pemohon berhasil diperbarui",
    "data": {
        "id": 1,
        "id_pendaftaran": "2025071900001",
        "nama": "BAGUS RIFAI UPDATED",
        "nik": "3173072208930002",
        "status_permohonan": "16",
        "updated_at": "2025-10-02T11:00:00.000000Z"
    }
}
```

### 5. Delete Data Pemohon

**DELETE** `/data-pemohon/{identifier}`

Deletes a data pemohon record.

#### Response Success (200):

```json
{
    "success": true,
    "message": "Data pemohon berhasil dihapus"
}
```

## Status Codes

-   `200`: Success
-   `201`: Created
-   `404`: Not Found
-   `422`: Validation Error
-   `500`: Internal Server Error

## Field Mapping

| API Field                 | Database Field            | Type    | Description                     |
| ------------------------- | ------------------------- | ------- | ------------------------------- |
| book_number               | id_pendaftaran            | string  | Unique registration ID          |
| name                      | nama                      | string  | Full name                       |
| nik                       | nik                       | string  | National ID                     |
| mobile_phone_number       | no_hp                     | string  | Phone number                    |
| job                       | pekerjaan                 | string  | Occupation                      |
| salary                    | gaji                      | decimal | Monthly salary                  |
| npwp                      | npwp                      | string  | Tax ID                          |
| is_valid_npwp             | validasi_npwp             | boolean | NPWP validation status          |
| marital_status            | status_kawin              | integer | 0=Single, 1=Married, 2=Divorced |
| is_couple_dki             | is_couple_dki             | boolean | Spouse is DKI resident          |
| couple_name               | nama2                     | string  | Spouse name                     |
| couple_job                | pekerjaan2                | string  | Spouse occupation               |
| couple_income             | gaji2                     | decimal | Spouse income                   |
| education_name            | pendidikan                | string  | Education level                 |
| residence_status_name     | sts_rumah                 | string  | Housing status                  |
| settlement_name           | lokasi_rumah              | string  | Housing location                |
| unit_type                 | tipe_unit                 | string  | Unit type                       |
| price                     | harga_unit                | decimal | Unit price                      |
| province_name             | provinsi_dom              | string  | Province                        |
| city_name                 | kabupaten_dom             | string  | City                            |
| district_name             | kecamatan_dom             | string  | District                        |
| village_name              | kelurahan_dom             | string  | Village                         |
| address                   | alamat_dom                | string  | Address                         |
| aset_hunian               | aset_hunian               | json    | Housing assets                  |
| reason_of_choose_location | reason_of_choose_location | json    | Location choice reasons         |
| booking_files             | booking_files             | json    | Uploaded files                  |

## Notes

1. **Automatic Fields**:

    - `status_permohonan` defaults to "15" (Verifikasi Dokumen Pendaftaran)
    - `id_pendaftaran` auto-generated if not provided
    - `username` auto-generated from name
    - `created_by` and `updated_by` set to current user (if authenticated)

2. **JSON Fields**:

    - `aset_hunian`, `reason_of_choose_location`, `booking_files` are stored as JSON

3. **Boolean Fields**:

    - Accept `true/false`, `1/0`, or boolean values

4. **Date Format**:

    - Accepts ISO 8601 format: `YYYY-MM-DD HH:MM:SS`

5. **Search**:

    - Searches in name, NIK, and id_pendaftaran fields

6. **Validation**:
    - Comprehensive validation using FormRequest
    - Custom error messages in Indonesian

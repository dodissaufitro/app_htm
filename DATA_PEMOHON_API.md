# Data Pemohon API Documentation

## Base URL

```
http://localhost:8000/api/data-pemohon
```

## Authentication

Currently no authentication required. Can be added later with Sanctum tokens.

## Endpoints

### 1. Get All Data Pemohon

```http
GET /api/data-pemohon
```

**Query Parameters:**

-   `search` (optional): Search by name, NIK, or registration ID
-   `status` (optional): Filter by status permohonan
-   `per_page` (optional): Number of records per page (default: 15)
-   `page` (optional): Page number for pagination

**Example:**

```http
GET /api/data-pemohon?search=BAGUS&status=pending&per_page=10
```

**Response:**

```json
{
  "success": true,
  "message": "Data pemohon retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [...],
    "total": 100,
    "per_page": 15
  }
}
```

### 2. Create New Data Pemohon

```http
POST /api/data-pemohon
```

**Request Body:**

```json
{
    "username": "bagus.rifai",
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
        }
    ]
}
```

**Response:**

```json
{
  "success": true,
  "message": "Data pemohon created successfully",
  "data": {
    "id": 1,
    "id_pendaftaran": "2025100100001",
    "username": "bagus.rifai",
    "nama": "BAGUS RIFAI",
    ...
  }
}
```

### 3. Get Single Data Pemohon

```http
GET /api/data-pemohon/{id}
```

**Response:**

```json
{
    "success": true,
    "message": "Data pemohon retrieved successfully",
    "data": {
        "id": 1,
        "id_pendaftaran": "2025100100001",
        "nama": "BAGUS RIFAI",
        "aset_hunian": [],
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

### 4. Update Data Pemohon

```http
PUT /api/data-pemohon/{id}
PATCH /api/data-pemohon/{id}
```

**Request Body:** (same as create, but all fields optional)

```json
{
    "nama": "BAGUS RIFAI UPDATED",
    "salary": 8000000,
    "job": "Senior Developer"
}
```

### 5. Delete Data Pemohon

```http
DELETE /api/data-pemohon/{id}
```

**Response:**

```json
{
    "success": true,
    "message": "Data pemohon deleted successfully"
}
```

### 6. Get Data Pemohon by Book Number

```http
GET /api/data-pemohon/book-number/{bookNumber}
```

**Example:**

```http
GET /api/data-pemohon/book-number/2025071900001
```

## File Upload

For `booking_files`, you can either:

1. **Upload by filename** (if file already exists in storage):

```json
{
    "booking_files": [
        {
            "fname": "existing_file.jpg",
            "file_type": "KTP"
        }
    ]
}
```

2. **Upload via Base64**:

```json
{
    "booking_files": [
        {
            "base64": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
            "file_type": "KTP"
        }
    ]
}
```

## Error Responses

### Validation Error (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "nik": ["The nik must be exactly 16 characters."],
        "email_address": ["The email address must be a valid email address."]
    }
}
```

### Not Found (404)

```json
{
    "success": false,
    "message": "Data pemohon not found"
}
```

### Server Error (500)

```json
{
    "success": false,
    "message": "Failed to create data pemohon",
    "error": "Database connection failed"
}
```

## Field Mapping

Based on your JSON structure, here's the field mapping:

| JSON Field                | Database Field            | Type      | Description                    |
| ------------------------- | ------------------------- | --------- | ------------------------------ |
| book_number               | id_pendaftaran            | string    | Auto-generated if not provided |
| settlement_id             | settlement_id             | string    | Settlement/project ID          |
| settlement_name           | settlement_name           | string    | Settlement/project name        |
| bdtime                    | created_at                | timestamp | Creation timestamp             |
| npwp                      | npwp                      | string    | NPWP number                    |
| nik                       | nik                       | string    | NIK (16 digits)                |
| email_address             | email_address             | string    | Email address                  |
| name                      | nama                      | string    | Full name                      |
| mobile_phone_number       | no_hp                     | string    | Phone number                   |
| job                       | job                       | string    | Job/occupation                 |
| salary                    | salary                    | numeric   | Monthly salary                 |
| marital_status            | marital_status            | string    | 0=single, 1=married            |
| is_couple_dki             | is_couple_dki             | boolean   | Is couple from DKI             |
| education_name            | pendidikan                | string    | Education level                |
| residence_status_name     | sts_rumah                 | string    | Residence status               |
| count_of_vehicle1         | count_of_vehicle1         | integer   | Number of motorcycles          |
| count_of_vehicle2         | count_of_vehicle2         | integer   | Number of cars                 |
| is_have_saving_bank       | is_have_saving_bank       | boolean   | Has bank savings               |
| is_have_home_credit       | is_have_home_credit       | boolean   | Has home credit                |
| atp_name                  | -                         | string    | Payment ability description    |
| mounthly_expense1         | mounthly_expense1         | numeric   | Monthly expense 1              |
| mounthly_expense2         | mounthly_expense2         | numeric   | Monthly expense 2              |
| reason_of_choose_location | reason_of_choose_location | json      | Location choice reasons        |
| booking_files             | booking_files             | json      | Uploaded documents             |

## Testing with curl

```bash
# Get all data pemohon
curl -X GET "http://localhost:8000/api/data-pemohon"

# Create new data pemohon
curl -X POST "http://localhost:8000/api/data-pemohon" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "test_user",
    "nama": "Test User",
    "nik": "1234567890123456",
    "email_address": "test@example.com"
  }'

# Get specific data pemohon
curl -X GET "http://localhost:8000/api/data-pemohon/1"

# Update data pemohon
curl -X PUT "http://localhost:8000/api/data-pemohon/1" \
  -H "Content-Type: application/json" \
  -d '{"nama": "Updated Name"}'

# Delete data pemohon
curl -X DELETE "http://localhost:8000/api/data-pemohon/1"
```

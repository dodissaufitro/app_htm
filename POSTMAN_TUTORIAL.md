# 🚀 Step-by-Step Tutorial: Testing API Data Pemohon di Postman

## 🔧 **Setup Awal (5 menit)**

### **Step 1: Download Files**

Download 2 file berikut dari project Laravel:

1. `data_pemohon_api.postman_collection.json`
2. `Laravel_API_Local.postman_environment.json`

### **Step 2: Import ke Postman**

1. Buka Postman
2. Klik **Import** (pojok kiri atas)
3. Drag & drop kedua file JSON ke dalam area import
4. Klik **Import**
5. ✅ Anda akan melihat collection "Data Pemohon API - Complete Collection"

### **Step 3: Setup Environment**

1. Klik dropdown **"No Environment"** di pojok kanan atas
2. Pilih **"Laravel API - Local Environment"**
3. ✅ Base URL otomatis ter-set ke `http://localhost:8000`

### **Step 4: Start Laravel Server**

```bash
# Di terminal/command prompt
cd c:\laragon\www\app_htm
php artisan serve
```

✅ Server running di `http://localhost:8000`

---

## 🧪 **Testing Scenarios (Urutan Recommended)**

### **🔍 Scenario 1: Basic Read Operations**

#### **Test 1.1: Get All Data**

1. Pilih request **"📋 Get All Data Pemohon"**
2. Klik **Send**
3. ✅ **Expected**: Status 200, response dengan data array

**Hasil yang diharapkan:**

```json
{
    "success": true,
    "message": "Data pemohon retrieved successfully",
    "data": {
        "current_page": 1,
        "data": [...],
        "total": X
    }
}
```

#### **Test 1.2: Search Functionality**

1. Pilih request **"🔍 Search Data Pemohon"**
2. Perhatikan parameter `search=BAGUS`
3. Klik **Send**
4. ✅ **Expected**: Hasil yang mengandung "BAGUS" di nama/NIK

#### **Test 1.3: Filter by Status**

1. Pilih request **"🏷️ Filter by Status"**
2. Parameter `status=pending`
3. Klik **Send**
4. ✅ **Expected**: Hanya data dengan status "pending"

---

### **➕ Scenario 2: Create Operations**

#### **Test 2.1: Create New Data**

1. Pilih request **"➕ Create Data Pemohon"**
2. Lihat **Body** → JSON sudah menggunakan variables untuk data dinamis
3. Klik **Send**
4. ✅ **Expected**: Status 201, data baru dibuat

**Body otomatis menggunakan:**

-   `{{random_username}}` - username unik
-   `{{random_nik}}` - NIK dinamis
-   `{{timestamp}}` - waktu saat ini

#### **Test 2.2: Verify Creation**

1. Setelah create berhasil, pilih **"👁️ Get Single Data Pemohon"**
2. URL otomatis menggunakan `{{last_created_id}}`
3. Klik **Send**
4. ✅ **Expected**: Data yang baru saja dibuat

---

### **✏️ Scenario 3: Update Operations**

#### **Test 3.1: Full Update (PUT)**

1. Pilih request **"✏️ Update Data Pemohon (PUT)"**
2. Body berisi data update lengkap
3. Klik **Send**
4. ✅ **Expected**: Status 200, data terupdate

#### **Test 3.2: Partial Update (PATCH)**

1. Pilih request **"🔧 Partial Update (PATCH)"**
2. Body hanya berisi 2 field: salary dan job
3. Klik **Send**
4. ✅ **Expected**: Hanya field yang dikirim yang berubah

---

### **📖 Scenario 4: Special Operations**

#### **Test 4.1: Get by Book Number**

1. Pilih request **"📖 Get by Book Number"**
2. URL menggunakan `{{last_created_book_number}}`
3. Klik **Send**
4. ✅ **Expected**: Data ditemukan berdasarkan nomor buku

#### **Test 4.2: File Upload (Base64)**

1. Pilih request **"📁 Create with Base64 Upload"**
2. Body mengandung field `base64` dengan data gambar
3. Klik **Send**
4. ✅ **Expected**: File ter-upload dan disimpan

---

### **❌ Scenario 5: Error Testing**

#### **Test 5.1: Validation Errors**

1. Pilih request **"❌ Error Test - Invalid Data"**
2. Body mengandung data tidak valid (NIK pendek, email salah)
3. Klik **Send**
4. ✅ **Expected**: Status 422, error validation

#### **Test 5.2: Not Found Error**

1. Pilih request **"❌ Error Test - Not Found"**
2. URL menggunakan ID yang tidak ada (99999)
3. Klik **Send**
4. ✅ **Expected**: Status 404, not found

---

### **🗑️ Scenario 6: Delete Operation**

#### **Test 6.1: Delete Data**

1. Pilih request **"🗑️ Delete Data Pemohon"**
2. Menggunakan `{{last_created_id}}`
3. Klik **Send**
4. ✅ **Expected**: Status 200, data terhapus

---

## 🎯 **Advanced Testing Tips**

### **🔄 Testing Variables**

Setelah menjalankan beberapa request, cek **Environment Variables**:

1. Klik mata (👁️) di pojok kanan atas
2. Lihat current values:
    - `last_created_id` - ID data terakhir yang dibuat
    - `random_username` - Username random yang dibuat
    - `timestamp` - Timestamp saat ini

### **🧪 Automated Tests**

Setiap request memiliki **Tests** yang otomatis jalan:

1. Klik tab **"Test Results"** setelah Send
2. ✅ Hijau = Test passed
3. ❌ Merah = Test failed

### **📊 Collection Runner**

Untuk run semua test sekaligus:

1. Klik **"..."** di collection
2. Pilih **"Run collection"**
3. Pilih semua requests
4. Klik **"Run Data Pemohon API"**
5. ✅ Lihat hasil semua test

---

## 🔧 **Troubleshooting Common Issues**

### **❌ "Connection Refused"**

```
Error: connect ECONNREFUSED 127.0.0.1:8000
```

**Solusi:**

1. Pastikan Laravel server running: `php artisan serve`
2. Check URL di environment: `http://localhost:8000`

### **❌ "422 Validation Error"**

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {...}
}
```

**Solusi:**

1. Cek format data di Body
2. NIK harus 16 digit
3. Email harus format valid

### **❌ "404 Not Found"**

**Solusi:**

1. Pastikan ID yang digunakan valid
2. Jalankan "Get All" dulu untuk dapat ID yang valid
3. Gunakan variables `{{last_created_id}}`

### **❌ Environment Variables Kosong**

**Solusi:**

1. Jalankan "Create Data Pemohon" dulu
2. Variables akan ter-set otomatis
3. Atau set manual di Environment

---

## 📋 **Test Checklist**

Gunakan checklist ini untuk memastikan semua test berhasil:

-   [ ] ✅ Import collection & environment
-   [ ] ✅ Laravel server running
-   [ ] ✅ GET all data (status 200)
-   [ ] ✅ Search functionality working
-   [ ] ✅ Filter by status working
-   [ ] ✅ CREATE new data (status 201)
-   [ ] ✅ GET single data (status 200)
-   [ ] ✅ UPDATE data (PUT - status 200)
-   [ ] ✅ PATCH data (status 200)
-   [ ] ✅ GET by book number (status 200)
-   [ ] ✅ Base64 upload working
-   [ ] ✅ Validation errors (status 422)
-   [ ] ✅ Not found errors (status 404)
-   [ ] ✅ DELETE data (status 200)

---

## 🎉 **Expected Final Result**

Setelah menjalankan semua test, Anda akan memiliki:

1. **📊 Complete API Coverage**: Semua endpoint tested
2. **🧪 Automated Validation**: Test scripts validate responses
3. **🔄 Dynamic Data**: Variables untuk testing realistic
4. **📝 Comprehensive Logging**: Semua request/response ter-record
5. **🎯 Error Handling**: Validation dan error scenarios tested

**🚀 Selamat! API Data Pemohon Anda sudah fully tested dan ready untuk production!**

---

## 📞 **Next Steps**

1. **Authentication**: Tambahkan token-based auth jika diperlukan
2. **Performance**: Test dengan data volume besar
3. **Integration**: Integrate dengan frontend application
4. **Documentation**: Share collection dengan tim development

**Happy Testing! 🎯**

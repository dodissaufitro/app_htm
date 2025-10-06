# Resource Persetujuan Developer

## Overview

Resource ini dibuat khusus untuk mengelola permohonan yang telah disetujui verifikator (status_permohonan = 2) dan hanya dapat diakses oleh user dengan urutan = 3 (Developer).

## Features

### 1. Access Control

-   **Navigation**: Hanya muncul untuk user dengan urutan = 3
-   **Access**: Hanya bisa diakses oleh user dengan urutan = 3
-   **Badge Count**: Menampilkan jumlah permohonan yang perlu diproses Developer

### 2. Data Filtering

-   Hanya menampilkan data pemohon dengan `status_permohonan = 2`
-   Sorting berdasarkan `updated_at` DESC (terbaru di atas)
-   Include relasi: bank, status, appVerifikator

### 3. Table Columns

-   ID Pendaftaran (searchable, sortable, badge)
-   Nama Pemohon (searchable, sortable, bold)
-   NIK (searchable, copyable)
-   No. HP (searchable, copyable)
-   Gaji (money format)
-   Bank (badge)
-   Harga Unit (money format)
-   Status (badge dengan warna)
-   Catatan (dengan tooltip)
-   Terakhir Update (datetime)

### 4. Actions

#### Single Actions:

-   **Lihat Detail**: View detail permohonan
-   **Proses Persetujuan**: Edit form untuk update status
-   **Lanjut ke Bank**: Quick action untuk status = 9
-   **Tunda**: Quick action untuk status = 6
-   **Tolak**: Quick action untuk status = 3

#### Bulk Actions:

-   **Lanjut ke Bank (Bulk)**: Proses multiple records sekaligus

### 5. Form Fields

-   Informasi Pemohon (read-only)
-   Detail Permohonan (read-only)
-   Status Permohonan (editable dengan options: 2, 9, 6, 3)
-   Catatan Developer (editable textarea)

### 6. Filters

-   Filter berdasarkan Bank
-   Filter berdasarkan range Gaji
-   Filter berdasarkan range Tanggal Update

### 7. Tabs (List Page)

-   **Semua**: Semua permohonan
-   **Perlu Perhatian**: Permohonan > 3 hari tidak diupdate
-   **Terbaru**: Permohonan dalam 1 hari terakhir

### 8. Navigation

-   Group: "Developer Workflow"
-   Icon: heroicon-o-code-bracket
-   Sort: 3
-   Badge: Menampilkan count permohonan status = 2

## Status Mapping

-   `2`: Approval Pengembang/Developer (Current)
-   `9`: Lanjut ke Bank
-   `6`: Ditunda Developer
-   `3`: Ditolak

## Testing

Resource telah ditest dan memiliki:

-   ✅ Access control yang benar untuk user urutan = 3
-   ✅ Badge count yang akurat
-   ✅ Query filtering yang tepat
-   ✅ Blocking access untuk user non-developer

## Integration dengan Observer

Setiap perubahan status akan otomatis:

-   Membuat/update record di `app_verifikator` melalui `DataPemohonObserver`
-   Menggunakan catatan dari field `keterangan` jika tersedia
-   Mencegah duplikasi record verifikator

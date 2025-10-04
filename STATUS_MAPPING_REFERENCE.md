# Status Mapping Reference

## Status Permohonan (status_permohonan)

-   **1** = Ditunda (Pending)
-   **2** = Disetujui (Approved)
-   **3** = Ditolak (Rejected)

## Keputusan Verifikator (appVerifikator.keputusan)

-   **pending** = Menunggu
-   **approved** = Disetujui
-   **rejected** = Ditolak
-   **revision** = Perlu Revisi

## Mapping dari Verifikator ke Status Permohonan

```php
$statusMapping = [
    'pending'  => '1',  // Ditunda
    'approved' => '2',  // Disetujui
    'rejected' => '3',  // Ditolak
    'revision' => '1',  // Perlu Revisi = Ditunda
];
```

## Color Coding di UI

-   **Kuning/Warning** = Status 1 (Ditunda)
-   **Hijau/Success** = Status 2 (Disetujui)
-   **Merah/Danger** = Status 3 (Ditolak)

## Database Fields

-   `status_permohonan` di tabel `data_pemohon`
-   `keputusan` di tabel `app_verifikator`
-   `keterangan` di tabel `data_pemohon` (catatan persetujuan)
-   `catatan` di tabel `app_verifikator` (catatan verifikator)

## Important Notes

-   Field `status` di model DataPemohon ditambahkan untuk mencegah mass assignment error
-   Tidak menggunakan relationship `status` di eager loading untuk menghindari konflik
-   Custom form handling untuk memisahkan data pemohon dan verifikator
-   Database transaction untuk konsistensi data

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreDataPemohonRequest;
use App\Models\DataPemohon;
use App\Models\Status;
use App\Models\DaftarBank;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DataPemohonController extends Controller
{
    /**
     * Store a new data pemohon
     */
    public function store(StoreDataPemohonRequest $request): JsonResponse
    {
        try {
            // Validation is handled by StoreDataPemohonRequest

            DB::beginTransaction();

            // Generate unique id_pendaftaran if book_number not provided
            $idPendaftaran = $request->book_number ?? $this->generateIdPendaftaran();

            // Map API fields to database fields
            $dataPemohon = [
                'id_pendaftaran' => $idPendaftaran,
                'username' => $this->generateUsername($request->name),
                'nik' => $request->nik,
                'nama' => $request->name,
                'no_hp' => $request->mobile_phone_number,
                'npwp' => $request->npwp,
                'validasi_npwp' => $request->is_valid_npwp ? 1 : 0,
                'nama_npwp' => $request->checked_npwp_name,
                'status_npwp' => $request->is_valid_npwp ? 1 : 0,
                'pekerjaan' => $request->job,
                'gaji' => $request->salary,
                'status_kawin' => $request->marital_status ?? 0,
                'is_couple_dki' => $request->is_couple_dki ?? false,
                'nik2' => $request->couple_id_card_number,
                'nama2' => $request->couple_name,
                'is_have_booking_kpr_dpnol' => $request->is_have_booking_kpr_dpnol ?? false,
                'tipe_unit' => $request->unit_type,
                'harga_unit' => $request->price,
                'pendidikan' => $request->education_name,
                'sts_rumah' => $request->residence_status_name,
                'korespondensi' => $this->mapCorrespondenceAddress($request->correspondence_address),
                'chkDomisili' => $request->is_domicile_same_with_ektp,
                'chkDomisili2' => $request->is_domicile_same_with_couple,
                'pekerjaan2' => $request->couple_job,
                'gaji2' => $request->couple_income,
                'count_of_vehicle1' => $request->count_of_vehicle1 ?? 0,
                'count_of_vehicle2' => $request->count_of_vehicle2 ?? 0,
                'is_have_saving_bank' => $request->is_have_saving_bank ?? false,
                'is_have_home_credit' => $request->is_have_home_credit ?? false,
                'atpid' => $request->atpid,
                'mounthly_expense1' => $request->mounthly_expense1,
                'mounthly_expense2' => $request->mounthly_expense2,
                'bapenda' => $request->bapenda,
                'aset_hunian' => json_encode($request->aset_hunian ?? []),
                'reason_of_choose_location' => json_encode($request->reason_of_choose_location ?? []),
                'booking_files' => json_encode($request->booking_files ?? []),
                'status_permohonan' => '15', // Default to "Verifikasi Dokumen Pendaftaran"
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

            // Handle address fields
            if ($request->filled('province_name')) {
                $dataPemohon['provinsi_dom'] = $request->province_name;
            }
            if ($request->filled('city_name')) {
                $dataPemohon['kabupaten_dom'] = $request->city_name;
            }
            if ($request->filled('district_name')) {
                $dataPemohon['kecamatan_dom'] = $request->district_name;
            }
            if ($request->filled('village_name')) {
                $dataPemohon['kelurahan_dom'] = $request->village_name;
            }
            if ($request->filled('address')) {
                $dataPemohon['alamat_dom'] = $request->address;
            }

            // Handle couple address fields
            if ($request->filled('couple_province_name')) {
                $dataPemohon['provinsi2'] = $request->couple_province_name;
            }
            if ($request->filled('couple_city_name')) {
                $dataPemohon['kabupaten2'] = $request->couple_city_name;
            }
            if ($request->filled('couple_district_name')) {
                $dataPemohon['kecamatan2'] = $request->couple_district_name;
            }
            if ($request->filled('couple_village_name')) {
                $dataPemohon['kelurahan2'] = $request->couple_village_name;
            }
            if ($request->filled('couple_address')) {
                $dataPemohon['alamat2'] = $request->couple_address;
            }

            // Handle settlement information
            if ($request->filled('settlement_name')) {
                $dataPemohon['lokasi_rumah'] = $request->settlement_name;
            }

            // Create the data pemohon record
            $createdPemohon = DataPemohon::create($dataPemohon);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon berhasil dibuat',
                'data' => [
                    'id' => $createdPemohon->id,
                    'id_pendaftaran' => $createdPemohon->id_pendaftaran,
                    'nama' => $createdPemohon->nama,
                    'nik' => $createdPemohon->nik,
                    'status_permohonan' => $createdPemohon->status_permohonan,
                    'created_at' => $createdPemohon->created_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data pemohon by ID or id_pendaftaran
     */
    public function show(Request $request, $identifier): JsonResponse
    {
        try {
            // Try to find by ID first, then by id_pendaftaran
            $dataPemohon = DataPemohon::where('id', $identifier)
                ->orWhere('id_pendaftaran', $identifier)
                ->with(['bank', 'status'])
                ->first();

            if (!$dataPemohon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pemohon tidak ditemukan'
                ], 404);
            }

            // Transform data to match API format
            $responseData = $this->transformDataPemohonToApiFormat($dataPemohon);

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon berhasil ditemukan',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of data pemohon with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            $query = DataPemohon::with(['bank', 'status']);

            // Apply filters if provided
            if ($request->filled('status_permohonan')) {
                $query->where('status_permohonan', $request->status_permohonan);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('id_pendaftaran', 'like', "%{$search}%");
                });
            }

            $dataPemohon = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $transformedData = $dataPemohon->getCollection()->map(function ($item) {
                return $this->transformDataPemohonToApiFormat($item);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon berhasil diambil',
                'data' => $transformedData,
                'meta' => [
                    'current_page' => $dataPemohon->currentPage(),
                    'last_page' => $dataPemohon->lastPage(),
                    'per_page' => $dataPemohon->perPage(),
                    'total' => $dataPemohon->total(),
                    'from' => $dataPemohon->firstItem(),
                    'to' => $dataPemohon->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update data pemohon
     */
    public function update(Request $request, $identifier): JsonResponse
    {
        try {
            // Find data pemohon
            $dataPemohon = DataPemohon::where('id', $identifier)
                ->orWhere('id_pendaftaran', $identifier)
                ->first();

            if (!$dataPemohon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pemohon tidak ditemukan'
                ], 404);
            }

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:100',
                'mobile_phone_number' => 'sometimes|string|max:100',
                'salary' => 'sometimes|numeric',
                'job' => 'sometimes|string|max:100',
                // Add other validation rules as needed
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Map API fields to database fields for update
            $updateData = [];

            if ($request->filled('name')) {
                $updateData['nama'] = $request->name;
            }
            if ($request->filled('mobile_phone_number')) {
                $updateData['no_hp'] = $request->mobile_phone_number;
            }
            if ($request->filled('salary')) {
                $updateData['gaji'] = $request->salary;
            }
            if ($request->filled('job')) {
                $updateData['pekerjaan'] = $request->job;
            }
            if ($request->filled('status_permohonan')) {
                $updateData['status_permohonan'] = $request->status_permohonan;
            }

            // Add updated_by
            $updateData['updated_by'] = Auth::id();

            // Update the record
            $dataPemohon->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon berhasil diperbarui',
                'data' => [
                    'id' => $dataPemohon->id,
                    'id_pendaftaran' => $dataPemohon->id_pendaftaran,
                    'nama' => $dataPemohon->nama,
                    'nik' => $dataPemohon->nik,
                    'status_permohonan' => $dataPemohon->status_permohonan,
                    'updated_at' => $dataPemohon->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete data pemohon
     */
    public function destroy($identifier): JsonResponse
    {
        try {
            // Find data pemohon
            $dataPemohon = DataPemohon::where('id', $identifier)
                ->orWhere('id_pendaftaran', $identifier)
                ->first();

            if (!$dataPemohon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pemohon tidak ditemukan'
                ], 404);
            }

            $dataPemohon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique id_pendaftaran
     */
    private function generateIdPendaftaran(): string
    {
        do {
            $idPendaftaran = date('Y') . date('m') . date('d') . sprintf('%05d', rand(1, 99999));
        } while (DataPemohon::where('id_pendaftaran', $idPendaftaran)->exists());

        return $idPendaftaran;
    }

    /**
     * Generate username from name
     */
    private function generateUsername(string $name): string
    {
        $username = Str::slug($name, '');
        $counter = 1;
        $originalUsername = $username;

        while (DataPemohon::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Map correspondence address
     */
    private function mapCorrespondenceAddress(?string $correspondenceAddress): ?string
    {
        if (!$correspondenceAddress) {
            return null;
        }

        return match (strtolower($correspondenceAddress)) {
            'alamat ktp' => '1',
            'alamat domisili' => '2',
            default => '1'
        };
    }

    /**
     * Transform DataPemohon model to API format
     */
    private function transformDataPemohonToApiFormat(DataPemohon $dataPemohon): array
    {
        return [
            'id' => $dataPemohon->id,
            'book_number' => $dataPemohon->id_pendaftaran,
            'settlement_id' => null, // Not stored in current table
            'settlement_name' => $dataPemohon->lokasi_rumah,
            'bdtime' => $dataPemohon->created_at?->format('Y-m-d H:i:s'),
            'npwp' => $dataPemohon->npwp,
            'nik' => $dataPemohon->nik,
            'email_address' => null, // Not stored in current table
            'name' => $dataPemohon->nama,
            'mobile_phone_number' => $dataPemohon->no_hp,
            'job' => $dataPemohon->pekerjaan,
            'salary' => $dataPemohon->gaji,
            'marital_status' => $dataPemohon->status_kawin,
            'is_couple_dki' => $dataPemohon->is_couple_dki,
            'couple_id_card_number' => $dataPemohon->nik2,
            'couple_name' => $dataPemohon->nama2,
            'is_have_booking_kpr_dpnol' => $dataPemohon->is_have_booking_kpr_dpnol,
            'unit_type' => $dataPemohon->tipe_unit,
            'price' => $dataPemohon->harga_unit,
            'is_valid_npwp' => $dataPemohon->validasi_npwp == 1,
            'checked_npwp_number' => $dataPemohon->npwp,
            'checked_npwp_name' => $dataPemohon->nama_npwp,
            'checked_npwp_message' => null,
            'education_id' => null,
            'education_name' => $dataPemohon->pendidikan,
            'residence_status_id' => null,
            'residence_status_name' => $dataPemohon->sts_rumah,
            'correspondence_address' => $dataPemohon->korespondensi == '1' ? 'Alamat KTP' : 'Alamat Domisili',
            'is_domicile_same_with_ektp' => $dataPemohon->chkDomisili,
            'province_id' => null,
            'province_name' => $dataPemohon->provinsi_dom,
            'city_id' => null,
            'city_name' => $dataPemohon->kabupaten_dom,
            'district_id' => null,
            'district_name' => $dataPemohon->kecamatan_dom,
            'village_id' => null,
            'village_name' => $dataPemohon->kelurahan_dom,
            'address' => $dataPemohon->alamat_dom,
            'is_domicile_same_with_couple' => $dataPemohon->chkDomisili2,
            'couple_province_id' => null,
            'couple_province_name' => $dataPemohon->provinsi2,
            'couple_city_id' => null,
            'couple_city_name' => $dataPemohon->kabupaten2,
            'couple_district_id' => null,
            'couple_district_name' => $dataPemohon->kecamatan2,
            'couple_village_id' => null,
            'couple_village_name' => $dataPemohon->kelurahan2,
            'couple_address' => $dataPemohon->alamat2,
            'couple_job' => $dataPemohon->pekerjaan2,
            'couple_income' => $dataPemohon->gaji2,
            'count_of_vehicle1' => $dataPemohon->count_of_vehicle1,
            'count_of_vehicle2' => $dataPemohon->count_of_vehicle2,
            'is_have_saving_bank' => $dataPemohon->is_have_saving_bank,
            'is_have_home_credit' => $dataPemohon->is_have_home_credit,
            'atpid' => $dataPemohon->atpid,
            'atp_name' => null, // Not stored, would need lookup table
            'mounthly_expense1' => $dataPemohon->mounthly_expense1,
            'mounthly_expense2' => $dataPemohon->mounthly_expense2,
            'bapenda' => $dataPemohon->bapenda,
            'aset_hunian' => json_decode($dataPemohon->aset_hunian, true) ?? [],
            'reason_of_choose_location' => json_decode($dataPemohon->reason_of_choose_location, true) ?? [],
            'government_assistance_aid' => [], // Not stored in current table
            'booking_files' => json_decode($dataPemohon->booking_files, true) ?? [],
            'status_permohonan' => $dataPemohon->status_permohonan,
            'status_name' => $dataPemohon->status?->nama_status,
            'bank_id' => $dataPemohon->id_bank,
            'bank_name' => $dataPemohon->bank?->nama_bank,
            'created_at' => $dataPemohon->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $dataPemohon->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

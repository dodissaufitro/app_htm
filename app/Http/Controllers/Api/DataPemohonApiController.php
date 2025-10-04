<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataPemohon;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DataPemohonApiController extends Controller
{
    /**
     * Display a listing of data pemohon
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DataPemohon::with(['status']);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status_permohonan', $request->status);
            }

            // Search by name or NIK
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('id_pendaftaran', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $dataPemohon = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon retrieved successfully',
                'data' => $dataPemohon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data pemohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created data pemohon
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validatePemohonData($request);

            // Generate unique registration ID if not provided
            if (!isset($validatedData['id_pendaftaran'])) {
                $validatedData['id_pendaftaran'] = $this->generateRegistrationId();
            }

            // Handle file uploads
            if ($request->has('booking_files')) {
                $validatedData['booking_files'] = $this->processBookingFiles($request->booking_files);
            }

            // Handle JSON fields
            $jsonFields = ['aset_hunian', 'reason_of_choose_location', 'government_assistance_aid'];
            foreach ($jsonFields as $field) {
                if ($request->has($field)) {
                    $validatedData[$field] = is_string($request->$field)
                        ? $request->$field
                        : json_encode($request->$field);
                }
            }

            $dataPemohon = DataPemohon::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon created successfully',
                'data' => $dataPemohon->load('status')
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create data pemohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified data pemohon
     */
    public function show($id): JsonResponse
    {
        try {
            $dataPemohon = DataPemohon::with(['status'])->findOrFail($id);

            // Parse JSON fields for response
            $dataPemohon->aset_hunian = json_decode($dataPemohon->aset_hunian ?? '[]');
            $dataPemohon->reason_of_choose_location = json_decode($dataPemohon->reason_of_choose_location ?? '[]');
            $dataPemohon->government_assistance_aid = json_decode($dataPemohon->government_assistance_aid ?? '[]');
            $dataPemohon->booking_files = json_decode($dataPemohon->booking_files ?? '[]');

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon retrieved successfully',
                'data' => $dataPemohon
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pemohon not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data pemohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified data pemohon
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $dataPemohon = DataPemohon::findOrFail($id);
            $validatedData = $this->validatePemohonData($request, $id);

            // Handle file uploads
            if ($request->has('booking_files')) {
                $validatedData['booking_files'] = $this->processBookingFiles($request->booking_files);
            }

            // Handle JSON fields
            $jsonFields = ['aset_hunian', 'reason_of_choose_location', 'government_assistance_aid'];
            foreach ($jsonFields as $field) {
                if ($request->has($field)) {
                    $validatedData[$field] = is_string($request->$field)
                        ? $request->$field
                        : json_encode($request->$field);
                }
            }

            $dataPemohon->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon updated successfully',
                'data' => $dataPemohon->load('status')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pemohon not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update data pemohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified data pemohon
     */
    public function destroy($id): JsonResponse
    {
        try {
            $dataPemohon = DataPemohon::findOrFail($id);

            // Delete associated files
            if ($dataPemohon->booking_files) {
                $files = json_decode($dataPemohon->booking_files, true);
                foreach ($files as $file) {
                    if (isset($file['fname']) && $file['fname']) {
                        Storage::disk('public')->delete('booking_files/' . $file['fname']);
                    }
                }
            }

            $dataPemohon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pemohon not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete data pemohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data pemohon by book number
     */
    public function getByBookNumber($bookNumber): JsonResponse
    {
        try {
            $dataPemohon = DataPemohon::with(['status'])
                ->where('id_pendaftaran', $bookNumber)
                ->firstOrFail();

            // Parse JSON fields for response
            $dataPemohon->aset_hunian = json_decode($dataPemohon->aset_hunian ?? '[]');
            $dataPemohon->reason_of_choose_location = json_decode($dataPemohon->reason_of_choose_location ?? '[]');
            $dataPemohon->government_assistance_aid = json_decode($dataPemohon->government_assistance_aid ?? '[]');
            $dataPemohon->booking_files = json_decode($dataPemohon->booking_files ?? '[]');

            return response()->json([
                'success' => true,
                'message' => 'Data pemohon retrieved successfully',
                'data' => $dataPemohon
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pemohon not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data pemohon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate pemohon data
     */
    private function validatePemohonData(Request $request, $id = null): array
    {
        $rules = [
            'id_pendaftaran' => 'sometimes|string|max:255|unique:data_pemohon,id_pendaftaran,' . $id,
            'username' => 'required|string|max:100',
            'nik' => 'nullable|string|size:16',
            'kk' => 'nullable|string|size:16',
            'nama' => 'nullable|string|max:100',
            'pendidikan' => 'nullable|string|max:100',
            'npwp' => 'nullable|string|max:100',
            'nama_npwp' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:100',
            'email_address' => 'nullable|email|max:255',
            'job' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric',
            'marital_status' => 'nullable|in:0,1',
            'is_couple_dki' => 'nullable|boolean',
            'couple_id_card_number' => 'nullable|string|max:16',
            'couple_name' => 'nullable|string|max:100',
            'couple_job' => 'nullable|string|max:255',
            'couple_income' => 'nullable|numeric',
            'education_id' => 'nullable|string|max:10',
            'education_name' => 'nullable|string|max:100',
            'residence_status_id' => 'nullable|string|max:10',
            'residence_status_name' => 'nullable|string|max:100',
            'unit_type' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'count_of_vehicle1' => 'nullable|integer|min:0',
            'count_of_vehicle2' => 'nullable|integer|min:0',
            'is_have_saving_bank' => 'nullable|boolean',
            'is_have_home_credit' => 'nullable|boolean',
            'is_have_booking_kpr_dpnol' => 'nullable|boolean',
            'atpid' => 'nullable|string|max:10',
            'atp_name' => 'nullable|string|max:255',
            'mounthly_expense1' => 'nullable|numeric',
            'mounthly_expense2' => 'nullable|numeric',
            'settlement_id' => 'nullable|string|max:10',
            'settlement_name' => 'nullable|string|max:255',
            'status_permohonan' => 'nullable|string|exists:status,kode',
            'correspondence_address' => 'nullable|string|max:255',
            'is_domicile_same_with_ektp' => 'nullable|in:0,1',
            'is_domicile_same_with_couple' => 'nullable|in:0,1',
            // Address fields
            'province_id' => 'nullable|string|max:10',
            'province_name' => 'nullable|string|max:100',
            'city_id' => 'nullable|string|max:10',
            'city_name' => 'nullable|string|max:100',
            'district_id' => 'nullable|string|max:10',
            'district_name' => 'nullable|string|max:100',
            'village_id' => 'nullable|string|max:10',
            'village_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            // Couple address fields
            'couple_province_id' => 'nullable|string|max:10',
            'couple_province_name' => 'nullable|string|max:100',
            'couple_city_id' => 'nullable|string|max:10',
            'couple_city_name' => 'nullable|string|max:100',
            'couple_district_id' => 'nullable|string|max:10',
            'couple_district_name' => 'nullable|string|max:100',
            'couple_village_id' => 'nullable|string|max:10',
            'couple_village_name' => 'nullable|string|max:100',
            'couple_address' => 'nullable|string|max:500',
        ];

        return Validator::make($request->all(), $rules)->validate();
    }

    /**
     * Process booking files
     */
    private function processBookingFiles($bookingFiles): string
    {
        $processedFiles = [];

        if (is_string($bookingFiles)) {
            return $bookingFiles; // Already JSON string
        }

        foreach ($bookingFiles as $file) {
            $processedFile = [
                'fname' => $file['fname'] ?? null,
                'base64' => $file['base64'] ?? null,
                'file_type' => $file['file_type'] ?? null,
            ];

            // If base64 is provided, save the file
            if (!empty($file['base64'])) {
                $fileName = $this->saveBase64File($file['base64'], $file['file_type'] ?? 'document');
                $processedFile['fname'] = $fileName;
                $processedFile['base64'] = null; // Don't store base64 in database
            }

            $processedFiles[] = $processedFile;
        }

        return json_encode($processedFiles);
    }

    /**
     * Save base64 file to storage
     */
    private function saveBase64File($base64Data, $fileType): string
    {
        // Remove data:image/jpeg;base64, prefix if exists
        if (strpos($base64Data, ',') !== false) {
            $base64Data = explode(',', $base64Data)[1];
        }

        $fileData = base64_decode($base64Data);
        $fileName = Str::random(40) . '.jpg'; // Generate random filename

        Storage::disk('public')->put('booking_files/' . $fileName, $fileData);

        return $fileName;
    }

    /**
     * Generate unique registration ID
     */
    private function generateRegistrationId(): string
    {
        $date = now()->format('Ymd');
        $lastNumber = DataPemohon::where('id_pendaftaran', 'like', $date . '%')
            ->count() + 1;

        return $date . str_pad($lastNumber, 5, '0', STR_PAD_LEFT);
    }
}

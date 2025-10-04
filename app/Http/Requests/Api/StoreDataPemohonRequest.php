<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreDataPemohonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Set to true for now, add authentication logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'book_number' => 'sometimes|string|max:255',
            'settlement_id' => 'sometimes|string|max:100',
            'settlement_name' => 'sometimes|string|max:255',
            'bdtime' => 'sometimes|date',
            'npwp' => 'sometimes|string|max:100',
            'nik' => 'required|string|max:16',
            'email_address' => 'sometimes|email|max:255',
            'name' => 'required|string|max:100',
            'mobile_phone_number' => 'required|string|max:100',
            'job' => 'sometimes|string|max:100',
            'salary' => 'sometimes|numeric|min:0',
            'marital_status' => 'sometimes|integer|in:0,1,2',
            'is_couple_dki' => 'sometimes|boolean',
            'couple_id_card_number' => 'sometimes|nullable|string|max:100',
            'couple_name' => 'sometimes|nullable|string|max:100',
            'is_have_booking_kpr_dpnol' => 'sometimes|boolean',
            'unit_type' => 'sometimes|nullable|string|max:255',
            'price' => 'sometimes|nullable|numeric|min:0',
            'is_valid_npwp' => 'sometimes|boolean',
            'checked_npwp_number' => 'sometimes|nullable|string|max:100',
            'checked_npwp_name' => 'sometimes|nullable|string|max:255',
            'checked_npwp_message' => 'sometimes|nullable|string|max:500',
            'education_id' => 'sometimes|string|max:10',
            'education_name' => 'sometimes|string|max:100',
            'residence_status_id' => 'sometimes|string|max:10',
            'residence_status_name' => 'sometimes|string|max:100',
            'correspondence_address' => 'sometimes|string|max:255',
            'is_domicile_same_with_ektp' => 'sometimes|string|in:0,1',
            'province_id' => 'sometimes|nullable|string|max:10',
            'province_name' => 'sometimes|nullable|string|max:100',
            'city_id' => 'sometimes|nullable|string|max:10',
            'city_name' => 'sometimes|nullable|string|max:100',
            'district_id' => 'sometimes|nullable|string|max:10',
            'district_name' => 'sometimes|nullable|string|max:100',
            'village_id' => 'sometimes|nullable|string|max:10',
            'village_name' => 'sometimes|nullable|string|max:100',
            'address' => 'sometimes|nullable|string|max:255',
            'is_domicile_same_with_couple' => 'sometimes|string|in:0,1',
            'couple_province_id' => 'sometimes|nullable|string|max:10',
            'couple_province_name' => 'sometimes|nullable|string|max:100',
            'couple_city_id' => 'sometimes|nullable|string|max:10',
            'couple_city_name' => 'sometimes|nullable|string|max:100',
            'couple_district_id' => 'sometimes|nullable|string|max:10',
            'couple_district_name' => 'sometimes|nullable|string|max:100',
            'couple_village_id' => 'sometimes|nullable|string|max:10',
            'couple_village_name' => 'sometimes|nullable|string|max:100',
            'couple_address' => 'sometimes|nullable|string|max:255',
            'couple_job' => 'sometimes|nullable|string|max:100',
            'couple_income' => 'sometimes|numeric|min:0',
            'count_of_vehicle1' => 'sometimes|integer|min:0',
            'count_of_vehicle2' => 'sometimes|integer|min:0',
            'is_have_saving_bank' => 'sometimes|boolean',
            'is_have_home_credit' => 'sometimes|boolean',
            'atpid' => 'sometimes|integer',
            'atp_name' => 'sometimes|string|max:255',
            'mounthly_expense1' => 'sometimes|numeric|min:0',
            'mounthly_expense2' => 'sometimes|numeric|min:0',
            'bapenda' => 'sometimes|nullable|string',
            'aset_hunian' => 'sometimes|array',
            'aset_hunian.*' => 'sometimes|array',
            'reason_of_choose_location' => 'sometimes|array',
            'reason_of_choose_location.*' => 'sometimes|array',
            'reason_of_choose_location.*.id' => 'sometimes|integer',
            'reason_of_choose_location.*.name' => 'sometimes|string',
            'government_assistance_aid' => 'sometimes|array',
            'government_assistance_aid.*' => 'sometimes|array',
            'booking_files' => 'sometimes|array',
            'booking_files.*' => 'sometimes|array',
            'booking_files.*.fname' => 'sometimes|string|nullable',
            'booking_files.*.base64' => 'sometimes|string|nullable',
            'booking_files.*.file_type' => 'sometimes|string',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'name' => 'nama',
            'mobile_phone_number' => 'nomor handphone',
            'salary' => 'gaji',
            'job' => 'pekerjaan',
            'npwp' => 'NPWP',
            'email_address' => 'alamat email',
            'marital_status' => 'status pernikahan',
            'couple_name' => 'nama pasangan',
            'couple_income' => 'gaji pasangan',
            'education_name' => 'pendidikan',
            'residence_status_name' => 'status tempat tinggal',
            'province_name' => 'nama provinsi',
            'city_name' => 'nama kota',
            'district_name' => 'nama kecamatan',
            'village_name' => 'nama kelurahan',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi',
            'nik.max' => 'NIK maksimal 16 karakter',
            'name.required' => 'Nama wajib diisi',
            'name.max' => 'Nama maksimal 100 karakter',
            'mobile_phone_number.required' => 'Nomor handphone wajib diisi',
            'mobile_phone_number.max' => 'Nomor handphone maksimal 100 karakter',
            'email_address.email' => 'Format email tidak valid',
            'salary.numeric' => 'Gaji harus berupa angka',
            'salary.min' => 'Gaji tidak boleh negatif',
            'marital_status.in' => 'Status pernikahan harus 0 (belum menikah), 1 (menikah), atau 2 (cerai)',
            'price.numeric' => 'Harga harus berupa angka',
            'price.min' => 'Harga tidak boleh negatif',
            'couple_income.numeric' => 'Gaji pasangan harus berupa angka',
            'couple_income.min' => 'Gaji pasangan tidak boleh negatif',
            'count_of_vehicle1.integer' => 'Jumlah kendaraan 1 harus berupa angka',
            'count_of_vehicle1.min' => 'Jumlah kendaraan 1 tidak boleh negatif',
            'count_of_vehicle2.integer' => 'Jumlah kendaraan 2 harus berupa angka',
            'count_of_vehicle2.min' => 'Jumlah kendaraan 2 tidak boleh negatif',
            'mounthly_expense1.numeric' => 'Pengeluaran bulanan 1 harus berupa angka',
            'mounthly_expense1.min' => 'Pengeluaran bulanan 1 tidak boleh negatif',
            'mounthly_expense2.numeric' => 'Pengeluaran bulanan 2 harus berupa angka',
            'mounthly_expense2.min' => 'Pengeluaran bulanan 2 tidak boleh negatif',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

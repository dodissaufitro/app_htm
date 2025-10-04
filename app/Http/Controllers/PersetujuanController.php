<?php

namespace App\Http\Controllers;

use App\Services\BapendaService;
use App\Models\DataPemohon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersetujuanController extends Controller
{
    private BapendaService $bapendaService;

    public function __construct(BapendaService $bapendaService)
    {
        $this->bapendaService = $bapendaService;
    }

    /**
     * Update data Bapenda untuk pemohon dalam proses persetujuan
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateBapenda(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:data_pemohon,id'
            ]);

            $id = $request->input('id');

            Log::info("PersetujuanController: Starting Bapenda update for pemohon ID: {$id}");

            // Update data Bapenda menggunakan service
            $result = $this->bapendaService->updateBapendaDataById($id);

            if ($result['success']) {
                session()->flash('success', $result['message']);
                Log::info("PersetujuanController: Bapenda update successful", [
                    'id' => $id,
                    'nik' => $result['nik']
                ]);
            } else {
                session()->flash('error', $result['message']);
                Log::error("PersetujuanController: Bapenda update failed", [
                    'id' => $id,
                    'error' => $result['error']
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Data tidak valid: ' . implode(', ', $e->errors()['id'] ?? ['ID tidak valid']));
        } catch (\Exception $e) {
            Log::error("PersetujuanController: Exception in updateBapenda", [
                'id' => $request->input('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        // Redirect kembali ke halaman persetujuan
        return redirect()->route('persetujuan.pemohon', ['id' => $request->input('id')]);
    }

    /**
     * Update data Bapenda untuk pemohon berdasarkan NIK dalam proses persetujuan
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateBapendaByNik(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'nik' => 'required|string|min:16|max:16'
            ]);

            $nik = $request->input('nik');

            Log::info("PersetujuanController: Starting Bapenda update for NIK: {$nik}");

            // Cari data pemohon berdasarkan NIK
            $dataPemohon = DataPemohon::where('nik', $nik)->first();

            if (!$dataPemohon) {
                session()->flash('error', "Data pemohon dengan NIK {$nik} tidak ditemukan");
                return redirect()->back();
            }

            // Update data Bapenda menggunakan service
            $result = $this->bapendaService->updateBapendaDataById($dataPemohon->id);

            if ($result['success']) {
                session()->flash('success', $result['message']);
                Log::info("PersetujuanController: Bapenda update by NIK successful", [
                    'nik' => $nik,
                    'id' => $dataPemohon->id,
                    'id_pendaftaran' => $result['id_pendaftaran']
                ]);

                // Redirect ke halaman persetujuan pemohon
                return redirect()->route('persetujuan.pemohon', ['id' => $dataPemohon->id]);
            } else {
                session()->flash('error', $result['message']);
                Log::error("PersetujuanController: Bapenda update by NIK failed", [
                    'nik' => $nik,
                    'id' => $dataPemohon->id,
                    'error' => $result['error']
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->flatten()->implode(', ');
            session()->flash('error', 'Data tidak valid: ' . $errors);
        } catch (\Exception $e) {
            Log::error("PersetujuanController: Exception in updateBapendaByNik", [
                'nik' => $request->input('nik'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }

        // Redirect kembali jika ada error
        return redirect()->back();
    }

    /**
     * Tampilkan halaman pencarian dan update Bapenda berdasarkan NIK
     *
     * @return \Illuminate\View\View
     */
    public function searchByNik()
    {
        return view('persetujuan.search-by-nik');
    }

    /**
     * Tampilkan halaman persetujuan pemohon
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function pemohon(Request $request)
    {
        try {
            $id = $request->query('id');

            if (!$id) {
                return redirect()->back()->with('error', 'ID pemohon tidak ditemukan');
            }

            $dataPemohon = DataPemohon::with(['bank', 'status'])
                ->find($id);

            if (!$dataPemohon) {
                return redirect()->back()->with('error', 'Data pemohon tidak ditemukan');
            }

            // Parse data Bapenda jika ada
            $bapendaData = null;
            $asetHunianData = null;
            $bapendaPasanganData = null;
            $bapendaPasanganPbbData = null;

            if (!empty($dataPemohon->bapenda)) {
                try {
                    $bapendaData = json_decode($dataPemohon->bapenda, true);
                } catch (\Exception $e) {
                    Log::warning("Failed to parse bapenda data for pemohon ID: {$id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!empty($dataPemohon->aset_hunian)) {
                try {
                    $asetHunianData = json_decode($dataPemohon->aset_hunian, true);
                } catch (\Exception $e) {
                    Log::warning("Failed to parse aset_hunian data for pemohon ID: {$id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Parse data Bapenda pasangan jika ada
            if (!empty($dataPemohon->bapenda_pasangan)) {
                try {
                    $bapendaPasanganData = json_decode($dataPemohon->bapenda_pasangan, true);
                } catch (\Exception $e) {
                    Log::warning("Failed to parse bapenda_pasangan data for pemohon ID: {$id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!empty($dataPemohon->bapenda_pasangan_pbb)) {
                try {
                    $bapendaPasanganPbbData = json_decode($dataPemohon->bapenda_pasangan_pbb, true);
                } catch (\Exception $e) {
                    Log::warning("Failed to parse bapenda_pasangan_pbb data for pemohon ID: {$id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return view('persetujuan.pemohon', compact(
                'dataPemohon',
                'bapendaData',
                'asetHunianData',
                'bapendaPasanganData',
                'bapendaPasanganPbbData'
            ));
        } catch (\Exception $e) {
            Log::error("PersetujuanController: Exception in pemohon view", [
                'id' => $request->query('id'),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem');
        }
    }

    /**
     * Update status persetujuan pemohon
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateStatus(Request $request): RedirectResponse
    {
        try {
            // Check if current user has permission to update status (urutan = 1)
            $currentUser = Auth::user();
            if (!$currentUser || $currentUser->urutan !== 1) {
                session()->flash('error', 'Anda tidak memiliki akses untuk mengubah status. Hanya user dengan urutan 1 yang dapat mengubah status.');
                return redirect()->back();
            }

            $request->validate([
                'id' => 'required|integer|exists:data_pemohon,id',
                'status_permohonan' => 'required|string',
                'keterangan' => 'nullable|string|max:1000'
            ]);

            $id = $request->input('id');
            $newStatus = $request->input('status_permohonan');
            $dataPemohon = DataPemohon::find($id);

            if (!$dataPemohon) {
                return redirect()->back()->with('error', 'Data pemohon tidak ditemukan');
            }

            // Prevent duplicate submission - check if status is already the same
            if ($dataPemohon->status_permohonan == $newStatus) {
                Log::info("PersetujuanController: Status unchanged, skipping update", [
                    'id' => $id,
                    'current_status' => $dataPemohon->status_permohonan,
                    'requested_status' => $newStatus
                ]);

                session()->flash('info', 'Status permohonan sudah sama, tidak ada perubahan');
                return redirect()->route('persetujuan.pemohon', ['id' => $id]);
            }

            // Use transaction to ensure atomicity
            DB::transaction(function () use ($dataPemohon, $newStatus, $request) {
                // Update status
                $dataPemohon->update([
                    'status_permohonan' => $newStatus,
                    'keterangan' => $request->input('keterangan'),
                    'updated_by' => Auth::id(),
                ]);

                Log::info("PersetujuanController: Status updated", [
                    'id' => $dataPemohon->id,
                    'old_status' => $dataPemohon->getOriginal('status_permohonan'),
                    'new_status' => $newStatus,
                    'updated_by' => Auth::id(),
                    'user_urutan' => Auth::user()->urutan
                ]);
            });

            session()->flash('success', 'Status permohonan berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = collect($e->errors())->flatten()->implode(', ');
            session()->flash('error', 'Data tidak valid: ' . $errors);
        } catch (\Exception $e) {
            Log::error("PersetujuanController: Exception in updateStatus", [
                'id' => $request->input('id'),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Terjadi kesalahan sistem');
        }

        return redirect()->route('persetujuan.pemohon', ['id' => $request->input('id')]);
    }
}

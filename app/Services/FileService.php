<?php

namespace App\Services;

use App\Services\Interfaces\FileServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileService implements FileServiceInterface
{
    /**
     * Upload formulir file
     *
     * @param mixed $file
     * @return array
     */
    public function uploadFormulir($file): array
    {
        if (!$file) {
            return [
                'success' => false,
                'message' => 'File formulir wajib diunggah.'
            ];
        }

        try {
            $fileName = 'formulir_peminjaman_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/forms', $fileName);
            
            DB::table('m_formulir')->insert([
                'formulir_path' => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Formulir berhasil diunggah!',
                'path' => $path
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengunggah formulir: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Download formulir file
     *
     * @return array
     */
    public function downloadFormulir(): array
    {
        $formulir = DB::table('m_formulir')->orderBy('formulir_id', 'desc')->first();

        if (!$formulir) {
            return [
                'success' => false,
                'message' => 'Belum ada formulir yang tersedia.'
            ];
        }

        $path = storage_path('app/' . $formulir->formulir_path);

        if (!file_exists($path)) {
             return [
                'success' => false,
                'message' => 'File fisik formulir tidak ditemukan.'
            ];
        }

        $headers = [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        $waktu = date('Y-m-d H:i:s');
        $namaDownload = 'Formulir JTI Peminjaman Ruangan JTI_' . $waktu . '.pdf';

        return [
            'success' => true,
            'path' => $path,
            'headers' => $headers,
            'downloadName' => $namaDownload
        ];
    }
}

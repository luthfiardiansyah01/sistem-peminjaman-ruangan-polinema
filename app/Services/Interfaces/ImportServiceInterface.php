<?php

namespace App\Services\Interfaces;

interface ImportServiceInterface
{
    /**
     * Validate and import an uploaded Excel file by user type.
     *
     * @param mixed $file
     * @param string $type
     * @return array
     */
    public function importUploadedUserFile($file, string $type): array;

    /**
     * Import dosen from Excel file
     *
     * @param string $filePath
     * @return array
     */
    public function importDosenFromExcel(string $filePath): array;

    /**
     * Import mahasiswa from Excel file
     *
     * @param string $filePath
     * @return array
     */
    public function importMahasiswaFromExcel(string $filePath): array;

    /**
     * Import tendik from Excel file
     *
     * @param string $filePath
     * @return array
     */
    public function importTendikFromExcel(string $filePath): array;
}

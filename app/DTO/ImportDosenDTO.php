<?php

namespace App\DTO;

/**
 * Data Transfer Object for importing Dosen users from Excel
 * 
 * This DTO encapsulates Dosen import data with validation and sanitization
 * 
 * Validates: Requirements 11.1, 11.2, 11.3, 11.4, 19.1, 19.2, 19.3, 19.4, 30.1
 * 
 * @package App\DTO
 */
class ImportDosenDTO extends ImportUserDTO
{
    /**
     * Get the user type
     * 
     * @return string
     */
    public static function getUserType(): string
    {
        return 'DSN';
    }
    
    /**
     * Get identifier field name
     * 
     * @return string
     */
    public static function getIdentifierFieldName(): string
    {
        return 'NIP/NIDN';
    }

    /**
     * Create ImportDosenDTO from Excel row data
     *
     * @param array $rowData Excel row data
     * @return self
     */
    public static function fromExcelRow(array $rowData): self
    {
        return new self(
            $rowData[0] ?? '', // NIP/NIDN
            $rowData[1] ?? '', // Nama
            $rowData[2] ?? null, // No HP
            $rowData[3] ?? null, // Email
            $rowData[4] ?? null, // Prodi Nama
            null // Dosen doesn't have class
        );
    }
    
    /**
     * Validate Dosen-specific data
     * 
     * @param array $errors Reference to errors array
     */
    protected function validateConcrete(array &$errors): void
    {
        // Identifier validation (NIP/NIDN) — aturan panjang final per jenis (NIDN 10 / NIP 18,
        // revisi ke-2 poin 4) belum diterapkan, masih perlu dikonfirmasi ulang oleh klien.
        if (empty($this->identifier)) {
            $errors['identifier'] = ['NIP/NIDN wajib diisi'];
        } elseif (!preg_match('/^[0-9]{10,16}$/', $this->identifier)) {
            $errors['identifier'] = ['NIP/NIDN harus berupa angka 10-16 digit'];
        }
        
        // Name validation
        if (empty($this->name)) {
            $errors['name'] = ['Nama wajib diisi'];
        } elseif (strlen($this->name) < 2) {
            $errors['name'] = ['Nama minimal 2 karakter'];
        } elseif (strlen($this->name) > 100) {
            $errors['name'] = ['Nama maksimal 100 karakter'];
        }
        
        // Phone number validation (optional)
        if (!empty($this->phoneNumber) && !preg_match('/^[0-9]{10,15}$/', $this->phoneNumber)) {
            $errors['phone_number'] = ['Nomor telepon harus berupa angka 10-15 digit'];
        }
        
        // Email validation (optional)
        if (!empty($this->email)) {
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = ['Format email tidak valid'];
            } elseif (strlen($this->email) > 100) {
                $errors['email'] = ['Email maksimal 100 karakter'];
            }
        }
        
        // Prodi name validation (required for Dosen)
        if (empty($this->prodiName)) {
            $errors['prodi_name'] = ['Program studi wajib diisi untuk Dosen'];
        } elseif (strlen($this->prodiName) > 100) {
            $errors['prodi_name'] = ['Nama program studi maksimal 100 karakter'];
        }
    }
}
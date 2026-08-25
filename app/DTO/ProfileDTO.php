<?php

namespace App\DTO;

/**
 * Data Transfer Object for user profile information
 * 
 * This DTO encapsulates user profile data for consistent transfer between layers
 * 
 * @package App\DTO
 */
class ProfileDTO
{
    /**
     * @var int User ID
     */
    public int $userId;
    
    /**
     * @var string Username
     */
    public string $username;
    
    /**
     * @var string User display name
     */
    public string $displayName;
    
    /**
     * @var string User role code (ADM, DSN, TDK, MHS)
     */
    public string $roleCode;
    
    /**
     * @var string User role name
     */
    public string $roleName;
    
    /**
     * @var string|null Person identifier based on role (NIDN for Admin/Dosen/Tendik, NIM for Mahasiswa)
     */
    public ?string $personIdentifier;
    
    /**
     * @var string|null Phone number
     */
    public ?string $phoneNumber;
    
    /**
     * @var string|null Program study name (if applicable)
     */
    public ?string $prodiName;
    
    /**
     * @var string|null Class name (for Mahasiswa only)
     */
    public ?string $className;
    
    /**
     * @var \DateTimeInterface User creation date
     */
    public \DateTimeInterface $createdAt;
    
    /**
     * @var \DateTimeInterface User last update date
     */
    public \DateTimeInterface $updatedAt;
    
    /**
     * Constructor
     * 
     * @param int $userId
     * @param string $username
     * @param string $displayName
     * @param string $roleCode
     * @param string $roleName
     * @param \DateTimeInterface $createdAt
     * @param \DateTimeInterface $updatedAt
     * @param string|null $personIdentifier
     * @param string|null $phoneNumber
     * @param string|null $prodiName
     * @param string|null $className
     */
    public function __construct(
        int $userId,
        string $username,
        string $displayName,
        string $roleCode,
        string $roleName,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $updatedAt,
        ?string $personIdentifier = null,
        ?string $phoneNumber = null,
        ?string $prodiName = null,
        ?string $className = null
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->displayName = $displayName;
        $this->roleCode = $roleCode;
        $this->roleName = $roleName;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->personIdentifier = $personIdentifier;
        $this->phoneNumber = $phoneNumber;
        $this->prodiName = $prodiName;
        $this->className = $className;
    }
    
    /**
     * Convert DTO to array for JSON serialization
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'username' => $this->username,
            'display_name' => $this->displayName,
            'role_code' => $this->roleCode,
            'role_name' => $this->roleName,
            'person_identifier' => $this->personIdentifier,
            'phone_number' => $this->phoneNumber,
            'prodi_name' => $this->prodiName,
            'class_name' => $this->className,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Create ProfileDTO from UserModel
     * 
     * @param \App\Models\UserModel $user
     * @return self
     */
    public static function fromUserModel(\App\Models\UserModel $user): self
    {
        $roleCode = $user->getRole();
        $roleName = $user->getRoleName();
        
        // Initialize variables
        $displayName = '-';
        $personIdentifier = null;
        $phoneNumber = null;
        $prodiName = null;
        $className = null;
        
        // Determine person-specific data based on role
        switch ($roleCode) {
            case 'ADM':
                if ($user->admin) {
                    $displayName = $user->admin->admin_nama;
                    $personIdentifier = $user->admin->admin_nidn;
                    $phoneNumber = $user->admin->admin_noHp;
                    if ($user->admin->prodi) {
                        $prodiName = $user->admin->prodi->prodi_nama;
                    }
                }
                break;
                
            case 'DSN':
                if ($user->dosen) {
                    $displayName = $user->dosen->dosen_nama;
                    $personIdentifier = $user->dosen->dosen_nidn;
                    $phoneNumber = $user->dosen->dosen_noHp;
                    if ($user->dosen->prodi) {
                        $prodiName = $user->dosen->prodi->prodi_nama;
                    }
                }
                break;
                
            case 'TDK':
                if ($user->tendik) {
                    $displayName = $user->tendik->tendik_nama;
                    $personIdentifier = $user->tendik->tendik_nidn;
                    $phoneNumber = $user->tendik->tendik_noHp;
                }
                break;
                
            case 'MHS':
                if ($user->mahasiswa) {
                    $displayName = $user->mahasiswa->mahasiswa_nama;
                    $personIdentifier = $user->mahasiswa->mahasiswa_nim;
                    $phoneNumber = $user->mahasiswa->mahasiswa_noHp;
                    if ($user->mahasiswa->prodi) {
                        $prodiName = $user->mahasiswa->prodi->prodi_nama;
                    }
                    if ($user->mahasiswa->kelas) {
                        $className = $user->mahasiswa->kelas->kelas_nama;
                    }
                }
                break;
        }
        
        return new self(
            $user->user_id,
            $user->username,
            $displayName,
            $roleCode,
            $roleName,
            $user->created_at,
            $user->updated_at,
            $personIdentifier,
            $phoneNumber,
            $prodiName,
            $className
        );
    }
}

<?php

namespace App\Constants;

/**
 * Role constants for user level codes (level_kode in m_level table).
 *
 * ALL references to role codes (ADM, DSN, TDK, MHS) across the application
 * MUST use these constants instead of hardcoded strings. This ensures:
 * - Single source of truth for role code values
 * - Easy refactoring if level codes change in database
 * - Compile-time safety via static analysis
 */
class RoleConstants
{
    public const ADMIN = 'ADM';
    public const DOSEN = 'DSN';
    public const TENDIK = 'TDK';
    public const MAHASISWA = 'MHS';

    /**
     * All valid role codes.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [self::ADMIN, self::DOSEN, self::TENDIK, self::MAHASISWA];
    }

    /**
     * Roles that are borrowers (can create pengajuan).
     *
     * @return array<string>
     */
    public static function borrowers(): array
    {
        return [self::DOSEN, self::TENDIK, self::MAHASISWA];
    }

    /**
     * Roles that can view rooms and schedules (read-only).
     *
     * @return array<string>
     */
    public static function viewers(): array
    {
        return [self::ADMIN, self::DOSEN, self::TENDIK, self::MAHASISWA];
    }

    /**
     * Check if given role code is valid.
     *
     * @param string $role
     * @return bool
     */
    public static function isValid(string $role): bool
    {
        return in_array($role, self::all(), true);
    }
}


<?php

namespace Tests\Unit;

use App\Services\DashboardService;
use PHPUnit\Framework\TestCase;

class DashboardServiceTest extends TestCase
{
    /**
     * Stub ringan yang hanya mengimplementasikan getRole() — resolveWelcomeViewName()
     * sekarang match berdasarkan role (level_kode), bukan level_id mentah, supaya tidak
     * bergantung pada urutan/isi m_level.
     */
    protected function userWithRole(?string $role): object
    {
        return new class($role) {
            public function __construct(private ?string $role) {}
            public function getRole(): ?string { return $this->role; }
        };
    }

    public function test_resolve_welcome_view_name_returns_expected_view_for_each_role(): void
    {
        $service = new DashboardService();

        $this->assertSame('admin.welcome', $service->resolveWelcomeViewName($this->userWithRole('ADM')));
        $this->assertSame('dosen.welcome', $service->resolveWelcomeViewName($this->userWithRole('DSN')));
        $this->assertSame('tendik.welcome', $service->resolveWelcomeViewName($this->userWithRole('TDK')));
        $this->assertSame('mahasiswa.welcome', $service->resolveWelcomeViewName($this->userWithRole('MHS')));
    }

    /**
     * Akun dengan role di luar ADM/DSN/TDK/MHS (mis. sisa akun 'VRF' lama sebelum jabatan
     * approval dipindah ke m_jabatan_approval) dianggap "tanpa permission" — diarahkan ke
     * dashboard/no-permission.blade.php, bukan crash atau view kosong.
     */
    public function test_resolve_welcome_view_name_returns_no_permission_view_for_unrecognized_role(): void
    {
        $service = new DashboardService();

        $this->assertSame('dashboard.no-permission', $service->resolveWelcomeViewName($this->userWithRole('VRF')));
        $this->assertSame('dashboard.no-permission', $service->resolveWelcomeViewName($this->userWithRole('')));
        $this->assertSame('dashboard.no-permission', $service->resolveWelcomeViewName(null));
    }
}

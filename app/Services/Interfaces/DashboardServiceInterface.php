<?php

namespace App\Services\Interfaces;

interface DashboardServiceInterface
{
    /**
     * Get dashboard statistics
     *
     * @return array
     */
    public function getDashboardStats(): array;

    /**
     * Build the view name and payload for the welcome dashboard.
     */
    public function getWelcomeDashboardData(?object $user): array;

    /**
     * Resolve the welcome view name for a user.
     */
    public function resolveWelcomeViewName(?object $user): string;

    /**
     * Get dashboard statistics for a specific month
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function getDashboardStatsForMonth(int $month, int $year): array;

    /**
     * Data agregat yang aman ditampilkan ke publik (tanpa login) — subset dari
     * getDashboardStats() yang tidak menyentuh data personal per akun/pengajuan.
     */
    public function getGuestDashboardData(): array;
}

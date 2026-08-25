<?php

namespace App\Services\Interfaces;

use App\Models\LevelModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LevelServiceInterface
{
    /**
     * Get level by kode with caching
     *
     * @param string $kode
     * @return LevelModel|null
     */
    public function getLevelByKode(string $kode): ?LevelModel;

    /**
     * Get all levels with caching
     *
     * @return Collection
     */
    public function getAllLevels(): Collection;

    /**
     * Get all levels with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllLevelsPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Invalidate cache for level operations
     *
     * @return void
     */
    public function invalidateCache(): void;

    /**
     * Invalidate specific level cache
     *
     * @param string $kode
     * @return void
     */
    public function invalidateLevelCache(string $kode): void;
}
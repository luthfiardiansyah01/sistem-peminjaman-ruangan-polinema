<?php

namespace App\Services\Interfaces;

use App\Models\ProdiModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProdiServiceInterface
{
    /**
     * Get prodi by ID with caching
     *
     * @param int $id
     * @return ProdiModel|null
     */
    public function getProdiById(int $id): ?ProdiModel;

    /**
     * Get prodi by kode with caching
     *
     * @param string $kode
     * @return ProdiModel|null
     */
    public function getProdiByKode(string $kode): ?ProdiModel;

    /**
     * Get all prodis with caching
     *
     * @return Collection
     */
    public function getAllProdis(): Collection;

    /**
     * Get all prodis with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllProdisPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get prodis by level with caching
     *
     * @param int $levelId
     * @return Collection
     */
    public function getProdisByLevel(int $levelId): Collection;

    /**
     * Invalidate cache for prodi operations
     *
     * @return void
     */
    public function invalidateCache(): void;

    /**
     * Invalidate specific prodi cache
     *
     * @param int $id
     * @return void
     */
    public function invalidateProdiCache(int $id): void;

    /**
     * Invalidate specific prodi cache by kode
     *
     * @param string $kode
     * @return void
     */
    public function invalidateProdiCacheByKode(string $kode): void;
}
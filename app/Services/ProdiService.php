<?php

namespace App\Services;

use App\Models\ProdiModel;
use App\Services\Interfaces\ProdiServiceInterface;
use Illuminate\Support\Facades\Cache;

class ProdiService implements ProdiServiceInterface
{
    /**
     * Get prodi by ID with caching
     *
     * @param int $id
     * @return ProdiModel|null
     */
    public function getProdiById(int $id): ?ProdiModel
    {
        $cacheKey = "prodi:{$id}";
        
        try {
            // Try to get from cache first
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $prodi = ProdiModel::find($id);
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put($cacheKey, $prodi, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key {$cacheKey}: " . $e->getMessage());
            }
            
            return $prodi;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return ProdiModel::find($id);
        }
    }

    /**
     * Get prodi by kode with caching
     *
     * @param string $kode
     * @return ProdiModel|null
     */
    public function getProdiByKode(string $kode): ?ProdiModel
    {
        $cacheKey = "prodi:kode:{$kode}";
        
        try {
            // Try to get from cache first
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $prodi = ProdiModel::where('prodi_kode', $kode)->first();
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put($cacheKey, $prodi, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key {$cacheKey}: " . $e->getMessage());
            }
            
            return $prodi;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return ProdiModel::where('prodi_kode', $kode)->first();
        }
    }

    /**
     * Get all prodis with caching
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProdis(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // Try to get from cache first
            $cachedValue = Cache::get('prodis:all');
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $prodis = ProdiModel::all();
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put('prodis:all', $prodis, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key prodis:all: " . $e->getMessage());
            }
            
            return $prodis;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return ProdiModel::all();
        }
    }

    /**
     * Get all prodis with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllProdisPaginated(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $cacheKey = 'prodis:all:paginated:' . $perPage;
        
        try {
            // Try to get from cache first
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $prodis = ProdiModel::paginate($perPage);
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put($cacheKey, $prodis, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key {$cacheKey}: " . $e->getMessage());
            }
            
            return $prodis;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return ProdiModel::paginate($perPage);
        }
    }

    /**
     * Get prodis by level with caching
     *
     * @param int $levelId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProdisByLevel(int $levelId): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "prodis:level:{$levelId}";
        
        try {
            // Try to get from cache first
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $prodis = ProdiModel::where('level_id', $levelId)->get();
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put($cacheKey, $prodis, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key {$cacheKey}: " . $e->getMessage());
            }
            
            return $prodis;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return ProdiModel::where('level_id', $levelId)->get();
        }
    }

    /**
     * Invalidate cache for prodi operations
     *
     * @return void
     */
    public function invalidateCache(): void
    {
        Cache::forget('prodis:all');
        Cache::forget('prodis:all:paginated:*');
        Cache::forget('prodis:level:*');
        
        $prodis = ProdiModel::all();
        foreach ($prodis as $prodi) {
            Cache::forget("prodi:{$prodi->prodi_id}");
            Cache::forget("prodi:kode:{$prodi->prodi_kode}");
        }
    }

    /**
     * Invalidate specific prodi cache
     *
     * @param int $id
     * @return void
     */
    public function invalidateProdiCache(int $id): void
    {
        Cache::forget("prodi:{$id}");
        
        // Also invalidate by kode if we have the prodi
        $prodi = ProdiModel::find($id);
        if ($prodi) {
            Cache::forget("prodi:kode:{$prodi->prodi_kode}");
        }
    }

    /**
     * Invalidate specific prodi cache by kode
     *
     * @param string $kode
     * @return void
     */
    public function invalidateProdiCacheByKode(string $kode): void
    {
        Cache::forget("prodi:kode:{$kode}");
        
        // Also invalidate by id if we have the prodi
        $prodi = ProdiModel::where('prodi_kode', $kode)->first();
        if ($prodi) {
            Cache::forget("prodi:{$prodi->prodi_id}");
        }
    }
}

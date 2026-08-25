<?php

namespace App\Services;

use App\Models\LevelModel;
use App\Services\Interfaces\LevelServiceInterface;
use Illuminate\Support\Facades\Cache;

class LevelService implements LevelServiceInterface
{
    /**
     * Get level by kode with caching
     *
     * @param string $kode
     * @return LevelModel|null
     */
    public function getLevelByKode(string $kode): ?LevelModel
    {
        $cacheKey = "level:{$kode}";
        
        try {
            // Try to get from cache first
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $level = LevelModel::where('level_kode', $kode)->first();
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put($cacheKey, $level, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key {$cacheKey}: " . $e->getMessage());
            }
            
            return $level;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return LevelModel::where('level_kode', $kode)->first();
        }
    }

    /**
     * Get all levels with caching
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllLevels(): \Illuminate\Database\Eloquent\Collection
    {
        try {
            // Try to get from cache first
            $cachedValue = Cache::get('levels:all');
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $levels = LevelModel::all();
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put('levels:all', $levels, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key levels:all: " . $e->getMessage());
            }
            
            return $levels;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return LevelModel::all();
        }
    }

    /**
     * Get all levels with pagination
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllLevelsPaginated(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $cacheKey = 'levels:all:paginated:' . $perPage;
        
        try {
            // Try to get from cache first
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                return $cachedValue;
            }
            
            // If cache fails or is empty, query database
            $levels = LevelModel::paginate($perPage);
            
            // Try to cache the result, but continue even if caching fails
            try {
                Cache::put($cacheKey, $levels, 3600);
            } catch (\Exception $e) {
                // Log cache failure but continue execution
                // error_log("Cache put failed for key {$cacheKey}: " . $e->getMessage());
            }
            
            return $levels;
        } catch (\Exception $e) {
            // If cache retrieval fails, fallback to database
            return LevelModel::paginate($perPage);
        }
    }

    /**
     * Invalidate cache for level operations
     *
     * @return void
     */
    public function invalidateCache(): void
    {
        Cache::forget('levels:all');
        Cache::forget('levels:all:paginated:*');
        
        $levels = LevelModel::all();
        foreach ($levels as $level) {
            Cache::forget("level:{$level->level_kode}");
        }
    }

    /**
     * Invalidate specific level cache
     *
     * @param string $kode
     * @return void
     */
    public function invalidateLevelCache(string $kode): void
    {
        Cache::forget("level:{$kode}");
    }
}

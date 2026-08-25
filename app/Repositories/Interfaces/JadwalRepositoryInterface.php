<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface JadwalRepositoryInterface
{
    /**
     * Get schedule by user ID
     *
     * @param int $userId
     * @param int|null $limit
     * @return Collection
     */
    public function getScheduleByUser(int $userId, ?int $limit = null): Collection;

    /**
     * Get schedule by room ID
     *
     * @param int $roomId
     * @param \Carbon\Carbon|null $date
     * @return Collection
     */
    public function getScheduleByRoom(int $roomId, ?\Carbon\Carbon $date = null): Collection;

    /**
     * Create new schedule
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update schedule
     *
     * @param int $scheduleId
     * @param array $data
     * @return Model
     */
    public function update(int $scheduleId, array $data): Model;

    /**
     * Delete schedule
     *
     * @param int $scheduleId
     * @return bool
     */
    public function delete(int $scheduleId): bool;
}

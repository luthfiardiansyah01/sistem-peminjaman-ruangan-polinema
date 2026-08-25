<?php

namespace App\Repositories;

use App\Repositories\Interfaces\JadwalRepositoryInterface;
use App\Models\JadwalModel;
use App\Models\RuanganModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EloquentJadwalRepository implements JadwalRepositoryInterface
{
    /**
     * @var JadwalModel
     */
    protected $jadwal;

    /**
     * @var RuanganModel
     */
    protected $ruangan;

    /**
     * Constructor
     *
     * @param JadwalModel $jadwal
     * @param RuanganModel $ruangan
     */
    public function __construct(JadwalModel $jadwal, RuanganModel $ruangan)
    {
        $this->jadwal = $jadwal;
        $this->ruangan = $ruangan;
    }

    /**
     * Get schedule by user ID
     *
     * @param int $userId
     * @param int|null $limit
     * @return Collection
     */
    public function getScheduleByUser(int $userId, ?int $limit = null): Collection
    {
        return $this->jadwal
            ->with(['user', 'ruangans'])
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get schedule by room ID
     *
     * @param int $roomId
     * @param \Carbon\Carbon|null $date
     * @return Collection
     */
    public function getScheduleByRoom(int $roomId, ?\Carbon\Carbon $date = null): Collection
    {
        $query = $this->jadwal
            ->with(['user', 'ruangans'])
            ->whereHas('ruangans', function ($q) use ($roomId) {
                $q->where('ruangan_id', $roomId);
            });

        if ($date) {
            $query->whereDate('jadwal_tgl', $date);
        }

        return $query->latest()->get();
    }

    /**
     * Create new schedule
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->jadwal->create($data);
    }

    /**
     * Update schedule
     *
     * @param int $scheduleId
     * @param array $data
     * @return Model
     */
    public function update(int $scheduleId, array $data): Model
    {
        $schedule = $this->jadwal->findOrFail($scheduleId);
        $schedule->update($data);
        
        return $schedule;
    }

    /**
     * Delete schedule
     *
     * @param int $scheduleId
     * @return bool
     */
    public function delete(int $scheduleId): bool
    {
        $schedule = $this->jadwal->findOrFail($scheduleId);
        
        return $schedule->delete();
    }
}

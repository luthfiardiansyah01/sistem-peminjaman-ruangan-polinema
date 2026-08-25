<?php

namespace App\Services;

use App\Models\ProdiModel;
use App\Services\Interfaces\ProdiServiceInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class ProdiCrudService
{
    /**
     * @var ProdiServiceInterface
     */
    protected $prodiService;

    /**
     * Constructor
     *
     * @param ProdiServiceInterface $prodiService
     */
    public function __construct(ProdiServiceInterface $prodiService)
    {
        $this->prodiService = $prodiService;
    }
    public function indexData(): array
    {
        return [
            'breadcrumb' => (object) ['title' => 'Daftar Program Studi', 'list' => ['Home', 'Daftar Program Studi']],
            'page' => (object) ['title' => 'Daftar program studi yang terdaftar dalam sistem'],
            'activeMenu' => 'prodi',
            'prodi' => ProdiModel::all(),
        ];
    }

    public function create(array $data): array
    {
        $result = $this->save(new ProdiModel(), $data, 'Data program studi berhasil ditambahkan!', 201);
        
        // Invalidate cache after successful creation
        if ($result['status']) {
            $this->prodiService->invalidateCache();
        }
        
        return $result;
    }

    public function update(int $id, array $data): array
    {
        $prodi = ProdiModel::find($id);
        if (!$prodi) {
            return $this->error('Data program studi tidak ditemukan!', 404);
        }

        $result = $this->save($prodi, $data, 'Data program studi berhasil diupdate!', 200, $id);
        
        // Invalidate cache after successful update
        if ($result['status']) {
            $this->prodiService->invalidateProdiCache($id);
            if (isset($data['prodi_kode'])) {
                $this->prodiService->invalidateProdiCacheByKode($data['prodi_kode']);
            }
        }
        
        return $result;
    }

    public function delete(int $id): array
    {
        $prodi = ProdiModel::find($id);
        if (!$prodi) {
            return $this->error('Data program studi tidak ditemukan!', 404);
        }

        try {
            $prodi->delete();
            
            // Invalidate cache after successful deletion
            $this->prodiService->invalidateProdiCache($id);
            $this->prodiService->invalidateProdiCacheByKode($prodi->prodi_kode);
            
            return ['status' => true, 'message' => 'Data program studi berhasil dihapus!'];
        } catch (QueryException $e) {
            return $this->error('Data gagal dihapus karena masih digunakan di tabel lain!', 500);
        }
    }

    public function find(int $id): ?ProdiModel
    {
        return $this->prodiService->getProdiById($id);
    }

    protected function save(ProdiModel $prodi, array $data, string $message, int $status, ?int $id = null): array
    {
        $validator = Validator::make($data, $this->rules($id));
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $prodi->fill(['prodi_kode' => $data['prodi_kode'], 'prodi_nama' => $data['prodi_nama']])->save();
        return ['status' => true, 'message' => $message, 'http_status' => $status];
    }

    protected function rules(?int $id = null): array
    {
        return ['prodi_kode' => 'required|string|max:5|unique:m_prodi,prodi_kode,' . $id . ',prodi_id', 'prodi_nama' => 'required|string|max:100'];
    }

    protected function error(string $message, int $status): array
    {
        return ['status' => false, 'message' => $message, 'http_status' => $status];
    }
}

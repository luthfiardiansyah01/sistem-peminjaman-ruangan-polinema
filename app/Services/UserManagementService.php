<?php

namespace App\Services;

use App\Models\LevelModel;
use App\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Yajra\DataTables\Facades\DataTables;

class UserManagementService
{
    public function indexData(): array
    {
        return ['breadcrumb' => (object) ['title' => 'Daftar User', 'list' => ['Home', 'User']], 'page' => (object) ['title' => 'Daftar user yang ada dalam sistem'], 'level' => LevelModel::all(), 'activeMenu' => 'user'];
    }

    public function dataTable($request)
    {
        $query = $this->baseQuery();
        if ($request->level_id) {
            $query->where('level_id', $request->level_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(fn ($query) => $this->applySearch($query, request('search.value')))
            ->addColumn('nama', fn ($user) => $user->nama)
            ->addColumn('aksi', fn ($user) => $this->actionButtons($user))
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function create(array $data): array
    {
        $validator = Validator::make($data, ['level_id' => 'required|integer', 'username' => 'required|string|min:3|unique:m_user,username', 'password' => 'required|min:6']);
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        // C8: Whitelist only expected fields to prevent mass assignment
        $user = UserModel::create([
            'level_id' => $data['level_id'],
            'username' => $data['username'],
            'password' => $data['password'],
        ]);
        return ['status' => true, 'message' => 'Lanjutkan dengan mengisi detail user.', 'user_id' => $user->user_id, 'level_id' => $data['level_id']];
    }

    public function update(int $id, array $data): array
    {
        $user = UserModel::with('level', 'admin', 'dosen', 'tendik', 'mahasiswa')->find($id);
        if (!$user) {
            return ['status' => false, 'message' => 'Level tidak dikenali atau data tidak ditemukan.'];
        }

        $this->updateRoleData($user, $data);
        $this->updateUserData($user, $data);
        return ['status' => true, 'message' => 'Data berhasil diedit!'];
    }

    public function delete(int $id): array
    {
        $user = UserModel::find($id);
        if (!$user) {
            return ['status' => false, 'message' => 'Data tidak ditemukan!'];
        }

        $user->delete();
        return ['status' => true, 'message' => 'Data berhasil dihapus!'];
    }

    public function import($file): array
    {
        $validator = Validator::make(['file_user' => $file], ['file_user' => ['required', 'mimes:xlsx', 'max:1024']]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validasi Gagal', 'msgField' => $validator->errors()];
        }

        $rows = IOFactory::createReader('Xlsx')->load($file->getRealPath())->getActiveSheet()->toArray(null, false, true, true);
        return $this->insertRows($rows);
    }

    public function levels()
    {
        return LevelModel::select('level_id', 'level_nama', 'level_kode')->get();
    }

    public function findUser(int $id)
    {
        return UserModel::with(['admin', 'dosen', 'tendik', 'mahasiswa'])->find($id);
    }

protected function baseQuery()
    {
        return UserModel::query()
            ->select([
                'm_user.user_id',
                'm_user.level_id',
                'm_user.username',
            ])
            ->with('level')
            ->leftJoin('m_admin', 'm_admin.user_id', '=', 'm_user.user_id')
            ->leftJoin('m_dosen', 'm_dosen.user_id', '=', 'm_user.user_id')
            ->leftJoin('m_tendik', 'm_tendik.user_id', '=', 'm_user.user_id')
            ->leftJoin('m_mahasiswa', 'm_mahasiswa.user_id', '=', 'm_user.user_id')
            ->selectRaw('COALESCE(m_admin.admin_nama, m_dosen.dosen_nama, m_tendik.tendik_nama, m_mahasiswa.mahasiswa_nama) AS nama');
    }

    protected function applySearch($query, ?string $search): void
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('m_user.username', 'like', "%{$search}%")
                  ->orWhere('m_admin.admin_nama', 'like', "%{$search}%")
                  ->orWhere('m_dosen.dosen_nama', 'like', "%{$search}%")
                  ->orWhere('m_tendik.tendik_nama', 'like', "%{$search}%")
                  ->orWhere('m_mahasiswa.mahasiswa_nama', 'like', "%{$search}%");
            });
        }
    }

    protected function actionButtons($user): string
    {
        $btn = '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/show_ajax') . '\')" class="btn btn-info btn-sm">Detail</button> ';
        $btn .= '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/edit_ajax') . '\')" class="btn btn-warning btn-sm">Edit</button> ';
        return $btn . '<button onclick="modalAction(\'' . url('/user/' . $user->user_id . '/delete_ajax') . '\')" class="btn btn-danger btn-sm">Hapus</button> ';
    }

    /**
     * Update role-specific data with whitelisted fields only.
     * C8: Explicitly extract only allowed fields per role instead of passing raw $data,
     * preventing mass assignment of unintended attributes.
     */
    protected function updateRoleData($user, array $data): void
    {
        $role = ['ADM' => $user->admin, 'DSN' => $user->dosen, 'TDK' => $user->tendik, 'MHS' => $user->mahasiswa][$user->level->level_kode] ?? null;
        if (!$role) {
            return;
        }

        // Whitelist: hanya field yang diizinkan per role
        $allowedFields = [
            'ADM' => ['admin_noHp'],
            'DSN' => ['dosen_noHp'],
            'TDK' => ['tendik_noHp'],
            'MHS' => ['mahasiswa_noHp'],
        ];

        $fields = $allowedFields[$user->level->level_kode] ?? [];
        $payload = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (!empty($payload)) {
            $role->update($payload);
        }
    }

    protected function updateUserData($user, array $data): void
    {
        $payload = array_filter(['username' => $data['username'] ?? null, 'password' => !empty($data['password']) ? Hash::make($data['password']) : null]);
        if ($payload) {
            $user->update($payload);
        }
    }

    /**
     * Import rows from Excel spreadsheet with column mapping validation.
     * C8: Explicitly whitelist column letters and cast values to prevent
     * mass assignment from malformed Excel columns.
     */
    protected function insertRows(array $rows): array
    {
        if (count($rows) <= 1) {
            return ['status' => false, 'message' => 'Tidak ada data yang diimport.'];
        }

        // Column mapping: Excel column letter => DB column + expected type
        $columnMap = [
            'A' => ['field' => 'level_id', 'cast' => 'intval'],
            'B' => ['field' => 'username', 'cast' => 'strval'],
            'D' => ['field' => 'password', 'cast' => 'strval'],
        ];

        $records = [];
        foreach (array_slice($rows, 1) as $r) {
            $record = [];
            foreach ($columnMap as $letter => $mapping) {
                $value = $r[$letter] ?? null;
                $castFn = $mapping['cast'];
                $record[$mapping['field']] = $castFn($value);
            }
            $record['created_at'] = now();
            $records[] = $record;
        }

        if (empty($records)) {
            return ['status' => false, 'message' => 'Tidak ada data yang valid untuk diimport.'];
        }

        UserModel::insertOrIgnore($records);
        return ['status' => true, 'message' => 'Data berhasil diimport!'];
    }

    /**
     * @deprecated Replaced by leftJoin + COALESCE in baseQuery() for C8 SQL injection fix.
     */
    protected function nameCase(): string
    {
        return 'CASE WHEN level_id = 1 THEN (SELECT admin_nama FROM m_admin WHERE m_admin.user_id = m_user.user_id) WHEN level_id = 2 THEN (SELECT dosen_nama FROM m_dosen WHERE m_dosen.user_id = m_user.user_id) WHEN level_id = 3 THEN (SELECT tendik_nama FROM m_tendik WHERE m_tendik.user_id = m_user.user_id) WHEN level_id = 4 THEN (SELECT mahasiswa_nama FROM m_mahasiswa WHERE m_mahasiswa.user_id = m_user.user_id) ELSE NULL END';
    }
}

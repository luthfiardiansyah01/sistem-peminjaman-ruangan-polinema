<?php

namespace App\Services;

use App\Models\AdminModel;
use App\Models\DosenModel;
use App\Models\KelasModel;
use App\Models\MahasiswaModel;
use App\Models\ProdiModel;
use App\Models\TendikModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileService
{
    public function showData($user): array
    {
        $role = $user->getRole();
        return ['user' => $user, 'data' => $this->profileData($user, $role, true), 'role' => $role];
    }

    public function editData($user): array
    {
        $role = $user->getRole();
        return ['user' => $user, 'data' => $this->profileData($user, $role), 'role' => $role, 'prodi' => ProdiModel::all(), 'kelas' => KelasModel::all()];
    }

    public function update($user, array $data): array
    {
        $validator = Validator::make($data, $this->rules($user));
        if ($validator->fails()) {
            return ['status' => false, 'msg' => 'Validasi gagal', 'errors' => $validator->errors()];
        }

        $this->updateAccount($user, $data);
        $this->updatePhone($user, $data['no_hp'] ?? null);
        return ['status' => true, 'msg' => 'Profil berhasil diperbarui!'];
    }

    protected function profileData($user, string $role, bool $withRelations = false)
    {
        return match ($role) {
            'ADM' => AdminModel::when($withRelations, fn ($q) => $q->with('prodi'))->where('user_id', $user->user_id)->first(),
            'DSN' => DosenModel::when($withRelations, fn ($q) => $q->with('prodi'))->where('user_id', $user->user_id)->first(),
            'TDK' => TendikModel::where('user_id', $user->user_id)->first(),
            'MHS' => MahasiswaModel::when($withRelations, fn ($q) => $q->with(['prodi', 'kelas']))->where('user_id', $user->user_id)->first(),
            default => null,
        };
    }

    protected function rules($user): array
    {
        return ['username' => 'string|min:3|unique:m_user,username,' . $user->user_id . ',user_id', 'password' => 'nullable|min:5', 'no_hp' => 'nullable|string|max:15'];
    }

    protected function updateAccount($user, array $data): void
    {
        $user->username = $data['username'] ?? $user->username;
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
    }

    protected function updatePhone($user, ?string $phone): void
    {
        $profile = $this->profileData($user, $user->getRole());
        $column = ['ADM' => 'admin_noHp', 'DSN' => 'dosen_noHp', 'TDK' => 'tendik_noHp', 'MHS' => 'mahasiswa_noHp'][$user->getRole()] ?? null;
        if ($profile && $column) {
            $profile->update([$column => $phone]);
        }
    }
}

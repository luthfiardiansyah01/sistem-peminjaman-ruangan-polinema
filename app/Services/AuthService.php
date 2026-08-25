<?php

namespace App\Services;

use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    // Maksimum percobaan login gagal sebelum dikunci sementara (per kombinasi
    // username+IP, mengikuti pola Illuminate\Foundation\Auth\ThrottlesLogins bawaan
    // Laravel) — mencegah brute-force tanpa memblokir seluruh IP/jaringan bersama
    // (mis. NAT kampus) hanya karena satu akun dicoba berulang kali.
    protected const MAX_LOGIN_ATTEMPTS = 5;
    protected const LOCKOUT_DECAY_SECONDS = 60;

    /**
     * Authenticate user with credentials
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array
    {
        $username = trim((string) ($credentials['username'] ?? ''));
        $password = (string) ($credentials['password'] ?? '');

        if ($username === '' || $password === '') {
            return [
                'status' => false,
                'message' => 'Login gagal, username atau password salah.'
            ];
        }

        $throttleKey = $this->throttleKey($username);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return [
                'status' => false,
                'message' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
            ];
        }

        try {
            $attemptedCredentials = [
                'username' => $username,
                'password' => $password,
            ];

            // Attempt authentication using Laravel Auth facade
            if (!Auth::attempt($attemptedCredentials)) {
                RateLimiter::hit($throttleKey, self::LOCKOUT_DECAY_SECONDS);

                return [
                    'status' => false,
                    'message' => 'Login gagal, username atau password salah.'
                ];
            }

            // Get authenticated user
            $user = Auth::user();

            if (!$user) {
                RateLimiter::hit($throttleKey, self::LOCKOUT_DECAY_SECONDS);

                return [
                    'status' => false,
                    'message' => 'Login gagal, username atau password salah.'
                ];
            }

            // Login berhasil — reset hitungan percobaan gagal untuk kombinasi ini.
            RateLimiter::clear($throttleKey);

            // Get user name and role-based welcome message
            $userName = $this->getUserDisplayName($user);
            $redirectUrl = $this->getRedirectUrl($user);

            return [
                'status' => true,
                'message' => 'Selamat datang, ' . $userName . '.',
                'redirect' => $redirectUrl
            ];
        } catch (\Exception $e) {
            // Log generic error without exposing sensitive details or stack traces
            Log::warning('Authentication attempt failed', [
                'username' => $username,
                'ip' => request()->ip(),
                'reason' => 'system_error',
            ]);

            return [
                'status' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];
        }
    }

    /**
     * Get user display name based on their role
     *
     * @param \App\Models\UserModel $user
     * @return string
     */
    protected function getUserDisplayName($user): string
    {
        // Gunakan closure agar pengecekan relation bisa ditangani dengan aman
        // tanpa throw exception saat relation null atau tidak ter-load.
        $resolve = function () use ($user): string {
            $username = (string) ($user->username ?? '');
            $levelKode = $user->getRole();

            // Fallback: jika relasi level tidak ter-load, gunakan level_id
            // (misal di unit test atau kode lama yang belum eager-load level).
            if (empty($levelKode)) {
                $levelId = (int) ($user->level_id ?? 0);
                $levelKode = match ($levelId) {
                    1 => 'ADM', 2 => 'DSN', 3 => 'TDK', 4 => 'MHS',
                    default => '',
                };
            }

            return match ($levelKode) {
                'ADM' => $user->admin?->admin_nama ?? 'Admin',
                'DSN' => $user->dosen?->dosen_nama ?? 'Dosen',
                'TDK' => $user->tendik?->tendik_nama ?? 'Tendik',
                'MHS' => $user->mahasiswa?->mahasiswa_nama ?? 'Mahasiswa',
                default => $username !== '' ? $username : 'User',
            };
        };

        try {
            return $resolve();
        } catch (\Exception $e) {
            return (string) ($user->username ?? 'User');
        }
    }

    /**
     * Check if user is already authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Kunci rate limiter untuk percobaan login: kombinasi username + IP, bukan
     * IP saja — supaya satu akun yang di-brute-force tidak ikut mengunci user
     * lain yang login dari IP yang sama (mis. jaringan kampus/NAT bersama).
     */
    protected function throttleKey(string $username): string
    {
        return Str::lower($username) . '|' . request()->ip();
    }

    /**
     * Get redirect URL based on user role
     *
     * @param \App\Models\UserModel $user
     * @return string
     */
    protected function getRedirectUrl($user): string
    {
        // For now, all roles redirect to dashboard
        // This can be customized later based on business requirements
        return url('/dashboard');
    }

    /**
     * Logout current authenticated user
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     * @throws \Exception
     */
    public function logout(Request $request): void
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Exception $e) {
            // Log the error but continue with logout process
            Log::error('Logout error: ' . $e->getMessage());

            // Still attempt to clear session even if there's an error
            try {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Exception $sessionError) {
                Log::error('Session clearing error: ' . $sessionError->getMessage());
            }
        }
    }

    /**
     * Get user profile with role information
     *
     * @param \App\Models\UserModel $user
     * @return array
     */
    protected function getUserProfile($user): array
    {
        return [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'level_id' => $user->level_id,
            'level_kode' => $user->level?->level_kode ?? '',
            'level_nama' => $user->level?->level_nama ?? '',
            'role_name' => $user->getRoleName(),
            'has_role' => function ($role) use ($user) {
                return $user->hasRole($role);
            }
        ];
    }
}

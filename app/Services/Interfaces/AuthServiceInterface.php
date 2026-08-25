<?php

namespace App\Services\Interfaces;

interface AuthServiceInterface
{
    /**
     * Authenticate user with credentials
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array;

    /**
     * Logout current authenticated user
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function logout(\Illuminate\Http\Request $request): void;

    /**
     * Check if user is already authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool;
}

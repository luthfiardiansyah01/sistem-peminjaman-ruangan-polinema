<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @var AuthServiceInterface
     */
    protected $authService;

    /**
     * Constructor
     *
     * @param AuthServiceInterface $authService
     */
    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function login()
    {
        // Redirect to dashboard if already authenticated
        if ($this->authService->isAuthenticated()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // Delegate authentication to AuthService for both AJAX and form-based requests
        $result = $this->authService->login($credentials);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        if (($result['status'] ?? false) && !empty($result['redirect'])) {
            return redirect($result['redirect']);
        }

        return redirect('login');
    }

    public function logout(Request $request)
    {
        // Delegate logout to AuthService
        $this->authService->logout($request);
        
        return redirect('/');
    }
}

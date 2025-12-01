<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $result = $this->authService->register($request->validated());
            return ApiResponse::success('User registered successfully.', $result, 201);
        } catch (\Exception $e) {
            return ApiResponse::error('Registration failed.', ['exception' => $e->getMessage()], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());
            if (!$result) {
                return ApiResponse::error('Invalid credentials.', [], 401);
            }
            return ApiResponse::success('Login successful.', $result);
        } catch (\Exception $e) {
            return ApiResponse::error('Login failed.', ['exception' => $e->getMessage()], 500);
        }
    }

    public function me(Request $request)
    {
        return ApiResponse::success('User information fetched Successfully.', $request->user());
    }

    public function refresh(Request $request)
    {
        try {
            $result = $this->authService->refresh($request->user());
            return ApiResponse::success('Token refreshed', $result);
        } catch (\Exception $e) {
            return ApiResponse::error('Token refresh failed', ['exception' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());
            return ApiResponse::success('Logged out successfully.');
        } catch (\Exception $e) {
            return ApiResponse::error('Logout failed', ['exception' => $e->getMessage()], 500);
        }
    }
}

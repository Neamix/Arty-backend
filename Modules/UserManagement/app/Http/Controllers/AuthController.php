<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\UserManagement\Http\Requests\LoginRequest;
use Modules\UserManagement\Http\Requests\RegisterRequest;
use Modules\UserManagement\Http\Resources\UserResource;
use Modules\UserManagement\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('workspaces')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}

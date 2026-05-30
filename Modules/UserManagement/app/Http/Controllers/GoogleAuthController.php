<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\UserManagement\Http\Resources\UserResource;
use Modules\UserManagement\Services\GoogleAuthService;

class GoogleAuthController extends Controller
{
    public function __construct(private GoogleAuthService $googleAuthService) {}

    public function redirect(): JsonResponse
    {
        return response()->json([
            'redirect_url' => $this->googleAuthService->getRedirectUrl(),
        ]);
    }

    public function callback(): JsonResponse
    {
        $result = $this->googleAuthService->handleCallback();

        return response()->json([
            'message' => $result['created'] ? 'Account created via Google.' : 'Logged in via Google.',
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
            'created' => $result['created'],
        ], $result['created'] ? 201 : 200);
    }
}

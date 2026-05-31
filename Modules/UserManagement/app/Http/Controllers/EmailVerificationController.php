<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\UserManagement\Http\Requests\ResendEmailVerificationRequest;
use Modules\UserManagement\Http\Requests\VerifyEmailRequest;
use Modules\UserManagement\Http\Resources\UserResource;
use Modules\UserManagement\Services\AuthService;

class EmailVerificationController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function verify(VerifyEmailRequest $request): JsonResponse
    {
        $user = $this->authService->verifyEmail(
            $request->user()->email,
            $request->validated('otp'),
        );

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => new UserResource($user->load('workspaces')),
        ]);
    }

    public function resend(ResendEmailVerificationRequest $request): JsonResponse
    {
        $this->authService->resendEmailVerification($request->user()->email);

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ]);
    }
}

<?php

namespace Modules\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\UserManagement\Enums\OtpUsage;
use Modules\UserManagement\Events\OtpRequested;
use Modules\UserManagement\Http\Requests\ForgotPasswordRequest;
use Modules\UserManagement\Http\Requests\ResetPasswordRequest;
use Modules\UserManagement\Http\Requests\VerifyOtpRequest;
use Modules\UserManagement\Http\Resources\UserResource;
use Modules\UserManagement\Services\AuthService;
use Modules\UserManagement\Services\OtpService;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private OtpService $otpService,
        private AuthService $authService,
    ) {}

    public function sendOtp(ForgotPasswordRequest $request): JsonResponse
    {
        OtpRequested::dispatch($request->validated('email'), OtpUsage::PasswordReset);

        return response()->json([
            'message' => 'Verification code sent to your email.',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $this->otpService->verify(
            $request->validated('email'),
            $request->validated('otp'),
            OtpUsage::from($request->validated('usage')),
        );

        return response()->json([
            'message' => 'Code verified. You may now reset your password.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = $this->authService->resetPassword($request->validated());

        return response()->json([
            'message' => 'Password reset successfully.',
            'user' => new UserResource($user),
        ]);
    }
}

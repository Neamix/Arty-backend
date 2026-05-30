<?php

namespace Modules\UserManagement\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthException extends Exception
{
    public function __construct(string $message, private readonly int $status = Response::HTTP_UNAUTHORIZED)
    {
        parent::__construct($message);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->status);
    }
}

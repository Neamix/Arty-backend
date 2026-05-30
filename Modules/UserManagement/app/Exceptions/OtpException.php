<?php

namespace Modules\UserManagement\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class OtpException extends Exception
{
    public function __construct(string $message, private readonly int $status = Response::HTTP_UNPROCESSABLE_ENTITY)
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

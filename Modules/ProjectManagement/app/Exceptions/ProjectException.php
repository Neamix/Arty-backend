<?php

namespace Modules\ProjectManagement\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectException extends Exception
{
    public function __construct(
        public string $messageText,
        public int $statusCode = 422,
    ) {
        parent::__construct($messageText);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->messageText,
        ], $this->statusCode);
    }

    public static function invalidCardTitleField(): self
    {
        return new self('The card title field must reference one of the project form fields.', 422);
    }

    public static function stageNotInProject(): self
    {
        return new self('The target stage does not belong to this project.', 422);
    }
}

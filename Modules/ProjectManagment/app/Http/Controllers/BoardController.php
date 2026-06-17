<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Services\BoardService;

class BoardController extends Controller
{
    public function __construct(private BoardService $boardService) {}

    public function show(int $project): JsonResponse
    {
        return response()->json([
            'data' => $this->boardService->show($project),
        ]);
    }
}

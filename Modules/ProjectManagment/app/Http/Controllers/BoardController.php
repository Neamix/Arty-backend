<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\ShowBoardRequest;
use Modules\ProjectManagment\Services\BoardService;

class BoardController extends Controller
{
    public function __construct(private BoardService $boardService) {}

    public function show(ShowBoardRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->boardService->show($request->validated('project_id')),
        ]);
    }
}

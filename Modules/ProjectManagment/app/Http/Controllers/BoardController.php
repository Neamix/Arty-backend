<?php

namespace Modules\ProjectManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ProjectManagment\Http\Requests\ShowBoardRequest;
use Modules\ProjectManagment\Http\Requests\ShowProjectModeRequest;
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

    public function kanban(ShowBoardRequest $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'mode' => 'kanban',
                ...$this->boardService->kanban($request->validated('project_id')),
            ],
        ]);
    }

    public function sheet(ShowProjectModeRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->boardService->sheet($request->validated('project_id')),
        ]);
    }

    public function calendar(ShowProjectModeRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->boardService->calendar(
                $request->validated('project_id'),
                $request->validated('week_start'),
            ),
        ]);
    }
}

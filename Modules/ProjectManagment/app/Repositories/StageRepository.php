<?php

namespace Modules\ProjectManagment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagment\Models\Stage;

class StageRepository
{
    public function __construct(private Stage $stage) {}

    public function filter(array $filters): Collection
    {
        return $this->stage->filter($filters)
            ->orderBy('sort_order')
            ->get();
    }

    public function find(int $id): Stage
    {
        return $this->stage->findOrFail($id);
    }

    public function create(array $data): Stage
    {
        return $this->stage->create($data);
    }

    public function update(Stage $stage, array $data): Stage
    {
        $stage->update($data);

        return $stage->refresh();
    }

    public function delete(Stage $stage): void
    {
        $stage->delete();
    }

    public function nextSortOrder(int $projectId): int
    {
        return (int) $this->stage->where('project_id', $projectId)
            ->max('sort_order') + 1;
    }
}

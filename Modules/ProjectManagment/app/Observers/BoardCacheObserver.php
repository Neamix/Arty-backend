<?php

namespace Modules\ProjectManagment\Observers;

use Illuminate\Database\Eloquent\Model;
use Modules\ProjectManagment\Models\Field;
use Modules\ProjectManagment\Models\FieldOption;
use Modules\ProjectManagment\Models\Form;
use Modules\ProjectManagment\Models\Project;
use Modules\ProjectManagment\Models\Stage;
use Modules\ProjectManagment\Services\BoardService;

class BoardCacheObserver
{
    public function saved(Model $model): void
    {
        $this->forget($model);
    }

    public function deleted(Model $model): void
    {
        $this->forget($model);
    }

    private function forget(Model $model): void
    {
        BoardService::forgetSkeleton($this->projectId($model));
    }

    private function projectId(Model $model): ?int
    {
        return match (true) {
            $model instanceof Project => $model->id,
            $model instanceof Form, $model instanceof Stage => $model->project_id,
            $model instanceof Field => $model->form?->project_id,
            $model instanceof FieldOption => $model->field?->form?->project_id,
            default => null,
        };
    }
}

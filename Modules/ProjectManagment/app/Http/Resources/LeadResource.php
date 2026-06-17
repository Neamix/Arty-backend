<?php

namespace Modules\ProjectManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagment\Models\Lead;
use Modules\ProjectManagment\Models\LeadAnswer;

/**
 * @mixin Lead
 */
class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stage_id' => $this->stage_id,
            'title' => $this->title(),
            'due_date' => $this->due_date?->toIso8601String(),
            'answers' => $this->answers->map(fn (LeadAnswer $answer) => [
                'field_id' => $answer->field_id,
                'value' => $answer->value,
            ])->values(),
        ];
    }
}

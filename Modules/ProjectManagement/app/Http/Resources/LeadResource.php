<?php

namespace Modules\ProjectManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectLeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_stage_id' => $this->project_stage_id,
            'sort_order' => $this->sort_order,
            'title' => $this->resolveTitle(),
            'values' => $this->whenLoaded('values', fn () => $this->values->mapWithKeys(
                fn ($value): array => [$value->project_form_field_id => $value->value],
            )),
            'created_at' => $this->created_at,
        ];
    }

    private function resolveTitle(): ?string
    {
        $titleFieldId = $this->resource->getAttribute('card_title_field_id');

        if ($titleFieldId === null || ! $this->relationLoaded('values')) {
            return null;
        }

        $value = $this->values->firstWhere('project_form_field_id', $titleFieldId)?->value;

        return is_array($value) ? implode(', ', $value) : (is_null($value) ? null : (string) $value);
    }
}

<?php

namespace Modules\ProjectManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => $this->icon,
            'card_title_field_id' => $this->card_title_field_id,
            'created_by' => $this->created_by,
            'stages_count' => $this->whenCounted('stages'),
            'leads_count' => $this->whenCounted('leads'),
            'form_fields' => ProjectFormFieldResource::collection($this->whenLoaded('formFields')),
            'stages' => ProjectStageResource::collection($this->whenLoaded('stages')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

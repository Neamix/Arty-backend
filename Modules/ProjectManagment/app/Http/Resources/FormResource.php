<?php

namespace Modules\ProjectManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagment\Models\Form;

/**
 * @mixin Form
 */
class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'fields' => FieldResource::collection($this->whenLoaded('fields')),
        ];
    }
}

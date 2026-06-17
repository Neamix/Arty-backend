<?php

namespace Modules\ProjectManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagment\Models\Field;

/**
 * @mixin Field
 */
class FieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type,
            'is_required' => $this->is_required,
            'sort_order' => $this->sort_order,
            'config' => $this->config,
            'default_value' => $this->default_value,
            'is_title' => $this->is_title,
            'options' => FieldOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}

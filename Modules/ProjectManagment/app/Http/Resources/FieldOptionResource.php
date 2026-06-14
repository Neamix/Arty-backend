<?php

namespace Modules\ProjectManagment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagment\Models\FieldOption;

/**
 * @mixin FieldOption
 */
class FieldOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'value' => $this->value,
            'sort_order' => $this->sort_order,
        ];
    }
}

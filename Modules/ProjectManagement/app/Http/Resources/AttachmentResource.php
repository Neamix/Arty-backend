<?php

namespace Modules\ProjectManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_id' => $this->lead_id,
            'size' => $this->size,
            'real_name' => $this->real_name,
            'uploaded_name' => $this->uploaded_name,
            'created_at' => $this->created_at,
        ];
    }
}

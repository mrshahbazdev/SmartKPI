<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'industry' => $this->industry,
            'size' => $this->size,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
            'departments' => $this->whenLoaded('departments', fn () =>
                $this->departments->map(fn ($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'is_active' => $d->is_active,
                ])
            ),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

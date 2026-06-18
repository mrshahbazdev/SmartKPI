<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_de' => $this->name_de,
            'name_en' => $this->name_en,
            'description_de' => $this->description_de,
            'description_en' => $this->description_en,
            'formula' => $this->formula,
            'unit' => $this->unit,
            'target_value' => $this->target_value ? (float) $this->target_value : null,
            'warning_threshold' => $this->warning_threshold ? (float) $this->warning_threshold : null,
            'critical_threshold' => $this->critical_threshold ? (float) $this->critical_threshold : null,
            'frequency' => $this->frequency,
            'direction' => $this->direction,
            'category' => $this->category,
            'is_template' => $this->is_template,
            'is_active' => $this->is_active,
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'company' => $this->whenLoaded('company', fn () => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ]),
            'latest_value' => $this->whenLoaded('latestValue', fn () => $this->latestValue ? [
                'value' => (float) $this->latestValue->value,
                'status' => $this->latestValue->status,
                'recorded_at' => $this->latestValue->recorded_at->format('Y-m-d'),
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

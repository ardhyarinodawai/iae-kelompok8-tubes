<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->tenant_id,           
            'name' => $this->tenant_name,       
            'email' => $this->tenant_email,     
            'phone' => null,                    
            'nik' => null,                      
            'contracts_count' => $this->contracts->count(),
            'contracts' => $this->contracts,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

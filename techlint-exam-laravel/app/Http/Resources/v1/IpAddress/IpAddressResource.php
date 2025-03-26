<?php

namespace App\Http\Resources\v1\IpAddress;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IpAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'label' => $this->label,
            'comment' => $this->comment,
            'createdBy' => UserResource::make($this->createdBy),
        ];
    }
}

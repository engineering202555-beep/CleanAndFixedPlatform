<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'image_url'       => $this->icon ? Storage::disk('public')->url($this->icon) : null,
            'providers_count' => (int) $this->providers_count,
            'requests_count'  => (int) $this->requests_count,
        ];
    }
}

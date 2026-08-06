<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceComparisonResource extends JsonResource
{
    public function toArray(Request $request): array
{
    return [
        'rows'     => $this['rows'],
        'insights' => $this['insights'],
    ];
}
}

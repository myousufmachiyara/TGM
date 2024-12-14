<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurPOResource extends JsonResource
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
            'fabric' => $this->fabric,
            'rate' => $this->rate,
            'quantity' => $this->quantity,
            'payment_term' => $this->payment_term,
            'delivery_date' => $this->delivery_date->toDateString(), // Format the date
            'vendor_name' => $this->vendor_name,
            'created_at' => $this->created_at->toDateString(),
            'updated_at' => $this->updated_at->toDateString(),
        ];
    }
}

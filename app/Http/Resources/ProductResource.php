<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            // Add these new fields matching your database columns
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'stock' => (int) $this->stock,
            'image' => $this->image_url ? asset('storage/' . $this->image_url) : 'https://cdn.quasar.dev/img/parallax1.jpg',
            'category' => $this->category,
            // Helper to check if it's "New" (e.g., created in last 7 days)
            'is_new' => $this->created_at > now()->subDays(7),
        ];
    }
}

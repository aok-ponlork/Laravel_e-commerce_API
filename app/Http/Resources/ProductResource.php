<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($request->routeIs('products.index') || $request->routeIs('products.ByCategory') || $request->routeIs('product.ByProductID')) {
            return [
                'id' => (string)$this->product_id,
                'attributes' => [
                    'product_name' => $this->product_name,
                    'price' => $this->price,
                    'cost' => $this->cost,
                    'stock_qty' => $this->stock_qty,
                    'description' => $this->description,
                    'sale_count' => $this->sale_count,
                    'image' => $this->getImageUrl(),
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at
                ],
                'relationship' => [
                    'category_id' => $this->category->category_id,
                    'category_name' => $this->category->category_name
                ]
            ];
        } elseif ($request->routeIs('products.')) {
            return [
                // Include data for a single product
            ];
        }
        return [
            'data' => '',
        ];
    }
    private function getImageUrl()
    {
        return url('images/products/' . $this->image);
    }
}

<?php

namespace App\Services\v1;

use App\Models\Product;
use Illuminate\Http\Response;

class ProductService
{
    public function existenceCheck(Product $product)
    {
        if ($product->exists() && $product->deleted_by != null) {
            return response()->json([
                'message' => 'This product is deleted'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
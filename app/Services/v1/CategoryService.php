<?php

namespace App\Services\v1;

use App\Models\Category;
use Illuminate\Http\Response;

class CategoryService
{
    public function existenceCheck(Category $category)
    {
        if ($category->exists() && $category->deleted_by != null) {
            return response()->json([
                'message' => 'This category is deleted'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
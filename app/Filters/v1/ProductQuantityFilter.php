<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ProductQuantityFilter extends ApiFilter
{
    protected $allowedParams = [
        'colorId' => ['eq', 'ne'],
        'sizeId' => ['eq', 'ne'],
        'initialQuantity' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
        'currentQuantity' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
    ];

    protected $columnMap = [
        'colorId' => 'color_id',
        'sizeId' => 'size_id',
        'initialQuantity' => 'initial_quantity',
        'currentQuantity' => 'current_quantity',
    ];
}
<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ProductImageFilter extends ApiFilter
{
    protected $allowedParams = [
        'colorId' => ['eq', 'ne'],
    ];

    protected $columnMap = [
        'colorId' => 'color_id',
    ];
}
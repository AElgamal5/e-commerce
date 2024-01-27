<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ProductImageFilter extends ApiFilter
{
    protected $allowedParams = [
        'imageColorId' => ['eq', 'ne'],
    ];

    protected $columnMap = [
        'imageColorId' => 'color_id',
    ];
}
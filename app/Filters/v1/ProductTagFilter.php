<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ProductTagFilter extends ApiFilter
{
    protected $allowedParams = [
        'tagId' => ['eq', 'ne'],
    ];

    protected $columnMap = [
        'tagId' => 'tag_id',
    ];
}
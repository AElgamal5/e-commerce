<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class SizeFilter extends ApiFilter
{
    protected $allowedParams = [
        'code' => ['eq', 'lk'],
        'createdBy' => ['eq'],
        'updatedBy' => ['eq'],
        'deletedBy' => ['eq'],
    ];

    protected $columnMap = [
        'createdBy' => 'created_by',
        'updatedBy' => 'updated_by',
        'deletedBy' => 'deleted_by',
    ];
}
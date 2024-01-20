<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class TagFilter extends ApiFilter
{
    protected $allowedParams = [
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
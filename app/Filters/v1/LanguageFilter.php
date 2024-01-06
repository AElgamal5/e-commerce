<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class LanguageFilter extends ApiFilter
{
    protected $allowedParams = [
        'name' => ['eq'],
        'code' => ['eq'],
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
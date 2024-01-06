<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class UserFilter extends ApiFilter
{
    protected $allowedParams = [
        'name' => ['eq'],
        'email' => ['eq'],
        'role' => ['eq'],
        'phone' => ['eq'],
        'countryCode' => ['eq'],
        'createdBy' => ['eq'],
        'updatedBy' => ['eq'],
        'deletedBy' => ['eq'],
    ];

    protected $columnMap = [
        'countryCode' => 'country_code',
        'createdBy' => 'created_by',
        'updatedBy' => 'updated_by',
        'deletedBy' => 'deleted_by',
    ];
}
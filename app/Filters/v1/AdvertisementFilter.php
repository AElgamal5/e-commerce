<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class AdvertisementFilter extends ApiFilter
{
    protected $allowedParams = [
        'name' => ['eq', 'lk'],
        'status' => ['eq'],
        'link' => ['eq', 'lk'],
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
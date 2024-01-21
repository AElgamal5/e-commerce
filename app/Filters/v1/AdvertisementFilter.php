<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class AdvertisementFilter extends ApiFilter
{
    protected $allowedParams = [
        'text' => ['eq'],
        'status' => ['eq'],
        'link' => ['eq'],
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
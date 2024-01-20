<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ProductFilter extends ApiFilter
{
    protected $allowedParams = [
        'status' => ['eq', 'ne'],
        'year' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
        'price' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
        'discount_type' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
        'discount_value' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
        'initial_quantity' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
        'current_quantity' => ['eq', 'ne', 'gt', 'gte', 'lt', 'lte'],
        'category_id' => ['eq'],
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
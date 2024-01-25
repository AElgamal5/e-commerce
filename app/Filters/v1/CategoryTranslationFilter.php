<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class CategoryTranslationFilter extends ApiFilter
{
    protected $allowedParams = [
        'translationsName' => ['eq', 'lk'],
        'translationsDescription' => ['lk'],
        'translationsCreatedBy' => ['eq'],
        'translationsUpdatedBy' => ['eq'],
        'translationsDeletedBy' => ['eq'],
    ];

    protected $columnMap = [
        'translationsName' => 'name',
        'translationsCreatedBy' => 'created_by',
        'translationsUpdatedBy' => 'updated_by',
        'translationsDeletedBy' => 'deleted_by',
    ];
}
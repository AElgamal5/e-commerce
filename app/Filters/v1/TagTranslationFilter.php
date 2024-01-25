<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class TagTranslationFilter extends ApiFilter
{
    protected $allowedParams = [
        'name' => ['eq', 'lk'],
        'description' => ['eq', 'lk'],
        'translationsCreatedBy' => ['eq'],
        'translationsUpdatedBy' => ['eq'],
        'translationsDeletedBy' => ['eq'],
    ];

    protected $columnMap = [
        'translationsCreatedBy' => 'created_by',
        'translationsUpdatedBy' => 'updated_by',
        'translationsDeletedBy' => 'deleted_by',
    ];
}
<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ColorTranslationFilter extends ApiFilter
{
    protected $allowedParams = [
        'translationsName' => ['eq', 'lk'],
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
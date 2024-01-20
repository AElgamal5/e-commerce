<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ContactUsFilter extends ApiFilter
{
    protected $allowedParams = [
        'name' => ['eq'],
        'email' => ['eq'],
        'phone' => ['eq'],
        'countryCode' => ['eq'],
        'title' => ['eq'],
        'description' => ['eq'],
    ];

    protected $columnMap = [
        'countryCode' => 'country_code',
    ];
}
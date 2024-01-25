<?php

namespace App\Filters\v1;

use App\Filters\ApiFilter;

class ContactUsFilter extends ApiFilter
{
    protected $allowedParams = [
        'name' => ['eq', 'lk'],
        'email' => ['eq', 'lk'],
        'phone' => ['eq', 'lk'],
        'countryCode' => ['eq', 'lk'],
        'title' => ['eq', 'lk'],
        'description' => ['eq', 'lk'],
    ];

    protected $columnMap = [
        'countryCode' => 'country_code',
    ];
}
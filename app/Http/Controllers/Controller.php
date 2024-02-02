<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function generateCacheKey($tag, $page = 1, $pageSize = 15, $filters = [])
    {
        return $tag . "_page_" . $page . "_pageSize_" . $pageSize . "_query_" . md5(serialize($filters));
    }

    protected function generateCacheKeyForOne($tag, $id, $filters = [])
    {
        return $tag . "_id_" . $id . "_query_" . md5(serialize($filters));
    }
}

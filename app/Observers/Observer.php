<?php

namespace App\Observers;

use Illuminate\Support\Facades\Redis;

class Observer
{
    protected function deletedCache(string $tag): void
    {
        // Redis::flushall();
        $keysToDelete = Redis::keys('*' . $tag . '*');
        for ($i = 0; $i < count($keysToDelete); $i++) {
            //key starts with 'laravel_database_'
            Redis::del(substr($keysToDelete[$i], 17));
        }
    }
}

<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Redis;

class ProductObserver
{
    private function deletedCachedProducts()
    {
        // Redis::flushall();
        $keysToDelete = Redis::keys('*products*');
        for ($i = 0; $i < count($keysToDelete); $i++) {
            //key starts with 'laravel_database_'
            Redis::del(substr($keysToDelete[$i], 17));
        }
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->deletedCachedProducts();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->deletedCachedProducts();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->deletedCachedProducts();
    }
}

<?php

namespace App\Observers;

class ProductObserver extends Observer
{

    /**
     * Handle the Product "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('products');
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('products');
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('products');
    }
}

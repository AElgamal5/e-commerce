<?php

namespace App\Observers;

class CategoryObserver extends Observer
{
    /**
     * Handle the Category "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('categories');
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('categories');
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('categories');
    }

}

<?php

namespace App\Observers;

class SizeObserver extends Observer
{
    /**
     * Handle the Size "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('sizes');
    }

    /**
     * Handle the Size "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('sizes');
    }

    /**
     * Handle the Size "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('sizes');
    }

}

<?php

namespace App\Observers;

class AdvertisementObserver extends Observer
{
    /**
     * Handle the Advertisement "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('advertisements');
    }

    /**
     * Handle the Advertisement "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('advertisements');
    }

    /**
     * Handle the Advertisement "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('advertisements');
    }

}

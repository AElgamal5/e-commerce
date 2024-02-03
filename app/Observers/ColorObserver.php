<?php

namespace App\Observers;


class ColorObserver extends Observer
{
    /**
     * Handle the Color "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('colors');
    }

    /**
     * Handle the Color "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('colors');
    }

    /**
     * Handle the Color "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('colors');
    }

}

<?php

namespace App\Observers;


class TagObserver extends Observer
{
    /**
     * Handle the Tag "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('tags');
    }

    /**
     * Handle the Tag "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('tags');
    }

    /**
     * Handle the Tag "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('tags');
    }

}

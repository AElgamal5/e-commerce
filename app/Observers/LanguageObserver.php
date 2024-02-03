<?php

namespace App\Observers;

class LanguageObserver extends Observer
{
    /**
     * Handle the Language "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('languages');
    }

    /**
     * Handle the Language "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('languages');
    }

    /**
     * Handle the Language "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('languages');
    }
}

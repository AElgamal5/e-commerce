<?php

namespace App\Observers;

class UserObserver extends Observer
{
    /**
     * Handle the User "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('users');
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(): void
    {
        $this->deletedCache('users');
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('users');
    }
}

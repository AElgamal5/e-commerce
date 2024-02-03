<?php

namespace App\Observers;

class ContactUsObserver extends Observer
{
    /**
     * Handle the ContactUs "created" event.
     */
    public function created(): void
    {
        $this->deletedCache('contact_us');
    }


    /**
     * Handle the ContactUs "deleted" event.
     */
    public function deleted(): void
    {
        $this->deletedCache('contact_us');
    }

}

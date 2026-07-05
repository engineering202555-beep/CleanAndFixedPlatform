<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
 use App\Jobs\SendWelcomeEmailJob;
class SendWelcomeNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
  

public function handle(UserRegistered $event): void
{
  
    SendWelcomeEmailJob::dispatch(
        $event->user
    );
}
}

<?php

namespace App\Jobs;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
     public function __construct(
        public User $user
    ) {}

    public function handle(): void
    {
        Log::info(
            'Welcome Email Sent To : '
            .$this->user->email
        );
    }
}

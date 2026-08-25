<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserCreated $event
     * @return void
     */
    public function handle(UserCreated $event): void
    {
        $user = $event->user;
        
        // Send welcome email (placeholder implementation)
        // Mail::to($user->email)->send(new WelcomeEmail($user));
        
        // For now, just log that the email would be sent
        \Illuminate\Support\Facades\Log::info('Welcome email queued for user', [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}

<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserCreation
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
        
        Log::info('User created', [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'level_id' => $user->level_id,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}

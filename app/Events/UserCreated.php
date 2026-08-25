<?php

namespace App\Events;

use App\Models\UserModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var UserModel
     */
    public $user;

    /**
     * Create a new event instance
     *
     * @param UserModel $user
     * @return void
     */
    public function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user-created');
    }
}

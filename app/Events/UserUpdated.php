<?php

namespace App\Events;

use App\Models\UserModel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var UserModel
     */
    public $user;

    /**
     * @var array
     */
    public $changes;

    /**
     * Create a new event instance
     *
     * @param UserModel $user
     * @param array $changes
     * @return void
     */
    public function __construct(UserModel $user, array $changes = [])
    {
        $this->user = $user;
        $this->changes = $changes;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user-updated');
    }
}

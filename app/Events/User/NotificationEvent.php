<?php

namespace App\Events\User;

use App\Models\User;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $notification;
    public $user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notification_content,User $user)
    {
        $this->notification = $notification_content;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return ["user-notification-".$this->user->id];
    }

    public function broadcastAs()
    {
        return "user-dashboard-notification-push";
    }
}

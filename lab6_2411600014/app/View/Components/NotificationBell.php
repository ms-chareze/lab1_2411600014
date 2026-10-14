<?php

namespace App\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

/** <x-notification-bell /> : bell icon, unread count, recent notifications. */
class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public Collection $notifications;

    public function __construct()
    {
        $user = auth()->user();

        $this->unreadCount = $user ? $user->unreadNotifications()->count() : 0;
        $this->notifications = $user
            ? $user->notifications()->latest()->take(6)->get()
            : collect();
    }

    public function render(): View
    {
        return view('components.notification-bell');
    }
}

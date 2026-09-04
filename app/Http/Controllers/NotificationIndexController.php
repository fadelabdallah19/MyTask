<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationIndexController extends Controller
{
    public function __invoke(AuthFactory $auth): View
    {
        $userId = $auth->guard()->id();

        $notifications = Notification::query()
            ->where('user_id', $userId)
            ->with('task:id,title')
            ->orderBy('sent_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => Notification::forUser($userId)->unread()->count(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(AuthFactory $auth, Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== $auth->guard()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return back();
    }

    /**
     * Mark all of the current user's notifications as read.
     */
    public function markAllAsRead(AuthFactory $auth): RedirectResponse
    {
        Notification::forUser($auth->guard()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}

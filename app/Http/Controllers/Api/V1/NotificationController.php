<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\NotificationResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Group('Notifications')]
class NotificationController extends ApiController
{
    /**
     * List notifications
     *
     * Get a list of all notifications for the authenticated user.
     */
    public function index()
    {
        return NotificationResource::collection(Auth::user()->notifications);
    }

    /**
     * Get a notification
     *
     * Get the details of a specific notification.
     */
    public function show(DatabaseNotification $notification)
    {
        return NotificationResource::make($notification);
    }

    /**
     * Mark a notification as read
     *
     * Mark a specific notification as read.
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        return NotificationResource::make($notification);
    }

    /**
     * Mark all notifications as read
     *
     * Mark all unread notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return NotificationResource::collection(Auth::user()->notifications);
    }

    /**
     * Delete a notification
     *
     * Delete a specific notification.
     */
    public function destroy(DatabaseNotification $notification)
    {
        $notification->delete();

        return $this->response
            ->message('notification deleted successfully')
            ->build();
    }

    /**
     * Delete all notifications
     *
     * Delete all notifications for the authenticated user.
     */
    public function destroyAll()
    {
        //  NOTE: not entirely sure if this is required but doesn't hurt to have it
        return DB::transaction(function () {
            Auth::user()->notifications()->delete();

            return $this->response
                ->message('all notifications deleted successfully')
                ->build();
        });
    }
}

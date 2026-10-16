<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true]);

        if ($notification->link) {
            $path = parse_url($notification->link, PHP_URL_PATH) ?? '';
            $query = parse_url($notification->link, PHP_URL_QUERY);
            $basePath = rtrim((string) parse_url((string) config('app.url', ''), PHP_URL_PATH), '/');

            if ($basePath !== '' && $basePath !== '/' && str_starts_with($path, $basePath . '/')) {
                $path = substr($path, strlen($basePath));
            }

            $target = url(ltrim($path, '/')) . ($query ? '?' . $query : '');
            return redirect($target);
        }

        return back();
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'ทำเครื่องหมายว่าอ่านแล้วทั้งหมดเรียบร้อยแล้ว');
    }
}

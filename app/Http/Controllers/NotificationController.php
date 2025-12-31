<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
  public function markRead(Request $request)
  {
    $id = $request->input('id');
    $user = Auth::user();
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $notification = $user->unreadNotifications()->where('id', $id)->first();
    if (!$notification) {
      return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }

    $notification->markAsRead();
    return response()->json(['success' => true]);
  }

  public function markAllRead(Request $request)
  {
    $user = Auth::user();
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    $user->unreadNotifications->markAsRead();
    return response()->json(['success' => true]);
  }

  // Return unread notifications as JSON for client-side consumption
  public function unreadJson(Request $request)
  {
    $user = Auth::user();
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    // Do not expose notifications for konsultan or terapis roles
    if (in_array($user->role, ['konsultan', 'terapis'])) {
      return response()->json(['success' => true, 'notifications' => []]);
    }
    $items = $user->unreadNotifications->map(function ($n) {
      return [
        'id' => $n->id,
        'data' => $n->data,
        'created_at' => $n->created_at ? $n->created_at->toDateTimeString() : null,
      ];
    })->values();
    return response()->json(['success' => true, 'notifications' => $items]);
  }
}
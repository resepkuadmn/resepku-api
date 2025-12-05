<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // 1. AMBIL NOTIFIKASI
    public function index(Request $request)
    {
        $notif = Notifikasi::where('user_id', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json(['data' => $notif]);
    }

    // 2. TANDAI SEMUA SUDAH DIBACA
    public function markAsRead(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json(['message' => 'Success']);
    }
}
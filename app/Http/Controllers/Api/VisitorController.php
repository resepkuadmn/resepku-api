<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class VisitorController extends Controller
{
    public function store(Request $request)
    {
            // We'll support both bearer token (Sanctum) and session-based authenticated users.
            $plainToken = $request->bearerToken();

            $userId = null;
            $userType = 'guest';

            if ($plainToken) {
                $pat = PersonalAccessToken::findToken($plainToken);
                if ($pat && $pat->tokenable) {
                    $user = $pat->tokenable;
                    $userId = $user->id;
                    // Eloquent model attributes are accessed via magic methods, so use direct comparison.
                    $userType = ($user->role === 'admin') ? 'admin' : 'member';
                }
            }

            // if no bearer token was found, try to get request->user() (session or guard)
            if (!$userId && $request->user()) {
                $user = $request->user();
                $userId = $user->id;
                $userType = ($user->role === 'admin') ? 'admin' : 'member';
            }

            // use WIB timezone everywhere
            $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $ip = $request->ip();

            // Debugging entry
            Log::info('VisitorController.detect', [
                'bearer' => $plainToken ? true : false,
                'user_id' => $userId,
                'user_type' => $userType,
                'ip' => $ip,
            ]);

            // For authenticated users: dedupe by user_id for the day; if role changed (member->admin) update the existing record
            if ($userId) {
                $existing = DB::table('tabel_pengunjung')
                            ->where('tanggal', $today)
                            ->where('user_id', $userId)
                            ->first();

                if ($existing) {
                    if (isset($existing->user_type) && $existing->user_type !== $userType) {
                        DB::table('tabel_pengunjung')
                            ->where('id', $existing->id)
                            ->update([
                                'user_type' => $userType,
                                'updated_at' => Carbon::now('Asia/Jakarta')
                            ]);
                        return response()->json(['success' => true, 'updated' => true]);
                    }

                    return response()->json(['success' => true, 'skipped' => true]);
                }

                DB::table('tabel_pengunjung')->insert([
                    'user_id' => $userId,
                    'ip_address' => $ip,
                    'tanggal' => $today,
                    'user_type' => $userType,
                    'created_at' => Carbon::now('Asia/Jakarta'),
                    'updated_at' => Carbon::now('Asia/Jakarta')
                ]);

                return response()->json(['success' => true]);
            }

            // Guests: dedupe by ip per day
            $existsGuest = DB::table('tabel_pengunjung')
                            ->where('tanggal', $today)
                            ->where('ip_address', $ip)
                            ->whereNull('user_id')
                            ->exists();

            if ($existsGuest) {
                return response()->json(['success' => true, 'skipped' => true]);
            }

            DB::table('tabel_pengunjung')->insert([
                'user_id' => null,
                'ip_address' => $ip,
                'tanggal' => $today,
                'user_type' => 'guest',
                'created_at' => Carbon::now('Asia/Jakarta'),
                'updated_at' => Carbon::now('Asia/Jakarta')
            ]);

            return response()->json(['success' => true]);

        return response()->json(['message' => 'Visit recorded']);
    }
}
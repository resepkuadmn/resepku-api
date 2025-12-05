<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // optional filter: type=members|guests|all (default all)
        $type = $request->query('type', 'all');
        // 1. Total Hari Ini & Bulan Ini
        $nowJakarta = Carbon::now('Asia/Jakarta');
        $today = $nowJakarta->format('Y-m-d');
        $currentMonth = $nowJakarta->format('Y-m');

        $queryToday = DB::table('tabel_pengunjung')->whereDate('tanggal', $today);
        if ($type === 'members') {
            // count only visits by logged-in users (user_id is not null or user_type == 'member')
            $queryToday = $queryToday->where(function($q) {
                $q->whereNotNull('user_id')->orWhere('user_type', 'member');
            });
        } elseif ($type === 'guests') {
            $queryToday = $queryToday->where('user_type', 'guest')->whereNull('user_id');
        }

        $totalToday = $queryToday->count();

        $queryMonth = DB::table('tabel_pengunjung')->where('tanggal', 'like', "$currentMonth%");
        if ($type === 'members') {
            $queryMonth = $queryMonth->where(function($q) {
                $q->whereNotNull('user_id')->orWhere('user_type', 'member');
            });
        } elseif ($type === 'guests') {
            $queryMonth = $queryMonth->where('user_type', 'guest')->whereNull('user_id');
        }

        $totalMonth = $queryMonth->count();

        // 2. Data Tren 7 Hari Terakhir
        $labels = [];
        $visitorData = [];

        // Loop 7 hari ke belakang (termasuk hari ini)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now('Asia/Jakarta')->subDays($i);
            $dateString = $date->format('Y-m-d');
            
            // Hitung pengunjung pada tanggal tersebut
            $queryDate = DB::table('tabel_pengunjung')->whereDate('tanggal', $dateString);
            if ($type === 'members') {
                $queryDate = $queryDate->where(function($q) {
                    $q->whereNotNull('user_id')->orWhere('user_type', 'member');
                });
            } elseif ($type === 'guests') {
                $queryDate = $queryDate->where('user_type', 'guest')->whereNull('user_id');
            }

            $count = $queryDate->count();
            
            // Format Label (misal: 24 Nov)
            $labels[] = $date->translatedFormat('d M'); 
            $visitorData[] = $count;
        }

        return response()->json([
            'total_today' => $totalToday,
            'total_month' => $totalMonth,
            'chart_labels' => $labels,
            'chart_data' => $visitorData,
            // provide server-side time (WIB) and formatted today label so the frontend can display a precise WIB clock
            'server_time' => $nowJakarta->format('H:i:s'),
            'today_label' => $nowJakarta->format('d M Y')
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RequestResep;
use App\Models\Notifikasi; // Import Model Notifikasi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // Import Facade Mail

class RequestResepController extends Controller
{
    // 1. GET ALL (Untuk Admin)
    public function index()
    {
        // UBAH DISINI: Tambahkan where('status', 'pending')
        $requests = RequestResep::with('user')
            ->where('status', 'pending') // <--- Filter Penting
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $requests]);
    }

    // 2. UPDATE STATUS (Admin Setuju/Tolak)
    public function updateStatus(Request $request, $id)
    {
        $reqResep = RequestResep::with('user')->find($id);
        
        if (!$reqResep) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $request->validate([
            'status' => 'required|in:disetujui,ditolak'
        ]);

        // Update Status
        $reqResep->update(['status' => $request->status]);

        // --- LOGIKA NOTIFIKASI OTOMATIS ---
        
        $statusMsg = $request->status == 'disetujui' ? 'DISETUJUI' : 'DITOLAK';
        $color = $request->status == 'disetujui' ? 'green' : 'red';
        
        // A. Pesan untuk Lonceng (Singkat)
        $pesanLonceng = "Request resep '{$reqResep->resep_diminta}' Anda telah {$statusMsg} oleh Admin.";

        // Simpan ke Tabel Notifikasi
        Notifikasi::create([
            'user_id' => $reqResep->user_id,
            'pesan' => $pesanLonceng,
            'status' => 'unread'
        ]);

        // B. Pesan untuk Email (HTML Lengkap)
        $pesanEmail = "
            <h3>Halo, {$reqResep->user->username}</h3>
            <p>Kami ingin memberitahukan update terbaru mengenai request resep Anda.</p>
            <p>Resep: <b>{$reqResep->resep_diminta}</b></p>
            <p>Status: <b style='color:{$color}'>{$statusMsg}</b></p>
            <br>
            <p>Terima kasih telah berkontribusi di Resepku!</p>
        ";

        // Kirim Email
        try {
            Mail::html($pesanEmail, function($message) use ($reqResep) {
                $message->to($reqResep->user->email)
                        ->subject('Update Status Request Resep');
            });
        } catch (\Exception $e) {
            // Jika email gagal, jangan hentikan proses, cukup log saja
            // Log::error("Gagal kirim email: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Status diperbarui & Notifikasi dikirim', 
            'data' => $reqResep
        ]);
    }
    
    // 3. STORE (User Kirim Request)
    public function store(Request $request)
    {
        $request->validate([
            'resep_diminta' => 'required|string',
            'jenis' => 'required|string'
        ]);

        $req = RequestResep::create([
            'user_id' => $request->user()->id,
            'username' => $request->user()->username,
            'email' => $request->user()->email,
            'resep_diminta' => $request->resep_diminta,
            'jenis' => $request->jenis,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Request terkirim', 'data' => $req], 201);
    }
}
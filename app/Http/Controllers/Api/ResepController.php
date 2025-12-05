<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use App\Models\User;
use App\Models\Notifikasi;
use App\Mail\NewItemNotification;
use App\Mail\DeleteItemNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
// Import Facade Cloudinary
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ResepController extends Controller
{
    // 1. GET ALL (Untuk Halaman Menu & Admin List)
    public function index()
    {
        // Ambil data terbaru dulu
        $resep = Resep::latest()->get();
        return response()->json(['data' => $resep]);
    }

    // 2. GET ONE (Untuk Edit & Detail Resep)
    public function show($id)
    {
        $resep = Resep::where('resep_id_string', $id)->orWhere('id', $id)->first();
        
        if (!$resep) {
            return response()->json(['message' => 'Resep tidak ditemukan'], 404);
        }
        return response()->json(['data' => $resep]);
    }

    // 3. CREATE (Tambah Resep Baru)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'jenis' => 'required|string',
            'waktu' => 'required|string',
            'porsi' => 'required|string',
            'bahan' => 'required|string',
            'cara_membuat' => 'required|string',
            'gambar' => 'required|image|mimes:jpg,jpeg,png,gif|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // --- UPLOAD KE CLOUDINARY ---
        $uploadedFileUrl = null;
        if ($request->hasFile('gambar')) {
            // Upload dan ambil URL HTTPS aman
            $uploadedFileUrl = Cloudinary::upload($request->file('gambar')->getRealPath())->getSecurePath();
        }

        // Buat ID String Unik
        $resepIdString = Str::slug($request->judul) . '-' . uniqid();

        $resep = Resep::create([
            'resep_id_string' => $resepIdString,
            'judul' => $request->judul,
            'jenis' => $request->jenis,
            'waktu' => $request->waktu,
            'porsi' => $request->porsi,
            'bahan' => $request->bahan, // React akan mengirim string HTML (<li>..)
            'cara_membuat' => $request->cara_membuat,
            'gambar' => $uploadedFileUrl // Simpan URL Cloudinary
        ]);

        // Notify all regular users (role = 'user') via database and email
        $users = User::where('role', 'user')->get();
        foreach ($users as $u) {
            Notifikasi::create([
                'user_id' => $u->id,
                'pesan' => "Resep baru: {$resep->judul}",
                'status' => 'unread'
            ]);

            // send email (best-effort, not queued)
            try {
                Mail::to($u->email)->send(new NewItemNotification('resep', $resep));
            } catch (\Exception $e) {
                // Log and continue silently
                Log::error('Mail send failed: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Resep berhasil dibuat', 'data' => $resep], 201);
    }

    // 4. UPDATE (Edit Resep)
    public function update(Request $request, $id)
    {
        $resep = Resep::find($id);
        if (!$resep) return response()->json(['message' => 'Not Found'], 404);

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'jenis' => 'required|string',
            'waktu' => 'required|string',
            'porsi' => 'required|string',
            'bahan' => 'required|string',
            'cara_membuat' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Data yang akan diupdate
        $data = $request->except(['gambar']);

        // Cek jika ada gambar baru
        if ($request->hasFile('gambar')) {
            // Upload gambar baru ke Cloudinary
            $uploadedFileUrl = Cloudinary::upload($request->file('gambar')->getRealPath())->getSecurePath();
            $data['gambar'] = $uploadedFileUrl;
        }

        $resep->update($data);

        return response()->json(['message' => 'Resep berhasil diupdate', 'data' => $resep]);
    }

    // 5. DELETE (Hapus Resep)
    public function destroy($id)
    {
        $resep = Resep::find($id);
        if (!$resep) return response()->json(['message' => 'Not Found'], 404);

        $resepJudul = $resep->judul; // Simpan judul sebelum dihapus

        // Tidak perlu unlink manual karena file ada di Cloudinary
        // (Opsional: Bisa tambah logika hapus di Cloudinary jika menyimpan public_id)

        $resep->delete();

        // Notify users that resep was deleted
        $users = User::where('role', 'user')->get();
        foreach ($users as $u) {
            Notifikasi::create([
                'user_id' => $u->id,
                'pesan' => "Resep dihapus: {$resepJudul}",
                'status' => 'unread'
            ]);
            try {
                Mail::to($u->email)->send(new DeleteItemNotification('resep', $resepJudul));
            } catch (\Exception $e) {
                Log::error('Mail send failed: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Resep berhasil dihapus']);
    }
}
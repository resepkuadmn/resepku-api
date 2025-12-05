<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tips;
use App\Models\User;
use App\Models\Notifikasi;
use App\Mail\NewItemNotification;
use App\Mail\DeleteItemNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
// Import Cloudinary
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TipsController extends Controller
{
    public function index() {
        return response()->json(['data' => Tips::latest()->get()]);
    }

    public function show($id) {
        $tips = Tips::find($id);
        if (!$tips) return response()->json(['message' => 'Not Found'], 404);
        return response()->json(['data' => $tips]);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'judul' => 'required', 'konten' => 'required',
            'gambar' => 'required|image|max:5120',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        // Upload Cloudinary
        $uploadedFileUrl = Cloudinary::upload($request->file('gambar')->getRealPath())->getSecurePath();

        $tips = Tips::create([
            'artikel_id_string' => Str::slug($request->judul) . '-' . uniqid(),
            'judul' => $request->judul,
            'konten' => $request->konten,
            'gambar' => $uploadedFileUrl
        ]);

        // Notify users
        $users = User::where('role', 'user')->get();
        foreach ($users as $u) {
            Notifikasi::create([
                'user_id' => $u->id,
                'pesan' => "Tips baru: {$tips->judul}",
                'status' => 'unread'
            ]);
            try {
                Mail::to($u->email)->send(new NewItemNotification('tips', $tips));
            } catch (\Exception $e) {
                Log::error('Mail send failed: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Success', 'data' => $tips]);
    }

    public function update(Request $request, $id) {
        $tips = Tips::find($id);
        if (!$tips) return response()->json(['message' => 'Not Found'], 404);

        $data = $request->except(['gambar']);
        
        if ($request->hasFile('gambar')) {
            $uploadedFileUrl = Cloudinary::upload($request->file('gambar')->getRealPath())->getSecurePath();
            $data['gambar'] = $uploadedFileUrl;
        }
        
        $tips->update($data);
        return response()->json(['message' => 'Updated', 'data' => $tips]);
    }

    public function destroy($id) {
        $tips = Tips::find($id);
        if (!$tips) return response()->json(['message' => 'Not Found'], 404);

        $tipsJudul = $tips->judul; // Simpan judul sebelum dihapus

        // Hapus data (File di Cloudinary biarkan atau hapus manual via dashboard jika mau)
        $tips->delete();

        // Notify users that tips was deleted
        $users = User::where('role', 'user')->get();
        foreach ($users as $u) {
            Notifikasi::create([
                'user_id' => $u->id,
                'pesan' => "Tips dihapus: {$tipsJudul}",
                'status' => 'unread'
            ]);
            try {
                Mail::to($u->email)->send(new DeleteItemNotification('tips', $tipsJudul));
            } catch (\Exception $e) {
                Log::error('Mail send failed: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Deleted']);
    }
}
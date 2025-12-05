<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\About;
use App\Models\User;
use App\Models\Notifikasi;
use App\Mail\NewItemNotification;
use App\Mail\DeleteItemNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AboutController extends Controller
{
    public function index() {
        return response()->json(['data' => About::latest()->get()]);
    }

    public function show($id) {
        $about = About::find($id);
        if (!$about) return response()->json(['message' => 'Not Found'], 404);
        return response()->json(['data' => $about]);
    }

    public function store(Request $request) {
        // Validasi & Upload (Mirip Resep)
        $validator = Validator::make($request->all(), [
            'judul' => 'required', 'deskripsi' => 'required',
            'gambar' => 'required|image|max:5120',
            'layout' => 'required|in:kiri,kanan',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $imageName = time() . '.' . $request->gambar->extension();
        $request->gambar->move(public_path('gambar'), $imageName);

        $about = About::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'gambar' => $imageName,
            'layout' => $request->layout // Ambil dari request
        ]);

        // Notify users
        $users = User::where('role', 'user')->get();
        foreach ($users as $u) {
            Notifikasi::create([
                'user_id' => $u->id,
                'pesan' => "About baru: {$about->judul}",
                'status' => 'unread'
            ]);
            try {
                Mail::to($u->email)->send(new NewItemNotification('about', $about));
            } catch (\Exception $e) {
                Log::error('Mail send failed: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Success', 'data' => $about]);
    }

    public function update(Request $request, $id) {
        $about = About::find($id);
        if (!$about) return response()->json(['message' => 'Not Found'], 404);

        $data = $request->except(['gambar']);
        if ($request->hasFile('gambar')) {
            if ($about->gambar && file_exists(public_path('gambar/' . $about->gambar))) {
                unlink(public_path('gambar/' . $about->gambar));
            }
            $imageName = time() . '.' . $request->file('gambar')->extension();
            $request->file('gambar')->move(public_path('gambar'), $imageName);
            $data['gambar'] = $imageName;
        }
        $about->update($data);
        return response()->json(['message' => 'Updated', 'data' => $about]);
    }

    public function destroy($id) {
        $about = About::find($id);
        if (!$about) return response()->json(['message' => 'Not Found'], 404);

        $aboutJudul = $about->judul; // Simpan judul sebelum dihapus

        if ($about->gambar && file_exists(public_path('gambar/' . $about->gambar))) {
            unlink(public_path('gambar/' . $about->gambar));
        }
        $about->delete();

        // Notify users that about was deleted
        $users = User::where('role', 'user')->get();
        foreach ($users as $u) {
            Notifikasi::create([
                'user_id' => $u->id,
                'pesan' => "About dihapus: {$aboutJudul}",
                'status' => 'unread'
            ]);
            try {
                Mail::to($u->email)->send(new DeleteItemNotification('about', $aboutJudul));
            } catch (\Exception $e) {
                Log::error('Mail send failed: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Deleted']);
    }
}
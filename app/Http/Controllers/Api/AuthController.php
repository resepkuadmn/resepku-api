<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:tabel_users',
            'email' => 'required|string|email|max:255|unique:tabel_users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Simpan User ke Database
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        // 3. Buat Token (Agar bisa langsung login kalau mau)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Kirim Email Notifikasi (Welcome Email)
        try {
            $htmlContent = "
                <div style='font-family: sans-serif; color: #333;'>
                    <h2 style='color: #e6a357;'>Halo, {$user->username}! 👋</h2>
                    <p>Selamat! Akun Anda di <b>Resepku</b> telah berhasil dibuat.</p>
                    <p>Silakan login untuk mulai menjelajahi resep lezat kami.</p>
                </div>
            ";

            Mail::html($htmlContent, function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Selamat Bergabung di Resepku!');
            });
        } catch (\Exception $e) {
            // If welcome email fails, log it for debugging (don't break registration)
            Log::error('Welcome email failed: ' . $e->getMessage());
        }

        // 5. Kirim Respon Sukses ke React - KONSISTEN dengan format login
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan login dengan akun Anda.',
            'data' => $user,
            'access_token' => $token
        ], 201); // 201 = Created (bukan 200)
    }

    /**
     * Login with username OR email.
     * Accepts a single `login` field which may be a username or email.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $login = $request->input('login');

        // Determine whether the login looks like an email
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $login)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau username tidak terdaftar.'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => $user,
            'access_token' => $token
        ], 200);
    }

    // Password reset handled by ForgotPasswordController (routes /forgot-password, /verify-otp, /reset-password)

    /**
     * Logout user by deleting the current access token.
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan logout.'
            ], 500);
        }
    }
}
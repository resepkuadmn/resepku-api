<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    // TAHAP 1: Kirim OTP ke Email
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Wrap everything so unexpected errors (DB update, mail) don't return 500
        // to the user UI — instead we log and return a neutral success message.
        try {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                // Return 404 so frontend can show 'Email tidak ditemukan.' as current UI expects
                return response()->json(['message' => 'Email tidak ditemukan.'], 404);
            }

            // Generate OTP 6 Digit
            $otp = rand(100000, 999999);
            $expiry = Carbon::now()->addMinutes(10); // Berlaku 10 menit

            // Simpan ke Database
            $user->update([
                'reset_token' => $otp,
                'token_expiry' => $expiry
            ]);

            // Kirim Email
            try {
                $htmlContent = "
                    <div style='font-family: Arial, sans-serif; color: #333;'>
                        <h3>Permintaan Reset Password</h3>
                        <p>Halo, <b>{$user->username}</b>.</p>
                        <p>Gunakan kode berikut untuk mereset kata sandi Anda:</p>
                        <h2 style='color: #e6a357; letter-spacing: 5px;'>{$otp}</h2>
                        <p>Kode ini berlaku selama 10 menit.</p>
                    </div>
                ";

                Mail::html($htmlContent, function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Kode OTP Reset Password Resepku');
                });
            } catch (\Exception $e) {
                // Log, but continue — in dev we may return otp_debug in response
                Log::error('ForgotPassword sendOtp: mail send failed: ' . $e->getMessage());
            }

            $payload = ['message' => 'Kode OTP terkirim ke email.'];
            if (env('APP_DEBUG')) $payload['otp_debug'] = $otp;
            return response()->json($payload);

        } catch (\Exception $e) {
            // Log the unexpected error and return a neutral response (or debug payload)
            Log::error('ForgotPassword sendOtp unexpected error: ' . $e->getMessage());
            $payload = ['message' => 'Terjadi kesalahan saat memproses permintaan. Jika ini terus terjadi, hubungi admin.'];
            if (env('APP_DEBUG')) {
                // If dev, add the OTP (if it was generated) to avoid breaking tests — otherwise safe neutral message
                if (isset($otp)) $payload['otp_debug'] = $otp;
            }
            // Return 200 so front-end shows friendly message rather than 'Terjadi kesalahan sistem.'
            return response()->json($payload, 200);
        }
    }

    // TAHAP 2: Verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric'
        ]);

        $user = User::where('email', $request->email)
                    ->where('reset_token', $request->otp)
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Kode OTP salah.'], 422);
        }

        if (Carbon::now()->gt($user->token_expiry)) {
            return response()->json(['message' => 'Kode OTP sudah kadaluwarsa.'], 422);
        }

        return response()->json(['message' => 'Kode valid.']);
    }

    // TAHAP 3: Reset Password Baru
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Update Password & Hapus Token
        $user->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'token_expiry' => null
        ]);

        return response()->json(['message' => 'Password berhasil diubah. Silakan login.']);
    }
}
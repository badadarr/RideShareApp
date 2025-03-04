<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|numeric|min:10',
            ]);

            $user = User::firstOrCreate(
                ['phone' => $request->phone],
            );


            if (!$user) {
                return response()->json(['message' => 'Tidak dapat proses pengguna dengan nomor telepon ini'], 401);
            }

            $user->notify(new LoginNeedsVerification());
            return response()->json(['message' => 'Kode akses login telah dikirim'], 200);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['message' => 'Kesalahan Response'], 401);
        }
    }

    public function verify(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|numeric|min:10',
                'login_code' => 'required|numeric|between:100000,999999',
            ]);

            $user = User::where('phone', $request->phone)
                ->where('login_code', $request->login_code)
                ->first();

            if (!$user) {
                return response()->json(['message' => 'Kode akses login tidak valid'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Kesalahan Response'], 401);
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout successful'], 200);
    }
}

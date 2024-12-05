<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
//use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
            'role' => 'default:staff'
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Bisa berupa email atau username
            'password' => 'required|string',
        ]);

        // Ambil input 'login' yang bisa berupa email atau username
        $login = $request->input('login');
        $password = $request->input('password');

        // Coba cari user berdasarkan email atau username
        $user = User::where('email', $login)
                    ->orWhere('username', $login)
                    ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal, periksa email/username dan password Anda.',
            ], 401);
        }

        // Buat token untuk user yang valid
        $token = auth()->guard('api')->login($user);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
        ], 200);
    }


    public function logout()
    {
        try {
            // Mendapatkan token dari request
            $token = JWTAuth::getToken();

            // Menghapus atau meng-invalidasi token
            if ($token) {
                JWTAuth::invalidate($token);

                return response()->json([
                    'success' => true,
                    'message' => 'Logout berhasil!',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak ditemukan.',
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal, token tidak valid atau sudah kedaluwarsa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

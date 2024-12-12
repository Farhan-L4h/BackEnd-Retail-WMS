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

    // CRUD User / Kelola User
    public function index()
    {
        $users = User::all(); // Ambil semua data user
        return response()->json([
            'message' => 'Daftar user berhasil diambil',
            'users' => $users,
        ], 200);
    }

    // Membuat user baru
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5|confirmed',
            // 'role' => 'default:staff'
            'role' => 'required|in:staff,admin'
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat',
            'user' => $user,
        ], 201);
    }

    // Menampilkan detail user berdasarkan ID
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail user berhasil diambil',
            'user' => $user,
        ], 200);
    }

    // Mengupdate user berdasarkan ID
    public function update(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    // Perform validation
    $validatedData = $request->validate([
        'username' => 'required|string|max:255|unique:users,username,' . $id, // Exclude current user's username from validation
        'email' => 'required|string|email|max:255|unique:users,email,' . $id, // Exclude current user's email from validation
        'password' => 'nullable|string|min:5|confirmed', // Make password optional
        'role' => 'required|in:staff,admin', // Ensure role is one of the accepted values
    ]);

    // Update the user details
    $user->username = $validatedData['username'] ?? $user->username;
    $user->email = $validatedData['email'] ?? $user->email;
    
    // If password is provided, update it. If not, keep the current password.
    if (isset($validatedData['password'])) {
        $user->password = Hash::make($validatedData['password']);
    }

    $user->role = $validatedData['role'] ?? $user->role;
    
    // Save the updated user
    $user->save();

    return response()->json([
        'message' => 'User berhasil diupdate',
        'user' => $user,
    ], 200);
}


    // Menghapus user berdasarkan ID
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus'], 200);
    }
}
?>

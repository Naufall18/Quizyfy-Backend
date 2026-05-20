<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('Register request received:', $request->all());

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'password_confirmation' => 'required|string|same:password',
                'role' => 'required|string|in:user,guru,admin',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            /** @var User $user */
            $user = User::create([
                'name' => $request->name,
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
            ]);

            // Fixed: Proper token creation with plainTextToken
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User created successfully:', ['id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            Log::error('Register error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Auth::attempt(['email' => strtolower($request->email), 'password' => $request->password])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            /** @var User $user */
            $user = Auth::user();

            // Fixed: Proper token creation with plainTextToken
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token
            ], 200);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get user error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * POST /auth/google
     *
     * Menerima Google ID Token dari Flutter, memverifikasinya ke Google,
     * lalu login atau register user secara otomatis.
     *
     * Payload: { id_token, email, name, photo_url? }
     */
    public function googleLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_token'  => 'required|string',
                'email'     => 'required|email',
                'name'      => 'required|string|max:255',
                'photo_url' => 'nullable|string|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // ── 1. Verifikasi ID Token ke Google tokeninfo endpoint ──────────
            $idToken  = $request->input('id_token');
            $googleResponse = \Illuminate\Support\Facades\Http::get(
                'https://oauth2.googleapis.com/tokeninfo',
                ['id_token' => $idToken]
            );

            if ($googleResponse->failed()) {
                Log::warning('Google token verification failed', [
                    'status' => $googleResponse->status(),
                    'body'   => $googleResponse->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Token Google tidak valid atau sudah kadaluarsa.',
                ], 401);
            }

            $googleData = $googleResponse->json();

            // Pastikan token memang untuk app kita (aud = client_id)
            // Jika GOOGLE_CLIENT_ID di .env kosong, skip validasi aud
            $clientId = config('services.google.client_id');
            if ($clientId && ($googleData['aud'] ?? '') !== $clientId) {
                Log::warning('Google token aud mismatch', [
                    'expected' => $clientId,
                    'got'      => $googleData['aud'] ?? 'none',
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Token Google tidak valid untuk aplikasi ini.',
                ], 401);
            }

            $googleId = $googleData['sub'] ?? null;
            if (!$googleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mendapatkan Google ID dari token.',
                ], 401);
            }

            // ── 2. Cari atau buat user ────────────────────────────────────────
            /** @var User $user */
            $user = User::where('google_id', $googleId)
                ->orWhere('email', strtolower($request->email))
                ->first();

            if ($user) {
                // User sudah ada — update google_id jika belum terhubung
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleId]);
                }
                // Update avatar dari Google jika user belum punya avatar sendiri
                if (!$user->avatar && $request->photo_url) {
                    $user->update(['google_avatar' => $request->photo_url]);
                }
            } else {
                // User baru — register otomatis dengan role 'user' (siswa)
                $user = User::create([
                    'name'          => $request->name,
                    'email'         => strtolower($request->email),
                    'google_id'     => $googleId,
                    'google_avatar' => $request->photo_url,
                    'role'          => 'user',
                    'is_active'     => true,
                    // password null — user Google tidak perlu password
                ]);
            }

            // ── 3. Buat Sanctum token ─────────────────────────────────────────
            // Hapus token lama agar tidak menumpuk
            $user->tokens()->where('name', 'google_auth_token')->delete();
            $token = $user->createToken('google_auth_token')->plainTextToken;

            Log::info('Google login success', ['user_id' => $user->id, 'email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'Login dengan Google berhasil',
                'user'    => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'avatar' => $user->avatar ?? $user->google_avatar,
                ],
                'token'   => $token,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Login Google gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur lupa password belum diimplementasikan.',
        ], 501);
    }

    public function resetPassword(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur reset password belum diimplementasikan.',
        ], 501);
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password'     => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 400);
            }

            $user->update(['password' => Hash::make($request->new_password)]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Change password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
            ], 500);
        }
    }
}
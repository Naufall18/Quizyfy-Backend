<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\AvatarHelper;
use App\Helpers\BaseResponse;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateBiodataRequest;

class SiswaController extends Controller
{
    /** Profil siswa yang sedang login. */
    public function index(): JsonResponse
    {
        $siswa = auth()->user();
        return BaseResponse::OK($this->formatProfile($siswa), 'Profil berhasil diambil');
    }

    /** Detail siswa by ID (admin/guru). */
    public function show(string $id): JsonResponse
    {
        $siswa = User::where('role', 'user')->findOrFail($id);
        return BaseResponse::OK($this->formatProfile($siswa), 'Data siswa ditemukan');
    }

    /** Update biodata siswa. */
    public function update(UpdateBiodataRequest $request): JsonResponse
    {
        $siswa = auth()->user();
        $siswa->update($request->validated());
        return BaseResponse::OK($this->formatProfile($siswa->fresh()), 'Profil berhasil diperbarui');
    }

    /**
     * Ganti password siswa.
     * PUT /user/profile/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $siswa = auth()->user();

        if (!Hash::check($request->current_password, $siswa->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini salah.'],
            ]);
        }

        $siswa->update(['password' => Hash::make($request->password)]);

        // Revoke semua token lama agar sesi lama tidak valid
        $siswa->tokens()->delete();
        $newToken = $siswa->createToken('api')->plainTextToken;

        return BaseResponse::OK([
            'access_token' => $newToken,
        ], 'Password berhasil diubah. Silakan login ulang.');
    }

    /** Update avatar siswa. */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $siswa = auth()->user();
        $request->validated();

        if ($request->hasFile('avatar')) {
            AvatarHelper::deleteAvatarIfExists($siswa->avatar);
            $siswa->avatar = AvatarHelper::storeAvatar($request->file('avatar'));
        }

        $siswa->save();

        return BaseResponse::OK([
            'avatar_url'      => AvatarHelper::getAvatarUrl($siswa, 'siswa'),
            'avatar_uploaded' => (bool) $siswa->avatar,
        ], 'Avatar berhasil diperbarui');
    }

    /** Hapus avatar siswa. */
    public function destroyAvatar(): JsonResponse
    {
        $siswa = auth()->user();

        if (!$siswa->avatar) {
            return BaseResponse::BadRequest('Tidak ada avatar untuk dihapus');
        }

        AvatarHelper::deleteAvatarIfExists($siswa->avatar);
        $siswa->update(['avatar' => null]);

        return BaseResponse::OK(null, 'Avatar berhasil dihapus');
    }

    // ─── Helper ───────────────────────────────────────────────
    private function formatProfile($user): array
    {
        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'phone'           => $user->phone_number,
            'gender'          => $user->gender,
            'avatar_url'      => AvatarHelper::getAvatarUrl($user, 'siswa'),
            'avatar_uploaded' => (bool) $user->avatar,
            'role'            => $user->role,
            'status'          => $user->status,
            'created_at'      => $user->created_at?->toIso8601String(),
        ];
    }
}

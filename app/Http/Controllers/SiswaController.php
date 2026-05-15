<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\AvatarHelper;
use App\Helpers\BaseResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdateBiodataRequest;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $siswa = auth()->user();

        $data = [
            'name' => $siswa->name,
            'email' => $siswa->email,
            'phone' => $siswa->phone_number,
            'gender' => $siswa->gender,
            'avatar_url' => AvatarHelper::getAvatarUrl($siswa, 'siswa'),
            'avatar_uploaded' => $siswa->avatar ? true : false,
            'role' => $siswa->role,
            'created_at' => $siswa->created_at,
            'status' => $siswa->status,
        ];
        return BaseResponse::OK($data, 'Biodata retrieved successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $siswa = User::where('role', 'siswa')->findOrFail($id);

        return BaseResponse::OK($siswa, 'Siswa found');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBiodataRequest $request)
    {
        $siswa = auth()->user();
        $validated = $request->validated();

        $siswa->update($validated);

        return BaseResponse::OK($siswa, 'Biodata updated successfully');
    }

    public function updateAvatar(UpdateAvatarRequest $request)
    {
        $siswa = auth()->user();
        $request->validated();

        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            AvatarHelper::deleteAvatarIfExists($siswa->avatar);

            // Simpan avatar baru
            $siswa->avatar = AvatarHelper::storeAvatar($request->file('avatar'));
        }

        $siswa->save();

        return BaseResponse::OK([
            'avatar_url' => AvatarHelper::getAvatarUrl($siswa, 'siswa'),
            'avatar_uploaded' => (bool) $siswa->avatar,
        ], 'Avatar updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyAvatar(string $id)
    {
        $siswa = auth()->user();

        if (!$siswa->avatar) {
            return BaseResponse::BadRequest('No avatar to delete');
        }

        AvatarHelper::deleteAvatarIfExists($siswa->avatar);

        $siswa->avatar = null;
        $siswa->save();

        return BaseResponse::OK(null, 'Avatar deleted successfully');
    }
}

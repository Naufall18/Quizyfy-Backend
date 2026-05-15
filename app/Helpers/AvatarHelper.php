<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class AvatarHelper
{

    public static function generateDefaultAvatar(string $name, string $role)
    {
        $background = match ($role) {
            'guru' => '007bff',   // biru
            'siswa' => '28a745',  // hijau
            default => '6c757d',  // abu-abu
        };

        return "https://ui-avatars.com/api/?name=" . urlencode($name)
            . "&background={$background}&color=ffffff&bold=true";
    }

    public static function getAvatarUrl($user, $type = 'guru')
    {
        return $user->avatar
            ? asset('storage/' . $user->avatar)
            : self::generateDefaultAvatar($user->name, $type);
    }

    public static function deleteAvatarIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function storeAvatar($file): string
    {
        return $file->store('avatars', 'public');
    }
}

<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Image paths used by website content. Two kinds:
 * - website paths ("/images/...") — files that ship with the Next.js site (the original content);
 * - uploaded paths ("products/abc.webp") — files uploaded in the admin, on the public disk.
 */
class Media
{
    public static function store(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, 'public');
    }

    public static function isUploaded(?string $path): bool
    {
        return filled($path) && ! Str::startsWith($path, ['/', 'http://', 'https://']);
    }

    public static function delete(?string $path): void
    {
        if (self::isUploaded($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /** For the website API: website paths stay relative (the site serves them), uploads become absolute. */
    public static function forWebsite(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        // url() makes sure uploads are absolute — the website lives on another host.
        return self::isUploaded($path) ? url(Storage::disk('public')->url($path)) : $path;
    }

    /** For admin previews: website paths are loaded from the website itself. */
    public static function forAdmin(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Str::startsWith($path, '/') ? config('thor.frontend_url').$path : Storage::disk('public')->url($path);
    }
}

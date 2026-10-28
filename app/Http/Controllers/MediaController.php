<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

/** Serves photos uploaded in the admin panel. Images only, and only from the uploads folder. */
class MediaController extends Controller
{
    private const TYPES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];

    public function show(string $path)
    {
        $type = self::TYPES[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? null;
        $disk = Storage::disk('public');
        abort_unless($type && $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

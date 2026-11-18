<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Home page hero film and the banner image of each inner page. */
class MediaController extends Controller
{
    public function edit(): View
    {
        return view('admin.media', [
            'hero' => Setting::get(Setting::HERO),
            'banners' => Setting::get(Setting::BANNERS),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $pages = array_keys(config('thor.banner_pages'));
        $request->validate([
            'youtube' => ['nullable', 'string', 'max:255'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4', 'max:25600'],
            'remove_video' => ['nullable', 'boolean'],
            'poster_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'banner_files' => ['nullable', 'array'],
            'banner_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'video_file.mimetypes' => 'The film must be an MP4 video.',
            'video_file.max' => 'Keep the film under 25 MB (a 10–20 second 1080p loop is ideal).',
            'banner_files.*.max' => 'Banner images must be 5 MB or smaller.',
        ]);

        $youtube = trim((string) $request->input('youtube'));
        $youtubeId = $youtube === '' ? '' : self::youtubeId($youtube);
        if ($youtubeId === null) {
            throw ValidationException::withMessages(['youtube' => 'Paste a YouTube link (e.g. https://www.youtube.com/watch?v=…) or the 11-character video ID.']);
        }

        // Hero: an uploaded MP4 takes priority over YouTube on the website.
        $hero = Setting::get(Setting::HERO) + ['video' => '', 'poster' => ''];
        $hero['youtube_id'] = $youtubeId;
        if ($request->hasFile('video_file') || $request->boolean('remove_video')) {
            Media::delete($hero['video']);
            $hero['video'] = $request->hasFile('video_file') ? Media::store($request->file('video_file'), 'hero') : '';
        }
        if ($request->hasFile('poster_file')) {
            Media::delete($hero['poster']);
            $hero['poster'] = Media::store($request->file('poster_file'), 'hero');
        }
        if (! $hero['youtube_id'] && ! $hero['video'] && ! $hero['poster']) {
            throw ValidationException::withMessages(['youtube' => 'The home page needs a film or at least a poster image.']);
        }
        Setting::put(Setting::HERO, $hero);

        $banners = Setting::get(Setting::BANNERS);
        $changed = false;
        foreach ($request->file('banner_files', []) as $page => $file) {
            if (in_array($page, $pages, true)) {
                Media::delete($banners[$page] ?? null);
                $banners[$page] = Media::store($file, 'banners');
                $changed = true;
            }
        }
        if ($changed) {
            Setting::put(Setting::BANNERS, $banners);
        }

        return back()->with('success', 'Hero & banners saved. The website updates straight away.');
    }

    /** Accepts youtu.be/…, youtube.com/watch?v=…, /embed/…, /shorts/… links or a bare ID. null = not recognised. */
    public static function youtubeId(string $input): ?string
    {
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
            return $input;
        }
        $pattern = '~^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/|youtube-nocookie\.com/embed/)([A-Za-z0-9_-]{11})~i';

        return preg_match($pattern, $input, $m) ? $m[1] : null;
    }
}

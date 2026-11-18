<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ListField;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Registrations, patent, awards and the certificate image ("Our Recognition" section). */
class CredentialsController extends Controller
{
    public function edit(): View
    {
        return view('admin.credentials', ['c' => Setting::get(Setting::CREDENTIALS)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $current = Setting::get(Setting::CREDENTIALS);

        $data = $request->validate([
            'dipp' => ['required', 'string', 'max:30'],
            'dipp_verify' => ['nullable', 'url:https', 'max:300'],
            'udyam' => ['nullable', 'string', 'max:30'],
            'gem' => ['nullable', 'string', 'max:30'],
            'patent' => ['nullable', 'string', 'max:30'],
            'awards' => ['nullable', 'string', 'max:1000'],
            'certificate_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], ['certificate_file.max' => 'The certificate image must be 5 MB or smaller.']);

        $data['awards'] = ListField::lines($data['awards'] ?? '', 'awards', maxLines: 8, maxLength: 120);
        $data['certificate'] = $current['certificate'] ?? null;
        $data['certificate_hd'] = $current['certificate_hd'] ?? null;

        // One upload serves both the thumbnail and the full-size view.
        if ($request->hasFile('certificate_file')) {
            Media::delete($data['certificate']);
            if ($data['certificate_hd'] !== $data['certificate']) {
                Media::delete($data['certificate_hd']);
            }
            $data['certificate'] = $data['certificate_hd'] = Media::store($request->file('certificate_file'), 'credentials');
        }
        unset($data['certificate_file']);

        Setting::put(Setting::CREDENTIALS, $data);

        return back()->with('success', 'Certificates & awards saved. The website updates straight away.');
    }
}

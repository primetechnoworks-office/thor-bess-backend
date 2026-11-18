<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Company details shown across the website (header, footer, contact page, product pages). */
class SettingsController extends Controller
{
    private const PHONE = 'regex:/^[0-9 +()\-]{7,20}$/';

    public function edit(): View
    {
        return view('admin.settings', ['s' => Setting::get(Setting::COMPANY)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', self::PHONE],
            'whatsapp' => ['nullable', 'string', self::PHONE],
            'email' => ['required', 'email', 'max:120'],
            'sales_email' => ['required', 'email', 'max:120'],
            'landline' => ['nullable', 'string', self::PHONE],
            'fax' => ['nullable', 'string', self::PHONE],
            'hours' => ['nullable', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:300'],
            'offices' => ['nullable', 'array', 'max:6'],
            'offices.*.label' => ['required', 'string', 'max:60'],
            'offices.*.lines' => ['required', 'string', 'max:500'],
            // Shown in an embedded map frame on the contact page, so only Google Maps links are accepted.
            'offices.*.map' => ['nullable', 'url:https', 'max:600', 'regex:#^https://(www\.|maps\.)?google\.[a-z.]+/maps#i'],
            'offices.*.directions' => ['nullable', 'url:https', 'max:600'],
            'google_profile' => ['nullable', 'url:https', 'max:300'],
            'google_review' => ['nullable', 'url:https', 'max:300'],
            'socials' => ['nullable', 'array'],
            'socials.*' => ['nullable', 'url:https', 'max:300'],
            'book_now_url' => ['nullable', 'url:https', 'max:300'],
            'indiamart_url' => ['nullable', 'url:https', 'max:300'],
        ], [
            'phone.regex' => 'Use digits, spaces, + ( ) or - only.',
            'whatsapp.regex' => 'Use digits, spaces, + ( ) or - only.',
            'offices.*.map.url' => 'The map link must be a full https:// address.',
            'offices.*.map.regex' => 'Use a Google Maps embed link (https://www.google.com/maps?…&output=embed).',
            'socials.*.url' => 'Social links must be full https:// addresses.',
        ]);

        $lines = fn (string $text) => array_values(array_filter(array_map('trim', preg_split('/\R/', $text))));

        $data['address'] = $lines($data['address']);
        $data['offices'] = array_values(array_map(fn ($o) => [
            'label' => trim($o['label']), 'lines' => $lines($o['lines']), 'map' => $o['map'] ?? '', 'directions' => $o['directions'] ?? '',
        ], $data['offices'] ?? []));
        $data['socials'] = array_intersect_key(array_map(fn ($v) => (string) $v, $data['socials'] ?? []), Setting::SOCIAL_NETWORKS);

        Setting::put(Setting::COMPANY, $data);

        return back()->with('success', 'Company details saved. The website updates straight away.');
    }
}

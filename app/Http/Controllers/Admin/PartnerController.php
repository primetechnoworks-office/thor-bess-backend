<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Setting;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Partner logos (home page slider) and the incubators list (About page). */
class PartnerController extends Controller
{
    public function index(): View
    {
        return view('admin.partners.index', [
            'partners' => Partner::ordered()->get(),
            'incubators' => Setting::get(Setting::INCUBATORS),
        ]);
    }

    public function create(): View
    {
        return view('admin.partners.form', ['partner' => new Partner(['is_active' => true, 'sort_order' => Partner::nextSortOrder()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Partner::create($this->validated($request));

        return redirect()->route('admin.partners.index')->with('success', 'Partner added.');
    }

    public function edit(Partner $partner): View
    {
        return view('admin.partners.form', ['partner' => $partner]);
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $partner->update($this->validated($request, $partner));

        return redirect()->route('admin.partners.index')->with('success', 'Partner saved.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        Media::delete($partner->logo);
        $partner->delete();

        return redirect()->route('admin.partners.index')->with('success', 'Partner deleted.');
    }

    public function incubators(Request $request): RedirectResponse
    {
        $text = $request->validate(['incubators' => ['nullable', 'string', 'max:3000']])['incubators'] ?? '';

        Setting::put(Setting::INCUBATORS, array_values(array_filter(array_map('trim', preg_split('/\R/', $text)))));

        return back()->with('success', 'Incubators list saved.');
    }

    private function validated(Request $request, ?Partner $partner = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'url' => ['nullable', 'url:http,https', 'max:255'],
            // No SVG: an uploaded SVG can carry script that runs if someone opens the file directly.
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ], ['photo.mimes' => 'Upload the logo as JPG, PNG or WebP (export SVG logos as PNG).']);

        unset($data['photo']);
        if ($request->hasFile('photo')) {
            Media::delete($partner?->logo);
            $data['logo'] = Media::store($request->file('photo'), 'partners');
        } elseif (! $partner?->logo) {
            throw ValidationException::withMessages(['photo' => 'Please upload the partner logo.']);
        }
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

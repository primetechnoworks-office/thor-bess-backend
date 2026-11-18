<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(): View
    {
        return view('admin.team.index', ['members' => TeamMember::ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.team.form', ['member' => new TeamMember([
            'is_active' => true, 'sort_order' => (int) TeamMember::max('sort_order') + 10,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        TeamMember::create($this->validated($request));

        return redirect()->route('admin.team.index')->with('success', 'Team member added.');
    }

    public function edit(TeamMember $team): View
    {
        return view('admin.team.form', ['member' => $team]);
    }

    public function update(Request $request, TeamMember $team): RedirectResponse
    {
        $team->update($this->validated($request, $team));

        return redirect()->route('admin.team.index')->with('success', 'Team member saved.');
    }

    public function destroy(TeamMember $team): RedirectResponse
    {
        Media::delete($team->image);
        $team->delete();

        return redirect()->route('admin.team.index')->with('success', 'Team member removed.');
    }

    private function validated(Request $request, ?TeamMember $member = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'role' => ['required', 'string', 'max:70'],
            'linkedin' => ['nullable', 'url:https', 'max:255', 'regex:#^https://([a-z]+\.)?linkedin\.com/#i'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ], ['linkedin.regex' => 'Please enter a LinkedIn profile link (https://www.linkedin.com/in/…).']);

        unset($data['photo'], $data['remove_photo']);
        if ($request->hasFile('photo') || $request->boolean('remove_photo')) {
            Media::delete($member?->image);
            $data['image'] = $request->hasFile('photo') ? Media::store($request->file('photo'), 'team') : null;
        }
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MilestoneController extends Controller
{
    public function index(): View
    {
        return view('admin.milestones.index', ['milestones' => Milestone::ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.milestones.form', ['milestone' => new Milestone(['is_active' => true, 'sort_order' => Milestone::nextSortOrder()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Milestone::create($this->validated($request));

        return redirect()->route('admin.milestones.index')->with('success', 'Milestone added.');
    }

    public function edit(Milestone $milestone): View
    {
        return view('admin.milestones.form', ['milestone' => $milestone]);
    }

    public function update(Request $request, Milestone $milestone): RedirectResponse
    {
        $milestone->update($this->validated($request, $milestone));

        return redirect()->route('admin.milestones.index')->with('success', 'Milestone saved.');
    }

    public function destroy(Milestone $milestone): RedirectResponse
    {
        Media::delete($milestone->image);
        $milestone->delete();

        return redirect()->route('admin.milestones.index')->with('success', 'Milestone deleted.');
    }

    private function validated(Request $request, ?Milestone $milestone = null): array
    {
        $data = $request->validate([
            'tag' => ['required', 'string', 'max:30'],
            'title' => ['required', 'string', 'max:90'],
            'copy' => ['required', 'string', 'max:300'],
            'badge' => ['nullable', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_photo' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        unset($data['photo'], $data['remove_photo']);
        if ($request->hasFile('photo') || $request->boolean('remove_photo')) {
            Media::delete($milestone?->image);
            $data['image'] = $request->hasFile('photo') ? Media::store($request->file('photo'), 'milestones') : null;
        }
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

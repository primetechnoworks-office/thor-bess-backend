<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->validate(['page' => ['nullable', Rule::in(array_keys(config('thor.faq_pages')))]])['page'] ?? null;

        return view('admin.faqs.index', [
            'faqs' => Faq::when($page, fn ($q) => $q->where('page', $page))->orderBy('page')->ordered()->get(),
            'page' => $page,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.faqs.form', ['faq' => new Faq([
            'page' => $request->query('page', array_key_first(config('thor.faq_pages'))),
            'is_active' => true, 'sort_order' => Faq::nextSortOrder(),
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $faq = Faq::create($this->validated($request));

        return redirect()->route('admin.faqs.index', ['page' => $faq->page])->with('success', 'Question added.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.form', ['faq' => $faq]);
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index', ['page' => $faq->page])->with('success', 'Question saved.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index', ['page' => $faq->page])->with('success', 'Question deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'page' => ['required', Rule::in(array_keys(config('thor.faq_pages')))],
            'question' => ['required', 'string', 'max:160'],
            'answer' => ['required', 'string', 'max:1200'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

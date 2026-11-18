<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->string('q')->trim()->toString();

        $users = User::query()
            ->when($term, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('admin.users.index', ['users' => $users, 'q' => $term]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => User::ROLE_EDITOR, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::create($data + ['email_verified_at' => now()]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        // Never let an admin lock themselves out.
        if ($user->is($request->user())) {
            $data['role'] = User::ROLE_ADMIN;
            $data['is_active'] = true;
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        if (! $user->is_active || isset($data['password'])) {
            $user->tokens()->delete();
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->tokens()->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:120', Rule::unique('users')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9 +()\-]{7,20}$/'],
            'role' => ['required', Rule::in(array_keys(config('thor.roles')))],
            'is_active' => ['nullable', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

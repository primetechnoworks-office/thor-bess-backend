<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/** Sanctum personal-access-token auth for API clients. */
class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['ok' => false, 'error' => trans('auth.failed')], 422);
        }
        if (! $user->is_active) {
            return response()->json(['ok' => false, 'error' => 'Your account has been deactivated.'], 403);
        }

        $user->forceFill(['last_login_at' => now()])->saveQuietly();
        $token = $user->createToken($data['device_name'] ?? 'api', [$user->role])->plainTextToken;

        return response()->json(['ok' => true, 'token' => $token, 'token_type' => 'Bearer', 'user' => $this->present($user)]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'user' => $this->present($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    private function present(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'phone', 'role']) + ['last_login_at' => $user->last_login_at?->toIso8601String()];
    }
}

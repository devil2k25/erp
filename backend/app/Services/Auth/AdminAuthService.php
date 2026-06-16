<?php

namespace App\Services\Auth;

use App\Models\Master\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    public function login(string $email, string $password): array
    {
        $admin = AdminUser::where('email', $email)->where('is_active', true)->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $admin->update(['last_login_at' => now()]);

        // organization_id is null for admin tokens — no tenant context
        $token = $admin->createToken('admin-token')->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token,
        ];
    }

    public function logout(AdminUser $admin): void
    {
        $admin->currentAccessToken()->delete();
    }
}

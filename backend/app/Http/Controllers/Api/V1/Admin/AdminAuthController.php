<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Auth\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    public function __construct(private AdminAuthService $authService) {}

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->authService->login($request->email, $request->password);
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Login successful']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'error' => 'Invalid credentials'], 401);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $request->user('admin')]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user('admin'));
        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    }
}

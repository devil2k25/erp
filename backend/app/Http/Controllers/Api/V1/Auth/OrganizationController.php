<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OrganizationOnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationOnboardingService $onboardingService) {}

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'owner_name'        => 'required|string|max:255',
            'owner_email'       => 'required|email',
            'password'          => 'required|string|min:8|confirmed',
            'owner_phone'       => 'nullable|string|max:20',
            'industry_type'     => 'nullable|string',
            'plan_slug'         => 'nullable|string',
            'timezone'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->onboardingService->register($request->validated());
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Organization registered successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

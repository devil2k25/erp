<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Master\Organization;
use App\Services\Auth\OrganizationOnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminOrganizationController extends Controller
{
    public function __construct(private OrganizationOnboardingService $onboardingService) {}

    public function index(Request $request): JsonResponse
    {
        $orgs = Organization::with('subscription.plan')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('owner_email', 'like', "%{$s}%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json(['success' => true, 'data' => $orgs]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_name' => 'required|string|max:255',
            'owner_name'        => 'required|string|max:255',
            'owner_email'       => 'required|email|unique:App\Models\Master\Organization,owner_email',
            'password'          => 'required|string|min:8',
            'owner_phone'       => 'nullable|string|max:20',
            'industry_type'     => 'nullable|string',
            'plan_slug'         => 'nullable|string|exists:App\Models\Master\Plan,slug',
            'timezone'          => 'nullable|string',
            'status'            => 'nullable|in:active,trial',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $result = $this->onboardingService->createByAdmin($validator->validated());
            return response()->json(['success' => true, 'data' => $result, 'message' => 'Organization created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Organization $organization): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $organization->load('subscription.plan', 'tenantDatabase'),
        ]);
    }

    public function updateStatus(Request $request, Organization $organization): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,suspended,trial,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $organization->update(['status' => $request->status]);

        return response()->json(['success' => true, 'data' => $organization, 'message' => 'Status updated']);
    }
}

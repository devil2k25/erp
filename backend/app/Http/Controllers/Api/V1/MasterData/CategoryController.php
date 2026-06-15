<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::with(['parent', 'children'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->parent_id, fn($q, $p) => $q->where('parent_id', $p))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $categories->items(), 'meta' => [
            'current_page' => $categories->currentPage(), 'last_page' => $categories->lastPage(),
            'per_page' => $categories->perPage(), 'total' => $categories->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:categories,code',
            'parent_id'   => 'nullable|uuid|exists:categories,id',
            'type'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $category = Category::create($validated);
        return response()->json(['success' => true, 'data' => $category->load('parent'), 'message' => 'Category created'], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $category->load('parent', 'children', 'products')]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'code'        => 'nullable|string|max:50|unique:categories,code,' . $category->id,
            'parent_id'   => 'nullable|uuid|exists:categories,id',
            'type'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $category->update($validated);
        return response()->json(['success' => true, 'data' => $category->fresh('parent', 'children'), 'message' => 'Category updated']);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted']);
    }
}

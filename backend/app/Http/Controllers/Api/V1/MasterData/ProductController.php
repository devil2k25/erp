<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'unit'])
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->category_id, fn($q, $c) => $q->where('category_id', $c));

        $products = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $products->items(), 'meta' => [
            'current_page' => $products->currentPage(), 'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(), 'total' => $products->total(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'sku'            => 'required|string|unique:products,sku',
            'unit_id'        => 'required|uuid|exists:units,id',
            'type'           => 'required|in:raw_material,semi_finished,finished_good,consumable,service',
            'category_id'    => 'nullable|uuid|exists:categories,id',
            'barcode'        => 'nullable|string|unique:products,barcode',
            'description'    => 'nullable|string',
            'hsn_code'       => 'nullable|string',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'cost_price'     => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'min_stock_level'=> 'nullable|numeric|min:0',
            'reorder_point'  => 'nullable|numeric|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'is_trackable'   => 'boolean',
        ]);

        $product = Product::create($validated);
        return response()->json(['success' => true, 'data' => $product->load('category', 'unit'), 'message' => 'Product created'], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $product->load('category', 'unit', 'bomItems.component')]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'sku'            => 'sometimes|string|unique:products,sku,' . $product->id,
            'unit_id'        => 'sometimes|uuid|exists:units,id',
            'type'           => 'sometimes|in:raw_material,semi_finished,finished_good,consumable,service',
            'category_id'    => 'nullable|uuid|exists:categories,id',
            'cost_price'     => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'min_stock_level'=> 'nullable|numeric|min:0',
            'reorder_point'  => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
        ]);

        $product->update($validated);
        return response()->json(['success' => true, 'data' => $product->fresh('category', 'unit'), 'message' => 'Product updated']);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }
}

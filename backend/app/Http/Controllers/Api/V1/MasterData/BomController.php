<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BomItem;
use App\Models\Tenant\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function forProduct(Product $product): JsonResponse
    {
        $bom = $product->bomItems()->with('component.unit', 'unit')->orderBy('sequence')->get();
        return response()->json(['success' => true, 'data' => $bom]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_product_id'    => 'required|uuid|exists:products,id',
            'component_product_id' => 'required|uuid|exists:products,id|different:parent_product_id',
            'quantity'             => 'required|numeric|min:0.0001',
            'unit_id'              => 'required|uuid|exists:units,id',
            'scrap_percentage'     => 'nullable|numeric|min:0|max:100',
            'sequence'             => 'nullable|integer|min:0',
            'notes'                => 'nullable|string',
        ]);

        $bom = BomItem::create($validated);
        return response()->json(['success' => true, 'data' => $bom->load('component', 'unit')], 201);
    }

    public function destroy(BomItem $bom): JsonResponse
    {
        $bom->delete();
        return response()->json(['success' => true, 'message' => 'BOM item removed']);
    }

    public function index(Request $request): JsonResponse
    {
        $bom = BomItem::with('parentProduct', 'component', 'unit')->paginate(15);
        return response()->json(['success' => true, 'data' => $bom]);
    }

    public function show(BomItem $bom): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $bom->load('component', 'unit')]);
    }

    public function update(Request $request, BomItem $bom): JsonResponse
    {
        $validated = $request->validate([
            'quantity'         => 'sometimes|numeric|min:0.0001',
            'scrap_percentage' => 'nullable|numeric|min:0|max:100',
            'sequence'         => 'nullable|integer',
            'notes'            => 'nullable|string',
        ]);
        $bom->update($validated);
        return response()->json(['success' => true, 'data' => $bom->fresh()]);
    }
}

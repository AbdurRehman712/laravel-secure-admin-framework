<?php

namespace Modules\ShopModule\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use \Modules\ShopModule\Models\Product;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $product = Product::paginate(15);
        
        return response()->json($product);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Add validation rules here
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            // Add validation rules here
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}

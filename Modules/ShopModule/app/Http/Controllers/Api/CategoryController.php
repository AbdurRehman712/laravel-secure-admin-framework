<?php

namespace Modules\ShopModule\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use \Modules\ShopModule\Models\Category;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $category = Category::paginate(15);
        
        return response()->json($category);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Add validation rules here
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            // Add validation rules here
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}

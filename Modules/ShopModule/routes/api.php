<?php

use Illuminate\Support\Facades\Route;
use Modules\ShopModule\Models\Category;
use Modules\ShopModule\Models\Product;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

Route::middleware(['api'])->prefix('shop-module')->group(function () {
    
    // Categories API
    Route::get('categories', function() {
        return Category::with('products')->get();
    });
    
    Route::post('categories', function(\Illuminate\Http\Request $request) {
        $category = Category::create($request->only(['name', 'description', 'slug', 'status']));
        return response()->json($category, 201);
    });
    
    Route::get('categories/{id}', function($id) {
        return Category::with('products')->findOrFail($id);
    });
    
    Route::put('categories/{id}', function(\Illuminate\Http\Request $request, $id) {
        $category = Category::findOrFail($id);
        $category->update($request->only(['name', 'description', 'slug', 'status']));
        return response()->json($category);
    });
    
    Route::delete('categories/{id}', function($id) {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    });

    // Products API
    Route::get('products', function() {
        return Product::with('category')->get();
    });
    
    Route::post('products', function(\Illuminate\Http\Request $request) {
        $product = Product::create($request->only(['name', 'description', 'price', 'stock_quantity', 'sku', 'status', 'category_id']));
        return response()->json($product->load('category'), 201);
    });
    
    Route::get('products/{id}', function($id) {
        return Product::with('category')->findOrFail($id);
    });
    
    Route::put('products/{id}', function(\Illuminate\Http\Request $request, $id) {
        $product = Product::findOrFail($id);
        $product->update($request->only(['name', 'description', 'price', 'stock_quantity', 'sku', 'status', 'category_id']));
        return response()->json($product->load('category'));
    });
    
    Route::delete('products/{id}', function($id) {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    });
});

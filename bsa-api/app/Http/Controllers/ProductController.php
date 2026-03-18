<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['variants', 'images'])
            ->where('is_active', true);

        if ($category = $request->query('category')) {
            $query->where('category', strtoupper($category));
        }

        if ($tags = $request->query('tags')) {
            foreach (explode(',', $tags) as $tag) {
                $query->whereJsonContains('tags', trim($tag));
            }
        }

        $limit = min((int) ($request->query('limit', 24)), 100);

        $products = $query->orderByDesc('created_at')->limit($limit)->get();

        return response()->json($products);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['variants', 'images', 'pairedWith.images', 'pairedWith.variants'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json($product);
    }
}

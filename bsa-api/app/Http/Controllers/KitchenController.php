<?php

namespace App\Http\Controllers;

use App\Models\KitchenItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = KitchenItem::where('is_active', true)->orderBy('sort_order');

        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->boolean('popular')) {
            $query->where('is_popular', true);
        }

        return response()->json($query->get());
    }

    public function show(string $slug): JsonResponse
    {
        $item = KitchenItem::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json($item);
    }
}

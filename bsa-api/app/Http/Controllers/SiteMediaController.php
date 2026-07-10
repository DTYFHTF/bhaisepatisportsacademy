<?php

namespace App\Http\Controllers;

use App\Models\SiteMedia;
use Illuminate\Http\JsonResponse;

class SiteMediaController extends Controller
{
    /**
     * Flat key => url map, e.g. { "home_hero_poster": "https://..." }.
     * CamelCaseResponse middleware turns keys into homeHeroPoster on the wire.
     */
    public function index(): JsonResponse
    {
        return response()->json(
            SiteMedia::pluck('url', 'key')
        );
    }
}

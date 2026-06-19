<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class DocumentationController extends Controller
{
    public function openApi(): JsonResponse
    {
        return response()->json(
            json_decode(file_get_contents(base_path('docs/openapi.json')), true)
        );
    }
}

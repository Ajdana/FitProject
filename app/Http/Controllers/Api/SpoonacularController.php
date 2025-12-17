<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class SpoonacularController extends Controller
{
    public function testSpoon(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*' => 'string',
        ]);

        $ingredients = implode(',', $request->ingredients);

        $response = Http::get(
            'https://api.spoonacular.com/recipes/findByIngredients',
            [
                'ingredients'   => $ingredients, // из Postman
                'number'        => $request->number ?? 10,
                'ranking'       => 1, // 1 = максимизировать использованные ингредиенты
                'ignorePantry'  => true,
                'apiKey'        => config('services.spoonacular.key'),
            ]
        );

        return response()->json($response->json());
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\ScanHistory;


class GeminiProductController extends Controller
{
    public function submit()
    {
        $data = request()->validate([
            'image' => 'required|image',
        ]);

        $imageFile = $data['image'];

        $base64 = base64_encode(file_get_contents($imageFile->getRealPath()));
        $mime = $imageFile->getMimeType();

        $prompt = "
Determine the products in the image and the quantity of each.

Return exactly one JSON object without markdown or text, format:

{
  \"products\": {\"product\": count},
}

";


        $response = Http::withHeaders([
            "Content-Type" => "application/json",
        ])->post(
            config('services.gemini.url') . '?key=' . config('services.gemini.key'),
            [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt],
                            [
                                "inline_data" => [
                                    "mime_type" => $mime,
                                    "data" => $base64
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $clean = preg_replace('/```json|```/', '', $text);
        $clean = trim($clean);

        $json = json_decode($clean, true);

        // Проверяем, что продукты найдены
        if (
            !isset($json['products']) ||
            !is_array($json['products']) ||
            count($json['products']) === 0
        ) {
            return response()->json([
                'error' => 'There are no products in the picture. Repeat the photo.'
            ], 400);
        }


        // Извлекаем продукты — ключи массива
        $productsArray = array_keys($json['products']);

        // Превращаем в строку для Spoonacular
        $ingredientsString = implode(',', $productsArray);


        // ---------- ЗАПРОС В SPOONACULAR ----------
        $recipesResponse = Http::get("https://api.spoonacular.com/recipes/findByIngredients", [
            "ingredients" => $ingredientsString,
            "number" => 5,
            "apiKey" => env("SPOONACULAR_KEY"),
        ]);

        $recipes = $recipesResponse->json();

        // Для каждого рецепта — получаем ссылку (sourceUrl)
        foreach ($recipes as &$recipe) {
            $info = Http::get("https://api.spoonacular.com/recipes/{$recipe['id']}/information", [
                "apiKey" => env("SPOONACULAR_KEY")
            ])->json();

            $recipe['sourceUrl'] = $info['sourceUrl'] ?? null;
            $recipe['instructions'] = $info['instructions'] ?? null;
        }

        // 1️⃣ Сохраняем фото
        $imagePath = $imageFile->store('scans', 'public');

        // 2️⃣ Формируем result (ВСЁ что вернул контроллер)
        $result = [
            'products' => $json['products'],
            'recipes' => $recipes,
        ];

        // 3️⃣ Создаём scan_history
        $scan = ScanHistory::create([
            'image' => $imagePath,
            'result' => $result,
        ]);

        //  если user не авторизован — сразу ошибка
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }

        // безопасное добавление в pivot
        $scan->users()->syncWithoutDetaching([auth()->id()]);

        return response()->json([
            'scan_id' => $scan->id,
            "products" => $json['products'],
            "recipes" => $recipes
        ]);
    }
}


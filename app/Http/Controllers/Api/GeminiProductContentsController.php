<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProductContentsController extends Controller
{
    public function submit()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = request()->validate([
            'image' => 'required|image',
        ]);

        $imageFile = $data['image'];

        $base64 = base64_encode(file_get_contents($imageFile->getRealPath()));
        $mime = $imageFile->getMimeType();

        $halalCheck = request()->boolean('halal_check');




        $prompt = "You receive an image containing a photograph of the product's ingredients.

Your task is to examine the ingredients carefully.

User parameter halal_check = " . ($halalCheck ? "true" : "false") . ".
The parameter is passed as: halal_check = true or halal_check = false.

Follow the rules strictly:

1) If halal_check = true:
- Restrictions are considered a halal product.
- Check the ingredients for the following:
• Gelatin (if halal beef is not specified, consider it suspicious)
• Carmine / E120
• Mono- and diglycerides E471
• Emulsifiers E472
• L-cysteine ​​E920
• Bone phosphate E542
• Any E-numbers derived from animals
• Alcohol components
- If there is a HALAL logo → Note.
- Subject to Halal Damu requirements → note.
- Return JSON is generated:

{
\"halal_check\": true,
\"halal\": true/false,
\"criteria\": [
\"no suspicious emulsifiers\",
\"no baby food colorings\",
\"external HALAL mark\",
\"ingredients comply with HalalDam\"
],
\"suspicious\": [\"E471\", \"E120\", \"gelatin\"],
\"explanation\": \"Short explanation.\"
}

2) If halal_check = false:
- Simply analyze the ingredients:
• harmful additives
• dangerous E-numbers
• allergenic components
• sugar, flavor enhancers, colorings
• general threat from a brand partner
- Return JSON is generated:

{
\"halal_check\": false,
\"safe\": true/false,
\"questions\": [\"too much sugar\", \"E621\"],
\"explanation\": \"A short explanation of the ingredients.\"
}

The response text must be a single JSON object, without markdowns, without ```, and without unnecessary words.
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


        // Проверка, что состав вообще распознан
        if (isset($json['explanation'])) {
            $exp = strtolower($json['explanation']);

            // ищем "no ingredients" и типичные опечатки
            $badPhrases = [
                'no ingredients',
                'No ingredients',
                'no ingrediants',
                'No ingrediants',
                'no ingredient',
                'No ingredient',
                'ingredients not found',
                'Ingredients not found',
                'no visible ingredients',
                'No visible ingredients',
                'not contain',
                'Not contain'
            ];

            foreach ($badPhrases as $phrase) {
                if (strpos($exp, $phrase) !== false) {
                    return response()->json([
                        'error' => "The product's ingredients were not found in the image. Please re-create the photo."
                    ], 400);
                }
            }
        }


        if (!$json) {
            return response()->json([
                'error' => 'Failed to convert to Json',
                'raw' => $clean
            ], 400);
        }

        return response()->json($json);
    }
}


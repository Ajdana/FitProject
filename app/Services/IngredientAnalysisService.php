<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class IngredientAnalysisService
{
    public function analyze($imageFile, bool $halalCheck): array
    {
        try {
            $base64 = base64_encode(file_get_contents($imageFile->getRealPath()));
            $mime = $imageFile->getMimeType();

            $prompt = $this->buildPrompt($halalCheck);

            $response = Http::withHeaders([
                "Content-Type" => "application/json",
            ])->post(
                config('services.gemini.url') . '?key=' . config('services.gemini.key'),
                [
                    "contents" => [[
                        "parts" => [
                            ["text" => $prompt],
                            [
                                "inline_data" => [
                                    "mime_type" => $mime,
                                    "data" => $base64
                                ]
                            ]
                        ]
                    ]]
                ]
            );

            if (!$response->successful()) {
                Log::error('Gemini API error', ['body' => $response->body()]);
                abort(500, 'AI service unavailable');
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
            $clean = trim(preg_replace('/```json|```/', '', $text));

            $json = json_decode($clean, true);

            if (!$json) {
                abort(400, 'Invalid AI response');
            }

            $this->validateExplanation($json);

            return $json;

        } catch (\Throwable $e) {
            Log::error('IngredientAnalysisService failed', [
                'error' => $e->getMessage()
            ]);

            abort(500, 'Internal server error');
        }
    }

    private function buildPrompt(bool $halalCheck): string
    {
        return "User parameter halal_check = " . ($halalCheck ? "true" : "false") . ". ...";
    }

    private function validateExplanation(array $json): void
    {
        if (!isset($json['explanation'])) {
            return;
        }

        $badPhrases = [
            'no ingredients',
            'ingredients not found',
            'no visible ingredients'
        ];

        $exp = strtolower($json['explanation']);

        foreach ($badPhrases as $phrase) {
            if (str_contains($exp, $phrase)) {
                abort(400, "The product's ingredients were not found in the image.");
            }
        }
    }
}

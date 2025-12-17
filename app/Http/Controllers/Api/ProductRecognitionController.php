<?php

namespace App\Http\Controllers\Api;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Illuminate\Http\Request;
use Google\Cloud\Vision\V1\Feature\Type; // Типы функций (например, OBJECT_LOCALIZATION)
use Google\Cloud\Vision\V1\Feature; // Объект, определяющий функцию
use Google\Cloud\Vision\V1\Image; // Объект изображения
use Google\Cloud\Vision\V1\AnnotateImageRequest; // Объект запроса
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log; // Для логирования ошибок
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
class ProductRecognitionController extends Controller
{
    /**
     * Анализирует загруженное изображение для обнаружения и подсчета продуктов.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeImage(Request $request)
    {
        // 1. Проверка файла
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'Файл изображения не найден.'], 400);
        }

        $imageFile = $request->file('image');
        $imagePath = $imageFile->getRealPath();

        $vision = null;
        try {
            // 2. Инициализация Vision Client (использует GOOGLE_APPLICATION_CREDENTIALS)
            $vision = new ImageAnnotatorClient();

            // Чтение содержимого файла
            $imageContent = file_get_contents($imagePath);

            // 3. Создание объектов Vision API

            // Объект изображения
            $image = (new Image())->setContent($imageContent);

            // Функции, которые мы хотим использовать
            $objectFeature = (new Feature())->setType(Type::OBJECT_LOCALIZATION);
            $labelFeature = (new Feature())->setType(Type::LABEL_DETECTION);
            $webFeature = (new Feature())->setType(Type::WEB_DETECTION);

            // Создание запроса (в версии 2.x нужно явно создавать AnnotateImageRequest)
            $requests = [
                (new AnnotateImageRequest())
                    ->setImage($image)
                    ->setFeatures([$objectFeature, $labelFeature, $webFeature]),            ];

            $batchRequest = (new BatchAnnotateImagesRequest())->setRequests($requests);

            // 4. Выполнение основного метода annotateBatch
            // Отправляем запрос, который обрабатывает обе функции сразу
            $response = $vision->batchAnnotateImages($batchRequest);
            // Получаем результат для нашего единственного изображения (индекс 0)
            $annotations = $response->getResponses()[0];

            // --- 5. Извлечение результатов Object Localization (Подсчет) ---
            $detectedObjects = [];
            $objects = $annotations->getLocalizedObjectAnnotations();

            foreach ($objects as $object) {
                // Добавляем имя объекта и его точность
                $detectedObjects[] = [
                    'name' => $object->getName(),
                    'score' => round($object->getScore(), 4),
                ];
            }


            // Агрегируем результаты (например, 3 'Egg')
            $finalCounts = $this->aggregateObjects($detectedObjects);


            // --- 6. Извлечение результатов Label Detection (Общие категории) ---
            $detectedLabels = [];
            $labels = $annotations->getLabelAnnotations();

            foreach ($labels as $label) {
                // Фильтруем метки с высокой уверенностью
                if ($label->getScore() > 0.80) {
                    $detectedLabels[] = [
                        'description' => $label->getDescription(),
                        'score' => round($label->getScore(), 4),
                    ];
                }
            }

            $webDetection = $annotations->getWebDetection();
            $webResults = [];

            if ($webDetection) {
                // 7а. Поиск страниц с похожими изображениями
                foreach ($webDetection->getPagesWithMatchingImages() as $page) {
                    $webResults['matched_pages'][] = $page->getUrl();
                }

                // 7б. Поиск визуально похожих изображений
                foreach ($webDetection->getVisuallySimilarImages() as $similarImage) {
                    // Здесь часто можно найти конкретные названия продуктов
                    $webResults['similar_images'][] = [
                        'url' => $similarImage->getUrl(),
                        'score' => $similarImage->getScore(),
                    ];
                }
            }

            // 7. Возвращаем результат
            return response()->json([
                'success' => true,
                'status' => 'Анализ завершен',
                'object_counts_by_localization' => $finalCounts,
                'labels_by_detection' => $detectedLabels,
                'web_detection_results' => $webResults,
            ]);

        } catch (\Exception $e) {
            // Логируем ошибку, если что-то пошло не так
            Log::error('Vision API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка API: ' . $e->getMessage()], 500);
        } finally {
            // 8. Обязательно закрываем клиент Vision API
            if ($vision) {
                $vision->close();
            }
        }
    }

    /**
     * Агрегирует и подсчитывает обнаруженные объекты по их именам.
     *
     * @param array $objects
     * @return array
     */
    private function aggregateObjects(array $objects): array
    {
        $counts = [];
        foreach ($objects as $object) {
            // Некоторые объекты могут иметь одинаковые имена (например, 3 яйца будут иметь имя 'Egg')
            $name = $object['name'];
            if (!isset($counts[$name])) {
                $counts[$name] = 0;
            }
            $counts[$name]++;
        }

        // Преобразование в удобный массив
        $result = [];
        foreach ($counts as $name => $count) {
            $result[] = ['name' => $name, 'count' => $count];
        }

        return $result;
    }
}

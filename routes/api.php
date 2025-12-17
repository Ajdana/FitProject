<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductRecognitionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\ScanHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
//Google Vision api analyze
Route::post('/analyze-products', [ProductRecognitionController::class, 'analyzeImage']);
Route::get('/test-spoonacular', [\App\Http\Controllers\Api\SpoonacularController::class, 'testSpoon']);
Route::post('/gemini-analyze' , [\App\Http\Controllers\Api\GeminiProductController::class, 'submit']);
Route::post('/gemini-contents' , [\App\Http\Controllers\Api\GeminiProductContentsController::class, 'submit']);

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});


Route::middleware(['auth:api'])->group(function () {
    //анализ продуктов по фото и вывод возможных рецептов
    Route::post('/gemini-analyze' , [\App\Http\Controllers\Api\GeminiProductController::class, 'submit']);
    //анализ состава продуктов
    Route::post('/gemini-contents' , [\App\Http\Controllers\Api\GeminiProductContentsController::class, 'submit']);
    //список рецептов по продуктам
    Route::get('/test-spoonacular', [\App\Http\Controllers\Api\SpoonacularController::class, 'testSpoon']);

    // 🔹 Создать профиль (user, admin)
    Route::post('/profiles', [ProfileController::class, 'store'])
        ->middleware('permission:profile.create.own');

    // 🔹 Текущий профиль (для user по токену)
    Route::get('/profiles/me', [ProfileController::class, 'me'])
        ->middleware('auth:api');

    // 🔹 Посмотреть любой профиль (для admin)
    Route::get('/profiles/{profile}', [ProfileController::class, 'show'])
        ->middleware('auth:api', 'permission:profile.read.all');


    // Обновить свой профиль
    Route::put('/profiles/me', [ProfileController::class, 'updateMe'])
        ->middleware('auth:api');

    // Удалить свой профиль
    Route::delete('/profiles/me', [ProfileController::class, 'destroyMe'])
        ->middleware('auth:api');


    // Обновить любой профиль (admin)
    Route::put('/profiles/{profile}', [ProfileController::class, 'update'])
        ->middleware('auth:api', 'permission:profile.update.all');

    // Удалить любой профиль (admin)
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy'])
        ->middleware('auth:api', 'permission:profile.delete.all');


    // 🔹 Список рецептов (index)
    // user → свои рецепты
    // admin → все рецепты
    Route::get('/recipes', [RecipeController::class, 'index'])
        ->middleware('permission:recipe.read.own|recipe.read.all');

    // 🔹 Создать рецепт
    Route::post('/recipes', [RecipeController::class, 'store'])
        ->middleware('permission:recipe.create');

    // 🔹 Показать рецепт
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show'])
        ->middleware('permission:recipe.read.own|recipe.read.all');

    // 🔹 Обновить рецепт
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update'])
        ->middleware('permission:recipe.update.own|recipe.update.all');

    // 🔹 Удалить рецепт
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy'])
        ->middleware('permission:recipe.delete.own|recipe.delete.all');

    // 🔹 Список сканов
    // user → свои
    // admin → все
    Route::get('/scan-histories', [ScanHistoryController::class, 'index'])
        ->middleware('permission:scan.read.own|scan.read.all');

    // 🔹 Показать один скан
    Route::get('/scan-histories/{scan}', [ScanHistoryController::class, 'show'])
        ->middleware('permission:scan.read.own|scan.read.all');

    // 🔹 Удалить скан (admin только)
    Route::delete('/scan-histories/{scan}', [ScanHistoryController::class, 'destroy'])
        ->middleware('permission:scan.delete.all');
});


<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Repositories\RecipeRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    use AuthorizesRequests;

    protected $repo;

    public function __construct(RecipeRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(RecipeRequest $request)
    {
        $userId = auth()->id();
        $user = auth()->user();
        $nameFilter = $request->query('name');

        Log::info('Recipe index request', [
            'user_id' => $userId,
            'name_filter' => $nameFilter ?? 'none'
        ]);

        try {
            if ($user->can('recipe.read.all')) {
                $recipes = $this->repo->getAllWithPagination(10, $nameFilter);
                Log::info('Recipe index returned with pagination', ['count' => $recipes->count()]);
            } else {
                $recipes = $this->repo->getAllForUser($userId, $nameFilter);
                Log::info('Recipe index returned all recipes for user', ['count' => $recipes->count()]);
            }

            return RecipeResource::collection($recipes);

        } catch (\Throwable $e) {
            Log::error('Recipe index failed', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch recipes'], 500);
        }
    }

    public function store(RecipeRequest $request)
    {
        Log::info('Recipe store request', [
            'user_id' => auth()->id(),
            'payload' => $request->all()
        ]);

        try {
            $this->authorize('create', Recipe::class);

            $data = $request->validated();

            $recipe = $this->repo->create([
                ...$data,
                'user_id' => auth()->id(),
            ]);

            Log::info('Recipe created', ['recipe_id' => $recipe->id]);

            return new RecipeResource($recipe);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error('Recipe store unauthorized', ['user_id' => auth()->id()]);
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Throwable $e) {
            Log::error('Recipe store failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to create recipe'], 500);
        }
    }

    public function show(Recipe $recipe)
    {
        Log::info('Recipe show request', [
            'recipe_id' => $recipe->id,
            'user_id' => auth()->id()
        ]);

        try {
            $this->authorize('view', $recipe);
            return new RecipeResource($recipe);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error('Recipe show unauthorized', ['recipe_id' => $recipe->id]);
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Throwable $e) {
            Log::error('Recipe show failed', [
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch recipe'], 500);
        }
    }

    public function update(RecipeRequest $request, Recipe $recipe)
    {
        Log::info('Recipe update request', [
            'recipe_id' => $recipe->id,
            'user_id' => auth()->id(),
            'payload' => $request->all()
        ]);

        try {
            $this->authorize('update', $recipe);

            $data = $request->only([
                'name', 'image', 'products', 'instructions', 'calories'
            ]);

            $recipe = $this->repo->update($recipe, $data);

            Log::info('Recipe updated', ['recipe_id' => $recipe->id]);

            return new RecipeResource($recipe);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error('Recipe update unauthorized', ['recipe_id' => $recipe->id]);
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Throwable $e) {
            Log::error('Recipe update failed', [
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to update recipe'], 500);
        }
    }

    public function destroy(Recipe $recipe)
    {
        Log::info('Recipe delete request', [
            'recipe_id' => $recipe->id,
            'user_id' => auth()->id()
        ]);

        try {
            $this->authorize('delete', $recipe);

            $this->repo->delete($recipe);

            Log::info('Recipe deleted', ['recipe_id' => $recipe->id]);

            return response()->json(['message' => 'Recipe deleted'], 200);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error('Recipe delete unauthorized', ['recipe_id' => $recipe->id]);
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Throwable $e) {
            Log::error('Recipe delete failed', [
                'recipe_id' => $recipe->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to delete recipe'], 500);
        }
    }
}

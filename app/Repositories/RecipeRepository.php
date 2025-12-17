<?php

namespace App\Repositories;

use App\Models\Recipe;

class RecipeRepository
{
    public function getAllForUser($userId, $nameFilter = null)
    {
        $query = Recipe::where('user_id', $userId)->with('user');
        if ($nameFilter) {
            $query->where('name', 'like', "%{$nameFilter}%");
        }
        return $query->get();
    }

    public function getAllWithPagination($perPage = 10, $nameFilter = null)
    {
        $query = Recipe::with('user');
        if ($nameFilter) {
            $query->where('name', 'like', "%{$nameFilter}%");
        }
        return $query->paginate($perPage);
    }

    public function findById($id)
    {
        return Recipe::findOrFail($id);
    }

    public function create(array $data)
    {
        return Recipe::create($data);
    }

    public function update(Recipe $recipe, array $data)
    {
        $recipe->update($data);
        return $recipe;
    }

    public function delete(Recipe $recipe)
    {
        $recipe->delete();
        return true;
    }
}

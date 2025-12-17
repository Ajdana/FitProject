<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    public function view(User $user, Recipe $recipe)
    {
        return $user->id === $recipe->user_id || $user->can('recipe.read.all');
    }

    public function create(User $user)
    {
        return $user->can('recipe.create');
    }

    public function update(User $user, Recipe $recipe)
    {
        return $user->id === $recipe->user_id || $user->can('recipe.update.all');
    }

    public function delete(User $user, Recipe $recipe)
    {
        return $user->id === $recipe->user_id || $user->can('recipe.delete.all');
    }
}

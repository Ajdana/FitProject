<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    public function view(User $user, Profile $profile)
    {
        return $user->id === $profile->user_id || $user->can('profile.read.all');
    }

    public function create(User $user)
    {
        return $user->can('profile.create.own');
    }

    public function update(User $user, Profile $profile)
    {
        return $user->id === $profile->user_id || $user->can('profile.update.all');
    }

    public function delete(User $user, Profile $profile)
    {
        return $user->id === $profile->user_id || $user->can('profile.delete.all');
    }
}


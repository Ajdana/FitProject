<?php

namespace App\Repositories;

use App\Models\Profile;
use App\Models\User;

class ProfileRepository
{
    public function create(array $data): Profile
    {
        return Profile::create($data);
    }

    public function update(Profile $profile, array $data): Profile
    {
        $profile->update($data);
        return $profile;
    }

    public function delete(Profile $profile): void
    {
        $profile->delete();
    }

    public function getByUserId(int $userId): ?Profile
    {
        return Profile::where('user_id', $userId)->first();
    }

}

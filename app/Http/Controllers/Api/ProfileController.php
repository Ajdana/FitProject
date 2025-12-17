<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProfileRequest;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    protected ProfileRepository $repo;

    public function __construct(ProfileRepository $repo)
    {
        $this->repo = $repo;
    }

    public function show(Profile $profile)
    {
        Log::info('Profile show request', [
            'profile_id' => $profile->id,
            'user_id' => auth()->id()
        ]);

        try {
            $this->authorize('view', $profile);
            return new ProfileResource($profile);
        } catch (\Throwable $e) {
            Log::error('Profile show failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function store(ProfileRequest $request)
    {
        Log::info('Profile store request', [
            'user_id' => auth()->id(),
            'payload' => $request->all()
        ]);

        try {
            $this->authorize('create', Profile::class);

            $data = $request->validated();

            $profile = $this->repo->create([
                ...$data,
                'user_id' => auth()->id(),
            ]);

            Log::info('Profile created', [
                'profile_id' => $profile->id
            ]);

            return new ProfileResource($profile);

        } catch (\Throwable $e) {
            Log::error('Profile store error', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            throw $e;
        }
    }

    public function update(ProfileRequest $request, Profile $profile)
    {
        Log::info('Profile update request', [
            'profile_id' => $profile->id,
            'payload' => $request->all()
        ]);

        try {
            $this->authorize('update', $profile);

            $profile = $this->repo->update($profile, $request->only([
                'full_name','avatar_path', 'age', 'gender', 'height', 'weight', 'goal'
            ]));

            return new ProfileResource($profile);

        } catch (\Throwable $e) {
            Log::error('Profile update failed', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function destroy(Profile $profile)
    {
        Log::info('Profile delete request', [
            'profile_id' => $profile->id,
            'user_id' => auth()->id()
        ]);

        try {
            $this->authorize('delete', $profile);
            $this->repo->delete($profile);

            return response()->json(['message' => 'Profile deleted'], 200);

        } catch (\Throwable $e) {
            Log::error('Profile delete failed', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function me()
    {
        Log::info('Profile me request', [
            'user_id' => auth()->id()
        ]);

        $profile = $this->repo->getByUserId(auth()->id());

        if (!$profile) {
            Log::warning('Profile me not found', [
                'user_id' => auth()->id()
            ]);
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return new ProfileResource($profile);
    }

    public function updateMe(ProfileRequest $request)
    {
        Log::info('Profile updateMe request', [
            'user_id' => auth()->id(),
            'payload' => $request->all()
        ]);

        $profile = $this->repo->getByUserId(auth()->id());

        if (!$profile) {
            Log::warning('Profile updateMe not found', [
                'user_id' => auth()->id()
            ]);
            return response()->json(['message' => 'Profile not found'], 404);
        }

        try {
            $data = $request->validated();

            $profile = $this->repo->update($profile, $data);

            return new ProfileResource($profile);

        } catch (\Throwable $e) {
            Log::error('Profile updateMe failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function destroyMe()
    {
        Log::info('Profile destroyMe request', [
            'user_id' => auth()->id()
        ]);

        $profile = $this->repo->getByUserId(auth()->id());

        if (!$profile) {
            Log::warning('Profile destroyMe not found', [
                'user_id' => auth()->id()
            ]);
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $this->repo->delete($profile);

        Log::info('Profile deleted by owner', [
            'profile_id' => $profile->id
        ]);

        return response()->json(['message' => 'Profile deleted'], 200);
    }
}

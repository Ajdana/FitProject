<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Profile;
use App\Models\Recipe;
use App\Models\ScanHistory;
use App\Policies\ProfilePolicy;
use App\Policies\RecipePolicy;
use App\Policies\ScanHistoryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Profile::class => ProfilePolicy::class,
        Recipe::class => RecipePolicy::class,
        ScanHistory::class => ScanHistoryPolicy::class,
    ];


    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}

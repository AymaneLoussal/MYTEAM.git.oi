<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\GameMatch;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bind {match} route parameter to GameMatch model
        Route::model('match', GameMatch::class);
    }
}

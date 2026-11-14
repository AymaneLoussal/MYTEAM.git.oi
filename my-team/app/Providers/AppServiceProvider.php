<?php

namespace App\Providers;

use App\Models\GameMatch;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by('login:'.strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(30)->by('login-ip:'.$request->ip()),
        ]);
        RateLimiter::for('registration', fn (Request $request) => Limit::perMinute(5)->by('registration:'.$request->ip()));
        RateLimiter::for('invitations', fn (Request $request) => Limit::perMinute(10)->by('invitations:'.session('user_id')));
        RateLimiter::for('chat', fn (Request $request) => Limit::perMinute(60)->by('chat:'.session('user_id')));
    }
}

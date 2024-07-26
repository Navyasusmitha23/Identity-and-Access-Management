<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(now()->adddays(1)); // Token expires in 1 minute
        Passport::refreshTokensExpireIn(now()->adddays(2)); // Refresh token expires in 2 minutes
        Passport::personalAccessTokensExpireIn(now()->adddays(4));
    }
}

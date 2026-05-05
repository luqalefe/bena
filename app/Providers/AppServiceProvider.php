<?php

namespace App\Providers;

use App\Support\BuddySprite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            BuddySprite::class,
            fn () => new BuddySprite(public_path('images/buddies'), '/images/buddies'),
        );
    }

    public function boot(): void
    {
        //
    }
}
